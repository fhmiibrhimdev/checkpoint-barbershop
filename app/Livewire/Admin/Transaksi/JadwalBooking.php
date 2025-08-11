<?php

namespace App\Livewire\Admin\Transaksi;

use Carbon\Carbon;
use App\Models\Kas;
use Livewire\Component;
use App\Models\Transaksi;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\DetailTransaksi;
use App\Models\TransaksiCounter;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class JadwalBooking extends Component
{
    use WithPagination;
    #[Title('Admin | Jadwal Booking')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        // 'no_transaksi' => 'required',
        'id_cabang' => 'required',
        'tanggal' => 'required',
    ];

    public $lengthData = 25;
    public $searchTerm, $searchProduk;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs, $pelanggans, $produks, $pembayarans;
    /**
     * @var \Illuminate\Support\Collection
     */
    public $karyawans = [];
    public $cartItems = [];
    public $isPersentase = false;
    public $check_id_kategori;
    public $totalOmset, $totalTunai, $totalTransfer, $totalPembayaran, $totalPiutang;
    public $filter_status, $filter_pembayaran = '';

    // Table: transaksi
    public $id_cabang, $id_user, $no_transaksi, $tanggal, $id_pelanggan, $catatan, $total_pesanan, $total_komisi, $total_sub_total, $total_diskon, $total_akhir, $laba_bersih, $id_metode_pembayaran, $jumlah_dibayarkan, $kembalian, $status;

    // Table: detail_transaksi
    public $id_transaksi, $id_produk, $nama_item, $kategori_item, $deskripsi_item, $harga_item, $jumlah, $sub_total, $input_diskon, $diskon, $total_harga, $id_karyawan, $nama_karyawan, $komisi_persen, $komisi_nominal;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        $this->id_cabang         = Auth::user()->id_cabang;

        $this->pelanggans        = $this->globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks           = $this->globalDataService->getProdukAndKategoriCustom($this->id_cabang);
        $this->pembayarans       = $this->globalDataService->getMetodePembayaran();

        // $this->id_cabang            = "1";
        $this->id_user              = Auth::user()->id;

        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('transaksi')->select('transaksi.id', 'transaksi.no_transaksi', 'transaksi.tanggal', 'daftar_pelanggan.nama_pelanggan', 'daftar_pelanggan.no_telp',  'transaksi.total_akhir', 'transaksi.status', 'detail.nama_item', 'detail.deskripsi_item', 'jumlah.jumlah_produk', 'kategori_pembayaran.nama_kategori', 'cabang_lokasi.nama_cabang')
            ->join('daftar_pelanggan', 'daftar_pelanggan.id', 'transaksi.id_pelanggan')
            ->join('kategori_pembayaran', 'kategori_pembayaran.id', 'transaksi.id_metode_pembayaran')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'transaksi.id_cabang')
            ->leftJoin(DB::raw('(
                SELECT id_transaksi, nama_item, deskripsi_item
                FROM detail_transaksi
                GROUP BY id_transaksi
            ) AS detail'), 'detail.id_transaksi', '=', 'transaksi.id')
            ->leftJoin(DB::raw('(
                SELECT id_transaksi, COUNT(*) as jumlah_produk
                FROM detail_transaksi
                GROUP BY id_transaksi
            ) AS jumlah'), 'jumlah.id_transaksi', '=', 'transaksi.id')
            ->where(function ($query) use ($search) {
                $query->where('no_transaksi', 'LIKE', $search);
                $query->orWhere('nama_pelanggan', 'LIKE', $search);
            })
            ->where('transaksi.id_cabang', $this->id_cabang)
            ->where('transaksi.status', '1')
            ->whereBetween('transaksi.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->orderBy('transaksi.no_transaksi', 'DESC')
            ->paginate($this->lengthData);

        return view('livewire.admin.transaksi.jadwal-booking', compact('data'));
    }

    public function store()
    {
        $this->validate();

        // Ambil ID produk yang perlu dicek stoknya
        $produkIdsToCheck = [];
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $produkIdsToCheck[] = $item['id_produk'];
            }
        }

        // Ambil stok produk dari database
        $stokProduk = DB::table('produk')
            ->whereIn('id', $produkIdsToCheck)
            ->pluck('stock', 'id'); // hasil: [id_produk => stock]

        // Cek apakah ada stok yang kurang
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $id_produk = $item['id_produk'];
                $qty_dibeli = $item['jumlah'];
                $stok_tersedia = $stokProduk[$id_produk] ?? 0;

                if ($qty_dibeli > $stok_tersedia) {
                    $this->dispatchAlert(
                        'error',
                        'Stok Tidak Cukup',
                        "Stok untuk '{$item['nama_item']}' hanya tersedia {$stok_tersedia}, tidak bisa membeli {$qty_dibeli}."
                    );
                    return; // hentikan proses store
                }
            }
        }

        DB::beginTransaction();
        try {
            $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
            $status = $this->kembalian < 0 ? "2" : "3";

            $no_transaksi = $this->generateNoTransaksi($this->id_cabang);

            $transaksi = Transaksi::create([
                'id_cabang'             => $this->id_cabang,
                'id_user'               => $this->id_user,
                'no_transaksi'          => $no_transaksi,
                'tanggal'               => $this->tanggal,
                'id_pelanggan'          => $this->id_pelanggan,
                'catatan'               => $this->catatan,
                'total_pesanan'         => $this->total_pesanan,
                'total_komisi_karyawan' => $this->total_komisi,
                'total_sub_total'       => $this->total_sub_total,
                'total_diskon'          => $this->total_diskon,
                'total_akhir'           => $this->total_akhir,
                'laba_bersih'           => $this->total_akhir - $this->total_komisi,
                'id_metode_pembayaran'  => $this->id_metode_pembayaran,
                'jumlah_dibayarkan'     => $this->jumlah_dibayarkan,
                'kembalian'             => $this->kembalian,
                'status'                => '1',
            ]);

            $detailData = [];
            $persediaanData = [];
            $stockMap = []; // id_produk => total_jumlah

            foreach ($this->cartItems as $item) {
                // Simpan ke detail transaksi
                $detailData[] = [
                    'id_transaksi'   => $transaksi->id,
                    'id_produk'      => $item['id_produk'],
                    'nama_item'      => $item['nama_item'],
                    'kategori_item'  => $item['kategori_item'],
                    'deskripsi_item' => $item['deskripsi_item'],
                    'harga'          => $item['harga'],
                    'jumlah'         => $item['jumlah'],
                    'sub_total'      => $item['sub_total'],
                    'diskon'         => $item['diskon'],
                    'total_harga'    => $item['total_harga'],
                    'id_karyawan'    => $item['id_karyawan'],
                    'nama_karyawan'  => $item['nama_karyawan'],
                    'komisi_persen'  => $item['komisi_persen'],
                    'komisi_nominal' => $item['komisi_nominal'],
                ];

                // Jika kategori item memerlukan pengurangan stok
                if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                    // Akumulasi stok
                    if (isset($stockMap[$item['id_produk']])) {
                        $stockMap[$item['id_produk']] += $item['jumlah'];
                    } else {
                        $stockMap[$item['id_produk']] = $item['jumlah'];
                    }

                    // Simpan ke persediaan
                    $persediaanData[] = [
                        'id_cabang'  => $this->id_cabang,
                        'id_user'    => (int)$item['id_karyawan'] + 1, // atau $this->id_user jika perlu
                        'id_produk'  => $item['id_produk'],
                        'tanggal'    => now(),
                        'qty'        => $item['jumlah'],
                        'keterangan' => 'Produk terjual dari ' . $no_transaksi,
                        'buku'       => '-',
                        'fisik'      => '-',
                        'selisih'    => '-',
                        'opname'     => 'no',
                        'status'     => 'Out',
                    ];
                }
            }

            // Simpan detail transaksi & persediaan
            DetailTransaksi::insert($detailData);
            if (!empty($persediaanData)) {
                DB::table('persediaan')->insert($persediaanData);
            }

            // Update stok secara efisien (raw SQL CASE)
            if (!empty($stockMap)) {
                $updateQuery = "UPDATE produk SET stock = CASE";
                foreach ($stockMap as $id_produk => $jumlah) {
                    $updateQuery .= " WHEN id = $id_produk THEN stock - $jumlah";
                }
                $ids = implode(',', array_keys($stockMap));
                $updateQuery .= " END WHERE id IN ($ids)";
                DB::statement($updateQuery);
            }

            $this->syncSetoranTransferHarian();

            DB::commit();

            // Setelah transaksi berhasil
            $this->dispatchSwalTransaksi($transaksi->id);
            $this->resetInputFields();
            $this->dispatch('closePembayaranModal');
            $this->dispatch('printNota', id: $transaksi->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function isEditingMode($mode)
    {
        $this->isEditing = $mode;
        $this->initSelect2();
    }

    public function edit(GlobalDataService $globalDataService, $id)
    {
        $this->isEditing = true;
        $this->dataId = $id;

        // Ambil data transaksi utama
        $transaksi = Transaksi::findOrFail($id);

        $this->id_cabang            = $transaksi->id_cabang;
        $this->id_user              = $transaksi->id_user;
        $this->no_transaksi         = $transaksi->no_transaksi;
        $this->tanggal              = $transaksi->tanggal;
        $this->id_pelanggan         = $transaksi->id_pelanggan;
        $this->catatan              = $transaksi->catatan;
        $this->total_pesanan        = $transaksi->total_pesanan;
        $this->total_komisi         = $transaksi->total_komisi_karyawan;
        $this->total_sub_total      = $transaksi->total_sub_total;
        $this->total_diskon         = $transaksi->total_diskon;
        $this->total_akhir          = $transaksi->total_akhir;
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

        $this->initSelect2(); // Jika ini penting untuk inisialisasi UI
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Ambil data transaksi lama
            $transaksi = Transaksi::findOrFail($this->dataId);

            // Ambil detail transaksi lama
            $oldDetails = DetailTransaksi::where('id_transaksi', $transaksi->id)->get();

            // Hitung kembali stok dari item lama yang perlu dikembalikan
            $restoreStockMap = [];
            foreach ($oldDetails as $oldItem) {
                if (in_array($oldItem->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                    $restoreStockMap[$oldItem->id_produk] = ($restoreStockMap[$oldItem->id_produk] ?? 0) + $oldItem->jumlah;
                }
            }

            // Kembalikan stok lama
            if (!empty($restoreStockMap)) {
                $updateQuery = "UPDATE produk SET stock = CASE";
                foreach ($restoreStockMap as $id_produk => $jumlah) {
                    $updateQuery .= " WHEN id = $id_produk THEN stock + $jumlah";
                }
                $ids = implode(',', array_keys($restoreStockMap));
                $updateQuery .= " END WHERE id IN ($ids)";
                DB::statement($updateQuery);
            }

            // Hapus detail transaksi lama dan persediaan lama
            DetailTransaksi::where('id_transaksi', $transaksi->id)->delete();
            DB::table('persediaan')
                ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
                ->where('id_cabang', $this->id_cabang)
                ->delete();

            // Hitung kembali data dari cart baru
            $detailData = [];
            $persediaanData = [];
            $newStockMap = [];

            foreach ($this->cartItems as $item) {
                $detailData[] = [
                    'id_transaksi'   => $transaksi->id,
                    'id_produk'      => $item['id_produk'],
                    'nama_item'      => $item['nama_item'],
                    'kategori_item'  => $item['kategori_item'],
                    'deskripsi_item' => $item['deskripsi_item'],
                    'harga'          => $item['harga'],
                    'jumlah'         => $item['jumlah'],
                    'sub_total'      => $item['sub_total'],
                    'diskon'         => $item['diskon'],
                    'total_harga'    => $item['total_harga'],
                    'id_karyawan'    => $item['id_karyawan'],
                    'nama_karyawan'  => $item['nama_karyawan'],
                    'komisi_persen'  => $item['komisi_persen'],
                    'komisi_nominal' => $item['komisi_nominal'],
                ];

                if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                    $newStockMap[$item['id_produk']] = ($newStockMap[$item['id_produk']] ?? 0) + $item['jumlah'];

                    $persediaanData[] = [
                        'id_cabang'  => $this->id_cabang,
                        'id_user'    => $item['id_karyawan'],
                        'id_produk'  => $item['id_produk'],
                        'tanggal'    => now(),
                        'qty'        => $item['jumlah'],
                        'keterangan' => 'Produk terjual dari ' . $transaksi->no_transaksi,
                        'buku'       => '-',
                        'fisik'      => '-',
                        'selisih'    => '-',
                        'opname'     => 'no',
                        'status'     => 'Out',
                    ];
                }
            }

            // Simpan detail baru dan persediaan baru
            DetailTransaksi::insert($detailData);
            if (!empty($persediaanData)) {
                DB::table('persediaan')->insert($persediaanData);
            }

            // Update stok baru
            if (!empty($newStockMap)) {
                $updateQuery = "UPDATE produk SET stock = CASE";
                foreach ($newStockMap as $id_produk => $jumlah) {
                    $updateQuery .= " WHEN id = $id_produk THEN stock - $jumlah";
                }
                $ids = implode(',', array_keys($newStockMap));
                $updateQuery .= " END WHERE id IN ($ids)";
                DB::statement($updateQuery);
            }

            // Update transaksi utama
            $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
            $status = $this->kembalian < 0 ? "2" : "3";

            $transaksi->update([
                'id_user'               => $this->id_user,
                'id_pelanggan'          => $this->id_pelanggan,
                'catatan'               => $this->catatan,
                'total_pesanan'         => $this->total_pesanan,
                'total_komisi_karyawan' => $this->total_komisi,
                'total_sub_total'       => $this->total_sub_total,
                'total_diskon'          => $this->total_diskon,
                'total_akhir'           => $this->total_akhir,
                'laba_bersih'           => $this->total_akhir - $this->total_komisi,
                'id_metode_pembayaran'  => $this->id_metode_pembayaran,
                'jumlah_dibayarkan'     => $this->jumlah_dibayarkan,
                'kembalian'             => $this->kembalian,
                'status'                => $status,
            ]);

            $this->syncSetoranTransferHarian();
            DB::commit();

            // Kirim notifikasi sukses dan tutup modal
            $this->dispatchSwalTransaksi($transaksi->id);
            $this->resetInputFields();
            $this->dispatch('closePembayaranModal');
            $this->dispatch('printNota', id: $transaksi->id);
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
            $transaksi = Transaksi::findOrFail($this->dataId);

            // Ambil detail transaksi untuk restorasi stok
            $details = DetailTransaksi::where('id_transaksi', $transaksi->id)->get();
            $restoreStockMap = [];
            foreach ($details as $d) {
                if (in_array($d->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                    $restoreStockMap[$d->id_produk] = ($restoreStockMap[$d->id_produk] ?? 0) + $d->jumlah;
                }
            }

            // Kembalikan stok produk
            if ($restoreStockMap) {
                $sql = "UPDATE produk SET stock = CASE";
                foreach ($restoreStockMap as $id => $qty) {
                    $sql .= " WHEN id = $id THEN stock + $qty";
                }
                $sql .= " END WHERE id IN (" . implode(',', array_keys($restoreStockMap)) . ")";
                DB::statement($sql);
            }

            // Hapus data terkait
            DetailTransaksi::where('id_transaksi', $transaksi->id)->delete();

            DB::table('persediaan')
                ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
                ->where('id_cabang', $this->id_cabang)
                ->delete();

            // Hapus transaksi utama
            $transaksi->delete();

            $this->syncSetoranTransferHarian();
            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data berhasil dihapus.');
            $this->dataId = null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function dibatalkan()
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($this->dataId);

            // Ambil semua item detail transaksi
            $details = DetailTransaksi::where('id_transaksi', $transaksi->id)->get();

            // Hitung produk yang perlu dikembalikan ke stok
            $restoreStock = [];
            foreach ($details as $detail) {
                if (in_array($detail->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                    $restoreStock[$detail->id_produk] = ($restoreStock[$detail->id_produk] ?? 0) + $detail->jumlah;
                }
            }

            // Update stok: tambah kembali
            if ($restoreStock) {
                $sql = "UPDATE produk SET stock = CASE";
                foreach ($restoreStock as $id => $qty) {
                    $sql .= " WHEN id = $id THEN stock + $qty";
                }
                $sql .= " END WHERE id IN (" . implode(',', array_keys($restoreStock)) . ")";
                DB::statement($sql);
            }

            DB::table('persediaan')
                ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
                ->where('id_cabang', $this->id_cabang)
                ->delete();

            // Update status transaksi menjadi dibatalkan (status = 4)
            $transaksi->update(['status' => '4']);

            $this->syncSetoranTransferHarian();
            DB::commit();

            $this->dispatchAlert('success', 'Success!', 'Jadwal Booking berhasil dibatalkan dan stok dikembalikan.');
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

    private function dispatchSwalTransaksi($idTransaksi)
    {
        $this->dispatch('swal:transaksi', [
            'idTransaksi' => Crypt::encrypt($idTransaksi), // enkripsi ID,
            'message'     => 'Transaksi berhasil!',
            'text'        => 'Apakah kamu ingin mencetak struk sekarang?',
        ]);
    }

    public function updatedIdCabang(GlobalDataService $globalDataService)
    {
        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks    = $globalDataService->getProdukAndKategoriCustom($this->id_cabang);
        $this->karyawans  = $globalDataService->getKaryawansCustom($this->id_cabang);

        $this->dispatch('refreshList');

        $this->cartItems = [];
        $this->resetInputFields();
    }

    public function listProduk()
    {
        $this->initSelect2();
    }

    public function updatedSearchProduk()
    {
        $search = '%' . $this->searchProduk . '%';

        $this->produks = DB::table('produk')->select('produk.id', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id_cabang', $this->id_cabang)
            ->where(function ($query) use ($search) {
                $query->where('nama_item', 'LIKE', $search);
                $query->orWhere('produk.deskripsi', 'LIKE', $search);
            })
            ->get();
    }

    public function cartProduk($id)
    {
        $data = DB::table('produk')->select('produk.id', 'produk.id_kategori', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id', $id)
            ->first();
        $this->id_produk         = $data->id;
        $this->check_id_kategori = $data->id_kategori;
        $this->nama_item         = $data->nama_item;
        $this->deskripsi_item    = $data->deskripsi;
        $this->kategori_item     = $data->nama_kategori;
        $this->harga_item        = $data->harga_jasa;
        $this->sub_total         = $this->harga_item * $this->jumlah;
        $this->total_harga       = $this->sub_total;

        if (in_array($this->check_id_kategori, ['2', '3'])) {
            $this->karyawans = DB::table('daftar_karyawan')
                ->select('daftar_karyawan.id', 'users.name')
                ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->join('komisi', 'komisi.id_karyawan', '=', 'daftar_karyawan.id')
                ->where('daftar_karyawan.role_id', 'capster')
                ->where('komisi.id_produk', $data->id)
                ->where('komisi.komisi_persen', '>', 0)
                ->where('daftar_karyawan.id_cabang', $this->id_cabang)
                ->distinct()
                ->get();
        } else if ($this->check_id_kategori == "4") {
            $this->karyawans = DB::table('daftar_karyawan')
                ->select('daftar_karyawan.id', 'users.name')
                ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->whereIn('daftar_karyawan.role_id', ['kasir'])
                ->where('daftar_karyawan.id_cabang', $this->id_cabang)
                ->get();
        } else {
            $this->karyawans = DB::table('daftar_karyawan')
                ->select('daftar_karyawan.id', 'users.name')
                ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->whereIn('daftar_karyawan.role_id', ['capster', 'kasir'])
                ->where('daftar_karyawan.id_cabang', $this->id_cabang)
                ->get();
        }

        // $this->id_karyawan = $this->karyawans->first()->id ?? null;
        $this->id_karyawan = '';
        $this->updatedIdKaryawan();

        $this->initSelect2();
    }

    public function updatedIdKaryawan()
    {
        // dd($this->check_id_kategori);
        if ($this->check_id_kategori == "2" || $this->check_id_kategori == "3") {
            // dd($this->id_karyawan, $this->id_produk);
            $this->komisi_persen = DB::table('komisi')
                ->select('komisi_persen')
                ->where('id_karyawan', $this->id_karyawan)
                ->where('id_produk', $this->id_produk)
                ->first()->komisi_persen ?? 0;

            $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
            // dd($this->komisi_persen);
        } else {
            $this->komisi_persen = DB::table('produk')
                ->select('komisi')
                ->where('id', $this->id_produk)
                ->first()->komisi;

            $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
        }

        // dd($this->komisi_persen);
    }

    public function incrementJumlah()
    {
        $this->initSelect2();
        $this->jumlah++;
        $this->sub_total = (int)$this->harga_item * (int)$this->jumlah;

        if ($this->input_diskon > 0) {
            $this->updatedInputDiskon();
        } else {
            $this->total_harga = $this->sub_total;
            $this->diskon = 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
    }

    public function decrementJumlah()
    {
        $this->initSelect2();
        if ($this->jumlah > 1) {
            $this->jumlah--;
        }

        $this->sub_total = (int)$this->harga_item * (int)$this->jumlah;

        if ($this->input_diskon > 0) {
            $this->updatedInputDiskon();
        } else {
            $this->total_harga = $this->sub_total;
            $this->diskon = 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
    }

    public function updatedIsPersentase()
    {
        $this->input_diskon = 0;
        $this->diskon       = 0;
        $this->total_harga  = $this->sub_total;
    }

    public function updatedInputDiskon()
    {
        if ($this->isPersentase) {
            // Diskon dalam persen dari subtotal
            $this->diskon = (int)$this->sub_total * (int)$this->input_diskon / 100;
        } else {
            // Diskon dalam nominal langsung
            $this->diskon = (int)$this->input_diskon;
        }

        // Opsional: total akhir setelah diskon
        $this->total_harga = $this->sub_total - $this->diskon;
    }

    public function cancelCartItems()
    {
        $this->komisi_persen  = 0;
        $this->komisi_nominal = 0;
        $this->jumlah         = 1;
        $this->input_diskon   = 0;
        $this->diskon         = 0;
        $this->dispatch('initSelect2');
    }

    public function addCartItems()
    {
        $this->dispatch('initSelect2');
        $this->validate([
            'id_karyawan' => 'required',
        ], [
            'id_karyawan.required' => 'Karyawan wajib dipilih.',
        ]);

        $this->nama_karyawan = null;

        if ($this->id_karyawan) {
            $this->nama_karyawan = DB::table('daftar_karyawan')
                ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
                ->where('daftar_karyawan.id', $this->id_karyawan)
                ->value('users.name');
        }

        $this->cartItems[] = [
            'id_produk'      => $this->id_produk,
            'nama_item'      => $this->nama_item,
            'kategori_item'  => $this->kategori_item,
            'deskripsi_item' => $this->deskripsi_item,
            'harga'          => $this->harga_item,
            'jumlah'         => $this->jumlah,
            'sub_total'      => $this->sub_total,
            'diskon'         => $this->diskon,
            'total_harga'    => $this->total_harga,
            'id_karyawan'    => $this->id_karyawan,
            'nama_karyawan'  => $this->nama_karyawan,
            'komisi_persen'  => $this->komisi_persen,
            'komisi_nominal' => $this->komisi_nominal,
        ];

        $this->dispatch('closeCart');
        $this->cancelCartItems();
        $this->getRingkasanTransaksi();
    }

    public function deleteCartItems($index)
    {
        unset($this->cartItems[$index]);                   // Hapus item di posisi tertentu
        $this->cartItems = array_values($this->cartItems); // Reindex array agar key berurutan
        $this->getRingkasanTransaksi();                    // Rehitung total ringkasan
        $this->dispatch('initSelect2');                   // Inisialisasi ulang Select2 jika diperlukan
    }

    public function getRingkasanTransaksi()
    {
        // Reset semua nilai ringkasan
        $total_pesanan   = 0;
        $total_sub_total = 0;
        $total_diskon    = 0;
        $total_akhir     = 0;
        $total_komisi    = 0;

        // Hitung total dari masing-masing item
        foreach ($this->cartItems as $item) {
            $total_pesanan      += $item['jumlah'] ?? 0;
            $total_sub_total    += $item['sub_total'] ?? 0;
            $total_diskon       += $item['diskon'] ?? 0;
            $total_komisi       += $item['komisi_nominal'] ?? 0;
            $total_akhir        += $item['total_harga'] ?? 0;
        }

        // Simpan hasil ke properti Livewire
        $this->total_pesanan   = $total_pesanan;
        $this->total_sub_total = $total_sub_total;
        $this->total_diskon    = $total_diskon;
        $this->total_komisi    = $total_komisi;
        $this->total_akhir     = $total_akhir;
    }

    public function formPembayaran()
    {
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $this->dispatch('initSelect2');
    }

    public function uangPas()
    {
        $this->jumlah_dibayarkan = $this->total_akhir;

        $this->initSelect2();
    }
    // ------------------ END formPembayaranModal Section ------------------

    private function resetInputFields()
    {
        $this->id_metode_pembayaran = $this->pembayarans->first()->id;
        $this->id_pelanggan         = $this->pelanggans->first()->id;

        $this->cartItems         = [];

        $this->tanggal           = now()->format('Y-m-d\TH:i');

        $this->catatan           = '-';
        $this->total_pesanan     = 0;
        $this->total_komisi      = 0;
        $this->total_sub_total   = 0;
        $this->total_diskon      = 0;
        $this->total_akhir       = 0;
        $this->laba_bersih       = 0;
        $this->jumlah_dibayarkan = 0;
        $this->kembalian         = 0;

        $this->nama_item         = '';
        $this->kategori_item     = '-';
        $this->deskripsi_item    = '-';
        $this->harga_item        = 0;
        $this->jumlah            = 1;
        $this->sub_total         = 0;
        $this->input_diskon      = 0;
        $this->diskon            = 0;
        $this->total_harga       = 0;
        $this->id_karyawan       = '';
        $this->nama_karyawan     = '-';
        $this->komisi_persen     = 0;
        $this->komisi_nominal    = 0;

        $this->initSelect2();
    }

    public function updated()
    {
        $this->dispatch('initSelect2');
    }

    public function cancel()
    {
        $this->resetInputFields();
    }

    public function cancelList()
    {
        $this->initSelect2();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function generateNoTransaksi($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = TransaksiCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TransaksiCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "TRX/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }

    public function resetFilter()
    {
        $this->filter_status     = '';
        $this->filter_pembayaran = '';
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
