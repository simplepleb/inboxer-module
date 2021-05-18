<?php


namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Inboxer\Library\Log as MailLog;
use Modules\Inboxer\Entities\Blacklist;

class Subscriber extends Model
{
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_BLACKLISTED = 'blacklisted';
    const STATUS_SPAM_REPORTED = 'spam-reported';
    const STATUS_UNCONFIRMED = 'unconfirmed';

    const SUBSCRIPTION_TYPE_DOUBLE_OPTIN = 'double';
    const SUBSCRIPTION_TYPE_SINGLE_OPTIN = 'single';
    const SUBSCRIPTION_TYPE_IMPORTED = 'imported';

    protected $dates = ['unsubscribed_at'];

    public static $rules = [
        'email' => ['required', 'email'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mail_list_id', 'email',
        'image',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $fields_rules = array(

    );

    protected $table = 'mkt_subscribers';

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function mailList()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\MailList');
    }

    public function emailVerifications()
    {
        return $this->hasMany('Modules\Inboxer\Entities\EmailVerification');
    }

    public function emailVerification()
    {
        return $this->hasOne('Modules\Inboxer\Entities\EmailVerification');
    }

    public function campaign()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Campaign');
    }

    public function subscriberFields()
    {
        return $this->hasMany('Modules\Inboxer\Entities\SubscriberField');
    }

    public function trackingLogs()
    {
        return $this->hasMany('Modules\Inboxer\Entities\TrackingLog');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (Subscriber::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Get rules.
     *
     * @var array
     */
    public function getRules()
    {
        $rules = $this->mailList->getFieldRules();
        $item_id = isset($this->id) ? $this->id : 'NULL';
        $rules['EMAIL'] = $rules['EMAIL'].'|unique:mkt_subscribers,email,'.$item_id.',id,mail_list_id,'.$this->mailList->id;

        return $rules;
    }

    /**
     * Blacklist a subscriber.
     *
     * @return bool
     */
    public function sendToBlacklist($reason = null)
    {
        // blacklist all email
        self::where('email', $this->email)->update(['status' => self::STATUS_BLACKLISTED]);

        // create an entry in blacklists table
        $r = Blacklist::firstOrNew(['email' => $this->email]);
        $r->reason = $reason;
        $r->save();

        return true;
    }

    /**
     * Mark a subscriber/list as abuse-reported.
     *
     * @return bool
     */
    public function markAsSpamReported()
    {
        $this->status = self::STATUS_SPAM_REPORTED;
        $this->save();

        return true;
    }

    /**
     * Update fields from request.
     */
    public function updateFields($params)
    {
        foreach ($params as $tag => $value) {
            $field = $this->mailList->getFieldByTag(str_replace('[]', '', $tag));
            if (is_object($field)) {
                $fv = SubscriberField::where('subscriber_id', '=', $this->id)->where('field_id', '=', $field->id)->first();
                if (!is_object($fv)) {
                    $fv = new SubscriberField();
                    $fv->subscriber_id = $this->id;
                    $fv->field_id = $field->id;
                }
                if (is_array($value)) {
                    $fv->value = implode(',', $value);
                } else {
                    $fv->value = $value;
                }
                $fv->save();

                // update email attribute of subscriber
                if ($field->tag == 'EMAIL') {
                    $this->email = $fv->value;
                    $this->save();
                }
            }
        }
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($query, $request)
    {
        /* does not support searching on subscriber fields, for the sake of performance
        $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id')
            ->leftJoin('mail_lists', 'subscribers.mail_list_id', '=', 'mail_lists.id');
        */
        $query = $query->leftJoin('mkt_mail_lists', 'mkt_subscribers.mail_list_id', '=', 'mkt_mail_lists.id');

        // Email verification join
        $query = $query->leftJoin('mkt_email_verifications', 'mkt_email_verifications.subscriber_id', '=', 'mkt_subscribers.id');

        if (isset($request)) {
            // Keyword
            if (!empty(trim($request->keyword))) {
                foreach (explode(' ', trim($request->keyword)) as $keyword) {
                    $query = $query->where(function ($q) use ($keyword) {
                        $q->orwhere('mkt_subscribers.email', 'like', '%'.$keyword.'%');
                            /* does not support searching on subscriber fields, for the sake of performance
                            ->orWhere('subscriber_fields.value', 'like', '%'.$keyword.'%');
                            */
                    });
                }
            }

            // filters
            $filters = $request->filters;
            if (!empty($filters)) {
                if (!empty($filters['status'])) {
                    $query = $query->where('mkt_subscribers.status', '=', $filters['status']);
                }
                if (!empty($filters['verification_result'])) {
                    $query = $query->where('mkt_email_verifications.result', '=', $filters['verification_result']);
                }
            }

            // outside filters
            if (!empty($request->status)) {
                $query = $query->where('mkt_subscribers.status', '=', $request->status);
            }
            if (!empty($request->verification_result)) {
                $query = $query->where('mkt_email_verifications.result', '=', $request->verification_result);
            }

            // Open
            if ($request->open == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('mkt_open_logs')
                        ->join('mkt_tracking_logs', 'mkt_open_logs.message_id', '=', 'mkt_tracking_logs.message_id')
                        ->whereRaw(table('mkt_tracking_logs').'.subscriber_id = '.table('mkt_subscribers').'.id');
                });
            }

            // Not Open
            if ($request->open == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('mkt_open_logs')
                        ->join('mkt_tracking_logs', 'mkt_open_logs.message_id', '=', 'mkt_tracking_logs.message_id')
                        ->whereRaw(table('mkt_tracking_logs').'.subscriber_id = '.table('mkt_subscribers').'.id');
                });
            }

            // Click
            if ($request->click == 'yes') {
                $query = $query->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('mkt_click_logs')
                        ->join('mkt_tracking_logs', 'mkt_click_logs.message_id', '=', 'mkt_tracking_logs.message_id')
                        ->whereRaw(table('mkt_tracking_logs').'.subscriber_id = '.table('mkt_subscribers').'.id');
                });
            }

            // Not Click
            if ($request->click == 'no') {
                $query = $query->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                        ->from('mkt_click_logs')
                        ->join('mkt_tracking_logs', 'mkt_click_logs.message_id', '=', 'mkt_tracking_logs.message_id')
                        ->whereRaw(table('mkt_tracking_logs').'.subscriber_id = '.table('mkt_subscribers').'.id');
                });
            }
        }

        return $query;
    }

    /**
     * Get all languages.
     *
     * @return collect
     */
    public static function search($request, $customer = null)
    {
        $query = self::select('mkt_subscribers.*', 'mkt_email_verifications.result as verify_result');

        // Filter by customer
        if (!isset($customer)) {
            $customer = $request->user()->customer;
        }
        $query = $query->where('mkt_mail_lists.customer_id', '=', $customer->id);

        // Filter
        $query = self::filter($query, $request);

        // Order
        if (isset($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * Get field value by list field.
     *
     * @return value
     */
    public function getValueByField($field)
    {
        $fv = $this->subscriberFields->filter(function ($r, $key) use ($field) { return $r->field_id == $field->id; })->first();
        if (is_object($fv)) {
            return $fv->value;
        } else {
            return '';
        }
    }

    /**
     * Get field value by list field.
     *
     * @return value
     */
    public function getValueByTag($tag)
    {
        $fv = SubscriberField::leftJoin('mkt_fields', 'mkt_fields.id', '=', 'mkt_subscriber_fields.field_id')
            ->where('mkt_subscriber_id', '=', $this->id)->where('mkt_fields.tag', '=', $tag)->first();
        if (is_object($fv)) {
            return $fv->value;
        } else {
            return '';
        }
    }

    /**
     * Set field.
     *
     * @return value
     */
    public function setField($field, $value)
    {
        $fv = SubscriberField::where('mkt_subscriber_id', '=', $this->id)->where('mkt_field_id', '=', $field->id)->first();
        if (!is_object($fv)) {
            $fv = new SubscriberField();
            $fv->field_id = $field->id;
            $fv->subscriber_id = $this->id;
        }

        $fv->value = $value;
        $fv->save();
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Import subscribers from file.
     * @todo Fix Path For Tenant
     * @return object
     */
    public static function import($user, $list)
    {
        $customer = $user->customer;
        $file_path = storage_path('/');
        $directory = $file_path.'import/';

        // Import to database
        $filename = $list->id.'-data.csv';
        $content = \File::get($directory.$filename);
        $lines = preg_split('/\r\n|\r|\n/', $content); // explode("\n\r", $content);
        $total = count($lines) - 1;
        $success = 0;
        $error = 0;
        $lines_per_second = 1;
        $headers = explode(',', $lines[0]);
        $header_names = explode(',', $lines[0]);

        // update header tags
        foreach ($headers as $key => $tag) {
            $tag = trim(strtoupper(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($tag)))));
            $headers[$key] = $tag;
            if ($tag == 'EMAIL') {
                $main_index = $key;
            }
        }

        // check valid file
        if (!in_array('EMAIL', $headers)) {
            $str = "error\n<span style='color:red'>".trans('inboxer::messages.invalid_csv_file').'</span>';
            $str .= "\n0";
            $bytes_written = \File::put($directory.$list->id.'-process.log', $str);

            return;
        }

        $content_cache = '';
        $count = '0';
        foreach ($lines as $key => $line) {

            // authorize
            /*if ($user->cannot('create', new \Modules\Inboxer\Entities\Subscriber(['mail_list_id' => $list->id]))) {
                $str = "error\n<span style='color:red'>".trans('inboxer::messages.error_add_max_quota').'</span><br />'.$content_cache;
                $str .= "\n".$count;
                $bytes_written = \File::put($directory.$list->id.'-process.log', $str);

                // Action Log
                $list->log('import_max_error', $customer, ['count' => $count]);

                $error_detail = trans('inboxer::messages.error_add_max_quota');
                $myfile = file_put_contents($directory.$list->uid.'-detail.log', $error_detail.PHP_EOL, FILE_APPEND | LOCK_EX);

                return;
            }*/

            if ($key > 0) {
                $parts = explode(',', $line);
                if (isset($parts[$main_index])) {
                    $email = strtolower(trim(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($parts[$main_index])))));
                } else {
                    $email = '';
                }

                $valid = $list->checkExistsEmail($email);
                if ($valid) {
                    //// save subscribers
                    $subscriber = new Subscriber();
                    $subscriber->mail_list_id = $list->id;
                    $subscriber->email = $email;
                    $subscriber->status = 'subscribed';
                    $subscriber->save();

                    foreach ($parts as $key => $value) {
                        $value = trim(preg_replace('!\s+!', '_', preg_replace('![\'|\"|\\r|\\n]!', '', rtrim($value))));
                        if (isset($headers[$key])) {
                            $lf = $list->fields()->where('tag', '=', $headers[$key])->first();
                            if (is_object($lf)) {
                                //// save fields
                                $lfv = new SubscriberField(array(
                                                'field_id' => $lf->id,
                                                'subscriber_id' => $subscriber->id,
                                                'value' => $value,
                                            ));
                                $lfv->save();
                            }
                        }
                    }

                    ++$success;
                    $error_detail = trans('inboxer::messages.email_imported', ['time' => \Carbon\Carbon::now()->timezone($user->customer->timezone)->format(trans('inboxer::messages.datetime_format_2')), 'email' => $email]);
                } else {
                    ++$error;
                    $error_detail = trans('inboxer::messages.email_existed_invalid', ['time' => \Carbon\Carbon::now()->timezone($user->customer->timezone)->format(trans('inboxer::messages.datetime_format_2')), 'email' => $email]);
                }
                if ($key % $lines_per_second == 0) {
                    $content_cache = trans('inboxer::messages.import_export_statistics_line', [
                                            'total' => $total,
                                            'processed' => $success + $error,
                                            'success' => $success,
                                            'error' => $error,
                                        ]); //"Total of ".$total." subscribers; ".($success+$error)." have been processed; ".$success." successfully and ".$error." with errors.";
                    $count = round((($success + $error) / $total) * 100, 0);
                    $str = "processing\n".$content_cache;
                    $str .= "\n".$count.'';
                    $bytes_written = \File::put($directory.$list->id.'-process.log', $str);

                    // Details
                    $myfile = file_put_contents($directory.$list->uid.'-detail.log', $error_detail."\r\n".PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            }
        }

        $content_cache = trans('inboxer::messages.import_export_statistics_line', [
                                            'total' => $total,
                                            'processed' => $success + $error,
                                            'success' => $success,
                                            'error' => $error,
                                        ]);
        $str = "finished\n".$content_cache."\n100";
        $bytes_written = \File::put($directory.$list->id.'-process.log', $str);

        // Action Log
        $list->log('import_success', $customer, ['count' => $success, 'error' => $error]);
    }

    /**
     * Export subscribers to csv.
     *
     * @return object
     */
    public static function export($user, $list)
    {
        $customer = $user->customer;
        $file_path = storage_path('/');
        $directory = $file_path.'export/';
        // Import to database
        $total = $list->subscribersCount(); // no cache
        $success = 0;
        $error = 0;
        $lines_per_second = 1;
        $data = [];
        $headers = [];
        foreach ($list->getFields as $key => $field) {
            $headers[] = $field->tag;
        }
        $headers = implode(',', $headers);

        foreach ($list->subscribers as $key => $item) {
            $cols = [];
            if (true) {
                if (true) {
                    foreach ($list->fields as $key => $field) {
                        $value = $item->getValueByField($field);
                        $cols[] = $value;
                    }
                    $data[] = implode(',', $cols);

                    ++$success;
                } else {
                    ++$error;
                }
                if ($key % $lines_per_second == 0) {
                    $content_cache = trans('inboxer::messages.import_export_statistics_line', [
                                            'total' => $total,
                                            'processed' => $success + $error,
                                            'success' => $success,
                                            'error' => $error,
                                        ]);
                    $str = "processing\n".$content_cache;
                    $str .= "\n".round((($success + $error) / $total) * 100, 0).'';
                    $bytes_written = \File::put($directory.$list->id.'-process.log', $str);
                }
            }
        }

        $str = $headers."\n".implode("\n", $data);
        $bytes_written = \File::put($directory.$list->id.'-data.csv', $str);
        $content_cache = trans('inboxer::messages.import_export_statistics_line', [
                                            'total' => $total,
                                            'processed' => $success + $error,
                                            'success' => $success,
                                            'error' => $error,
                                        ]);
        $str = "finished\n".$content_cache."\n100";
        $bytes_written = \File::put($directory.$list->id.'-process.log', $str);

        // Action Log
        $list->log('export_success', $customer, ['count' => $success, 'error' => $error]);
    }

    /**
     * Get secure code for updating subscriber.
     *
     * @param string $action
     */
    public function getSecurityToken($action)
    {
        $string = $this->email.$action.config('app.key');

        return md5($string);
    }

    /**
     * Create customer action log.
     *
     * @param string   $cat
     * @param Customer $customer
     * @param array    $add_datas
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
                'id' => $this->id,
                'email' => $this->email,
                'list_id' => $this->mail_list_id,
                'list_name' => $this->mailList->name,
        ];

        $data = array_merge($data, $add_datas);

        \Modules\Inboxer\Entities\Log::create([
                                'customer_id' => $customer->id,
                                'type' => 'subscriber',
                                'name' => $name,
                                'data' => json_encode($data),
                            ]);
    }

    /**
     * Copy to list.
     *
     * @param MailList $list
     */
    public function copy($list, $type = 'keep')
    {
        // find exists
        $copy = $list->subscribers()->where('email', '=', $this->email)->first();

        if (!is_object($copy)) {
            $copy = $this->replicate();
            $copy->mail_list_id = $list->id;
            $copy->save();

            $copy = Subscriber::find($copy->id);
        } else {
            // return if keep
            if ($type == 'keep') {
                return false;
            }
        }

        // update fields
        foreach ($this->subscriberFields as $item) {
            foreach ($copy->mailList->fields as $field) {
                if ($item->field->tag == $field->tag) {
                    $copy->setField($field, $item->value);
                }
            }
        }

        return $copy;
    }

    /**
     * Move to list.
     *
     * @param MailList $list
     */
    public function move($list, $type = 'keep')
    {
        $fromList = $this->mailList;
        $this->copy($list, $type);
        $this->delete();

        // update related campaign cache information
        $fromList->updateCachedInfo();
        $list->updateCachedInfo();
    }

    /**
     * Get tracking log.
     *
     * @param MailList $list
     */
    public function trackingLog($campaign)
    {
        $query = \Modules\Inboxer\Entities\TrackingLog::where('mkt_tracking_logs.subscriber_id', '=', $this->id);
        $query = $query->where('mkt_tracking_logs.campaign_id', '=', $campaign->id)->orderBy('created_at', 'desc')->first();

        return $query;
    }

    /**
     * Get all subscriber's open logs.
     *
     * @param MailList $list
     */
    public function openLogs($campaign = null)
    {
        $query = \Modules\Inboxer\Entities\OpenLog::leftJoin('mkt_tracking_logs', 'mkt_tracking_logs.message_id', '=', 'mkt_open_logs.message_id')
            ->where('mkt_tracking_logs.subscriber_id', '=', $this->id);

        if (isset($campaign)) {
            $query = $query->where('mkt_tracking_logs.campaign_id', '=', $campaign->id);
        }

        return $query;
    }

    /**
     * Get last open.
     *
     * @param MailList $list
     */
    public function lastOpenLog($campaign = null)
    {
        $query = $this->openLogs($campaign);

        $query = $query->orderBy('mkt_open_logs.created_at', 'desc')->first();

        return $query;
    }

    /**
     * Get all subscriber's click logs.
     *
     * @param MailList $list
     */
    public function clickLogs($campaign = null)
    {
        $query = \Modules\Inboxer\Entities\ClickLog::leftJoin('mkt_tracking_logs', 'mkt_tracking_logs.message_id', '=', 'mkt_click_logs.message_id')
            ->where('mkt_tracking_logs.subscriber_id', '=', $this->id);

        if (isset($campaign)) {
            $query = $query->where('mkt_tracking_logs.campaign_id', '=', $campaign->id);
        }

        return $query;
    }

    /**
     * Get last click.
     *
     * @param MailList $list
     */
    public function lastClickLog($campaign = null)
    {
        $query = $this->clickLogs();
        $query = $query->orderBy('mkt_click_logs.created_at', 'desc')->first();

        return $query;
    }

    /**
     * Update subscriber status.
     *
     * @param MailList $list
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Is overide copy/move subscriber.
     *
     * return array
     */
    public static function copyMoveExistSelectOptions()
    {
        return [
            ['text' => trans('inboxer::messages.update_if_subscriber_exists'), 'value' => 'update'],
            ['text' => trans('inboxer::messages.keep_if_subscriber_exists'), 'value' => 'keep'],
        ];
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Verify subscriber email address using a given service.
     */
    public function verify($verifier)
    {
        //MailLog::info(sprintf('Start verifying %s (%s)', $this->email, $this->id));
        $log = new EmailVerification();
        list($result, $details) = $verifier->verify($this->email);
        $log->result = $result;
        $log->details = $details;
        $log->email_verification_server_id = $verifier->id;
        $this->emailVerifications()->save($log);
        //MailLog::info(sprintf('Finish verifying %s (%s)', $this->email, $this->id));
        return $log->isDeliverable();
    }

    /**
     * Get email verification of a subcriber.
     *
     * @return EmailVerification
     */
    public function getEmailVerification()
    {
        return $this->emailVerifications()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get email verification of a subcriber.
     *
     * @return EmailVerification
     */
    public function getVerificationResult()
    {
        if (!$this->isVerifed()) {
            return '';
        }

        return $this->getEmailVerification()->result;
    }

    /**
     * Check if subscriber is verified.
     *
     * @return bool
     */
    public function isVerifed()
    {
        return is_object($this->getEmailVerification());
    }

    /**
     * Reset subscriber verification.
     */
    public function resetVerification()
    {
        return $this->emailVerifications()->delete();
    }

    /**
     * Upload and resize avatar.
     *
     * @var string
     *
     * @return string
     */
    public function uploadImage($file)
    {
        $path = 'subscriber/';
        $file_path = storage_path('/');
        $upload_path = $file_path.$path;

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = $this->uid.'.jpg';

        // save to server
        $file->move($upload_path, $filename);

        // create thumbnails
        $img = \Image::make($upload_path.$filename);
        $img->fit(120, 120)->save($upload_path.$filename);

        return $path.$filename;
    }

    /**
     * Get image thumb path.
     *
     * @return string
     */
    public function imagePath()
    {
        $file_path = storage_path('/');
        if (!empty($this->uid) && is_file($file_path.'subscriber/'.$this->uid.'.jpg')) {
            return $file_path.'subscriber/'.$this->uid.'.jpg';
        } else {
            return '';
        }
    }

    /**
     * Remove thumb path.
     */
    public function removeImage()
    {
        $file_path = storage_path('/');
        if (!empty($this->uid)) {
            $path = $file_path.'subscriber/'.$this->uid;
            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($path.'.jpg')) {
                unlink($path.'.jpg');
            }
        }
    }

    /**
     * Check if the subscriber is listed in the Blacklist database.
     */
    public function isListedInBlacklist()
    {
        // @todo Filter by current user only
        return Blacklist::where('email', '=', $this->email)->exists();
    }

    public function lead(){
        return $this->hasOne('Modules\Workaday\Model\Leads', 'email','email');
    }
}
