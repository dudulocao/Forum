<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use Favorable, RecordsActivity;
    protected $guarded = [];

    protected $with = ['creator', 'channel'];
    
    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('replyCount', function ($builder) {
        //     $builder->withCount('replies');
        // });

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });

    }
    
    
    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }
    
    public function replies()
    {
        return $this->hasMany(Reply::class);
            // ->withCount('favorites')
            // ->with('owner');
    }

    public function creator()
    {
        return $this->belongsTo(User::Class, 'user_id');
    }
    
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    
    public function addReply($reply)
    {
        return $this->replies()->create($reply);
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id()
        ]);
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions() //find all subscriptions
        ->where('user_id', $userId ?: auth()->id()) //filtered for the ones that have YOUR user id
        ->delete(); //delete them
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }
    
}
