<?php

namespace App\Livewire\Keuangan;

use App\Models\CashOnBank;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\DetailHutang;
use Livewire\WithPagination;
use App\Models\HutangCounter;
use App\Models\DaftarSupplier;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Hutang as ModelsHutang;
use App\Models\KategoriPembayaran;

class Hutang extends Component
{
    use WithPagination;
    #[Title('Hutang')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'no_referensi'        => '',
        'id_supplier'         => 'required',
        'tanggal_beli'        => 'required',
        'total_tagihan'       => 'required',
        'total_dibayarkan'    => '',
        'sisa_hutang'         => '',
        'status'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs, $suppliers;
    public $hutang = [];
    public $id_hutang, $tanggal_bayar, $jumlah_bayar, $keterangan, $id_metode_pembayaran, $created_by;
    public $id_cabang, $no_referensi, $id_supplier, $tanggal_beli, $total_tagihan, $total_dibayarkan, $sisa_hutang, $status;
    public $filter_id_cabang;


    public function mount($id_hutang, GlobalDataService $globalDataService)
    {
        try {
            $this->id_hutang = Crypt::decrypt($id_hutang);
            // dd($this->id_hutang);
            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->cabangs = $globalDataService->getCabangs();
            $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
            $this->id_hutang = null;
            $this->getSuppliers($this->filter_id_cabang);
            $this->id_supplier = '';

            // $this->id_supplier         = $this->suppliers->first()->id ?? '';
            $this->resetInputFields();
        }

        $this->id_cabang = Auth::user()->id_cabang;
    }

    private function getSuppliers($id_cabang)
    {
        $this->suppliers = DB::table('daftar_supplier')->select('daftar_supplier.id', 'nama_supplier', 'nama_cabang')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'daftar_supplier.id_cabang')
            ->where('daftar_supplier.id_cabang', $id_cabang)
            ->get();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $periode_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $periode_end = Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($this->isDetailMode()) {
            $data = DetailHutang::select('detail_hutang.id', 'detail_hutang.tanggal_bayar', 'detail_hutang.jumlah_bayar', 'detail_hutang.keterangan', 'kategori_pembayaran.nama_kategori as metode_pembayaran')
                ->join('kategori_pembayaran', 'kategori_pembayaran.id', '=', 'detail_hutang.id_metode_pembayaran')
                ->where(function ($query) use ($search) {
                    $query->where('tanggal_bayar', 'LIKE', $search);
                })
                ->where('id_hutang', $this->id_hutang)
                ->orderBy('id', 'ASC')
                ->paginate($this->lengthData);

            $this->hutang = ModelsHutang::select('hutang.*', 'daftar_supplier.nama_supplier')
                ->join('daftar_supplier', 'daftar_supplier.id', '=', 'hutang.id_supplier')
                ->where('hutang.id', $this->id_hutang)
                ->first()
                ?->toArray() ?? [];

            return view('livewire.keuangan.hutang', compact('data'));
        }

        $data = ModelsHutang::select('hutang.*', 'daftar_supplier.nama_supplier', 'cabang_lokasi.nama_cabang')
            ->join('daftar_supplier', 'daftar_supplier.id', '=', 'hutang.id_supplier')
            ->join('cabang_lokasi', 'cabang_lokasi.id', '=', 'daftar_supplier.id_cabang')
            ->where(function ($query) use ($search) {
                $query->where('no_referensi', 'LIKE', $search)
                    ->orWhere('daftar_supplier.nama_supplier', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('hutang.id_cabang', $this->filter_id_cabang);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.keuangan.hutang-all', compact('data'));
    }

    public function store()
    {
        $this->isDetailMode()
            ? $this->storeDetail()
            : $this->storeHutangBaru();
    }

    private function storeDetail()
    {
        $this->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:1',
            'id_metode_pembayaran' => 'required',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $hutang = ModelsHutang::lockForUpdate()->findOrFail($this->id_hutang);
            $sisa_baru = $hutang->sisa_hutang + $this->jumlah_bayar;

            // Cek apakah jumlah bayar melebihi sisa hutang
            if ($sisa_baru > 0) {
                DB::rollBack(); // pastikan rollback kalau udah lock
                $this->dispatchAlert('error', 'Gagal!', 'Jumlah bayar melebihi sisa hutang.');
                return;
            }

            $detail_hutang = DetailHutang::create([
                'id_hutang' => $this->id_hutang,
                'tanggal_bayar' => $this->tanggal_bayar,
                'jumlah_bayar' => $this->jumlah_bayar,
                'keterangan' => $this->keterangan,
                'id_metode_pembayaran' => $this->id_metode_pembayaran,
                'created_by' => Auth::id(),
            ]);

            $hutang->increment('total_dibayarkan', $this->jumlah_bayar);
            $hutang->increment('sisa_hutang', $this->jumlah_bayar);

            $metode_pembayaran = KategoriPembayaran::where('id', $this->id_metode_pembayaran)->first()->nama_kategori;

            CashOnBank::create([
                'id_cabang'     => $hutang->id_cabang,
                'tanggal'       => $this->tanggal_bayar,
                'sumber_tabel'  => 'Hutang',
                'no_referensi'  => $hutang->no_referensi,
                'jenis'         => 'Out',
                'jumlah'        => $this->jumlah_bayar,
                'keterangan'    => 'Bayar Hutang ke Supplier: ISTAR via ' . $metode_pembayaran,
                'id_sumber'     => $detail_hutang->id,
            ]);

            $this->refreshStatusHutang($hutang);
            $this->updateSisaHutangSupplier($hutang->id_supplier);

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Pembayaran berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function storeHutangBaru()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $id_cabang = DaftarSupplier::where('id', $this->id_supplier)->value('id_cabang');
            $no_referensi = $this->generateNoHutang($id_cabang);

            $hutang = ModelsHutang::create([
                'id_cabang' => $id_cabang,
                'no_referensi' => $no_referensi,
                'id_supplier' => $this->id_supplier,
                'tanggal_beli' => $this->tanggal_beli,
                'total_tagihan' => $this->total_tagihan,
                'total_dibayarkan' => 0,
                'sisa_hutang' => -$this->total_tagihan,
                'status' => $this->status,
            ]);

            $this->updateSisaHutangSupplier($this->id_supplier);

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Hutang berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing        = true;
        if ($this->id_hutang) {
            $data = DetailHutang::from('detail_hutang')
                ->join('hutang', 'hutang.id', '=', 'detail_hutang.id_hutang')
                ->where('detail_hutang.id', $id)
                ->firstOrFail([
                    'detail_hutang.id',
                    'detail_hutang.tanggal_bayar',
                    'detail_hutang.jumlah_bayar',
                    'detail_hutang.keterangan',
                    'detail_hutang.id_metode_pembayaran',
                    'hutang.no_referensi',
                ]);

            $this->dataId           = $data->id;
            $this->no_referensi     = $data->no_referensi;
            $this->tanggal_bayar    = $data->tanggal_bayar;
            $this->jumlah_bayar     = $data->jumlah_bayar;
            $this->keterangan       = $data->keterangan;
            $this->id_metode_pembayaran = $data->id_metode_pembayaran;
        } else {
            $data = ModelsHutang::select('id', 'id_supplier', 'tanggal_beli', 'total_tagihan', 'total_dibayarkan', 'sisa_hutang')
                ->findOrFail($id);
            $this->dataId           = $id;
            $this->id_supplier      = $data->id_supplier;
            $this->tanggal_beli     = $data->tanggal_beli;
            $this->total_tagihan    = $data->total_tagihan;
            $this->total_dibayarkan = $data->total_dibayarkan;
            $this->sisa_hutang      = $data->sisa_hutang;
        }
    }

    public function update()
    {
        $this->isDetailMode()
            ? $this->updateDetailPembayaran()
            : $this->updateHutangUtama();
    }

    private function updateDetailPembayaran()
    {
        $this->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
            'id_metode_pembayaran' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $detail = DetailHutang::findOrFail($this->dataId);
            $hutang = ModelsHutang::findOrFail($this->id_hutang);

            $selisih = $this->jumlah_bayar - $detail->jumlah_bayar;
            $sisa_baru = $hutang->sisa_hutang + $selisih;

            if ($sisa_baru > 0) {
                DB::rollBack(); // pastikan rollback kalau udah lock
                $this->dispatchAlert('error', 'Gagal!', 'Jumlah bayar melebihi sisa hutang.');
                return;
            }

            $hutang->update([
                'total_dibayarkan' => $hutang->total_dibayarkan + $selisih,
                'sisa_hutang' => $sisa_baru,
            ]);

            $detail->update([
                'tanggal_bayar' => $this->tanggal_bayar,
                'jumlah_bayar' => $this->jumlah_bayar,
                'keterangan' => $this->keterangan,
                'id_metode_pembayaran' => $this->id_metode_pembayaran,
                'created_by' => Auth::id(),
            ]);

            $metode_pembayaran = KategoriPembayaran::where('id', $this->id_metode_pembayaran)->first()->nama_kategori;

            CashOnBank::where([
                'no_referensi' => $this->no_referensi,
                'id_sumber' => $this->dataId,
            ])->update([
                'tanggal'       => $this->tanggal_bayar,
                'jumlah'        => $this->jumlah_bayar,
                'keterangan'    => 'Bayar Hutang ke Supplier: ISTAR via ' . $metode_pembayaran,
            ]);

            $this->refreshStatusHutang($hutang);
            $this->updateSisaHutangSupplier($hutang->id_supplier);

            DB::commit();
            $this->dispatchAlert('success', 'Berhasil', 'Data berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $this->dataId = null;
        $this->isEditing = false;
        $this->resetInputFields();
    }

    private function updateHutangUtama()
    {
        $this->validate();

        try {
            $hutang = ModelsHutang::findOrFail($this->dataId);

            $hutang->update([
                'id_supplier' => $this->id_supplier,
                'tanggal_beli' => $this->tanggal_beli,
                'total_tagihan' => $this->total_tagihan,
                'sisa_hutang' => -$this->total_tagihan + $this->total_dibayarkan,
            ]);

            $this->updateSisaHutangSupplier($hutang->id_supplier);

            $this->dispatchAlert('success', 'Berhasil', 'Data hutang berhasil diperbarui.');
            $this->dataId = null;
        } catch (\Throwable $e) {
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
        $this->isDetailMode()
            ? $this->deleteDetailPembayaran()
            : $this->deleteHutangUtama();
    }


    private function deleteDetailPembayaran()
    {
        DB::beginTransaction();
        try {
            // Kunci row biar aman dari race condition
            $detail = DetailHutang::lockForUpdate()->findOrFail($this->dataId);
            $hutang = ModelsHutang::lockForUpdate()->findOrFail($this->id_hutang);

            $hutang->decrement('total_dibayarkan', $detail->jumlah_bayar);
            $hutang->decrement('sisa_hutang', $detail->jumlah_bayar);

            // Hapus di CashOnBank (sesuaikan kolom identifikasi kamu)
            CashOnBank::where('no_referensi', $hutang->no_referensi)
                ->where('id_sumber', $detail->id)
                ->delete();

            $detail->delete();

            $this->refreshStatusHutang($hutang);
            $this->updateSisaHutangSupplier($hutang->id_supplier);

            DB::commit();
            $this->dispatchAlert('success', 'Berhasil', 'Pembayaran berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function deleteHutangUtama()
    {
        DB::beginTransaction();
        try {
            $hutang = ModelsHutang::lockForUpdate()->findOrFail($this->dataId);
            $supplierId = $hutang->id_supplier;

            // Hapus di CashOnBank (sesuaikan kolom identifikasi kamu)
            CashOnBank::where('no_referensi', $hutang->no_referensi)
                ->delete();

            // Hapus semua detail juga
            DetailHutang::where('id_hutang', $this->dataId)->delete();
            $hutang->delete();

            $this->updateSisaHutangSupplier($supplierId);

            DB::commit();
            $this->dispatchAlert('success', 'Berhasil', 'Data hutang berhasil dihapus.');
        } catch (\Throwable $e) {
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
        $this->initSelect2();
        $this->isEditing = $mode;
    }

    private function resetInputFields()
    {
        if ($this->id_hutang) {
            $this->tanggal_bayar       = date('Y-m-d');
            $this->jumlah_bayar              = '0';
            $this->keterangan          = '-';
            $this->id_metode_pembayaran = '';
            $this->created_by          = Auth::user()->id;
        } else {
            $this->tanggal_beli        = date('Y-m-d');
            $this->total_tagihan       = '0';
            $this->total_dibayarkan    = '0';
            $this->sisa_hutang         = '0';
            $this->status              = 'Belum Lunas';
        }
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }

    public function updated()
    {
        $this->getSuppliers($this->filter_id_cabang);
        $this->initSelect2();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    private function isDetailMode(): bool
    {
        return !is_null($this->id_hutang);
    }

    private function updateSisaHutangSupplier($supplierId): void
    {
        DaftarSupplier::where('id', $supplierId)->update([
            'sisa_hutang' => ModelsHutang::where('id_supplier', $supplierId)->sum('sisa_hutang')
        ]);
    }

    private function refreshStatusHutang(ModelsHutang $hutang): void
    {
        $status = ((int)$hutang->sisa_hutang >= 0) ? 'Sudah Lunas' : 'Belum Lunas';
        $hutang->update(['status' => $status]);
    }

    public function generateNoHutang($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = HutangCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = HutangCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "HUTG/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }
}
