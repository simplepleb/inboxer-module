<?php


namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_FEEDBACK_ABUSE = 'feedback-abuse';
    const STATUS_FEEDBACK_SPAM = 'feedback-spam';

    protected $fillable = ['campaign_id', 'message_id', 'runtime_message_id', 'subscriber_id', 'sending_server_id', 'customer_id', 'status', 'error', 'auto_trigger_id', 'sub_account_id'];

    protected $table = 'mkt_tracking_logs';

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\User', 'customer_id', 'id');
    }

    public function campaign()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Campaign');
    }

    public function mailList()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\MailList');
    }

    public function sendingServer()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\SendingServer');
    }

    public function subscriber()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Subscriber');
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('mkt_tracking_logs.*');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $customer = $user->customer;
        $query = self::select('mkt_tracking_logs.*');
        $query = $query->leftJoin('mkt_subscribers', 'mkt_subscribers.id', '=', 'mkt_tracking_logs.subscriber_id');
        $query = $query->leftJoin('mkt_campaigns', 'mkt_campaigns.id', '=', 'mkt_tracking_logs.campaign_id');
        $query = $query->leftJoin('mkt_sending_servers', 'mkt_sending_servers.id', '=', 'mkt_tracking_logs.sending_server_id');
        $query = $query->leftJoin('mkt_customers', 'mkt_customers.id', '=', 'mkt_tracking_logs.customer_id');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('mkt_campaigns.name', 'like', '%'.$keyword.'%')
                        ->orwhere('mkt_tracking_logs.status', 'like', '%'.$keyword.'%')
                        ->orwhere('mkt_sending_servers.name', 'like', '%'.$keyword.'%')
                        ->orwhere(\DB::raw('CONCAT(first_name, last_name)'), 'like', '%'.$keyword.'%')
                        ->orwhere('mkt_subscribers.email', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['campaign_uid'])) {
                $query = $query->where('mkt_campaigns.uid', '=', $filters['campaign_uid']);
            }
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request, $campaign = null)
    {
        $query = self::filter($request);

        if (isset($campaign)) {
            $query = $query->where('mkt_tracking_logs.campaign_id', '=', $campaign->id);
        }

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;
}
