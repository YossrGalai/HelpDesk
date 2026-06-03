<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'created_by',
        'assigned_to'
    ];

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function assignee()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function comments()
    {
        return $this->hasMany(
            TicketComment::class
        );
    }
}
