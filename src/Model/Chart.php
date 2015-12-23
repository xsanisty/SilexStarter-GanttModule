<?php

namespace Xsanisty\Gantt\Model;

class Chart extends Model
{
    protected $table    = 'gantt_charts';
    protected $guarded  = ['id'];
    protected $casts    = [
        'settings' => 'array',
    ];

    public function members()
    {
        return $this->belongsToMany('Cartalyst\Sentry\Users\Eloquent\User', 'gantt_members', 'chart_id', 'user_id')
                    ->withPivot('permissions');
    }

    public function author()
    {
        return $this->belongsTo('Cartalyst\Sentry\Users\Eloquent\User', 'author_id');
    }

    public function tasks()
    {
        return $this->hasMany('Xsanisty\Gantt\Model\Task', 'chart_id');
    }

    public function links()
    {
        return $this->hasMany('Xsanisty\Gantt\Model\Link', 'chart_id');
    }
}
