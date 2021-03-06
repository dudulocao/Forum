<?php

namespace App;

trait RecordsActivity
{
    protected static function bootRecordsActivity()
    {
        if (auth()->guest()) return;

        foreach (static::getActivitiesToRecord() as $event) {
            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }

        static::deleting(function ($model) {
            $model->activity()->delete();
        }); //this is basically saying: when deleting, also delete the activity
    }

    protected function recordActivity($event)
    {
        $this->activity()->create([
            'user_id' => auth()->id(),
            'type' => $this->getActivityType($event),
        ]);
    }

    protected static function getActivitiesToRecord()
    {
        return ['created']; //doing this we will only record activities which create, and not delete sth
    }

    public function activity()
    {
        return $this->morphMany('App\Activity', 'subject'); //this is similar to hasMany but we don't need to hardcode anything..
    }

    protected function getActivityType($event)
    {
        $type = strtolower((new \ReflectionClass($this))->getShortName());
        
        return "{$event}_{$type}";
    }
    
}