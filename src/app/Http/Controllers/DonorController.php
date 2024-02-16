<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Donor;
use App\Models\Program;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DonorResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreDonorRequest;
use App\Http\Requests\UpdateDonorRequest;
use Spatie\SimpleExcel\SimpleExcelReader;

class DonorController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'program'           => ['exists:programs,ulid'],
            'penanggung_jawab'  => ['exists:users,ulid'],
            'status'            => ['in:active,inactive']
        ]);
        $per_page = (int)($request->get('per_page') ?? 10);
        $name = !empty($request->name) ?  $request->name : false;
        $program = !empty($request->program) ?  $request->program : false;
        $pj = !empty($request->penanggung_jawab) ?  $request->penanggung_jawab : false;
        $status = !empty($request->status) ?  $request->status : false;
        $data = Donor::query();

        if ($program) {
            $id = Program::where('ulid', $program)->first();
            $data->withWhereHas('programs', function ($query) use ($id) {
                $query->where('program_id', $id->id);
            });
        }

        if ($pj) {
            $id_pj = User::where('ulid', $pj)->first();
            $data->where('asign_to', $id_pj->id);
        }

        if ($status) {
            $data->where('status', $status);
        }

        if ($name) {
            $data->where('kode_donatur', 'LIKE', '%' . $name . '%')
                ->orWhere('donor_name', 'LIKE', '%' . $name . '%')
                ->orWhere('email', 'LIKE', '%' . $name . '%')
                ->orWhere('email2', 'LIKE', '%' . $name . '%')
                ->orWhere('mobile', 'LIKE', '%' . $name . '%')
                ->orWhere('npwp', 'LIKE', '%' . $name . '%')
                ->orWhere('mobile2', 'LIKE', '%' . $name . '%')
                ->orWhere('home_phone', 'LIKE', '%' . $name . '%');
        }

        return DonorResource::collection($data->with('detail', 'asignTo', 'updatedBy', 'createdBy', 'programs')->paginate($per_page));
    }

    public function export(Request $request)
    {
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

    //update donatur pernah berdonasi pada program apa saja termasuk total dan retensi nya
    public function updateProgram()
    {
        try {
            // Hapus data yang sudah ada sebelumnya diluar transaksi
            DB::statement("TRUNCATE TABLE donor_program");

            DB::beginTransaction();
            // Update total_donasi_program pada donor_program
            DB::statement("
                INSERT INTO donor_program (donor_id, program_id, total_donasi_program)
                SELECT
                    donor_id,
                    program_id,
                    COALESCE(SUM(nominal), 0) AS total_donasi_program
                FROM
                    transaction_details
                    JOIN transactions ON transaction_details.transaction_id = transactions.id
                GROUP BY
                    donor_id,
                    program_id
                ON DUPLICATE KEY UPDATE
                    total_donasi_program = total_donasi_program + VALUES(total_donasi_program)
            ");

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


    //refresh status donatur berdasarakan terkahir kali transakasi
    public function refresh()
    {
        DB::beginTransaction();
        try {
            $chunkSize = 10000;
            DB::table('transactions')
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('transactions')
                        ->groupBy('donor_id');
                })
                ->select('donor_id', 'kode_transaksi', 'tanggal_kuitansi')
                ->orderBy('donor_id')
                ->chunk($chunkSize, function ($transactions) {
                    foreach ($transactions as $transaction) {
                        $status = '';
                        $transactionDate = Carbon::parse($transaction->tanggal_kuitansi);
                        $now = Carbon::now();

                        // Menghitung perbedaan dalam tahun
                        $diffYears = $now->diffInYears($transactionDate);

                        // Menentukan status berdasarkan perbedaan tahun
                        if ($diffYears < 3) {
                            $status = 'AKTIF';
                        } elseif ($diffYears >= 3 && $diffYears < 5) {
                            $status = 'RECOVERY';
                        } else {
                            $status = 'MATI';
                        }

                        // Memperbarui record donors dengan status baru
                        DB::table('donors')
                            ->where('id', $transaction->donor_id)
                            ->update([
                                'last_transaction' => $transaction->tanggal_kuitansi,
                                'status_donatur' => $status
                            ]);
                    }
                });

            DB::commit();
            return $this->success('', 'Update Data Success', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return $this->error('', "Update data donatur failed", 500);
        }
    }



    public function show(Request $request,  Donor $donor)
    {
        $validated = $request->validate([
            'program'           => ['exists:programs,ulid']
        ]);


        $program = !empty($request->program) ?  $request->program : false;
        if ($program) {
            $id = Program::where('ulid', $program)->value('id');
            $donor->loadMissing(['programs' => function ($query) use ($id) {
                $query->where('program_id', $id);
            }]);
        }

        return new DonorResource($donor->loadMissing(
            ['detail', 'asignTo', 'updatedBy', 'createdBy', 'programs']
        ));
    }
}
