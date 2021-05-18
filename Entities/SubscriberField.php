<?php


namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class SubscriberField extends Model
{
    protected $table = 'mkt_subscriber_fields';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_id', 'subscriber_id', 'value',
    ];

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function field()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\Field');
    }
}
