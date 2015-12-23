<?php

namespace Xsanisty\Gantt\Model;

use Carbon\Carbon;

class Task extends Model
{
    protected $table = 'gantt_tasks';
    protected $guarded = ['id'];
    protected $casts = [
        'open' => 'boolean',
    ];

    /** relationship */

    public function subTasks()
    {
        return $this->hasMany('Xsanisty\Gantt\Model\Task', 'parent');
    }

    public function subTasksRecursive()
    {
        return $this->subTasks->with('subTasksRecursive');
    }

    public function parentTask()
    {
        return $this->belongsTo('Xsanisty\Gantt\Model\Task', 'parent');
    }

    public function parentTaskRecursive()
    {
        return $this->parentTask->with('parentTaskRecursive');
    }

    public function chart()
    {
        return $this->belongsTo('Xsanisty\Gantt\Model\Chart', 'chart_id');
    }

    /** Mutators */

    /**public function getStartDateAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['start_date'])->format('d-m-Y');
    }

    public function getEndDateAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['end_date'])->format('d-m-Y');
    }*/

    public function setParentAttribute($value)
    {
        $this->attributes['parent'] = $value == 0 ? null : $value;
    }
}
