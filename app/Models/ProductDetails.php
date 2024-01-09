<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetails extends Model
{
    use HasFactory;

    protected $table = 'SerialNumber';

    protected $fillable = [
        'ProductID',
        'SerialNumber',
    ];

    protected $primaryKey = 'SerialNumberID';
    public $timestamps = false;


    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'SerialNumberID', 'SerialNumberID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaktion::class, 'SerialNumberID', 'SerialNumberID');
    }
}
