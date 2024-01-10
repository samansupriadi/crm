<?php

namespace App\Models;

use App\Models\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramCategory extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'category_name',
        'type',
        'bagian_pengelola',
        'total_penghimpunan',
        'created_by',
        'deleted_by',
        'updated_by',
        'last_refresh_total'

    ];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName(){
        return 'ulid';
    }

    public function programs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Program::class, 'program_category_id', 'id');
    }
}
