<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoredEvent extends Model
{
    use HasFactory;

    protected $table = 'tbl_stored_event';

    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $fillable = [
        'event_body',
        'occurred_on',
        'type_name',
    ];

    protected $casts = [
        'occurred_on' => 'datetime',
    ];
}
