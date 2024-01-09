<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'Inventory';

    protected $fillable = [
        'ProductID',
        'SerialNumberID',
        'LocationID',
        'Quantity'


    ];

    protected $primaryKey = 'InventoryID';
    public $timestamps = false;

    public function location()
    {
        return $this->belongsTo(Location::class, 'LocationID', 'LocationID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetails::class, 'SerialNumberID', 'SerialNumberID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaktion::class, 'ProductID', 'ProductID');
    }
}
