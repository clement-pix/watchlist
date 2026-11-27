<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['name', 'emoji', 'description', 'criterion_type', 'threshold', 'enabled', 'sort_order'];

    protected function casts(): array
    {
        return ['threshold' => 'integer', 'enabled' => 'boolean', 'sort_order' => 'integer'];
    }
}
