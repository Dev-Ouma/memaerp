<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'dean',
        'departments_count',
        'programmes_count',
        'email',
        'phone',
        'building',
        'description',
        'status',
    ];

    protected $casts = [
        'departments_count' => 'integer',
        'programmes_count' => 'integer',
    ];
}
