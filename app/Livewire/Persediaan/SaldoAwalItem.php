<?php

namespace App\Livewire\Persediaan;

use Carbon\Carbon;
use App\Models\Produk;
use Livewire\Component;
use App\Models\Persediaan;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class SaldoAwalItem extends Component
{
    use WithPagination;
    #[Title('Saldo Awal Item')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_produk'           => 'required',
        'tanggal'             => 'required',
        'qty'                 => 'required',
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

        $this->updatedFilterIdCabang();

        $this->resetInputFields();
    }

    public function updatedFilterIdCabang()
    {
        $this->produks = DB::table('produk')->select('produk.id', 'nama_item')
            ->whereIn('id_kategori', ['1', '4'])
            ->where('produk.id_cabang', $this->filter_id_cabang)
            ->get();

        $this->id_produk           = $this->produks->first()->id ?? null;
    }

    public function updated()
    {
        $this->initSelect2();
    }

    private function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('persediaan')->select('persediaan.id', 'persediaan.tanggal', 'persediaan.qty', 'persediaan.keterangan', 'cabang_lokasi.nama_cabang', 'produk.nama_item', 'users.name')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'persediaan.id_cabang')
            ->join('produk', 'produk.id', 'persediaan.id_produk')
            ->join('users', 'users.id', 'persediaan.id_user')
            ->where('persediaan.status', 'Balance')
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

        return view('livewire.persediaan.saldo-awal-item', compact('data'));
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
        $stock_terakhir_barang = $produk->stock; // Stock terakhir produk

        // Hitung total stock baru setelah penambahan quantity
        $tambah_stock = $stock_terakhir_barang + $this->qty;

        // Cek apakah penambahan stock menghasilkan nilai minus
        if ($tambah_stock < 0) {
            // Jika stock menjadi minus, tampilkan pesan error
            $this->dispatchAlert('error', 'Terjadi Kesalahan!', 'Stock minus tidak diperbolehkan!');
        } else {
            // Jika stock tidak minus, simpan data persediaan baru
            Persediaan::create([
                'id_cabang'  => $this->id_cabang,
                'id_user'    => Auth::user()->id,   // ID user yang melakukan input
                'id_produk'  => $this->id_produk,   // ID produk yang diupdate
                'tanggal'    => $this->tanggal,     // Tanggal transaksi
                'qty'        => $this->qty,         // Quantity yang ditambahkan
                'keterangan' => $this->keterangan,  // Keterangan tambahan
                'status'     => $this->status,      // Status transaksi (misalnya, masuk/keluar)
            ]);

            // Update stock produk dengan stock baru
            Produk::where('id', $this->id_produk)
                ->update(['stock' => $tambah_stock]);

            // Tampilkan pesan sukses setelah data berhasil disimpan
            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        }
    }


    public function edit($id)
    {
        $this->isEditing        = true;
        $data = Persediaan::select('id', 'id_cabang', 'id_produk', 'tanggal', 'qty', 'keterangan')->where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_cabang        = $data->id_cabang;
        $this->id_produk        = $data->id_produk;
        $this->tanggal          = $data->tanggal;
        $this->qty              = $data->qty;
        $this->keterangan       = $data->keterangan;

        $this->dispatch('initSelect2');
    }

    public function update()
    {
        // Validasi input
        $this->validate();

        // $tanggal = date('Y-m-d H:i:s'); // Ambil tanggal saat ini

        // Ambil data persediaan berdasarkan ID yang diberikan
        $persediaan = Persediaan::select('id_produk', 'qty')
            ->where('id', $this->dataId)
            ->first();

        // Ambil ID produk dan quantity lama dari persediaan
        $id_produk = $persediaan->id_produk;
        $qty_lama = $persediaan->qty; // Quantity lama (misalnya 7)

        // Ambil data produk berdasarkan ID produk
        $produk = Produk::select('id', 'id_cabang', 'stock')
            ->where('id', $id_produk)
            ->first();

        // Set ID cabang untuk persediaan
        $this->id_cabang = $produk->id_cabang;

        // Ambil total stock dari produk
        $total_stock_sekarang = $produk->stock; // Stock yang ada di tabel produk (misalnya 20)

        // Ambil quantity baru yang dimasukkan oleh user
        $qty_baru = $this->qty; // Quantity baru yang dimasukkan (misalnya 10)

        // Hitung selisih antara quantity baru dan quantity lama
        $total = (int)$qty_baru - (int)$qty_lama; // 10 - 7 = 3

        // Hitung total stock baru setelah perubahan
        $total_stock = $total_stock_sekarang + $total; // 20 + 3 = 23

        // Cek apakah total stock menjadi minus
        if ($total_stock < 0) {
            // Jika stock menjadi minus, tampilkan pesan error
            $this->dispatchAlert('error', 'Terjadi Kesalahan!', 'Stock minus tidak diperbolehkan!');
        } else {
            // Jika stock tidak minus, update stock produk
            Produk::where('id', $id_produk)
                ->update([
                    'stock' => $total_stock
                ]);
        }

        // Jika data ID tersedia, update data persediaan
        if ($this->dataId) {
            Persediaan::findOrFail($this->dataId)->update([
                'id_cabang' => $this->id_cabang,
                'id_user'   => Auth::user()->id,  // Set ID user yang mengupdate
                'tanggal'   => $this->tanggal,    // Set tanggal update
                'qty'       => $this->qty,        // Set quantity baru
                'keterangan' => $this->keterangan, // Set keterangan update
            ]);

            // Tampilkan pesan sukses
            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');

            // Reset data ID setelah update selesai
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
        // Ambil data persediaan berdasarkan ID yang diberikan
        $persediaan = Persediaan::select('qty', 'id_produk')->where('id', $this->dataId)->first(); // Ambil ID barang

        // Ambil quantity terakhir dari persediaan
        $qty_terakhir = $persediaan->qty; // Misalnya quantity terakhir: 10

        // Ambil ID produk terkait
        $id_produk = $persediaan->id_produk;

        // Ambil stock terakhir dari produk
        $stock_terakhir = Produk::where('id', $id_produk)->first()->stock; // Misalnya stock produk: 10

        // Hitung total stock setelah persediaan dihapus
        $total_stock = $stock_terakhir - $qty_terakhir; // Kurangi stock dengan quantity yang dihapus

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
        $this->dispatch('initSelect2');
    }

    private function resetInputFields()
    {
        $this->id_produk           = $this->produks->first()->id ?? null;
        $this->tanggal             = date('Y-m-d H:i:s');
        $this->qty                 = '';
        $this->keterangan          = 'Saldo awal item';
        $this->status              = 'Balance';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
