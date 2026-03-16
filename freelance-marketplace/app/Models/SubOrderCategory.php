<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubOrderCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'main_order_category_id'
    ];

    public function mainOrderCategory() : BelongsTo
    {
        return $this->belongsTo(MainOrderCategory::class);
    }
}
