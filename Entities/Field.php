<?php


namespace Modules\Inboxer\Entities;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mail_list_id', 'type', 'label', 'tag', 'default_value', 'visible', 'required', 'custom_order',
    ];

    protected $table = 'mkt_fields';

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $fields_rules = array(

    );

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function mailList()
    {
        return $this->belongsTo('Modules\Inboxer\Entities\MailList');
    }

    public function fieldOptions()
    {
        return $this->hasMany('Modules\Inboxer\Entities\FieldOption');
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
            while (Field::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Format string to field tag.
     *
     * @var string
     */
    public static function formatTag($string)
    {
        return strtoupper(preg_replace('/[^0-9a-zA-Z_]/m', '', $string));
    }

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
     * Get select options.
     *
     * @return array
     */
    public function getSelectOptions()
    {
        $options = $this->fieldOptions->map(function ($item) {
            return ['value' => $item->value, 'text' => $item->label];
        });

        return $options;
    }

    /**
     * Get control name.
     *
     * @return string
     */
    public static function getControlNameByType($type)
    {
        if ($type == 'date') {
            return 'date';
        } elseif ($type == 'datetime') {
            return 'datetime';
        }

        return 'text';
    }
}
