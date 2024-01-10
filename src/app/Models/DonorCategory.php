<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonorCategory extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_by', 'updated_by', 'created_by'];
    protected $fillable = ['category_name', 'rules_nominal', 'created_by', 'deleted_by', 'updated_by', 'id'];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName(){
        return 'ulid';
    }

}
