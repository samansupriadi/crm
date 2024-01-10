<?php

namespace App\Models;

use App\Models\Transaction;
use App\Models\DonorInformationDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donor extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'id','kode_donatur', 'kode_donatur_lama', 'donor_name',  'sapaan', 'email', 'email2',
        'mobile', 'mobile2', 'npwp', 'gender', 'suf', 'tempat_lahir', 'temp_nama_asli_donatur',
        'birthday', 'alamat', 'alamat2', 'kota_kabupaten', 'provinsi_address', 'kode_pos',
        'wilayah_address', 'home_phone', 'pekerjaan', 'pekerjaan_detail', 'alamat_kantor',
        'kota_kantor',  'kode_post_kantor', 'wilayah_kantor', 'telp_kantor', 'facebook',
        'twitter',  'pendidikan', 'pendidikan_detail', 'paket_9in1', 'registerd_at'

    ];

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



    public function asignTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asign_to', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


    public function detail(): HasOne
    {
        return $this->hasOne(DonorInformationDetail::class, 'donor_id', 'id');
    }

    protected function kodeDonatur(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'DON-' . $value
        );
    }
    

    public function programs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Program::class, 'donor_program', 'donor_id', 'program_id')->withPivot(['total_donasi_program', 'id']);
    }
   
    

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'donor_id', 'id');
    }

    public function setMobileAttribute($value)
    {
        $mobile = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['mobile'] = $mobile;
    }

    public function setMobile2Attribute($value)
    {
        $mobile = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['mobile2'] = $mobile;
    }
    
    public function setHomePhoneAttribute($value)
    {
        $mobile = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['home_phone'] = $mobile;
    }

    public function setTelpKantorAttribute($value)
    {
        $mobile = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['telp_kantor'] = $mobile;
    }


    public static function ListSumberInformasi(){
        return [
            [
                'id' => 1,
                'text' => 'Referal Donatur'
            ],
            [
                'id' => 2,
                'text' => 'Website'
            ],
            [
                'id' => 3,
                'text' => 'Teman / Saudara'
            ],
            [
                'id' => 4,
                'text' => 'Facebook'
            ],
            [
                'id' => 5,
                'text' => 'Instagram'
            ],
            [
                'id' => 6,
                'text' => 'Youtube'
            ],
            [
                'id' => 7,
                'text' => 'Twitter'
            ],
            [
                'id' => 8,
                'text' => 'Radio'
            ],
            [
                'id' => 9,
                'text' => 'Televisi'
            ],
            [
                'id' => 10,
                'text' => 'Konter'
            ],
            [
                'id' => 11,
                'text' => 'Whatsapp'
            ],
            [
                'id' => 12,
                'text' => 'Mesjid'
            ],
            [
                'id' => 13,
                'text' => 'Mitra'
            ],
            [
                'id' => 14,
                'text' => 'Marketing Affiliate'
            ],
            [
                'id' => 15,
                'text' => 'Spanduk'
            ],
            [
                'id' => 16,
                'text' => 'Brosur'
            ],
        ];
    }
}
