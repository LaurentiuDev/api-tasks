<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    public $table = "history";
    protected $fillable = [
        'assign','status','task_id'
    ];

    public function task()
    {
        return $this->belongsTo('App\Task');
    }
}
