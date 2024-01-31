<?php

namespace App\Models;

use App\Models\Entity;
use App\Models\ProgramCategory;
use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory, SoftDeletes, HasUlids, Sluggable;

    protected $fillable = [
        'program_name', 'image', 'program_category_id',
        'status_target', 'status_program', 'total_penghimpunan',
        'target_nominal', 'campaign_type', 'from_date', 'to_date',
        'publish_web', 'slug', 'is_savings', 'created_by', 'deleted_by', 'updated_by'
    ];

    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function uniqueIds(): array
    {
        return [
            'ulid'
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'program_name'
            ]
        ];
    }

    protected static function uploadPicProgram($data)
    {
        $ext = $data->getClientOriginalExtension();
        $newName =   str_replace(' ', '-', \Illuminate\Support\Str::uuid()) . "." . $ext;
        $path = $data->storeAs('profile-program', $newName);

        return $path;
    }

    protected static function formatCampaign($data)
    {
        switch ($data['tipe_kampanye']) {
            case 5: //NOMINAL
                $data['from_date'] = NULL;
                $data['to_date'] = NULL;
                break;
            case 3: //TIME
            case 2: //YEARLY
                $data['target_nominal'] = 0;
                break;
            case 1: // PERMANENT
                $data['from_date'] = NULL;
                $data['to_date'] = NULL;
                $data['target_nominal'] = 0;
            default:
        }
        return $data;
    }

    public static function listcampaigntype()
    {
        return [
            [
                'id'    => 1,
                'text'  => 'PERMANENT',
                'desc'  => 'Tipe Kampanye Program yang aktif selamanya',
                'rules' => 'Tidak ada aturan khusus'
            ],
            [
                'id'    => 2,
                'text'  => 'YEARLY',
                'desc'  => 'Tipe Kampanye Program yang perhitungan rekaputilasi hasil transaksi nya setahun sekali',
                'rules' => 'Tidak ada aturan khsusus'
            ],
            [
                'id'    => 3,
                'text'  => 'TIME',
                'desc'  => 'Tipe Kampanye Program yang di batasi rentang waktu tertentu',
                'rules' => 'field from dan to date nya harus di isi'
            ],
            [
                'id'    => 4,
                'text'  => 'TARGET',
                'desc'  => 'Tipe Kampanye Program yang di batasi oleh range waktu terntetu DAN nominal tertentu',
                'rules' => 'field from, to dan target nominal harus di isi'
            ],
            [
                'id'    => 5,
                'text'  => 'NOMINAL',
                'desc'  => 'Tipe Kampanye Program yang di batasi oleh target nominal',
                'rules' => 'field nominal wajib di isi'
            ],
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'program_category_id', 'id');
    }


    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'program_id', 'id');
    }

    /**
     * Get the user that owns the Program
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entitas(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id', 'id');
    }
}
