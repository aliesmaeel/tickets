<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FcmTopic extends Model
{
    protected $table = 'fcm_topics';

    public $timestamps = false;

    protected $fillable = [
        'topic',
    ];

    public function subscriptions()
    {
        return $this->hasMany(FcmTopicSubscription::class, 'fcm_topic_id');
    }
}
