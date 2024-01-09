<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'Product';

    protected $fillable =
    [
        'ProductID',
        'ProductName',
        'Price',
        'Color',
        'Storage'
    ];

    protected $primaryKey = 'ProductID';
    public $timestamps = false;



    public function details()
    {
        return $this->hasMany(ProductDetails::class, 'ProductID', 'ProductID');
    }

    public function inventory()
    {
        return $this->hasmany(Inventory::class, 'ProductID', 'ProductID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaktion::class, 'ProductID', 'ProductID');
    }
}
