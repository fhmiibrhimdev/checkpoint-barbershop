<?php

namespace App\Livewire\Admin\Keuangan;

use Carbon\Carbon;
use App\Models\Kas;
use Livewire\Component;
use App\Models\Transaksi;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use App\Models\PiutangCounter;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use App\Models\Piutang as ModelsPiutang;

class Piutang extends Component
{
    use WithPagination;
    #[Title('Admin | Piutang')]

    protected $listeners = [
        'delete'
    ];
    protected $rules = [
        'tanggal_bayar' => 'required',
        'jumlah_bayar' => 'required',
        'id_metode_pembayaran' => 'required',
    ];
    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;
    public $pembayarans, $transaksis, $cabangs;
    public $totalOmset, $totalTunai, $totalTransfer, $totalPembayaran, $totalPiutang;
    public $id_cabang, $id_transaksi, $dataId, $tanggal_bayar, $jumlah_bayar, $keterangan, $id_metode_pembayaran;
    public $filter_id_cabang;
    public $sisa_piutang;

    public function mount(GlobalDataService $globalDataService, $id_transaksi = null)
    {
        $this->pembayarans = $globalDataService->getMetodePembayaran();
        $this->id_cabang = Auth::user()->id_cabang;
        try {
            $this->id_transaksi = Crypt::decrypt($id_transaksi);
            // $columns = Schema::getColumnListing('transaksi'); // Ambil semua kolom
            // $columns = array_diff($columns, ['created_at', 'updated_at']); // Kecualikan kolom tertentu
            // dd($this->id_transaksi);

            $this->transaksis = DB::table('transaksi as t')
                // ->select($columns)
                ->select('t.no_transaksi', 't.tanggal', 'daftar_pelanggan.nama_pelanggan', 't.catatan', 't.total_akhir', 't.jumlah_dibayarkan', 't.kembalian')
                ->join('daftar_pelanggan', 'daftar_pelanggan.id', '=', 't.id_pelanggan')
                ->where('t.id', $this->id_transaksi)
                ->first();

            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->filter_id_cabang = Auth::user()->id_cabang;
            $this->id_transaksi = null;
        }

        // dd($this->transaksis);
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        if ($this->id_transaksi == null) {
            $data = DB::table('transaksi')
                ->select('transaksi.id', 'transaksi.tanggal', 'piutang.no_referensi', 'transaksi.no_transaksi', 'nama_pelanggan', 'transaksi.total_akhir', 'transaksi.jumlah_dibayarkan', 'transaksi.kembalian', DB::raw('(SELECT SUM(piutang.jumlah_bayar) FROM piutang WHERE piutang.id_transaksi = transaksi.id) as total_bayar'), 'nama_cabang')
                ->join('piutang', 'piutang.id_transaksi', '=', 'transaksi.id')
                ->join('daftar_pelanggan', 'daftar_pelanggan.id', '=', 'transaksi.id_pelanggan')
                ->join('cabang_lokasi', 'cabang_lokasi.id', 'transaksi.id_cabang')
                ->where(function ($query) use ($search) {
                    $query->where('piutang.tanggal_bayar', 'LIKE', $search)
                        ->orWhere('piutang.jumlah_bayar', 'LIKE', $search)
                        ->orWhere('piutang.keterangan', 'LIKE', $search);
                })
                ->when($this->filter_id_cabang, function ($query) {
                    $query->where('transaksi.id_cabang', $this->filter_id_cabang);
                })
                ->distinct()
                ->paginate($this->lengthData);

            // dd($data);

            return view('livewire.admin.keuangan.piutang-all', compact('data'));
        } else {
            $data = DB::table('piutang')
                ->select('piutang.*', 'kategori_pembayaran.nama_kategori')
                ->join('kategori_pembayaran', 'piutang.id_metode_pembayaran', '=', 'kategori_pembayaran.id')
                ->where(function ($query) use ($search) {
                    $query->where('tanggal_bayar', 'LIKE', $search);
                })
                ->where('id_transaksi', $this->id_transaksi)
                ->paginate($this->lengthData);

            $this->sisa_piutang = DB::table('transaksi')
                ->select('kembalian', 'status')
                ->where('id', $this->id_transaksi)
                ->first();

            return view('livewire.transaksi.piutang', compact('data'));
        }
    }

    public function store()
    {
        $this->validate();

        // Pengecekan sebelum insert
        $transaksi = Transaksi::findOrFail($this->id_transaksi);
        if (($transaksi->kembalian + $this->jumlah_bayar) > 0) {
            $this->dispatchAlert('error', 'Gagal!', 'Jumlah bayar melebihi sisa piutang.');
            return; // Hentikan proses jika kondisi gagal
        }

        DB::beginTransaction();
        try {
            $no_referensi = $this->generateNoPiutang($transaksi->id_cabang);

            // Saat pelanggan bayar piutang:
            ModelsPiutang::create([
                'no_referensi'         => $no_referensi, // <- baru
                'id_transaksi'         => $this->id_transaksi, // Pastikan id_transaksi disimpan
                'tanggal_bayar'        => $this->tanggal_bayar,
                'jumlah_bayar'         => $this->jumlah_bayar,
                'keterangan'           => $this->keterangan,
                'id_metode_pembayaran' => $this->id_metode_pembayaran,
            ]);

            // Hitung total dibayar:
            $total_bayar = $transaksi->jumlah_dibayarkan +
                ModelsPiutang::where('id_transaksi', $this->id_transaksi)->sum('jumlah_bayar');

            if ($total_bayar >= $transaksi->total_akhir) {
                $transaksi->update([
                    'status' => '3', // lunas
                    'kembalian' => 0,
                ]);
            } else {
                $transaksi->update([
                    'status' => '2', // belum lunas
                    'kembalian' => $transaksi->kembalian + $this->jumlah_bayar
                ]);
            }

            $this->syncSetoranTransferHarian();
            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $data = ModelsPiutang::findOrFail($id);
        $this->dataId = $id;
        $this->tanggal_bayar  = $data->tanggal_bayar;
        $this->jumlah_bayar   = $data->jumlah_bayar;
        $this->keterangan      = $data->keterangan;
        $this->id_metode_pembayaran = $data->id_metode_pembayaran;
    }

    public function update()
    {
        $this->validate();

        // Pengecekan sebelum insert
        $transaksi = Transaksi::findOrFail($this->id_transaksi);
        DB::beginTransaction();
        try {
            // Saat pelanggan bayar piutang:
            $piutang = ModelsPiutang::findOrFail($this->dataId);
            $jumlah_bayar_lama = $piutang->jumlah_bayar;

            // Jumlah Dibayarkan : 10.000
            // Jumlah Kembalian: -1.500
            // Input Lama Jumlah Bayar: 500
            // Input Baru Jumlah Bayar: 250

            if ($this->jumlah_bayar > $jumlah_bayar_lama) { // 2000 > 500
                // Jika jumlah bayar baru lebih besar dari jumlah bayar lama
                $selisih = $this->jumlah_bayar - $jumlah_bayar_lama;  // 2000 - 500 = 1.500
                if ($transaksi->kembalian + $selisih > 0) { // -1.500 + 1.500 > 0
                    $this->dispatchAlert('error', 'Gagal!', 'Jumlah bayar melebihi sisa piutang.');
                    return; // Hentikan proses jika kondisi gagal
                }
                $transaksi->update([
                    'kembalian' => $transaksi->kembalian + $selisih,  // -1.500 + 1.500 = 0
                ]);
            } elseif ($this->jumlah_bayar < $jumlah_bayar_lama) { // 250 < 500
                // Jika jumlah bayar baru lebih kecil dari jumlah bayar lama
                $selisih = $this->jumlah_bayar - $jumlah_bayar_lama; // 250 - 500 = -250
                if ($transaksi->kembalian + $selisih > 0) { // -1.500 + (-250) > 0
                    $this->dispatchAlert('error', 'Gagal!', 'Jumlah bayar melebihi sisa piutang.');
                    return; // Hentikan proses jika kondisi gagal
                }
                $transaksi->update([
                    'kembalian' => $transaksi->kembalian + $selisih,  // -1.500 + (-250) = -1.750
                ]);
            }

            $piutang->update([
                'id_transaksi'         => $this->id_transaksi, // Pastikan id_transaksi disimpan
                'tanggal_bayar'        => $this->tanggal_bayar,
                'jumlah_bayar'         => $this->jumlah_bayar,
                'keterangan'           => $this->keterangan,
                'id_metode_pembayaran' => $this->id_metode_pembayaran,
            ]);

            // Hitung total dibayar:
            $total_bayar = $transaksi->jumlah_dibayarkan +
                ModelsPiutang::where('id_transaksi', $this->id_transaksi)->sum('jumlah_bayar'); // Total Bayar: 10.000 + 9.000 = 19.000

            if ($total_bayar >= $transaksi->total_akhir) { // Total Bayar: 19.000 >= Total Akhir: 20.000
                $transaksi->update([
                    'status' => '3', // lunas
                    'kembalian' => 0,
                ]);
            } else {
                $transaksi->update([
                    'status' => '2', // belum lunas
                    'kembalian' => $transaksi->kembalian
                ]);
            }

            $this->syncSetoranTransferHarian();

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
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
            // Ambil data piutang yang akan dihapus
            $piutang = ModelsPiutang::findOrFail($this->dataId);

            // Ambil transaksi terkait
            $transaksi = Transaksi::findOrFail($piutang->id_transaksi);

            // Hitung ulang kembalian setelah penghapusan
            $transaksi->update([
                'kembalian' => $transaksi->kembalian - $piutang->jumlah_bayar, // -10.000 - 10.000
            ]);

            // Hapus data piutang
            $piutang->delete();

            // Hitung ulang total pembayaran
            $total_bayar = $transaksi->jumlah_dibayarkan +
                ModelsPiutang::where('id_transaksi', $piutang->id_transaksi)->sum('jumlah_bayar');

            // Perbarui status transaksi
            if ($total_bayar >= $transaksi->total_akhir) {
                $transaksi->update([
                    'status' => '3', // lunas
                    'kembalian' => 0,
                ]);
            } else {
                $transaksi->update([
                    'status' => '2', // belum lunas
                ]);
            }

            $this->syncSetoranTransferHarian();

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
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

    public function isEditingMode($mode)
    {
        $this->isEditing = $mode;
    }

    private function resetInputFields()
    {
        $this->tanggal_bayar = date('Y-m-d');
        $this->jumlah_bayar = '0';
        $this->keterangan = '-';
        $this->id_metode_pembayaran = '';
    }

    public function cancel()
    {
        $this->resetInputFields();
    }

    public function generateNoPiutang($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = PiutangCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = PiutangCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "PIUT/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }

    private function getReportHarian()
    {
        $transaksiHariIni = DB::table('transaksi')
            ->selectRaw('
                SUM(total_akhir) as total_omset,
                SUM(CASE 
                        WHEN id_metode_pembayaran = 1 
                        THEN LEAST(jumlah_dibayarkan, total_akhir) 
                        ELSE 0 
                    END) as total_tunai,
                SUM(CASE 
                        WHEN id_metode_pembayaran = 2 
                        THEN LEAST(jumlah_dibayarkan, total_akhir) 
                        ELSE 0 
                    END) as total_transfer,
                SUM(CASE 
                        WHEN kembalian < 0 
                        THEN kembalian 
                        ELSE 0 
                    END) as total_piutang
            ')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->id_cabang)
            ->first();

        $piutangHariIni = DB::table('piutang')
            ->join('transaksi', 'piutang.id_transaksi', '=', 'transaksi.id')
            ->selectRaw('
                SUM(CASE WHEN piutang.id_metode_pembayaran = 1 THEN piutang.jumlah_bayar ELSE 0 END) as piutang_tunai,
                SUM(CASE WHEN piutang.id_metode_pembayaran = 2 THEN piutang.jumlah_bayar ELSE 0 END) as piutang_transfer
            ')
            ->whereDate('piutang.tanggal_bayar', Carbon::today())
            ->where('transaksi.id_cabang', $this->id_cabang)
            // ->whereIn('transaksi.status', ["1", "2", "3"])
            ->first();

        $this->totalOmset       = $transaksiHariIni->total_omset;
        $this->totalTunai       = $transaksiHariIni->total_tunai + ($piutangHariIni->piutang_tunai ?? 0);
        $this->totalTransfer    = $transaksiHariIni->total_transfer + ($piutangHariIni->piutang_transfer ?? 0);

        // Hitung ulang total piutang setelah dikurangi pembayaran hari ini
        $this->totalPembayaran  = $this->totalTunai + $this->totalTransfer;
        $this->totalPiutang     = $transaksiHariIni->total_piutang;
    }

    private function syncSetoranTransferHarian()
    {
        $this->getReportHarian();

        // Lock record di cash_on_bank
        DB::table('cash_on_bank')
            ->where('id_cabang', $this->id_cabang)
            ->where('tanggal', date('Y-m-d'))
            ->where('sumber_tabel', 'Setor Transfer')
            ->lockForUpdate()
            ->first();

        // Lock record di kas
        DB::table('kas')
            ->where('id_cabang', $this->id_cabang)
            ->where('no_referensi', 'STTF/' . $this->id_cabang . '/' . date('dmy'))
            ->lockForUpdate()
            ->first();

        // Update atau create Kas
        $kas = Kas::updateOrCreate(
            [
                'id_cabang'     => $this->id_cabang,
                'no_referensi'  => 'STTF/' . $this->id_cabang . '/' . date('dmy'),
                'tanggal'       => date('Y-m-d'),
            ],
            [
                'id_pembuat'    => Auth::user()->id,
                'keterangan'    => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
                'jumlah'        => $this->totalTransfer,
                'id_kategori_keuangan' => 2,
                'status'        => 'In',
            ]
        );

        // Update atau create CashOnBank (pakai id dari Kas)
        CashOnBank::updateOrCreate(
            [
                'id_cabang'     => $this->id_cabang,
                'tanggal'       => date('Y-m-d'),
                'sumber_tabel'  => 'Setor Transfer',
            ],
            [
                'no_referensi'  => $kas->no_referensi,
                'jenis'         => 'In',
                'jumlah'        => $this->totalTransfer,
                'keterangan'    => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
                'id_sumber'     => $kas->id,
            ]
        );
    }
}
