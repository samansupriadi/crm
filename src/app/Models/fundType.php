<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class fundType extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable =  ['fund_type_name'];
    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName(){
        return 'ulid';
    }

    public function members(): HasMany
    {
        return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
    }
    
}
