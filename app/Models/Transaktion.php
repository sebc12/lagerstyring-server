<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaktion extends Model
{
    use HasFactory;

    protected $table = 'Transaktion';

    protected $fillable = [
        'ProductID',
        'SerialNumberID',
        'FromLocationID',
        'ToLocationID',
        'QuantityMoved',
    ];

    protected $primaryKey = 'TransaktionID'; // Adjust the primary key name

    public $timestamps = false; // Automatically manage created_at and updated_at

    // Define relationships if needed
    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function details()
    {
        return $this->belongsTo(ProductDetails::class, 'SerialNumberID', 'SerialNumberID');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'FromLocationID', 'LocationID');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'ToLocationID', 'LocationID');
    }
}
