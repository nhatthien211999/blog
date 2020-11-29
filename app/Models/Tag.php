<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag','category_id','price','quatity','description',
    ];

    public function category()
    {
        return $this->hasOne(Profile::class);
    }
}
