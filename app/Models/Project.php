<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label()
    {
        return $this->hasMany(Label::class)->select(['id', 'project_id', 'name', 'color']);
    }

}
