<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['id','username','profile_picture','nickname','fullname','birthday','phone_number','gender','status'];
}
