<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Donor;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DonorResource;
use App\Jobs\updateProgramDonaturJob;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreDonorRequest;
use App\Http\Requests\UpdateDonorRequest;
use Spatie\SimpleExcel\SimpleExcelReader;

class DonorController extends Controller
{
    use HttpResponses;
    
    public function index()
    {
        return DonorResource::collection(Donor::with('detail', 'asignTo', 'updatedBy', 'createdBy', 'programs')->paginate(10));
    }

    public function store(StoreDonorRequest $request)
    {
        $datas = $request->validated();
        $kode_donatur = Donor::withTrashed()->max('kode_donatur') + 1;
        
        DB::beginTransaction();
        
        try {
            $donor = Donor::create(array_merge($datas, [
                'kode_donatur'  => $kode_donatur,
                'created_by'    => Auth::user()->id,
                'asign_to'      => Auth::user()->id
            ]));
            DB::commit();
            return new DonorResource($donor);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return $this->error('', 'Failed To Save New Data', 500);
        }        
    }
  
    public function update(UpdateDonorRequest $request, Donor $donor)
    {
        DB::beginTransaction();
        try {
            $donor->update([
                "donor_name"        => $request->donor_name,
                "sapaan"            => $request->sapaan,
                "email"             => $request->email,
                "email2"            => $request->email2,
                "mobile"            => $request->mobile,
                "mobile2"           => $request->mobil2,
                "gender"            => $request->gender,
                "suf"               => $request->suf,
                "tempat_lahir"      => $request->tempat_lahir,
                "birthday"          => $request->birthday,
                "alamat"            => $request->alamat,
                "alamat2"           => $request->alamat2,
                "kota_kabupaten"    => $request->kota_kabupaten,
                "provinsi_address"  => $request->provinsi_address,
                "kode_pos"          => $request->kode_pos,
                "wilayah_address"   => $request->wilayah_address,
                "home_phone"        => $request->home_phone,
                "pekerjaan"         => $request->pekerjaan,
                "pekerjaan_detail"  => $request->pekerjaan_detail,
                "alamat_kantor"     => $request->alamat_kantor,
                "kota_kantor"       => $request->kota_kantor,
                "kode_post_kantor"  => $request->kode_post_kantor,
                "wilayah_kantor"    => $request->wilayah_kantor,
                "telp_kantor"       => $request->telp_kantor,
                "facebook"          => $request->facebook,
                "twitter"           => $request->twitter,
                "pendidikan"        => $request->pendidikan,
                "pendidikan_detail" => $request->pendidikan_detail,
                "paket_9in1"        => $request->paket_9in1,
                'updated_by'        => Auth::user()->id
                
            ]);
            DB::commit();
            return new DonorResource($donor);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return $this->error('', 'Failed To Save New Data', 500);
        }
    }

   
    public function destroy(Donor $donor)
    {
        $donor->delete();
        $donor->update(['deleted_by' => Auth::user()->id]);
        return response()->noContent();
    }

    public function import(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'csv_file'  => 'required|mimes:csv,txt'
            ]);
            
            $file = $request->file('csv_file');
            $filePath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $filePath);
            
            $rows = SimpleExcelReader::create($fullPath)->getRows()->toArray();
            foreach (array_chunk($rows, 1000) as $chunk) {
                foreach ($chunk as $row) {
                    try {
                        DB::table('donors')->insert([
                            'id'                          => $row['contactid'],
                            'kode_donatur'                => preg_replace('/[^0-9]/', '', $row['contact_no']),
                            'kode_donatur_lama'           => $row['ID_LAMA'],
                            'ulid'                        => Ulid::generate(),
                            'sapaan'                      => $row['SAPAAN'],
                            'suf'                         => $row['SUF'],
                            'donor_name'                  => $row['firstname'] . ' ' . preg_replace('/\[[0-9]+\]/', '', $row['lastname']),
                            'email'                       => $row['email'] !== '' ? $row['email'] : null,
                            'mobile'                      => $row['MOBILE_PHONE'] !== '' ? $row['MOBILE_PHONE'] : null,
                            'mobile2'                     => $row['MOBILE_PHONE2'] !== '' ? $row['MOBILE_PHONE2'] : null,
                            'npwp'                        => $row['NPWP'],
                            'gender'                      => $row['GENDER'] === 'Laki-Laki' ? 'L' : ($row['GENDER'] === 'Perempuan' ? 'P' : 'U'),
                            'tempat_lahir'                => $row['TEMPAT_LAHIR'],
                            'temp_nama_asli_donatur'      => $row['TEMP_NAMA_ASLI_DONATUR'],
                            'birthday'                    => DateTime::createFromFormat('Y-m-d', $row['birthday']) !== false ? $row['birthday'] : null,
                            'alamat'                      => $row['mailingstreet'],
                            'kota_kabupaten'              => $row['KOTA_KABUPATEN_ADRESS'],
                            'provinsi_address'            => $row['PROVINSI_ADDRESS'],
                            'kode_pos'                    => $row['KODE_POS_ADDRESS'],
                            'wilayah_address'             => $row['WILAYAH_ADDRESS'],
                            'home_phone'                  => $row['homephone'] !== '' ? $row['homephone'] : null,
                            'pekerjaan'                   => $row['PEKERJAAN'],
                            'pekerjaan_detail'            => $row['PEKERJAAN_DET'],
                            'alamat_kantor'               => $row['ALAMAT_KANTOR'],
                            'kota_kantor'                 => $row['KANTOR_KABUPATEN_ADRESS'],
                            'kode_post_kantor'            => $row['KODE_POS_KANTOR'],
                            'telp_kantor'                 => $row['TELP_KANTOR'],
                            'wilayah_kantor'              => $row['WILAYAH_KANTOR'],
                            'facebook'                    => $row['facebook'],
                            'twitter'                     => $row['Twitter'],
                            'pendidikan'                  => $row['pendidikan'],
                            'pendidikan_detail'           => $row['Pendidikan_det'],
                            'paket_9in1'                  => $row['9in1'],
                            'asign_to'                    => $row['asign_to']

                        ]);
                        
                        DB::table('donor_information_details')->insert([
                            'donor_id'                    => $row['contactid'],
                            'ulid'                        => Ulid::generate(),
                        ]);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        Log::debug($th->getMessage());
                        return $this->error('', 'Failed To Import Data', 500);
                    }
                }
            }
            DB::commit();
            Storage::delete($fullPath);
            return $this->success('', 'Import Data Success', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
            return $this->error('', 'Failed To Import Data', 500);
        }
    }

    public function updateProgram()
    {
       try {
        $results = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->select('transactions.donor_id', 'transaction_details.program_id', DB::raw('SUM(transaction_details.nominal) AS total'))
                ->groupBy('transactions.donor_id', 'transaction_details.program_id')
                ->orderBy('transactions.donor_id')
                ->chunk(5000, function ($results) {
                    $data = $results->map(function ($item) {
                        return [
                            'donor_id'              => $item->donor_id,
                            'program_id'            => $item->program_id,
                            'total_donasi_program'  => $item->total
                        ];
                    });
                    DB::table('donor_program')->insert($data->toArray());
                });
            DB::commit();
            return $this->success('', 'Update Data Success', 201);
       } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return $this->error('', "Update data donatur program failed", 500);
       }
    }

    public function sumberInfo(Request $request)
    {
       return Donor::ListSumberInformasi();
    }
}
