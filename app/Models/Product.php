<?php

namespace Api\Models;

use Ecotone\Modelling\Attribute\Aggregate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
        'registered_at',
    ];
}
