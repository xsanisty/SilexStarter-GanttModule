<?php

namespace Xsanisty\Gantt\Model;

class Link extends Model
{
    protected $table = 'gantt_links';
    protected $guarded = ['id'];
    public $timestamps = false;
}
