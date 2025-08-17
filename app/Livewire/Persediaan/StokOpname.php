<?php

namespace App\Livewire\Persediaan;

use Carbon\Carbon;
use App\Models\Produk;
use Livewire\Component;
use App\Models\Persediaan;
use App\Services\GlobalDataService;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokOpname extends Component
{
    use WithPagination;
    #[Title('Stok Opname')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_produk' => 'required',
        'tanggal'   => 'required',
        'fisik'     => 'required',
        'buku'      => 'required',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs, $produks;
    public $id_cabang, $id_user, $id_produk, $tanggal, $qty, $keterangan, $buku, $fisik, $selisih, $opname, $status;
    public $filter_id_cabang;


    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs    = $globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;

        $this->getBukuFisik();

        // $this->updatedFilterIdCabang();

        $this->resetInputFields();
    }

    public function updatedFilterIdCabang()
    {
        $this->getBukuFisik();
    }

    public function updated()
    {
        $this->initSelect2();
    }

    private function getBukuFisik()
    {
        $this->produks    = DB::table('produk')->select('produk.id', 'nama_item', 'stock')
            ->whereIn('id_kategori', ['1', '4'])
            ->where('produk.id_cabang', $this->filter_id_cabang)
            ->get();

        $stock = $this->produks->first()->stock ?? null;
        $this->buku       = $stock;
        $this->fisik      = $stock;
        $this->id_produk  = $this->produks->first()->id ?? null;
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('persediaan')->select('persediaan.id', 'persediaan.tanggal', 'persediaan.buku', 'persediaan.fisik', 'persediaan.selisih', 'persediaan.keterangan', 'produk.nama_item', 'users.name')
            ->join('produk', 'produk.id', 'persediaan.id_produk')
            ->join('users', 'users.id', 'persediaan.id_user')
            ->where('persediaan.opname', 'yes')
            ->where(function ($query) use ($search) {
                $query->where('nama_item', 'LIKE', $search);
                $query->orWhere('tanggal', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('persediaan.id_cabang', $this->filter_id_cabang);
            })
            ->whereBetween('persediaan.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.persediaan.stok-opname', compact('data'));
    }

    public function updatedIdProduk()
    {
        $stock = Produk::select('stock')->where('id', $this->id_produk)->first()->stock;
        $this->buku = $stock;
        $this->fisik = $stock;
    }

    public function store()
    {
        // Validasi input
        $this->validate();

        // $tanggal = date('Y-m-d H:i:s'); // Ambil tanggal saat ini

        // Ambil data produk berdasarkan ID produk yang diberikan
        $produk = Produk::select('id', 'id_cabang', 'stock')
            ->where('id', $this->id_produk)
            ->first();

        // Set ID cabang dan stock terakhir dari produk
        $this->id_cabang = $produk->id_cabang;

        $selisih    = $this->fisik - $this->buku;

        $selisih > 0 ? $status = 'In' : $status = 'Out';

        Persediaan::create([
            'id_cabang'  => $this->id_cabang,
            'id_user'    => Auth::user()->id,   // ID user yang melakukan input
            'id_produk'  => $this->id_produk,   // ID produk yang diupdate
            'tanggal'    => $this->tanggal,     // Tanggal transaksi
            'qty'        => abs($selisih),         // Quantity yang ditambahkan
            'buku'       => $produk->stock,
            'fisik'      => $this->fisik,
            'selisih'    => $selisih,
            'keterangan' => $this->keterangan,  // Keterangan tambahan
            'opname'     => 'yes',
            'status'     => $status,      // Status transaksi (misalnya, masuk/keluar)
        ]);

        Produk::where('id', $this->id_produk)
            ->update(['stock' => $this->fisik]);

        // Tampilkan pesan sukses setelah data berhasil disimpan
        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
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
        // Ambil data persediaan berdasarkan ID yang diberikan
        $persediaan = Persediaan::select('id_produk', 'selisih')->where('id', $this->dataId)->first(); // Ambil ID barang

        // Ambil ID produk terkait
        $id_produk = $persediaan->id_produk;

        // Ambil quantity terakhir dari persediaan
        $selisih = $persediaan->selisih; // Misalnya quantity terakhir: 10

        // Ambil stock terakhir dari produk
        $stock_terakhir = Produk::select('stock')->where('id', $id_produk)->first()->stock; // Misalnya stock produk: 10

        // Hitung total stock setelah persediaan dihapus
        $total_stock = $stock_terakhir - $selisih; // Kurangi stock dengan quantity yang dihapus

        // Update stock produk dengan total stock yang baru
        Produk::where('id', $id_produk)
            ->update([
                'stock' => $total_stock
            ]);

        // Hapus data persediaan berdasarkan ID
        Persediaan::findOrFail($this->dataId)->delete();

        // Tampilkan pesan sukses setelah data berhasil dihapus
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
        $this->getBukuFisik();
        $this->dispatch('initSelect2');
    }

    private function resetInputFields()
    {
        $this->id_produk  = $this->produks->first()->id ?? null;
        $this->tanggal    = date('Y-m-d H:i:s');
        $this->keterangan = 'Stok Opname';
    }

    private function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
