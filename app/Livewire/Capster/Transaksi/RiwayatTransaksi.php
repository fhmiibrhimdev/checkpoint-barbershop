<?php

namespace App\Livewire\Capster\Transaksi;

use Livewire\Component;
use App\Models\Transaksi;
use Livewire\WithPagination;
use App\Models\DaftarKaryawan;
use Livewire\Attributes\Title;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class RiwayatTransaksi extends Component
{
    use WithPagination;
    #[Title('Riwayat Transaksi')]

    protected $listeners = [
        'delete'
    ];
    protected $rules = [
        'title' => 'required',
    ];
    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';

    public $title;
    public $id_karyawan;
    public $id_cabang, $id_user, $no_transaksi, $tanggal, $id_pelanggan, $catatan, $total_pesanan, $total_komisi, $total_sub_total, $total_diskon, $total_akhir, $total_hpp, $laba_bersih, $id_metode_pembayaran, $jumlah_dibayarkan, $kembalian, $status, $pelanggans = [], $produks = [], $cartItems = [], $isEditing = false, $dataId;
    public $nama_pelanggan;

    public function mount()
    {
        $user = Auth::user();
        $this->id_karyawan = DaftarKaryawan::where('id_user', $user->id)->value('id');
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('transaksi')
            ->select(
                'transaksi.id',
                DB::raw('DATE(transaksi.tanggal) as tanggal'),
                'transaksi.no_transaksi',
                'transaksi.total_komisi_karyawan',
                'daftar_pelanggan.nama_pelanggan'
            )
            ->join('detail_transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id')
            ->join('daftar_pelanggan', 'daftar_pelanggan.id', '=', 'transaksi.id_pelanggan')
            ->where(function ($query) use ($search) {
                $query->where('tanggal', 'like', $search)
                    ->orWhere('no_transaksi', 'like', $search);
            })
            ->distinct()
            ->whereIn('status', ['2', '3'])
            ->where('detail_transaksi.id_karyawan', $this->id_karyawan)
            ->paginate($this->lengthData);

        return view('livewire.capster.transaksi.riwayat-transaksi', compact('data'));
    }

    public function store()
    {
        $this->validate();

        Transaksi::create([
            'title'     => $this->title,
        ]);

        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
    }

    public function edit(GlobalDataService $globalDataService, $id)
    {
        $this->isEditing = true;
        $this->dataId = $id;

        // Ambil data transaksi utama
        $transaksi = Transaksi::select('transaksi.*', 'daftar_pelanggan.nama_pelanggan')
            ->join('daftar_pelanggan', 'daftar_pelanggan.id', 'transaksi.id_pelanggan')
            ->where('transaksi.id', $id)
            ->first();

        $this->id_cabang            = $transaksi->id_cabang;
        $this->id_user              = $transaksi->id_user;
        $this->no_transaksi         = $transaksi->no_transaksi;
        $this->tanggal              = $transaksi->tanggal;
        $this->nama_pelanggan       = $transaksi->nama_pelanggan;
        $this->catatan              = $transaksi->catatan;
        $this->total_pesanan        = $transaksi->total_pesanan;
        $this->total_komisi         = $transaksi->total_komisi_karyawan;
        $this->total_sub_total      = $transaksi->total_sub_total;
        $this->total_diskon         = $transaksi->total_diskon;
        $this->total_akhir          = $transaksi->total_akhir;
        $this->total_hpp            = $transaksi->total_hpp;
        $this->laba_bersih          = $transaksi->laba_bersih;
        $this->id_metode_pembayaran = $transaksi->id_metode_pembayaran;
        $this->jumlah_dibayarkan    = $transaksi->jumlah_dibayarkan;
        $this->kembalian            = $transaksi->kembalian;
        $this->status               = $transaksi->status;

        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks    = $globalDataService->getProdukAndKategoriCustom($this->id_cabang);

        // Ambil detail transaksi
        $detail = DetailTransaksi::where('id_transaksi', $id)->get();

        $this->cartItems = [];

        foreach ($detail as $item) {
            $this->cartItems[] = [
                'id_produk'      => $item->id_produk,
                'nama_item'      => $item->nama_item,
                'kategori_item'  => $item->kategori_item,
                'deskripsi_item' => $item->deskripsi_item,
                'harga'          => $item->harga,  // Hati-hati dengan nama kolom
                'jumlah'         => $item->jumlah,
                'sub_total'      => $item->sub_total,
                'diskon'         => $item->diskon,
                'total_harga'    => $item->total_harga,
                'id_karyawan'    => $item->id_karyawan,
                'nama_karyawan'  => $item->nama_karyawan,
                'komisi_persen'  => $item->komisi_persen,
                'komisi_nominal' => $item->komisi_nominal,
            ];
        }
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            Transaksi::findOrFail($this->dataId)->update([
                'title' => $this->title,
            ]);

            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
            $this->dataId = null;
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
        Transaksi::findOrFail($this->dataId)->delete();
        $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
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
        $this->title = '';
    }

    public function cancel()
    {
        $this->resetInputFields();
    }
}
