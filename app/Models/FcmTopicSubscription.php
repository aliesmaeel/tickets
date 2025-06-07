<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmTopicSubscription extends Model
{
    protected $table = 'fcm_topic_subscriptions';

    public $timestamps = false;

    protected $fillable = [
        'fcm_topic_id',
        'fcm_token',
    ];
}
