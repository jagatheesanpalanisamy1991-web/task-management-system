<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignmentRule extends Model
{
    protected $fillable = [
        'task_id',
        'rule_attribute',
        'rule_operator',
        'rule_value',
        'created_by',
    ];
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
