<?php

namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class CampaignsListsSegment extends Model
{
    protected $table = 'mkt_campaigns_lists_segments';

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function campaign()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Campaign');
    }

    public function mailList()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\MailList');
    }

    public function segment()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Segment');
    }

    /**
     * Get segment in the same campaign and mail list.
     *
     * @return collect
     */
    public function getRelatedSegments()
    {
        $segments = Segment::leftJoin('campaigns_lists_segments', 'campaigns_lists_segments.segment_id', '=', 'segments.id')
                        ->where('campaigns_lists_segments.campaign_id', '=', $this->campaign_id)
                        ->where('campaigns_lists_segments.mail_list_id', '=', $this->mail_list_id);

        return $segments->get();
    }
}
