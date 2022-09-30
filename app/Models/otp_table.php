<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class otp_table extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['otp','valid_until'];

}
