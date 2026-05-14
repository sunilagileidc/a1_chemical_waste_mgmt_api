<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;

class Approval extends Model
{
    use HasFactory, HasSlug;

    public $table = 'lookups';
}
