<?php


namespace Modules\Inboxer\Entities;

use App\Models\SystemJob;
use App\Models\User;

use Modules\Marketing\Library\Log as MailLog;
use DB;
use Modules\Marketing\Exceptions\CampaignPausedException;
use Modules\Marketing\Exceptions\CampaignErrorException;

class Campaign extends CoreCampaign
{
    protected $table = 'mkt_campaigns';


    /**
     * Start the campaign.
     */
    public function start($trigger = null)
    {
        //$file_path = storage_path('/');
        //
        try {
            // make sure the same campaign is not started twice
            if (!$this->refreshStatus()->isReady()) {
                //MailLog::info("Campaign ID: {$this->id} is not ready or already started");
                $arr = [ 'funct' => 'Campaign Error ', 'error_message' => 'Campaign Not Ready'];

                $user = User::first();
                $user->notify(new \App\Notifications\SlackNotification($arr));

                return;
            }

            // clean up the tracker to prevent the log file from growing very big
            $this->customer->cleanupQuotaTracker();

            // lock the campaign, marked as sending
            $this->sending();
            //MailLog::info('Starting campaign `'.$this->name.'`');

            // Reset max_execution_time so that command can run for a long time without being terminated
            self::resetMaxExecutionTime();

            // Only run multi-process if pcntl is enabled
            //$processes = (int) $this->customer->getOption('max_process');
            $processes = (int) 1;
            if (extension_loaded('pcntl') && $processes > 1) {
                $arr = [ 'funct' => 'Extension Is Loaded ', 'error_message' => $processes];

                $user = User::first();
                $user->notify(new \App\Notifications\SlackNotification($arr));
                //MailLog::info('Run in multi-process mode');
                $this->runMultiProcesses();
            } else {
                //MailLog::info('Run in single-process mode');
                $this->run();
            }
            //MailLog::info('Finish campaign `'.$this->name.'`');
            $arr = [ 'funct' => 'Emailer Message ', 'error_message' => 'Finish campaign '.$this->name];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));


            // runMultiProcesses() and run() are responsible for setting DONE status
        } catch (\Exception $ex) {
            // Set ERROR status
            $this->error($ex->getMessage());

            $arr = [ 'funct' => 'Starting campaign failed ', 'error_message' => $ex->getMessage()];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));

            //MailLog::error('Starting campaign failed. '.$ex->getMessage());
        }
    }

    /**
     * Start the campaign, using PHP fork() to launch multiple processes.
     *
     * @return mixed
     */
    public function runMultiProcesses()
    {
        // processes to fork
        $count = (int) $this->customer->getOption('max_process');

        //MailLog::info("Forking {$count} process(es)");
        $parentPid = getmypid();
        $children = [];
        for ($i = 0; $i < $count; $i += 1) {
            $pid = pcntl_fork();

            // for child process only
            if (!$pid) {
                // Reconnect to the DB to prevent connection closed issue when using fork
                DB::reconnect();

                // Re-initialize logging to capture the child process' PID
                try{
                    MailLog::fork();
                }
                catch (\Exception $e){
                    $arr = [ 'funct' => 'MailLog Fork Err ', 'error_message' => $e->getMessage()];

                    $user = User::first();
                    $user->notify(new \App\Notifications\SlackNotification($arr));
                }


                //MailLog::info(sprintf('Start child process %s of %s (forked from %s)', $i + 1, $count, $parentPid));
                sleep(self::WORKER_DELAY);
                $partition = [$i, $count];
                $this->run($partition);

                exit($i + 1);
                // end child process
            } else {
                $children[] = $pid;
            }
        }

        // wait for child processes to finish
        foreach ($children as $child) {
            $pid = pcntl_wait($status);
            if (pcntl_wifexited($status)) {
                $code = pcntl_wexitstatus($status);
                //MailLog::info("Child process $pid finished, status code: $code");
            } else {
                //MailLog::warning("Child process $pid did not normally exit");
                $this->error("Child process $pid did not normally exit");
            }
        }

        // after all child processes are done
        $this->refreshStatus();

        // If all child processes finish sucessfully, just mark campaign as done
        // Otherwise, mark campaign with error status (by child process)
        //
        // There are conventions here:
        //   + Child process does not update status from SENDING to DONE, it is left for the parent process
        //   + Child process only updates status from SENDING to ERROR
        //   + Parent process only updates status to DONE when current status is SENDING
        //     indicating that all child processes finish sucessfully
        //   + In case one child process update the status from SENDING to ERROR, it is left as the final status
        //     the latest error is kept
        if ($this->status == self::STATUS_SENDING) {
            $this->done();
        } else {
            // leave the latest error reported by child process
        }
    }

    /**
     * Send Campaign
     * Iterate through all subscribers and send email.
     */
    public function run($partition = null)
    {
        // check if the method is trigger by a child process (triggered by startMultiProcess method)
        $asChildProcess = !($partition == null);

        // try/catch to make sure child process does not stop without reporting any error
        try {
            $i = 0;
            $this->getPendingSubscribers($partition, function ($subscribers, $page, $total) use (&$i) {
                //MailLog::info("Fetching page $page (count: {$subscribers->count()})");
                $cnnt = count($subscribers);
                foreach ($subscribers as $subscriber) {
                    // @todo: customerneedcheck
                    while ($this->customer->overQuota()) {
                        // throw new \Exception('Customer has reached sending limit');
                        //MailLog::warning(sprintf("Campaign `%s` (%s) halted, user exceeds sending limit", $this->name, $this->uid));
                        $arr = [ 'funct' => 'Customer has reached sending limit ', 'error_message' => $this->name .' | '. $this->uid];
                        $user = User::first();
                        $user->notify(new \App\Notifications\SlackNotification($arr));
                        sleep(60);
                    }

                    $i += 1;
                    try{
                        MailLog::info("Sending to subscriber `{$subscriber->email}` ({$i}/{$total})");
                    }
                    catch (\Exception $e ){
                        $arr = [ 'funct' => 'Write Mail Log', 'error_message' => $e->getMessage()];

                        $user = User::first();
                        $user->notify(new \App\Notifications\SlackNotification($arr));
                    }


                    // Pick up an available sending server
                    // Throw exception in case no server available
                    $server = $this->pickSendingServer($this);

                    list($message, $msgId) = $this->prepareEmail($subscriber, $server);

                    // randomly check for campaign PAUSED status after 10 emails
                    // @todo performance concern here
                    // @todo need an exclusive lock, what if the campaign is PAUSED, then RESTARTED
                    //       after the status check and before actually sending?
                    if ($this->refreshStatus()->isPaused()) {
                        $this->updateCache();
                        throw new CampaignPausedException();
                    }

                    $sent = $server->send($message);


                    // additional log
                    MailLog::info("Sent to subscriber `{$subscriber->email}`");

                    //$arr = [ 'funct' => 'Sent to subscriber ', 'error_message' => ' '.$subscriber->email.' ('.$i.'/'.$cnnt.')'];
                    //$user = User::first();
                    //$user->notify(new \App\Notifications\SlackNotification($arr));

                    $this->trackMessage($sent, $subscriber, $server, $msgId);
                }
            });

            //MailLog::info("{$i} emails sent for the process");
            $arr = [ 'funct' => 'Campaign Complete ', 'error_message' => $i. ' Emails Sent'];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));

            // only mark campaign as done when running as its own process
            // as a child process, just finish and leave the parent process to update campaign status
            if (!$asChildProcess) {
                $this->done();
            }
        } catch (CampaignPausedException $e) {
            // just finish
            $arr = [ 'funct' => 'CampaignPausedException ', 'error_message' => $e->getMessage()];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));
            //MailLog::warning('Campaign paused');
        } catch (CampaignErrorException $e) {
            // just finish
            //MailLog::warning('Campaign terminated: '.$e->getMessage());
            $arr = [ 'funct' => 'CampaignErrorException ', 'error_message' => $e->getMessage()];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));
        } catch (\Exception $e) {
           // MailLog::error($e->getMessage());
            $arr = [ 'funct' => 'Mailer Send Error', 'error_message' => $e->getMessage()];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));
            $this->error($e->getMessage());
        } finally {
            // reset server pools: just in case DeliveryServerAmazonSesWebApi
            // --> setup SNS requires using from_email of the corresponding campaign
            // but SNS is only made once when the server is initiated
            // UPDATE: this is no longer the case as one process now only deals with one campaign
            //         however, the method is still needed to save sending servers''
            MailList::resetServerPools();
            $arr = [ 'funct' => 'Campaign Complete ', 'error_message' => $i. ' Emails Sent'];

            $user = User::first();
            $user->notify(new \App\Notifications\SlackNotification($arr));
            // update the campaign cache information
            $this->updateCache();
        }
    }

    /**
     * Transform Tags
     * Transform tags to actual values before sending.
     */
    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $tags = array(
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from_email,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $this->defaultMailList->contact->company,
            'CONTACT_COUNTRY' => $this->defaultMailList->contact->country->name,
            'CONTACT_STATE' => $this->defaultMailList->contact->state,
            'CONTACT_CITY' => $this->defaultMailList->contact->city,
            'CONTACT_ADDRESS_1' => $this->defaultMailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $this->defaultMailList->contact->address_2,
            'CONTACT_PHONE' => $this->defaultMailList->contact->phone,
            'CONTACT_URL' => $this->defaultMailList->contact->url,
            'CONTACT_EMAIL' => $this->defaultMailList->contact->email,
            'LIST_NAME' => $this->defaultMailList->name,
            'LIST_SUBJECT' => $this->defaultMailList->default_subject,
            'LIST_FROM_NAME' => $this->defaultMailList->from_name,
            'LIST_FROM_EMAIL' => $this->defaultMailList->from_email,
        );

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($this->defaultMailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $subscriber->getValueByField($field);
            }
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        return $message;
    }

    /**
     * Subscribers.
     *
     * @return collect
     */
    public function subscribers($params = [], $extra = ['email_verifications'])
    {
        // get subscribers from auto campaign or normal campaign
        if ($this->isAuto()) {
            // Get subscriber from auto event
            $query = $this->autoEvent()->subscribers();
        } else {
            if ($this->listsSegments->isEmpty()) {
                // this is a trick for returning an empty builder
                return Subscriber::limit(0);
            }
            $query = Subscriber::select('mkt_subscribers.*');
            // Tell MySQL to use the right index
            // $query->from(\DB::raw(\DB::getTablePrefix() . 'subscribers FORCE INDEX (subscribers_mail_list_id_email_index)'));
            // Email verification join
            if (in_array('email_verifications', $extra)) {
                $query = $query->leftJoin('mkt_email_verifications', 'mkt_email_verifications.subscriber_id', '=', 'mkt_subscribers.id');
            }
            // Get subscriber from mailist and segment
            $conditions = [];
            foreach ($this->listsSegments as $lists_segment) {
                if (!empty($lists_segment->segment_id)) {
                    // Segment
                    $conds = $lists_segment->segment->getSubscribersConditions();
                    if (!empty($conds['joins'])) {
                        foreach ($conds['joins'] as $joining) {
                            $query = $query->leftJoin($joining['table'], function ($join) use ($joining) {
                                $join->on($joining['ons'][0][0], '=', $joining['ons'][0][1]);
                                if (isset($joining['ons'][1])) {
                                    $join->on($joining['ons'][1][0], '=', $joining['ons'][1][1]);
                                }
                            });
                        }
                    }
                    // WHERE...
                    if (!empty($conds['conditions'])) {
                        $conditions[] = $conds['conditions'];
                    }
                } else {
                    // Entire list
                    $conditions[] = '('.table('mkt_subscribers.mail_list_id').' = '.$lists_segment->mail_list_id.')';
                }
            }

            if (!empty($conditions)) {
                $query = $query->whereRaw('('.implode(' OR ', $conditions).')');
            }
        }

        // Filters
        $filters = isset($params['filters']) ? $params['filters'] : null;
        if ((isset($filters) && (isset($filters['open']) || isset($filters['click']) || isset($filters['tracking_status'])))
        ) {
            $query = $query->leftJoin('mkt_tracking_logs', 'mkt_tracking_logs.subscriber_id', '=', 'mkt_subscribers.id');
            $query = $query->where('mkt_tracking_logs.id', '!=', 'NULL');
            $query = $query->where('mkt_tracking_logs.campaign_id', '=', $this->id);
        }
        if (isset($filters)) {
            if (isset($filters['open'])) {
                $equal = ($filters['open'] == 'opened') ? '!=' : '=';
                $query = $query->leftJoin('mkt_open_logs', 'mkt_tracking_logs.message_id', '=', 'mkt_open_logs.message_id')
                    ->where('mkt_open_logs.id', $equal, null);
            }
            if (isset($filters['click'])) {
                $equal = ($filters['click'] == 'clicked') ? '!=' : '=';
                $query = $query->leftJoin('mkt_click_logs', 'mkt_tracking_logs.message_id', '=', 'mkt_click_logs.message_id')
                    ->where('mkt_click_logs.id', $equal, null);
            }
            if (isset($filters['tracking_status'])) {
                $val = ($filters['tracking_status'] == 'not_sent') ? null : $filters['tracking_status'];
                $query = $query->where('mkt_tracking_logs.status', '=', $val);
            }
        }
        // keyword
        if (isset($params['keyword']) && !empty(trim($params['keyword']))) {
            foreach (explode(' ', trim($params['keyword'])) as $keyword) {
                $query = $query->leftJoin('mkt_subscriber_fields', 'mkt_subscribers.id', '=', 'mkt_subscriber_fields.subscriber_id');
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('mkt_subscribers.email', 'like', '%'.$keyword.'%')
                        ->orWhere('mkt_subscriber_fields.value', 'like', '%'.$keyword.'%');
                });
            }
        }

        return $query;
    }

    /**
     * Get Pending Subscribers
     * Select only subscribers that are ready for sending. Those whose status is `blacklisted`, `pending` or `unconfirmed` are not included.
     */
    public function getPendingSubscribers($partition = null, $callback)
    {
        /* *
         *
         * Using LEFT JOIN is much slower than NOT IN
         */
        /*$this->id = 3;
        $subscribers = $this->subscribers()
                ->leftJoin(DB::raw('(SELECT * FROM '.DB::getTablePrefix().'mkt_tracking_logs WHERE campaign_id = '.$this->id.') log'), 'mkt_subscribers.id', '=', 'subscriber_id')
                ->whereRaw('log.id IS NULL')
                ->whereRaw(DB::getTablePrefix()."mkt_subscribers.status = '".Subscriber::STATUS_SUBSCRIBED."'")
                ->select('mkt_subscribers.*')
                ->get();
        //dd( $subscribers );*/

        $builder = $this->subscribers([], ['mkt_email_verifications'])
                ->whereRaw(sprintf(table('mkt_subscribers').'.email NOT IN (SELECT email FROM %s t JOIN %s s ON t.subscriber_id = s.id WHERE t.campaign_id = %s)', table('mkt_tracking_logs'), table('mkt_subscribers'), $this->id))
                ->whereRaw(DB::getTablePrefix()."mkt_subscribers.status = '".Subscriber::STATUS_SUBSCRIBED."'")
                ->groupBy(DB::raw(table('mkt_subscribers.email')));

        //dd( $builder );

        if (!is_null($partition)) {
            // retrieve the subscribers list for this partition only
            // partitioning is based on email to prevent one email from being taken by more than one process
            // The idea is to generate a unique integer for an email address, then partition base on the number
            list($partitionId, $count) = $partition;
            $builder = $builder->whereRaw(sprintf('CONV(SUBSTR(MD5(%s),1, 4), 16, 10) MOD %s = %s', table('mkt_subscribers.email'), $count, $partitionId));
        }

        // the array_unique_by functions out-perform the Collection::unique of Laravel!!!
        // The following code snipet is no longer needed as the list is already unique by email
        //     $unique = array_unique_by($subscribers, function ($r) {
        //         return $r['email'];
        //     });

        $page = 1;
        $limit = 2000;

        while ($total = $builder->count()) {
            // @note eager loading will result in a very long query
            // which could exceed the [max_allowed_packet] setting of MySQL
            // so the value of $limit must be considered carefully
            $subscribers = $builder->limit($limit)->with(['mailList', 'subscriberFields'])->get();
            $callback($subscribers, $page, $total);
            $page += 1;
        }

        /*
         * @todo the paginate helper does not work here since data table will be changed during the retrieving process
        paginate($subscribers, function ($subset, $page) use ($callback, $total) {
            $callback($subset, $page, $total);
        }, ['count' => $total]);
        */
    }

    /**
     * Queue campaign for sending.
     */
    public function queue($trigger = null, $subscribers = null, $delay = 0)
    {

        $this->ready();

        //dd('After Ready...');
        $job = (new \Modules\Marketing\Jobs\RunCampaignJob($this))->delay($this->getDelayInSeconds());
        //dd( $job );
        dispatch($job);
    }

    /**
     * Queue campaign for sending.
     */
    public function clearAllJobs()
    {
        // cleanup jobs and system_jobs
        // @todo data should be a JSON field instead
        $systemJobs = SystemJob::where('name', '\Modules\Marketing\Jobs\RunCampaignJob')->where('data', $this->id)->get();
        foreach ($systemJobs as $systemJob) {
            // @todo what if jobs were already started? check `reserved` field?
            $systemJob->clear();
        }
    }

    /**
     * Pick up a delivery server for the campaign.
     *
     * @return mixed
     */
    public function pickSendingServer()
    {
        return $this->defaultMailList->pickSendingServer();
    }
}
