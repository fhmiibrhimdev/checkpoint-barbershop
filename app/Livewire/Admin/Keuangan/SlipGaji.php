<?php

namespace App\Livewire\Admin\Keuangan;

use Carbon\Carbon;
use App\Models\Kasbon;
use Livewire\Component;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use App\Models\KasbonCounter;
use Livewire\Attributes\Title;
use App\Models\SlipGajiCounter;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\SlipGaji as ModelsSlipGaji;

class SlipGaji extends Component
{
    use WithPagination;
    #[Title('Admin | Slip Gaji')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'periode_mulai'       => 'required',
        'periode_selesai'     => 'required',
        'id_karyawan'         => 'required',
        'total_tunjangan'     => '',
        'total_potongan'      => '',
        'total_gaji'          => '',
        'status'              => 'required',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $karyawans, $cabangs;
    /**
     * @var \Illuminate\Support\Collection
     */
    public $komisi_transaksi = [], $total_komisi = 0, $data_kasbon = [], $total_kasbon = 0;
    public $tunjangans = [], $potongans = [];
    public $periode_mulai, $periode_selesai, $id_karyawan, $total_tunjangan, $total_potongan, $total_gaji, $status, $nama_karyawan;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        // $this->karyawans = $globalDataService->getKaryawans();
        // $this->cabangs = $globalDataService->getCabangs();
        $this->filter_id_cabang = Auth::user()->id_cabang ?? null;
        $this->getKaryawans();
        $this->periode_mulai       = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->periode_selesai     = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->resetInputFields();
    }

    private function getKaryawans()
    {
        $this->karyawans = DB::table('daftar_karyawan')->select('daftar_karyawan.id', 'name')
            ->where('daftar_karyawan.id_cabang', $this->filter_id_cabang)
            ->join('users', 'users.id', 'daftar_karyawan.id_user')
            ->get();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = ModelsSlipGaji::select('slip_gaji.id', 'slip_gaji.no_referensi', 'slip_gaji.periode_mulai', 'slip_gaji.periode_selesai', 'slip_gaji.total_gaji', 'slip_gaji.status', 'users.name as nama_karyawan', 'cabang_lokasi.nama_cabang')
            ->join('daftar_karyawan', 'slip_gaji.id_karyawan', '=', 'daftar_karyawan.id')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'daftar_karyawan.id_cabang')
            ->join('users', 'daftar_karyawan.id_user', '=', 'users.id')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('slip_gaji.id_cabang', $this->filter_id_cabang);
            })
            ->whereBetween('slip_gaji.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            // ->where(function ($q) {
            //     $q->whereBetween('slip_gaji.periode_mulai', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            //         ->orWhereBetween('slip_gaji.periode_selesai', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            // })
            ->orderBy('id', 'ASC')
            ->orderBy('status', 'DESC')
            ->paginate($this->lengthData);

        return view('livewire.admin.keuangan.slip-gaji', compact('data'));
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $karyawan = DB::table('daftar_karyawan')
                ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->select('daftar_karyawan.id', 'daftar_karyawan.id_cabang', 'daftar_karyawan.saldo_kasbon', 'users.name')
                ->where('daftar_karyawan.id', $this->id_karyawan)
                ->lockForUpdate()
                ->firstOrFail();

            $id_cabang = $karyawan->id_cabang;
            $no_referensi = $this->generateNoSlipGaji($id_cabang);

            $slip = ModelsSlipGaji::create([
                'id_cabang'       => $id_cabang,
                'no_referensi'    => $no_referensi,
                'periode_mulai'   => $this->periode_mulai,
                'periode_selesai' => $this->periode_selesai,
                'id_karyawan'     => $this->id_karyawan,
                'total_tunjangan' => $this->total_tunjangan,
                'total_potongan'  => $this->total_potongan,
                'total_gaji'      => $this->total_gaji,
                'status'          => $this->status,
            ]);

            // Tunjangan
            $tunjanganInsert = [];
            foreach ($this->tunjangans as $item) {
                if (!empty($item['nama_komponen']) && $item['jumlah'] > 0) {
                    $tunjanganInsert[] = [
                        'id_slip_gaji'  => $slip->id,
                        'nama_komponen' => $item['nama_komponen'],
                        'jumlah'        => $item['jumlah'],
                        'tipe'          => 'tunjangan',
                    ];
                }
            }
            if (count($tunjanganInsert)) {
                DB::table('detail_slip_gaji')->insert($tunjanganInsert);
            }

            // Potongan
            $potonganInsert = [];
            foreach ($this->potongans as $item) {
                if (!empty($item['nama_komponen']) && $item['jumlah'] > 0) {
                    $potonganInsert[] = [
                        'id_slip_gaji'  => $slip->id,
                        'nama_komponen' => $item['nama_komponen'],
                        'jumlah'        => $item['jumlah'],
                        'tipe'          => 'potongan',
                    ];
                }
            }
            if (count($potonganInsert)) {
                DB::table('detail_slip_gaji')->insert($potonganInsert);
            }

            // Cek apakah ada potongan kasbon
            $kasbonPotongan = collect($this->potongans)->firstWhere('nama_komponen', 'Kasbon');

            if ($kasbonPotongan && $kasbonPotongan['jumlah'] > 0) {
                // Ambil saldo kasbon dari DB
                $saldoKasbon = DB::table('daftar_karyawan')->where('id', $this->id_karyawan)->value('saldo_kasbon');

                if ($kasbonPotongan['jumlah'] > $saldoKasbon) {
                    throw new \Exception("Potongan kasbon melebihi saldo kasbon karyawan. Saldo tersedia: Rp" . number_format($saldoKasbon, 0, ',', '.'));
                }

                // Simpan pelunasan kasbon
                $kasbon = Kasbon::create([
                    'no_referensi'  => $this->generateNoKasbon($id_cabang), // <- optional jika ingin track juga
                    'id_karyawan'   => $this->id_karyawan,
                    'jumlah'        => $kasbonPotongan['jumlah'],
                    'keterangan'    => 'Dipotong dari gaji',
                    'tgl_pengajuan' => date('Y-m-d'),
                    'status'        => 'disetujui',
                    'tgl_disetujui' => date('Y-m-d'),
                    'id_disetujui'  => Auth::id(),
                    'metode_input'  => 'manual',
                    'kategori'      => 'pelunasan',
                    'created_at'    => $slip->created_at,
                    'updated_at'    => $slip->created_at,
                ]);

                // Update saldo_kasbon karyawan
                DB::table('daftar_karyawan')
                    ->where('id', $this->id_karyawan)
                    ->decrement('saldo_kasbon', $kasbonPotongan['jumlah']);

                CashOnBank::create([
                    'id_cabang'    => $id_cabang,
                    'tanggal'      => $kasbon->tgl_disetujui,
                    'no_referensi' => $kasbon->no_referensi,
                    'jenis'        => 'In',
                    'jumlah'       => $kasbonPotongan['jumlah'],
                    'keterangan'   => 'Kasbon (Pelunasan): ' . $karyawan->name . ' dipotong dari gaji',
                    'sumber_tabel' => 'Kasbon (Pelunasan)',
                    'id_sumber'    => $kasbon->id,
                ]);
            }

            $noRef  = 'SLGJ/' . $id_cabang . '/' . date('dmy');
            $today  = date('Y-m-d');
            $amount = $this->total_gaji;

            // Kunci baris target kalau sudah ada
            $row = DB::table('cash_on_bank')
                ->where('sumber_tabel', 'Slip Gaji')
                ->where('id_sumber', '1')
                ->where('jenis', 'Out')
                ->where('no_referensi', $noRef)
                ->lockForUpdate()
                ->first();

            // dd($noRef, $row);

            if ($row) {
                // UPDATE (akumulasi)
                DB::table('cash_on_bank')
                    ->where('id', $row->id)
                    ->update([
                        'id_cabang'  => $id_cabang,
                        'tanggal'    => $today,
                        'keterangan' => 'GAJI KARYAWAN',
                        'jumlah'     => DB::raw('jumlah + ' . $amount),
                        'updated_at' => now(),
                    ]);
            } else {
                // INSERT baru
                DB::table('cash_on_bank')->insert([
                    'sumber_tabel' => 'Slip Gaji',
                    'id_sumber'    => '1',
                    'jenis'        => 'Out',
                    'no_referensi' => $noRef,
                    'id_cabang'    => $id_cabang,
                    'tanggal'      => $today,
                    'jumlah'       => $amount,
                    'keterangan'   => 'GAJI KARYAWAN',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();
            $this->dispatchSwalSlipGaji($slip->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
            report($e);
        }
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing = true;
        $data = DB::table('slip_gaji')->where('id', $id)->first();
        $this->dataId = $id;
        $this->periode_mulai  = $data->periode_mulai;
        $this->periode_selesai  = $data->periode_selesai;
        $this->id_karyawan  = $data->id_karyawan;
        $this->total_tunjangan  = $data->total_tunjangan;
        $this->total_potongan  = $data->total_potongan;
        $this->total_gaji  = $data->total_gaji;
        $this->status  = $data->status;

        $this->tunjangans = DB::table('detail_slip_gaji')
            ->where('id_slip_gaji', $id)
            ->where('tipe', 'tunjangan')
            ->select('nama_komponen', 'jumlah')
            ->get()
            ->map(function ($item) {
                return [
                    'nama_komponen' => $item->nama_komponen,
                    'jumlah' => $item->jumlah,
                ];
            })->toArray();

        $this->potongans = DB::table('detail_slip_gaji')
            ->where('id_slip_gaji', $id)
            ->where('tipe', 'potongan')
            ->select('nama_komponen', 'jumlah')
            ->get()
            ->map(function ($item) {
                return [
                    'nama_komponen' => $item->nama_komponen,
                    'jumlah' => $item->jumlah,
                ];
            })->toArray();
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {

            $daftar_karyawan = DB::table('daftar_karyawan')
                ->select('daftar_karyawan.id_cabang', 'users.name')
                ->join('users', 'users.id', 'daftar_karyawan.id_user')
                ->where('daftar_karyawan.id', $this->id_karyawan)
                ->first();
            $id_cabang = $daftar_karyawan->id_cabang;

            // Ambil data slip lama
            $slipLama = ModelsSlipGaji::findOrFail($this->dataId);

            // Ambil potongan kasbon lama
            $kasbonLamaData = DB::table('kasbon')
                ->select('id', 'no_referensi', 'jumlah')
                ->where('id_karyawan', $this->id_karyawan)
                ->where('kategori', 'pelunasan')
                ->where('keterangan', 'Dipotong dari gaji')
                ->where('created_at', $slipLama->created_at)
                ->first();

            $kasbonLamaId = $kasbonLamaData->id ?? null;
            $kasbonLamaNoReferensi = $kasbonLamaData->no_referensi ?? null;
            $kasbonLamaJumlah = $kasbonLamaData->jumlah ?? 0;

            // Ambil potongan kasbon baru dari form
            $kasbonBaru = collect($this->potongans)->firstWhere('nama_komponen', 'Kasbon');
            $jumlahBaru = $kasbonBaru['jumlah'] ?? 0;

            // ==== Kasbon dihapus ====
            if ($kasbonLamaId && $jumlahBaru == 0) {
                // Hapus CashOnBank
                DB::table('cash_on_bank')
                    ->where('no_referensi', $kasbonLamaNoReferensi)
                    ->where('id_sumber', $kasbonLamaId)
                    ->delete();

                // Hapus Kasbon
                DB::table('kasbon')
                    ->where('id', $kasbonLamaId)
                    ->delete();

                // Kembalikan saldo
                DB::table('daftar_karyawan')
                    ->where('id', $this->id_karyawan)
                    ->increment('saldo_kasbon', $kasbonLamaJumlah);
            }

            // ==== Kasbon baru ditambah / diubah ====
            if ($jumlahBaru > 0) {
                // Restore saldo lama kalau ada kasbon sebelumnya
                if ($kasbonLamaId) {
                    // dd($kasbonLamaId, $kasbonLamaNoReferensi);
                    DB::table('daftar_karyawan')
                        ->where('id', $this->id_karyawan)
                        ->increment('saldo_kasbon', $kasbonLamaJumlah);

                    // Hapus kasbon lama & cash_on_bank lama
                    DB::table('cash_on_bank')
                        ->where('no_referensi', $kasbonLamaNoReferensi)
                        ->where('id_sumber', $kasbonLamaId)
                        ->delete();

                    DB::table('kasbon')
                        ->where('id', $kasbonLamaId)
                        ->delete();
                }

                // Cek saldo
                $saldoKasbon = DB::table('daftar_karyawan')
                    ->where('id', $this->id_karyawan)
                    ->value('saldo_kasbon');

                if ($jumlahBaru > $saldoKasbon) {
                    throw new \Exception("Potongan kasbon melebihi saldo kasbon karyawan.");
                }

                // Insert kasbon baru
                $kasbonBaru = Kasbon::create([
                    'no_referensi'  => $this->generateNoKasbon($id_cabang),
                    'id_karyawan'   => $this->id_karyawan,
                    'jumlah'        => $jumlahBaru,
                    'keterangan'    => 'Dipotong dari gaji',
                    'tgl_pengajuan' => $slipLama->created_at,
                    'status'        => 'disetujui',
                    'tgl_disetujui' => $slipLama->created_at,
                    'id_disetujui'  => Auth::id(),
                    'metode_input'  => 'manual',
                    'kategori'      => 'pelunasan',
                    'created_at'    => $slipLama->created_at,
                    'updated_at'    => now(),
                ]);

                // Update saldo
                DB::table('daftar_karyawan')
                    ->where('id', $this->id_karyawan)
                    ->decrement('saldo_kasbon', $jumlahBaru);

                // Insert CashOnBank baru
                DB::table('cash_on_bank')->insert([
                    'id_cabang'    => $id_cabang,
                    'tanggal'      => $slipLama->created_at,
                    'no_referensi' => $kasbonBaru->no_referensi,
                    'jenis'        => 'In',
                    'jumlah'       => $jumlahBaru,
                    'keterangan'   => 'Kasbon (Pelunasan): ' . $daftar_karyawan->name . ' dipotong dari gaji',
                    'sumber_tabel' => 'Kasbon (Pelunasan)',
                    'id_sumber'    => $kasbonBaru->id,
                ]);
            }

            // ==== Update slip gaji ====
            ModelsSlipGaji::findOrFail($this->dataId)->update([
                'periode_mulai'   => $this->periode_mulai,
                'periode_selesai' => $this->periode_selesai,
                'total_tunjangan' => $this->total_tunjangan,
                'total_potongan'  => $this->total_potongan,
                'total_gaji'      => $this->total_gaji,
                'status'          => $this->status,
            ]);

            // --- Penyesuaian CashOnBank: GAJI KARYAWAN (Out) ---
            $delta = $this->total_gaji - $slipLama->total_gaji;
            if ($delta != 0) {
                $noRef = 'SLGJ/' . $id_cabang . '/' . date('dmy');
                $today = date('Y-m-d');

                $row = DB::table('cash_on_bank')
                    ->where('sumber_tabel', 'Slip Gaji')
                    ->where('id_sumber', '1')
                    ->where('jenis', 'Out')
                    ->where('no_referensi', $noRef)
                    ->lockForUpdate()
                    ->first();

                if ($row) {
                    DB::table('cash_on_bank')
                        ->where('id', $row->id)
                        ->update([
                            'id_cabang'  => $id_cabang,
                            'tanggal'    => $today,
                            'keterangan' => 'GAJI KARYAWAN',
                            'jumlah'     => DB::raw('jumlah + ' . $delta),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('cash_on_bank')->insert([
                        'sumber_tabel' => 'Slip Gaji',
                        'id_sumber'    => '1',
                        'jenis'        => 'Out',
                        'no_referensi' => $noRef,
                        'id_cabang'    => $id_cabang,
                        'tanggal'      => $today,
                        'jumlah'       => $delta,
                        'keterangan'   => 'GAJI KARYAWAN',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            // Reset detail slip & insert ulang
            DB::table('detail_slip_gaji')->where('id_slip_gaji', $this->dataId)->delete();

            if (!empty($this->tunjangans)) {
                DB::table('detail_slip_gaji')->insert(
                    collect($this->tunjangans)->filter(fn($t) => !empty($t['nama_komponen']) && $t['jumlah'] > 0)
                        ->map(fn($t) => [
                            'id_slip_gaji'  => $this->dataId,
                            'nama_komponen' => $t['nama_komponen'],
                            'jumlah'        => $t['jumlah'],
                            'tipe'          => 'tunjangan',
                        ])->toArray()
                );
            }

            if (!empty($this->potongans)) {
                DB::table('detail_slip_gaji')->insert(
                    collect($this->potongans)->filter(fn($p) => !empty($p['nama_komponen']) && $p['jumlah'] > 0)
                        ->map(fn($p) => [
                            'id_slip_gaji'  => $this->dataId,
                            'nama_komponen' => $p['nama_komponen'],
                            'jumlah'        => $p['jumlah'],
                            'tipe'          => 'potongan',
                        ])->toArray()
                );
            }

            DB::commit();

            $this->dispatchSwalSlipGaji($this->dataId);
            $this->resetInputFields();
            $this->tunjangans = [];
            $this->potongans = [];
            $this->dataId = null;
            $this->isEditing = false;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
            report($e);
        }
    }

    public function deleteConfirm($id)
    {
        $this->dataId = $id;
        $this->dispatch('swal:confirm', [
            'type'      => 'warning',
            'message'   => 'Are you sure?',
            'text'      => 'If you delete the data, it cannot be restored!'
        ]);
    }

    public function delete()
    {
        DB::beginTransaction();
        try {
            $slip = ModelsSlipGaji::findOrFail($this->dataId);

            // Ambil kasbon lama (jika ada) berdasarkan slip ini
            $kasbonLamaData = DB::table('kasbon')
                ->select('id', 'no_referensi', 'jumlah')
                ->where('id_karyawan', $slip->id_karyawan)
                ->where('kategori', 'pelunasan')
                ->where('keterangan', 'Dipotong dari gaji')
                ->where('created_at', $slip->created_at)
                ->first();

            if ($kasbonLamaData) {
                $kasbonLamaId     = $kasbonLamaData->id;
                $kasbonLamaJumlah = $kasbonLamaData->jumlah;
                $kasbonLamaNoReferensi = $kasbonLamaData->no_referensi;

                // Hapus CashOnBank yang terkait kasbon ini
                DB::table('cash_on_bank')
                    ->where('no_referensi', $kasbonLamaNoReferensi)
                    ->where('id_sumber', $kasbonLamaId)
                    ->delete();

                // Hapus kasbon
                DB::table('kasbon')
                    ->where('id', $kasbonLamaId)
                    ->delete();

                // Kembalikan saldo kasbon karyawan
                DB::table('daftar_karyawan')
                    ->where('id', $slip->id_karyawan)
                    ->increment('saldo_kasbon', $kasbonLamaJumlah);
            }

            // Hapus detail slip gaji
            DB::table('detail_slip_gaji')
                ->where('id_slip_gaji', $slip->id)
                ->delete();

            // --- Revert CashOnBank: GAJI KARYAWAN (Out) untuk slip ini ---
            $noRef = 'SLGJ/' . $slip->id_cabang . '/' . date('dmy');
            $today = date('Y-m-d');

            // Kunci baris target kalau ada
            $row = DB::table('cash_on_bank')
                ->where('sumber_tabel', 'Slip Gaji')
                ->where('id_sumber', '1')
                ->where('jenis', 'Out')
                ->where('no_referensi', $noRef)
                ->lockForUpdate()
                ->first();

            if ($row) {
                // Kurangi akumulasi dengan total gaji slip yang dihapus
                $newJumlah = (int)$row->jumlah - (int)$slip->total_gaji;

                if ($newJumlah === 0) {
                    // Optional: kalau nol, hapus barisnya biar bersih
                    DB::table('cash_on_bank')->where('id', $row->id)->delete();
                } else {
                    DB::table('cash_on_bank')
                        ->where('id', $row->id)
                        ->update([
                            'id_cabang'  => $slip->id_cabang,
                            'tanggal'    => $today,
                            'keterangan' => 'GAJI KARYAWAN',
                            'jumlah'     => $newJumlah,
                            'updated_at' => now(),
                        ]);
                }
            }

            // Hapus slip gaji utama
            $slip->delete();

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
            report($e);
        }
    }

    public function addTunjangan()
    {
        $this->initSelect2();
        $this->tunjangans[] = ['nama_komponen' => '', 'jumlah' => 0];
    }

    public function removeTunjangan($index)
    {
        $this->initSelect2();
        unset($this->tunjangans[$index]);
        $this->tunjangans = array_values($this->tunjangans); // reindex
    }

    public function addPotongan()
    {
        $this->initSelect2();
        $this->potongans[] = ['nama_komponen' => '', 'jumlah' => 0];
    }

    public function removePotongan($index)
    {
        $this->initSelect2();
        unset($this->potongans[$index]);
        $this->potongans = array_values($this->potongans); // reindex
    }

    public function updatedIdKaryawan()
    {
        $this->total_kasbon = DB::table('daftar_karyawan')
            ->where('id', $this->id_karyawan)
            ->value('saldo_kasbon');

        // Hapus semua potongan 'Kasbon' dulu
        $this->potongans = collect($this->potongans)
            ->reject(fn($item) => $item['nama_komponen'] === 'Kasbon')
            ->values() // reindex array
            ->toArray();

        // Tambahkan potongan 'Kasbon' jika ada saldo
        if ($this->total_kasbon > 0) {
            $this->potongans[] = [
                'nama_komponen' => 'Kasbon',
                'jumlah' => $this->total_kasbon,
            ];
        }
    }

    public function review()
    {
        // 
        $this->initSelect2();
        $this->validate([
            'id_karyawan' => 'required',
        ], [
            'id_karyawan.required' => 'Karyawan wajib dipilih.',
        ]);

        $this->nama_karyawan = $this->karyawans->firstWhere('id', $this->id_karyawan)->name ?? '';

        $this->komisi_transaksi = DB::table('transaksi')
            ->select('transaksi.no_transaksi', 'transaksi.tanggal', 'detail_transaksi.komisi_nominal')
            ->join('detail_transaksi', 'transaksi.id', '=', 'detail_transaksi.id_transaksi')
            ->where('detail_transaksi.id_karyawan', $this->id_karyawan)
            ->whereBetween('transaksi.tanggal', [Carbon::parse($this->periode_mulai)->startOfDay(), Carbon::parse($this->periode_selesai)->endOfDay()])
            ->whereIn('transaksi.status', ['2', '3'])
            ->get();

        // $this->total_kasbon = DB::table('kasbon')
        //     ->where('id_karyawan', $this->id_karyawan)
        //     ->whereBetween('tgl_disetujui', [
        //         $this->periode_mulai,
        //         $this->periode_selesai
        //     ])
        //     ->where('status', 'disetujui')
        //     ->sum('jumlah');

        $this->total_komisi = $this->komisi_transaksi->sum('komisi_nominal');

        $this->total_tunjangan = collect($this->tunjangans)->sum('jumlah');
        $this->total_potongan = collect($this->potongans)
            ->reject(fn($item) => $item['nama_komponen'] === 'Kasbon')
            ->sum('jumlah');

        $this->total_gaji = $this->total_komisi + $this->total_tunjangan - $this->total_potongan;

        // dd($this->komisi_transaksi);

        $this->dispatch('open-review-modal');
    }

    public function updatingLengthData()
    {
        $this->resetPage();
    }

    private function searchResetPage()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }

        $this->previousSearchTerm = $this->searchTerm;
    }

    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type'      => $type,
            'message'   => $message,
            'text'      => $text
        ]);

        $this->resetInputFields();
    }

    public function isEditingMode($mode)
    {
        $this->initSelect2();
        $this->isEditing = $mode;
    }

    private function resetInputFields()
    {
        $this->id_karyawan         = '';
        $this->total_tunjangan     = '0';
        $this->total_potongan      = '0';
        $this->total_gaji          = '0';
        $this->status              = 'final';
    }

    public function cancel()
    {
        $this->initSelect2();
        $this->isEditing       = false;
        // $this->resetInputFields();
    }

    public function cancelReview()
    {
        $this->initSelect2();
        // $this->resetInputFields();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function updated()
    {
        $this->getKaryawans();
        $this->initSelect2();
    }

    private function dispatchSwalSlipGaji($idSlipGaji)
    {
        $this->dispatch('swal:slipgaji', [
            'idSlipGaji'  => Crypt::encrypt($idSlipGaji), // enkripsi ID,
            'message'     => 'Berhasil disimpan!',
            'text'        => 'Apakah kamu ingin mencetak slip gaji sekarang?',
        ]);
        $this->initSelect2();
    }

    public function generateNoSlipGaji($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = SlipGajiCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = SlipGajiCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "SLGJ/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }

    public function generateNoKasbon($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = KasbonCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = KasbonCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "KBON/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }
}
