<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class splashscreen_mobile extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['meta_name','meta_value'];

}
