<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clinic extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['name','address','lat','description','long','clinic_photo','opening_hour','close_hour'];
}
