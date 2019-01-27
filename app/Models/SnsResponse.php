<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SnsResponse extends Model
{
    use SoftDeletes, Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'email',
        'notification_type',
        'type',
        'source_email',
        'source_arn',
        'data_payload',
        'datetime_payload',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'datetime_payload',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function toArray()
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'notification_type' => $this->notification_type,
            'type' => $this->type,
            'source_email' => $this->source_email,
            'source_arn' => $this->source_arn,
            'datetime_payload' => $this->datetime_payload->toDateTimeString()
        ];
    }
}
