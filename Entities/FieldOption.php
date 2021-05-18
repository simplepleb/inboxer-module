<?php


namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class FieldOption extends Model
{
    protected $table = 'mkt_field_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'value', 'field_id',
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
            while (FieldOption::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }
}
