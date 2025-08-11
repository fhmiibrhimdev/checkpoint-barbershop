<?php

namespace App\Livewire\Caspter\Transaksi;

use Carbon\Carbon;
use App\Models\Kas;
use Livewire\Component;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\DetailTransaksi;
use App\Models\TransaksiCounter;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Transaksi as ModelsTransaksi;

class Transaksi extends Component
{
    use WithPagination;

    #[Title('Capster | Transaksi')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = ['delete'];

    protected $rules = [
        'id_cabang' => 'required',
        'id_pelanggan' => 'required',
        'id_metode_pembayaran' => 'required',
    ];

    // UI State Properties
    public $lengthData = 25;
    public $searchTerm, $searchProduk;
    public $previousSearchTerm = '';
    public $isEditing = false;
    public $dataId;
    public $isPersentase = false;
    public $check_id_kategori;

    // Filter Properties
    public $filter_status, $filter_pembayaran = '', $filter_id_cabang;

    // Master Data Collections
    public $cabangs, $pelanggans, $produks, $pembayarans, $karyawans = [];
    public $cartItems = [];
    public $user;

    /**
     * @var \Illuminate\Support\Collection
     */
    // Report Properties
    public $detailOmset = [], $detailPembayaranCash = [], $detailPembayaranTransfer = [], $detailPiutang = [];
    public $totalDetailOmset, $totalDetailPembayaranCash, $totalDetailPembayaranTransfer, $totalDetailPiutang;
    public $totalOmset, $totalTunai, $totalTransfer, $totalPembayaran, $totalPiutang;

    // Transaksi Properties
    public $id_cabang, $id_user, $no_transaksi, $tanggal, $id_pelanggan, $catatan;
    public $total_pesanan, $total_komisi, $total_sub_total, $total_diskon, $total_akhir;
    public $total_hpp, $laba_bersih, $id_metode_pembayaran, $jumlah_dibayarkan, $kembalian, $status;

    // Detail Transaksi Properties
    public $id_transaksi, $id_produk, $nama_item, $kategori_item, $deskripsi_item, $harga_item;
    public $harga_pokok, $jumlah, $sub_total, $input_diskon, $diskon, $total_harga;
    public $id_karyawan, $nama_karyawan, $komisi_persen, $komisi_nominal;
    public $flag_reset_kunjungan = false;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->initializeServices($globalDataService);
        $this->loadMasterData();
        $this->initializeUserData();
        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $data = $this->getTransaksiData();
        return view('livewire.caspter.transaksi.transaksi', compact('data'));
    }

    // ============================================================================
    // INITIALIZATION METHODS
    // ============================================================================

    private function initializeServices(GlobalDataService $globalDataService): void
    {
        $this->globalDataService = $globalDataService;
        $this->user = Auth::user();
    }

    private function loadMasterData(): void
    {
        // Load data master dari service
        // $this->cabangs = $this->globalDataService->getCabangs();
        $this->pembayarans = $this->globalDataService->getMetodePembayaran();

        // Set cabang default dari user
        $id_cabang = $this->user->id_cabang;
        $this->id_cabang = $id_cabang;
        $this->filter_id_cabang = $id_cabang;

        // Load data tergantung cabang
        $this->pelanggans = $this->globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks = $this->getProdukByCabang($this->id_cabang);
    }

    private function initializeUserData(): void
    {
        $this->id_user = $this->user->id;

        // Ambil ID karyawan dari user yang login
        $this->id_karyawan = DB::table('daftar_karyawan')
            ->where('id_user', $this->user->id)
            ->value('id');

        $this->getReportHarian();
    }

    // ============================================================================
    // DATA RETRIEVAL METHODS
    // ============================================================================

    private function getTransaksiData()
    {
        $search = '%' . $this->searchTerm . '%';

        // Query utama dengan optimized subquery
        return DB::table('transaksi as t')
            ->select([
                't.id',
                't.no_transaksi',
                't.tanggal',
                't.total_akhir',
                't.status',
                'dp.nama_pelanggan',
                'dp.no_telp',
                'kp.nama_kategori',
                'detail.nama_item',
                'detail.deskripsi_item',
                'detail.id_karyawan',
                'jumlah.jumlah_produk'
            ])
            ->join('daftar_pelanggan as dp', 'dp.id', '=', 't.id_pelanggan')
            ->join('kategori_pembayaran as kp', 'kp.id', '=', 't.id_metode_pembayaran')
            ->leftJoinSub(
                // Subquery untuk detail transaksi (ambil yang pertama saja)
                DB::table('detail_transaksi')
                    ->select('id_transaksi', 'nama_item', 'deskripsi_item', 'id_karyawan')
                    ->whereRaw('id = (SELECT MIN(id) FROM detail_transaksi dt2 WHERE dt2.id_transaksi = detail_transaksi.id_transaksi)'),
                'detail',
                'detail.id_transaksi',
                '=',
                't.id'
            )
            ->leftJoinSub(
                // Subquery untuk count produk
                DB::table('detail_transaksi')
                    ->select('id_transaksi', DB::raw('COUNT(*) as jumlah_produk'))
                    ->groupBy('id_transaksi'),
                'jumlah',
                'jumlah.id_transaksi',
                '=',
                't.id'
            )
            ->where(function ($query) use ($search) {
                $query->where('t.no_transaksi', 'LIKE', $search)
                    ->orWhere('dp.nama_pelanggan', 'LIKE', $search);
            })
            ->when($this->filter_status, fn($query) => $query->where('t.status', $this->filter_status))
            ->when($this->filter_pembayaran, fn($query) => $query->where('t.id_metode_pembayaran', $this->filter_pembayaran))
            ->whereBetween('t.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->where('t.id_cabang', $this->filter_id_cabang)
            ->orderByRaw('(detail.id_karyawan = ?) DESC', [$this->id_karyawan])
            ->orderBy('t.status')
            ->orderBy('t.tanggal', 'desc')
            ->orderBy('t.id', 'desc')
            ->paginate($this->lengthData);
    }

    private function getProdukByCabang($id_cabang)
    {
        return DB::table('produk as p')
            ->select([
                'p.id',
                'p.nama_item',
                'p.harga_jasa',
                'p.deskripsi',
                'p.stock',
                'kp.nama_kategori'
            ])
            ->join('kategori_produk as kp', 'kp.id', '=', 'p.id_kategori')
            ->leftJoin('komisi as k', 'k.id_produk', '=', 'p.id')
            ->where('p.id_cabang', $id_cabang)
            ->whereRaw("LOWER(TRIM(kp.nama_kategori)) <> 'produk umum'")
            ->where(function ($query) {
                // Filter untuk Jasa Barbershop & Treatment (wajib ada komisi)
                $query->where(function ($q) {
                    $q->whereIn('kp.nama_kategori', ['Jasa Barbershop', 'Treatment'])
                        ->where('k.komisi_persen', '>', 0)
                        ->whereNotNull('k.komisi_persen')
                        ->whereNotNull('k.id');
                })
                    // Filter untuk kategori lain (selalu tampil)
                    ->orWhereNotIn('kp.nama_kategori', ['Jasa Barbershop', 'Treatment']);
            })
            ->where(function ($query) {
                // Filter stock untuk produk
                $query->where(function ($q) {
                    $q->where('kp.nama_kategori', 'Produk Barbershop')
                        ->where('p.stock', '>', 0);
                })
                    ->orWhere(function ($q) {
                        $q->where('kp.nama_kategori', 'Produk Umum')
                            ->where('p.stock', '>', 0);
                    })
                    // Untuk kategori selain produk, tidak perlu cek stock
                    ->orWhereNotIn('kp.nama_kategori', ['Produk Barbershop', 'Produk Umum']);
            })
            ->get();
    }

    private function getReportHarian(): void
    {
        // Get data transaksi hari ini
        $transaksiHariIni = $this->getTransaksiHariIni();

        // Get data pembayaran piutang hari ini
        $piutangHariIni = $this->getPiutangHariIni();

        // Hitung total
        $this->totalOmset = $transaksiHariIni->total_omset ?? 0;
        $this->totalTunai = ($transaksiHariIni->total_tunai ?? 0) + ($piutangHariIni->piutang_tunai ?? 0);
        $this->totalTransfer = ($transaksiHariIni->total_transfer ?? 0) + ($piutangHariIni->piutang_transfer ?? 0);
        $this->totalPembayaran = $this->totalTunai + $this->totalTransfer;
        $this->totalPiutang = $transaksiHariIni->total_piutang ?? 0;
    }

    private function getTransaksiHariIni()
    {
        return DB::table('transaksi')
            ->selectRaw('
                SUM(CAST(total_akhir AS DECIMAL(15,2))) as total_omset,
                SUM(CASE 
                    WHEN id_metode_pembayaran = 1 
                    THEN LEAST(CAST(jumlah_dibayarkan AS DECIMAL(15,2)), CAST(total_akhir AS DECIMAL(15,2))) 
                    ELSE 0 
                END) as total_tunai,
                SUM(CASE 
                    WHEN id_metode_pembayaran = 2 
                    THEN LEAST(CAST(jumlah_dibayarkan AS DECIMAL(15,2)), CAST(total_akhir AS DECIMAL(15,2))) 
                    ELSE 0 
                END) as total_transfer,
                SUM(CASE 
                    WHEN CAST(kembalian AS DECIMAL(15,2)) < 0 
                    THEN CAST(kembalian AS DECIMAL(15,2)) 
                    ELSE 0 
                END) as total_piutang
            ')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ['1', '2', '3'])
            ->where('id_cabang', $this->filter_id_cabang)
            ->first();
    }

    private function getPiutangHariIni()
    {
        return DB::table('piutang as p')
            ->join('transaksi as t', 'p.id_transaksi', '=', 't.id')
            ->selectRaw('
                SUM(CASE WHEN p.id_metode_pembayaran = 1 THEN p.jumlah_bayar ELSE 0 END) as piutang_tunai,
                SUM(CASE WHEN p.id_metode_pembayaran = 2 THEN p.jumlah_bayar ELSE 0 END) as piutang_transfer
            ')
            ->whereDate('p.tanggal_bayar', Carbon::today())
            ->where('t.id_cabang', $this->filter_id_cabang)
            ->first();
    }

    // ============================================================================
    // TRANSACTION METHODS
    // ============================================================================

    public function store()
    {
        $this->validate();

        if (empty($this->cartItems)) {
            $this->dispatchAlert('error', 'Gagal!', 'Silahkan isi items dulu ke keranjang!');
            return;
        }

        // Validasi stok untuk produk
        if (!$this->validateStock()) {
            return;
        }

        DB::beginTransaction();
        try {
            $transaksi = $this->createTransaksi();
            $this->processCartItems($transaksi);
            $this->syncSetoranTransferHarian();

            DB::commit();

            $this->handleSuccessfulTransaction($transaksi);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function validateStock(): bool
    {
        $produkIdsToCheck = [];

        // Ambil ID produk yang perlu dicek stoknya
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $produkIdsToCheck[] = $item['id_produk'];
            }
        }

        if (empty($produkIdsToCheck)) {
            return true;
        }

        // Ambil stok produk dari database
        $stokProduk = DB::table('produk')
            ->whereIn('id', $produkIdsToCheck)
            ->pluck('stock', 'id');

        // Validasi stok untuk setiap item
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $stokTersedia = $stokProduk[$item['id_produk']] ?? 0;

                if ($item['jumlah'] > $stokTersedia) {
                    $this->dispatchAlert(
                        'error',
                        'Stok Tidak Cukup',
                        "Stok untuk '{$item['nama_item']}' hanya tersedia {$stokTersedia}, tidak bisa membeli {$item['jumlah']}."
                    );
                    return false;
                }
            }
        }

        return true;
    }

    private function createTransaksi(): ModelsTransaksi
    {
        // Hitung kembalian dan status
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $status = $this->kembalian < 0 ? '2' : '3';

        // Generate nomor transaksi
        $this->no_transaksi = $this->generateNoTransaksi($this->id_cabang);

        return ModelsTransaksi::create([
            'id_cabang'             => $this->id_cabang,
            'id_user'               => $this->id_user,
            'no_transaksi'          => $this->no_transaksi,
            'tanggal'               => now(),
            'id_pelanggan'          => $this->id_pelanggan,
            'catatan'               => $this->catatan,
            'total_pesanan'         => $this->total_pesanan,
            'total_komisi_karyawan' => $this->total_komisi,
            'total_sub_total'       => $this->total_sub_total,
            'total_diskon'          => $this->total_diskon,
            'total_akhir'           => $this->total_akhir,
            'total_hpp'             => $this->total_hpp,
            'laba_bersih'           => $this->laba_bersih,
            'id_metode_pembayaran'  => $this->id_metode_pembayaran,
            'jumlah_dibayarkan'     => $this->jumlah_dibayarkan,
            'kembalian'             => $this->kembalian,
            'status'                => $status,
        ]);
    }

    private function processCartItems(ModelsTransaksi $transaksi): void
    {
        $detailData = [];
        $persediaanData = [];
        $stockUpdates = [];

        foreach ($this->cartItems as $item) {
            // Siapkan data detail transaksi
            $detailData[] = [
                'id_transaksi'   => $transaksi->id,
                'id_produk'      => $item['id_produk'],
                'nama_item'      => $item['nama_item'],
                'kategori_item'  => $item['kategori_item'],
                'deskripsi_item' => $item['deskripsi_item'],
                'harga'          => $item['harga'],
                'harga_pokok'    => $item['harga_pokok'],
                'jumlah'         => $item['jumlah'],
                'sub_total'      => $item['sub_total'],
                'diskon'         => $item['diskon'],
                'total_harga'    => $item['total_harga'],
                'id_karyawan'    => $item['id_karyawan'],
                'nama_karyawan'  => $item['nama_karyawan'],
                'komisi_persen'  => $item['komisi_persen'],
                'komisi_nominal' => $item['komisi_nominal'],
            ];

            // Proses produk yang perlu pengurangan stok
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                // Akumulasi stok untuk batch update
                $stockUpdates[$item['id_produk']] = ($stockUpdates[$item['id_produk']] ?? 0) + $item['jumlah'];

                // Siapkan data persediaan
                $persediaanData[] = [
                    'id_cabang'  => $this->id_cabang,
                    'id_user'    => (int)$item['id_karyawan'] + 1,
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

        // Batch insert detail transaksi
        DetailTransaksi::insert($detailData);

        // Update total kunjungan
        if ($this->flag_reset_kunjungan) {
            // Reset ke 0 jika sudah 10 kali
            DB::table('daftar_pelanggan')->where('id', $this->id_pelanggan)
                ->update(['total_kunjungan' => 0]);
        } else {
            // Tambah 1 kalau belum 10 kali
            DB::table('daftar_pelanggan')->where('id', $this->id_pelanggan)
                ->increment('total_kunjungan');
        }

        // Batch insert persediaan
        if (!empty($persediaanData)) {
            DB::table('persediaan')->insert($persediaanData);
        }

        // Batch update stok
        if (!empty($stockUpdates)) {
            $this->batchUpdateStock($stockUpdates, '-');
        }
    }

    private function batchUpdateStock(array $stockUpdates, string $operation): void
    {
        $sql = "UPDATE produk SET stock = CASE ";
        $ids = [];

        foreach ($stockUpdates as $id_produk => $jumlah) {
            $sql .= "WHEN id = {$id_produk} THEN stock {$operation} {$jumlah} ";
            $ids[] = $id_produk;
        }

        $sql .= "END WHERE id IN (" . implode(',', $ids) . ")";
        DB::statement($sql);
    }

    private function handleSuccessfulTransaction(ModelsTransaksi $transaksi): void
    {
        $this->dispatchSwalTransaksi($transaksi->id);
        $this->resetInputFields();
        $this->dispatch('closePembayaranModal');
        $this->dispatch('printNota', id: $transaksi->id);
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Rollback stok dan data lama
            $this->rollbackOldTransaction($transaksi);

            // Proses data baru
            $this->processCartItems($transaksi);

            // Update transaksi utama
            $this->updateTransaksiData($transaksi);

            $this->syncSetoranTransferHarian();
            DB::commit();

            $this->handleSuccessfulTransaction($transaksi);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function rollbackOldTransaction(ModelsTransaksi $transaksi): void
    {
        // Ambil detail transaksi lama untuk rollback stok
        $oldDetails = DB::table('detail_transaksi')
            ->where('id_transaksi', $transaksi->id)
            ->get();

        $stockRollback = [];
        foreach ($oldDetails as $detail) {
            if (in_array($detail->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                $stockRollback[$detail->id_produk] = ($stockRollback[$detail->id_produk] ?? 0) + $detail->jumlah;
            }
        }

        // Kembalikan stok
        if (!empty($stockRollback)) {
            $this->batchUpdateStock($stockRollback, '+');
        }

        // Hapus data lama
        DB::table('detail_transaksi')->where('id_transaksi', $transaksi->id)->delete();
        DB::table('persediaan')
            ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
            ->where('id_cabang', $this->id_cabang)
            ->delete();
    }

    private function updateTransaksiData(ModelsTransaksi $transaksi): void
    {
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $status = $this->kembalian < 0 ? '2' : '3';

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
    }

    // ============================================================================
    // DELETE & CANCEL METHODS
    // ============================================================================

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
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Rollback stok produk
            $this->rollbackTransactionStock($transaksi);

            // Hapus data terkait
            $this->deleteRelatedData($transaksi);

            // Hapus transaksi utama
            $transaksi->delete();

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
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Rollback stok produk
            $this->rollbackTransactionStock($transaksi);

            // Hapus data persediaan
            DB::table('persediaan')
                ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
                ->where('id_cabang', $this->id_cabang)
                ->delete();

            // Update status menjadi dibatalkan
            $transaksi->update(['status' => '4']);

            // Hapus piutang terkait
            DB::table('piutang')->where('id_transaksi', $transaksi->id)->delete();

            // Kurangi total kunjungan jika transaksi ini sebelumnya dihitung
            if (in_array($transaksi->status, ['1', '2', '3'])) {
                DB::table('daftar_pelanggan')->where('id', $transaksi->id_pelanggan)
                    ->decrement('total_kunjungan');
            }

            $this->syncSetoranTransferHarian();
            DB::commit();

            $this->dispatchAlert('success', 'Success!', 'Transaksi berhasil dibatalkan dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function rollbackTransactionStock(ModelsTransaksi $transaksi): void
    {
        $details = DB::table('detail_transaksi')
            ->where('id_transaksi', $transaksi->id)
            ->get();

        $stockRollback = [];
        foreach ($details as $detail) {
            if (in_array($detail->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                $stockRollback[$detail->id_produk] = ($stockRollback[$detail->id_produk] ?? 0) + $detail->jumlah;
            }
        }

        if (!empty($stockRollback)) {
            $this->batchUpdateStock($stockRollback, '+');
        }
    }

    private function deleteRelatedData(ModelsTransaksi $transaksi): void
    {
        DB::table('detail_transaksi')->where('id_transaksi', $transaksi->id)->delete();
        DB::table('persediaan')
            ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
            ->where('id_cabang', $this->id_cabang)
            ->delete();
    }

    // ============================================================================
    // EDIT & UPDATE METHODS
    // ============================================================================

    public function edit(GlobalDataService $globalDataService, $id)
    {
        $this->isEditing = true;
        $this->dataId = $id;

        // Load transaksi data
        $this->loadTransaksiForEdit($id);

        // Load master data
        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks = $this->getProdukByCabang($this->id_cabang);

        // Load cart items from detail transaksi
        $this->loadCartItemsForEdit($id);

        $this->initSelect2();
    }

    private function loadTransaksiForEdit($id): void
    {
        $transaksi = ModelsTransaksi::findOrFail($id);

        // Map semua properties transaksi
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
        $this->total_hpp            = $transaksi->total_hpp;
        $this->laba_bersih          = $transaksi->laba_bersih;
        $this->id_metode_pembayaran = $transaksi->id_metode_pembayaran;
        $this->jumlah_dibayarkan    = $transaksi->jumlah_dibayarkan;
        $this->kembalian            = $transaksi->kembalian;
        $this->status               = $transaksi->status;
    }

    private function loadCartItemsForEdit($id): void
    {
        $details = DB::table('detail_transaksi')
            ->where('id_transaksi', $id)
            ->get();

        $this->cartItems = [];
        foreach ($details as $item) {
            $this->cartItems[] = [
                'id_produk'      => $item->id_produk,
                'nama_item'      => $item->nama_item,
                'kategori_item'  => $item->kategori_item,
                'deskripsi_item' => $item->deskripsi_item,
                'harga'          => $item->harga,
                'harga_pokok'    => $item->harga_pokok ?? 0,
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

    // ============================================================================
    // CART MANAGEMENT METHODS
    // ============================================================================

    public function cartProduk($id)
    {
        // Get product data with category info
        $data = DB::table('produk as p')
            ->select([
                'p.id',
                'p.id_kategori',
                'p.nama_item',
                'p.harga_jasa',
                'p.harga_pokok',
                'p.deskripsi',
                'kp.nama_kategori'
            ])
            ->join('kategori_produk as kp', 'kp.id', '=', 'p.id_kategori')
            ->where('p.id', $id)
            ->first();

        if (!$data) {
            $this->dispatchAlert('error', 'Error!', 'Produk tidak ditemukan.');
            return;
        }

        // Set product data to form
        $this->setProductDataToForm($data);

        // Load karyawan based on category
        $this->loadKaryawanByCategory($data);

        // Update komisi
        $this->updatedIdKaryawan();

        $this->initSelect2();
    }

    private function setProductDataToForm($data): void
    {
        $this->id_produk         = $data->id;
        $this->check_id_kategori = $data->id_kategori;
        $this->nama_item         = $data->nama_item;
        $this->deskripsi_item    = $data->deskripsi;
        $this->kategori_item     = $data->nama_kategori;
        $this->harga_item        = $data->harga_jasa;
        $this->harga_pokok       = $data->harga_pokok ?? 0;
        $this->sub_total         = $this->harga_item * $this->jumlah;
        $this->total_harga       = $this->sub_total;
        $this->id_karyawan       = $this->id_karyawan ?: $this->id_karyawan;
    }

    private function loadKaryawanByCategory($data): void
    {
        if (in_array($this->check_id_kategori, ['2', '3'])) {
            // Kategori Jasa Barbershop & Treatment - harus ada komisi
            $this->karyawans = DB::table('daftar_karyawan as dk')
                ->select('dk.id', 'u.name')
                ->join('users as u', 'u.id', '=', 'dk.id_user')
                ->join('komisi as k', 'k.id_karyawan', '=', 'dk.id')
                ->where('k.id_produk', $data->id)
                ->where('k.komisi_persen', '>', 0)
                ->where('dk.id', $this->id_karyawan)
                ->distinct()
                ->get();
        } else {
            // Kategori lain
            $this->karyawans = DB::table('daftar_karyawan as dk')
                ->select('dk.id', 'u.name')
                ->join('users as u', 'u.id', '=', 'dk.id_user')
                ->where('dk.id', $this->id_karyawan)
                ->get();
        }
    }

    public function addCartItems()
    {
        $this->validate([
            'id_karyawan' => 'required',
        ], [
            'id_karyawan.required' => 'Karyawan wajib dipilih.',
        ]);

        // Get karyawan name
        $this->nama_karyawan = DB::table('daftar_karyawan as dk')
            ->join('users as u', 'u.id', '=', 'dk.id_user')
            ->where('dk.id', $this->id_karyawan)
            ->value('u.name');

        // Add to cart
        $this->cartItems[] = [
            'id_produk'      => $this->id_produk,
            'nama_item'      => $this->nama_item,
            'kategori_item'  => $this->kategori_item,
            'deskripsi_item' => $this->deskripsi_item,
            'harga'          => $this->harga_item,
            'harga_pokok'    => $this->harga_pokok,
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
        // Remove item and reindex array
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);

        $this->getRingkasanTransaksi();
        $this->dispatch('initSelect2');
    }

    public function getRingkasanTransaksi()
    {
        // Initialize totals
        $totals = [
            'pesanan' => 0,
            'sub_total' => 0,
            'diskon' => 0,
            'komisi' => 0,
            'akhir' => 0,
            'hpp' => 0
        ];

        // Calculate totals from cart items
        foreach ($this->cartItems as $item) {
            $totals['pesanan']   += $item['jumlah'] ?? 0;
            $totals['sub_total'] += $item['sub_total'] ?? 0;
            $totals['diskon']    += $item['diskon'] ?? 0;
            $totals['komisi']    += $item['komisi_nominal'] ?? 0;
            $totals['akhir']     += $item['total_harga'] ?? 0;
            $totals['hpp']       += ($item['harga_pokok'] ?? 0) * ($item['jumlah'] ?? 0);
        }

        // Set calculated values
        $this->total_pesanan   = $totals['pesanan'];
        $this->total_sub_total = $totals['sub_total'];
        $this->total_diskon    = $totals['diskon'];
        $this->total_komisi    = $totals['komisi'];
        $this->total_akhir     = $totals['akhir'];
        $this->total_hpp       = $totals['hpp'];
        $this->laba_bersih     = $totals['sub_total'] - $totals['diskon'] - $totals['komisi'] - $totals['hpp'];
    }

    // ============================================================================
    // QUANTITY & DISCOUNT MANAGEMENT
    // ============================================================================

    public function incrementJumlah()
    {
        $this->jumlah++;
        $this->recalculateItemTotals();
    }

    public function decrementJumlah()
    {
        if ($this->jumlah > 1) {
            $this->jumlah--;
        }
        $this->recalculateItemTotals();
    }

    private function recalculateItemTotals(): void
    {
        $this->sub_total = (int)$this->harga_item * (int)$this->jumlah;

        if ($this->input_diskon > 0) {
            $this->updatedInputDiskon();
        } else {
            $this->total_harga = $this->sub_total;
            $this->diskon = 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
        $this->initSelect2();
    }

    public function updatedIsPersentase()
    {
        // Reset discount when percentage mode changes
        $this->input_diskon = 0;
        $this->diskon = 0;
        $this->total_harga = $this->sub_total;
    }

    public function updatedInputDiskon()
    {
        if ($this->isPersentase) {
            // Calculate percentage discount
            $this->diskon = (int)$this->sub_total * (int)$this->input_diskon / 100;
        } else {
            // Direct nominal discount
            $this->diskon = (int)$this->input_diskon;
        }

        $this->total_harga = $this->sub_total - $this->diskon;
    }

    public function updatedIdKaryawan()
    {
        if (in_array($this->check_id_kategori, ['2', '3'])) {
            // Get komisi for Jasa Barbershop & Treatment
            $this->komisi_persen = DB::table('komisi')
                ->where('id_karyawan', $this->id_karyawan)
                ->where('id_produk', $this->id_produk)
                ->value('komisi_persen') ?? 0;
        } else {
            // Get komisi from product for other categories
            $this->komisi_persen = DB::table('produk')
                ->where('id', $this->id_produk)
                ->value('komisi') ?? 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
    }

    // ============================================================================
    // SEARCH & FILTER METHODS
    // ============================================================================

    public function updatedSearchProduk()
    {
        if (empty($this->searchProduk)) {
            $this->produks = $this->getProdukByCabang($this->id_cabang);
            return;
        }

        $search = '%' . $this->searchProduk . '%';

        $this->produks = DB::table('produk as p')
            ->select('p.id', 'p.nama_item', 'p.harga_jasa', 'p.deskripsi', 'kp.nama_kategori')
            ->join('kategori_produk as kp', 'kp.id', '=', 'p.id_kategori')
            ->where('p.id_cabang', $this->id_cabang)
            ->where(function ($query) use ($search) {
                $query->where('p.nama_item', 'LIKE', $search)
                    ->orWhere('p.deskripsi', 'LIKE', $search);
            })
            ->get();
    }

    public function updatedIdCabang(GlobalDataService $globalDataService)
    {
        // Reload data when cabang changes
        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks    = $globalDataService->getProdukAndKategoriCustom($this->id_cabang);
        $this->karyawans  = $globalDataService->getKaryawansCustom($this->id_cabang);

        $this->dispatch('refreshList');

        // Reset cart and form when changing cabang
        $this->cartItems = [];
        $this->resetInputFields();
    }

    private function searchResetPage(): void
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
            $this->previousSearchTerm = $this->searchTerm;
        }
    }

    public function updatingLengthData()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->filter_status = '';
        $this->filter_pembayaran = '';
    }

    // ============================================================================
    // PAYMENT & MODAL METHODS
    // ============================================================================

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

    public function updateMetodePembayaran()
    {
        $this->validate([
            'id_metode_pembayaran' => 'required',
        ]);

        DB::beginTransaction();
        try {
            ModelsTransaksi::findOrFail($this->dataId)->update([
                'id_pelanggan' => $this->id_pelanggan,
                'id_metode_pembayaran' => $this->id_metode_pembayaran,
            ]);

            $this->syncSetoranTransferHarian();
            DB::commit();

            $this->dispatchAlert('success', 'Success!', 'Metode pembayaran berhasil diperbarui.');
            $this->resetInputFields();
            $this->dispatch('closePembayaranModal');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function directPiutang()
    {
        return redirect()->to('/keuangan/piutang/' . Crypt::encrypt($this->dataId));
    }

    // ============================================================================
    // REPORT MODAL METHODS
    // ============================================================================

    public function totalOmsetModal()
    {
        $this->detailOmset = DB::table('transaksi')
            ->select('no_transaksi', 'total_akhir as jumlah')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ['1', '2', '3'])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get();

        $this->totalDetailOmset = $this->detailOmset->sum('jumlah');
    }

    public function totalPembayaranCashModal()
    {
        $today = Carbon::today();

        // Get data from both transaksi and piutang
        $cashData = $this->getCombinedPaymentData(1, $today);

        $this->detailPembayaranCash = $cashData;
        $this->totalDetailPembayaranCash = collect($cashData)->sum('jumlah');
    }

    public function totalPembayaranTransferModal()
    {
        $today = Carbon::today();

        // Get data from both transaksi and piutang
        $transferData = $this->getCombinedPaymentData(2, $today);

        $this->detailPembayaranTransfer = $transferData;
        $this->totalDetailPembayaranTransfer = collect($transferData)->sum('jumlah');
    }

    private function getCombinedPaymentData($metodePembayaran, $tanggal): array
    {
        // Get transaksi data
        $transaksi = DB::table('transaksi')
            ->select([
                'no_transaksi',
                DB::raw('LEAST(CAST(jumlah_dibayarkan AS UNSIGNED), CAST(total_akhir AS UNSIGNED)) as jumlah_dibayarkan')
            ])
            ->where('id_metode_pembayaran', $metodePembayaran)
            ->whereDate('tanggal', $tanggal)
            ->whereIn('status', ['1', '2', '3'])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get();

        // Get piutang data
        $piutang = DB::table('piutang as p')
            ->select('p.no_referensi', 'p.jumlah_bayar')
            ->join('transaksi as t', 'p.id_transaksi', '=', 't.id')
            ->where('p.id_metode_pembayaran', $metodePembayaran)
            ->whereDate('p.tanggal_bayar', $tanggal)
            ->where('t.id_cabang', $this->filter_id_cabang)
            ->get();

        $gabungan = [];

        // Add transaksi data
        foreach ($transaksi as $item) {
            if ($item->jumlah_dibayarkan > 0) {
                $gabungan[] = [
                    'no_transaksi' => $item->no_transaksi,
                    'sumber' => 'Transaksi',
                    'jumlah' => $item->jumlah_dibayarkan
                ];
            }
        }

        // Add piutang data
        foreach ($piutang as $item) {
            if ($item->jumlah_bayar > 0) {
                $gabungan[] = [
                    'no_transaksi' => $item->no_referensi,
                    'sumber' => 'Piutang',
                    'jumlah' => $item->jumlah_bayar
                ];
            }
        }

        return $gabungan;
    }

    public function totalPiutangModal()
    {
        $this->detailPiutang = DB::table('transaksi')
            ->select('no_transaksi', 'kembalian as jumlah')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ['1', '2', '3'])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get()
            ->filter(fn($item) => (int) $item->jumlah < 0)
            ->values();

        $this->totalDetailPiutang = $this->detailPiutang->sum('jumlah');
    }

    // ============================================================================
    // UTILITY METHODS
    // ============================================================================

    public function generateNoTransaksi($id_cabang): string
    {
        return DB::transaction(function () use ($id_cabang) {
            // Get current date for transaction counter
            $tanggal = Carbon::now()->startOfDay();

            // Get or create counter with lock
            $counter = DB::table('transaksi_counter')
                ->where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // Create new counter for today
                DB::table('transaksi_counter')->insert([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nomorUrut = 1;
            } else {
                // Increment existing counter
                DB::table('transaksi_counter')
                    ->where('id', $counter->id)
                    ->increment('nomor_terakhir');
                $nomorUrut = $counter->nomor_terakhir + 1;
            }

            // Format transaction number
            $nomorPadded = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "TRX/{$id_cabang}/{$tglFormat}/{$nomorPadded}";
        });
    }

    private function syncSetoranTransferHarian(): void
    {
        $this->getReportHarian();

        // Lock untuk mencegah race condition
        DB::table('cash_on_bank')
            ->where('id_cabang', $this->id_cabang)
            ->where('tanggal', date('Y-m-d'))
            ->where('sumber_tabel', 'Setor Transfer')
            ->lockForUpdate()
            ->first();

        DB::table('kas')
            ->where('id_cabang', $this->id_cabang)
            ->where('no_referensi', 'STTF/' . $this->id_cabang . '/' . date('dmy'))
            ->lockForUpdate()
            ->first();

        // Update atau create record Kas
        $kasData = [
            'id_pembuat'           => $this->user->id,
            'keterangan'           => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
            'jumlah'               => $this->totalTransfer,
            'id_kategori_keuangan' => 2,
            'status'               => 'In',
        ];

        $kas = DB::table('kas')->updateOrInsert(
            [
                'id_cabang'    => $this->id_cabang,
                'no_referensi' => 'STTF/' . $this->id_cabang . '/' . date('dmy'),
                'tanggal'      => date('Y-m-d'),
            ],
            array_merge($kasData, [
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );

        // Get kas ID untuk foreign key
        $kasId = DB::table('kas')
            ->where('id_cabang', $this->id_cabang)
            ->where('no_referensi', 'STTF/' . $this->id_cabang . '/' . date('dmy'))
            ->where('tanggal', date('Y-m-d'))
            ->value('id');

        // Update atau create CashOnBank
        DB::table('cash_on_bank')->updateOrInsert(
            [
                'id_cabang'    => $this->id_cabang,
                'tanggal'      => date('Y-m-d'),
                'sumber_tabel' => 'Setor Transfer',
            ],
            [
                'no_referensi' => 'STTF/' . $this->id_cabang . '/' . date('dmy'),
                'jenis'        => 'In',
                'jumlah'       => $this->totalTransfer,
                'keterangan'   => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
                'id_sumber'    => $kasId,
                'updated_at'   => now(),
                'created_at'   => now(),
            ]
        );
    }

    private function resetInputFields(): void
    {
        // Reset form fields
        $this->id_metode_pembayaran = '';
        $this->id_pelanggan = '';
        // $this->id_pelanggan = $this->pelanggans->first()->id ?? null;

        $this->cartItems = [];
        $this->catatan = '-';

        // Reset totals
        $this->total_pesanan = 0;
        $this->total_komisi = 0;
        $this->total_sub_total = 0;
        $this->total_diskon = 0;
        $this->total_akhir = 0;
        $this->laba_bersih = 0;
        $this->jumlah_dibayarkan = 0;
        $this->kembalian = 0;

        // Reset item fields
        $this->nama_item = '';
        $this->kategori_item = '-';
        $this->deskripsi_item = '-';
        $this->harga_item = 0;
        $this->jumlah = 1;
        $this->sub_total = 0;
        $this->input_diskon = 0;
        $this->diskon = 0;
        $this->total_harga = 0;
        $this->nama_karyawan = '-';
        $this->komisi_persen = 0;
        $this->komisi_nominal = 0;

        $this->initSelect2();
    }

    public function cancelCartItems(): void
    {
        $this->komisi_persen = 0;
        $this->komisi_nominal = 0;
        $this->jumlah = 1;
        $this->input_diskon = 0;
        $this->diskon = 0;
        $this->dispatch('initSelect2');
    }

    // ============================================================================
    // EVENT HANDLERS & DISPATCHERS
    // ============================================================================

    public function isEditingMode($mode): void
    {
        $this->isEditing = $mode;
        $this->initSelect2();
    }

    public function listProduk(): void
    {
        $this->initSelect2();
    }

    public function cancel(): void
    {
        $this->resetInputFields();
    }

    public function cancelList(): void
    {
        $this->initSelect2();
    }

    public function initSelect2(): void
    {
        $this->dispatch('initSelect2');
    }

    public function updated(): void
    {
        $this->getReportHarian();
        $this->dispatch('initSelect2');
    }

    private function dispatchAlert($type, $message, $text): void
    {
        $this->dispatch('swal:modal', [
            'type'    => $type,
            'message' => $message,
            'text'    => $text
        ]);

        if ($type === 'success') {
            $this->resetInputFields();
        }
    }

    private function dispatchSwalTransaksi($idTransaksi): void
    {
        $this->dispatch('swal:transaksi', [
            'idTransaksi' => Crypt::encrypt($idTransaksi),
            'message'     => 'Transaksi berhasil!',
            'text'        => 'Apakah kamu ingin mencetak struk sekarang?',
        ]);
    }
}
