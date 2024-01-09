<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'Location';

    protected $fillable =
    [
        'LocationName'
    ];

    protected $primaryKey = 'LocationID';
    public $timestamps = false;

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'LocationID', 'LocationID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaktion::class,  'LocationID', 'ProductID');
    }
}
