<?php

namespace App\Livewire\Keuangan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use App\Models\KasbonCounter;
use App\Models\DaftarKaryawan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use App\Models\Kasbon as ModelsKasbon;

class Kasbon extends Component
{
    use WithPagination;
    #[Title('Kasbon')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_karyawan'         => 'required',
        'jumlah'              => 'required',
        'tgl_pengajuan'       => 'required',
        'status'              => '',
        'tgl_disetujui'       => '',
        'id_disetujui'        => '',
        'metode_input'        => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $karyawans, $cabangs;
    public $data_karyawan;
    public $id_karyawan, $jumlah, $keterangan, $tgl_pengajuan, $status, $tgl_disetujui, $id_disetujui, $metode_input, $kategori;
    public $filter_id_cabang = null;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        $this->cabangs = $this->globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
        $this->getKaryawans($this->filter_id_cabang);

        $this->id_disetujui        = Auth::user()->id;
        $this->id_karyawan         = '';

        $this->resetInputFields();
    }

    private function getKaryawans($id_cabang)
    {
        $this->karyawans = DB::table('daftar_karyawan')->select('daftar_karyawan.id', 'name')
            ->where('daftar_karyawan.id_cabang', $id_cabang)
            ->join('users', 'users.id', 'daftar_karyawan.id_user')
            ->get();
    }

    public function updatedFilterIdCabang()
    {
        $this->id_karyawan = '';
        $this->getKaryawans($this->filter_id_cabang);
    }

    public function refreshData()
    {
        $this->render();
        $this->initSelect2();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = collect(); // default: kosong

        if (!empty($this->id_karyawan)) {
            $data = DB::table('kasbon')
                ->select('kasbon.id', 'kasbon.no_referensi', 'u1.name as nama_karyawan', 'kasbon.jumlah', 'kasbon.keterangan', 'kasbon.tgl_pengajuan', 'kasbon.status', 'kasbon.metode_input', 'u2.name as disetujui_oleh', DB::raw(" SUM(CASE WHEN kasbon.kategori = 'pelunasan' THEN -kasbon.jumlah ELSE kasbon.jumlah END) OVER (PARTITION BY kasbon.id_karyawan ORDER BY kasbon.no_referensi, kasbon.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW ) AS balancing "), 'kasbon.kategori')
                ->leftJoin('daftar_karyawan', 'kasbon.id_karyawan', '=', 'daftar_karyawan.id')
                ->leftJoin('users as u1', 'daftar_karyawan.id_user', '=', 'u1.id')
                ->leftJoin('users as u2', 'kasbon.id_disetujui', '=', 'u2.id')
                ->where('kasbon.id_karyawan', $this->id_karyawan)
                ->whereBetween('kasbon.tgl_pengajuan', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->where(function ($query) use ($search) {
                    $query->where('tgl_pengajuan', 'LIKE', $search)
                        ->orWhere('u2.name', 'LIKE', $search);
                })
                ->orderBy('id', 'ASC')
                ->paginate($this->lengthData);

            $this->dataKaryawan();
        }

        return view('livewire.keuangan.kasbon', compact('data'));
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Ambil data karyawan & kunci baris
            $karyawan = DaftarKaryawan::join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->select('daftar_karyawan.id', 'daftar_karyawan.id_cabang', 'daftar_karyawan.saldo_kasbon', 'users.name')
                ->where('daftar_karyawan.id', $this->id_karyawan)
                ->lockForUpdate()
                ->firstOrFail();

            $id_cabang  = $karyawan->id_cabang;
            $saldo_lama = $karyawan->saldo_kasbon;

            // 🔥 Generate nomor kasbon dulu
            $no_referensi = $this->generateNoKasbon($id_cabang);

            // Simpan header kasbon
            $kasbon = ModelsKasbon::create([
                'no_referensi'   => $no_referensi,
                'id_karyawan'    => $this->id_karyawan,
                'jumlah'         => $this->jumlah,
                'keterangan'     => $this->keterangan,
                'tgl_pengajuan'  => $this->tgl_pengajuan,
                'status'         => $this->status,        // pending | disetujui | ditolak
                'tgl_disetujui'  => $this->tgl_disetujui, // bisa null kalau belum disetujui
                'id_disetujui'   => $this->id_disetujui,  // user admin yang setujui (nullable)
                'metode_input'   => $this->metode_input,  // manual | pengajuan
                'kategori'       => $this->kategori,      // pengajuan | pelunasan
            ]);

            // Hanya proses saldo & CashOnBank kalau SUDAH DISETUJUI
            if ($this->status === 'disetujui') {
                // Validasi pelunasan tidak boleh melebihi saldo
                if ($this->kategori === 'pelunasan' && $this->jumlah > $saldo_lama) {
                    throw new \Exception("Jumlah pelunasan melebihi saldo yang tersedia.");
                }

                // Update saldo_kasbon karyawan
                if ($this->kategori === 'pengajuan') {
                    $karyawan->saldo_kasbon = $saldo_lama + $this->jumlah;
                } else { // pelunasan
                    $karyawan->saldo_kasbon = $saldo_lama - $this->jumlah;
                }
                $karyawan->save();

                // Tentukan jenis arus kas
                if ($this->kategori === 'pengajuan') {
                    $jenisCOB = 'Out';
                    $sumber_tabel = 'Kasbon (Pengajuan)';
                } else {
                    $jenisCOB = 'In';
                    $sumber_tabel = 'Kasbon (Pelunasan)';
                }

                // Tentukan tanggal transaksi kas (utamakan tgl_disetujui)
                $tanggalKas = $this->tgl_disetujui ?: ($this->tgl_pengajuan ?: date('Y-m-d'));

                // Keterangan yang informatif
                $namaKaryawan = $karyawan->name ?? 'Karyawan';
                $namaAdmin    = Auth::user()->name ?? '-';
                $kategoriText = ucfirst($this->kategori); // Pengajuan / Pelunasan

                if ($this->metode_input === 'manual') {
                    $metodeText = "diinput oleh {$namaAdmin}";
                } else {
                    $metodeText = "diajukan sendiri";
                }

                $keteranganCOB = "Kasbon ({$kategoriText}): {$namaKaryawan} {$metodeText}";

                // Catat ke CashOnBank
                CashOnBank::create([
                    'id_cabang'    => $id_cabang,
                    'tanggal'      => $tanggalKas,
                    'no_referensi' => $no_referensi,
                    'jenis'        => $jenisCOB,          // Out jika pengajuan, In jika pelunasan
                    'jumlah'       => $this->jumlah,
                    'sumber_tabel' => $sumber_tabel,
                    'keterangan'   => $keteranganCOB,
                    'id_sumber'    => $kasbon->id,        // refer ke kasbon
                ]);
            }

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing        = true;
        $data = ModelsKasbon::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_karyawan      = $data->id_karyawan;
        $this->jumlah           = $data->jumlah;
        $this->keterangan       = $data->keterangan;
        $this->tgl_pengajuan    = $data->tgl_pengajuan;
        $this->status           = $data->status;
        $this->tgl_disetujui    = $data->tgl_disetujui;
        $this->metode_input     = $data->metode_input;
        $this->kategori         = $data->kategori;
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Ambil kasbon lama (lock)
            $kasbon = DB::table('kasbon')
                ->select('id', 'id_karyawan', 'jumlah', 'kategori', 'status', 'no_referensi', 'tgl_pengajuan', 'tgl_disetujui', 'metode_input')
                ->where('id', $this->dataId)
                ->lockForUpdate()
                ->first();

            if (!$kasbon) {
                throw new \Exception("Data kasbon tidak ditemukan.");
            }

            // Ambil karyawan + user name (lock karyawan)
            $karyawan = DB::table('daftar_karyawan')
                ->select('id', 'id_cabang', 'saldo_kasbon', 'id_user')
                ->where('id', $this->id_karyawan)
                ->lockForUpdate()
                ->first();

            if (!$karyawan) {
                throw new \Exception("Data karyawan tidak ditemukan.");
            }

            $user = DB::table('users')
                ->select('name')
                ->where('id', $karyawan->id_user)
                ->first();

            $saldoBaru = $karyawan->saldo_kasbon;

            $oldApproved = $kasbon->status === 'disetujui';
            $newApproved = $this->status === 'disetujui';

            // 1) Reversal efek lama (hanya jika dulu disetujui)
            if ($oldApproved) {
                if ($kasbon->kategori === 'pengajuan') {
                    $saldoBaru -= $kasbon->jumlah; // dulu nambah, sekarang dikembalikan
                } else { // pelunasan
                    $saldoBaru += $kasbon->jumlah; // dulu ngurangin, sekarang dibalikin
                }
            }

            // 2) Validasi + apply efek baru (hanya jika sekarang disetujui)
            if ($newApproved) {
                if ($this->kategori === 'pelunasan' && $this->jumlah > $saldoBaru) {
                    throw new \Exception("Jumlah pelunasan melebihi saldo kasbon.");
                }
                if ($this->kategori === 'pengajuan') {
                    $saldoBaru += $this->jumlah;
                } else { // pelunasan
                    $saldoBaru -= $this->jumlah;
                }
            }


            // ✅ 3) Cek saldo tidak boleh negatif
            if ($saldoBaru < 0) {
                DB::rollBack();
                throw new \Exception('Saldo kasbon tidak boleh negatif.');
            }

            // Simpan saldo bila berubah
            if ($saldoBaru != $karyawan->saldo_kasbon) {
                DB::table('daftar_karyawan')
                    ->where('id', $karyawan->id)
                    ->update(['saldo_kasbon' => $saldoBaru]);
            }

            // Update header kasbon
            DB::table('kasbon')->where('id', $kasbon->id)->update([
                'id_karyawan'   => $this->id_karyawan,
                'jumlah'        => $this->jumlah,
                'keterangan'    => $this->keterangan,
                'tgl_pengajuan' => $this->tgl_pengajuan,
                'status'        => $this->status,
                'tgl_disetujui' => $this->tgl_disetujui,
                'id_disetujui'  => $this->id_disetujui,
                'metode_input'  => $this->metode_input,
                'kategori'      => $this->kategori,
                // no_referensi dibiarin tetap (identitas transaksi)
            ]);

            // 3) Sinkronisasi CashOnBank berdasarkan status baru
            $jenisCOB   = $this->kategori === 'pengajuan' ? 'Out' : 'In';
            $tanggalKas = $this->tgl_disetujui ?: ($this->tgl_pengajuan ?: date('Y-m-d'));

            $namaKaryawan = $user->name ?? 'Karyawan';
            $namaAdmin    = Auth::user()->name ?? '-';
            $kategoriText = ucfirst($this->kategori);
            $metodeText   = $this->metode_input === 'manual'
                ? "diinput oleh {$namaAdmin}"
                : "diajukan sendiri melalui sistem";

            $keteranganCOB = "Kasbon ({$kategoriText}): {$namaKaryawan} {$metodeText}";

            // Baris CashOnBank yang relevan (pakai no_referensi + id_sumber)
            $cobQuery = DB::table('cash_on_bank')
                ->where('no_referensi', $kasbon->no_referensi)
                ->where('id_sumber', $kasbon->id);

            $cobExists = $cobQuery->exists();

            if ($newApproved) {
                if ($cobExists) {
                    // Update arus kas yang sudah ada
                    $cobQuery->update([
                        'id_cabang'  => $karyawan->id_cabang,
                        'tanggal'    => $tanggalKas,
                        'jenis'      => $jenisCOB,
                        'jumlah'     => $this->jumlah,
                        'keterangan' => $keteranganCOB,
                    ]);
                } else {
                    // Insert baru
                    DB::table('cash_on_bank')->insert([
                        'id_cabang'    => $karyawan->id_cabang,
                        'tanggal'      => $tanggalKas,
                        'no_referensi' => $kasbon->no_referensi,
                        'jenis'        => $jenisCOB,
                        'jumlah'       => $this->jumlah,
                        'keterangan'   => $keteranganCOB,
                        'id_sumber'    => $kasbon->id,
                        // kalau kamu punya kolom 'sumber', isi 'Kasbon'
                        // 'sumber'    => 'Kasbon',
                    ]);
                }
            } else {
                // Jika status bukan disetujui → pastikan baris arus kas dihapus
                if ($cobExists) {
                    $cobQuery->delete();
                }
            }

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
            $this->dataId = null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
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
            // Ambil kasbon (kunci baris)
            $kasbon = DB::table('kasbon')
                ->select('id', 'id_karyawan', 'jumlah', 'kategori', 'status', 'no_referensi')
                ->where('id', $this->dataId)
                ->lockForUpdate()
                ->first();

            if (!$kasbon) {
                throw new \Exception('Data kasbon tidak ditemukan.');
            }

            // Ambil karyawan (kunci baris)
            $karyawan = DB::table('daftar_karyawan')
                ->select('id', 'saldo_kasbon')
                ->where('id', $kasbon->id_karyawan)
                ->lockForUpdate()
                ->first();

            if (!$karyawan) {
                throw new \Exception('Data karyawan tidak ditemukan.');
            }

            $saldoBaru = $karyawan->saldo_kasbon;

            // Reversal efek hanya kalau dulu SUDAH DISETUJUI
            if ($kasbon->status === 'disetujui') {
                if ($kasbon->kategori === 'pengajuan') {
                    // Dulu saldo ditambah, sekarang balikkan
                    $saldoBaru -= $kasbon->jumlah;
                } else { // pelunasan
                    // Dulu saldo dikurang, sekarang balikkan
                    $saldoBaru += $kasbon->jumlah;
                }

                // Hapus baris CashOnBank yang terkait kasbon ini
                DB::table('cash_on_bank')
                    ->where('no_referensi', $kasbon->no_referensi)
                    ->where('id_sumber', $kasbon->id)
                    ->delete();
            }

            // (Opsional) proteksi saldo tidak negatif
            if ($saldoBaru < 0) {
                throw new \Exception('Saldo kasbon tidak boleh negatif setelah penghapusan.');
            }

            // Simpan saldo bila berubah
            if ($saldoBaru != $karyawan->saldo_kasbon) {
                DB::table('daftar_karyawan')
                    ->where('id', $karyawan->id)
                    ->update(['saldo_kasbon' => $saldoBaru]);
            }

            // Hapus kasbon
            DB::table('kasbon')->where('id', $kasbon->id)->delete();

            DB::commit();

            $this->dispatchAlert('success', 'Success!', 'Data kasbon berhasil dihapus dan saldo diperbarui.');
            $this->initSelect2();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function isEditingMode($mode, $kategori)
    {
        $this->initSelect2();
        $this->isEditing = $mode;
        $this->kategori  = $kategori;
    }

    private function resetInputFields()
    {
        $this->jumlah              = '0';
        $this->keterangan          = '-';
        $this->tgl_pengajuan       = date('Y-m-d H:i:s');
        $this->tgl_disetujui       = date('Y-m-d H:i:s');
        $this->status              = 'disetujui';
        $this->metode_input        = 'manual';
    }

    public function cancel()
    {
        $this->initSelect2();
        $this->isEditing       = false;
        $this->resetInputFields();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function dataKaryawan()
    {
        $this->data_karyawan = DB::table('daftar_karyawan')
            ->select('cabang_lokasi.nama_cabang', 'users.name', 'daftar_karyawan.no_telp', 'daftar_karyawan.saldo_kasbon')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'daftar_karyawan.id_cabang')
            ->join('users', 'users.id', 'daftar_karyawan.id_user')
            ->where('daftar_karyawan.id', $this->id_karyawan)
            ->first();
        // dd($this->data_karyawan);
    }

    public function updated()
    {
        $this->initSelect2();
        // $this->getKaryawans($this->filter_id_cabang);
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
