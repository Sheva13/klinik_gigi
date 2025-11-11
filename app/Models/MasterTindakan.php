<?php

namespace App\Models;

use Illuminate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTindakan extends Model
{
    use HasFactory;
    protected $table = 'master_tindakan';
    protected $fillable = ['tindakan', 'biaya_tindakan'];
}