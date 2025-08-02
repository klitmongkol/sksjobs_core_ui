<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $connection = 'mysql';
    protected $table = 'employee';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employeeid',
        'gender',
        'name',
        'lastname',
        'bankcode',
        'bankaccount',
        'startedwork',
        'section',
        'line',
        'plant',
        'idcardnumber',
        'password',
        'email',
        'emailstatus',
        'location',
        'created_at',
        'updated_at',
    ];
}
