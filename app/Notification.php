<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
      'task_id','user_id', 'assign' , 'body'
    ];
}
