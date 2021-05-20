<?php

namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model {

    protected $table = 'mkt_segments';

    protected $fillable = [
        'name', 'matching',
    ];


    public function mailList()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\MailList');
    }
}
