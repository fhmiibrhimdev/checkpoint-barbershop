<?php

namespace App\Livewire\Kasir\Transaksi;

use Carbon\Carbon;
use App\Models\Kas;
use Livewire\Component;
use App\Models\CashOnBank;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\DaftarPelanggan;
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

    #[Title('Kasir | Transaksi')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = ['delete'];

    protected $rules = [
        'id_cabang' => 'required',
        'id_pelanggan' => 'required',
        'id_metode_pembayaran' => 'required',
    ];

    // UI Properties
    public $lengthData = 25;
    public $searchTerm, $searchProduk;
    public $previousSearchTerm = '';
    public $isEditing = false;
    public $dataId;
    public $isPersentase = false;
    public $check_id_kategori;

    // Filter Properties
    public $filter_status, $filter_pembayaran = '', $filter_id_cabang;

    // Collections
    public $cabangs, $pelanggans, $produks, $pembayarans;
    public $karyawans = [];
    public $cartItems = [];

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
    public $id_transaksi, $id_produk, $nama_item, $kategori_item, $deskripsi_item;
    public $harga_item, $harga_pokok, $jumlah, $sub_total, $input_diskon, $diskon, $total_harga;
    public $id_karyawan, $nama_karyawan, $komisi_persen, $komisi_nominal;
    public $flag_reset_kunjungan = false;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        $this->initializeBasicData();
        $this->loadReportHarian();
        $this->resetInputFields();
    }

    /**
     * Inisialisasi data dasar untuk component
     */
    private function initializeBasicData()
    {
        // Ambil cabang user yang sedang login
        $id_cabang = Auth::user()->id_cabang;
        $this->id_cabang = $id_cabang;
        $this->filter_id_cabang = $id_cabang;

        // Load data master
        $this->pelanggans = $this->globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks = $this->loadProdukAndKategori($this->id_cabang);
        $this->pembayarans = $this->globalDataService->getMetodePembayaran();

        // Set ID user dan karyawan
        $user = Auth::user();
        $this->id_user = $user->id;

        // Ambil ID karyawan berdasarkan user yang login
        $this->id_karyawan = DB::table('daftar_karyawan')
            ->where('id_user', Auth::id())
            ->value('id');
    }

    /**
     * Load produk dan kategori dengan filter stok
     */
    private function loadProdukAndKategori($id_cabang)
    {
        return DB::table('produk')
            ->select('produk.id', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi', 'produk.stock')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id_cabang', $id_cabang)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Produk Barbershop dengan stok > 0
                    $q->where('nama_kategori', 'Produk Barbershop')
                        ->where('stock', '>', 0);
                })->orWhere(function ($q) {
                    // Produk Umum dengan stok > 0
                    $q->where('nama_kategori', 'Produk Umum')
                        ->where('stock', '>', 0);
                });
            })
            ->whereIn('nama_kategori', ['Produk Barbershop', 'Produk Umum'])
            ->get();
    }

    /**
     * Load laporan harian untuk dashboard
     */
    private function loadReportHarian()
    {
        // Data transaksi hari ini
        $transaksiHariIni = DB::table('transaksi')
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
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->filter_id_cabang)
            ->first();

        // Data pembayaran piutang hari ini
        $piutangHariIni = DB::table('piutang')
            ->join('transaksi', 'piutang.id_transaksi', '=', 'transaksi.id')
            ->selectRaw('
                SUM(CASE WHEN piutang.id_metode_pembayaran = 1 THEN piutang.jumlah_bayar ELSE 0 END) as piutang_tunai,
                SUM(CASE WHEN piutang.id_metode_pembayaran = 2 THEN piutang.jumlah_bayar ELSE 0 END) as piutang_transfer
            ')
            ->whereDate('piutang.tanggal_bayar', Carbon::today())
            ->where('transaksi.id_cabang', $this->filter_id_cabang)
            ->first();

        // Set total untuk dashboard
        $this->totalOmset = $transaksiHariIni->total_omset;
        $this->totalTunai = $transaksiHariIni->total_tunai + ($piutangHariIni->piutang_tunai ?? 0);
        $this->totalTransfer = $transaksiHariIni->total_transfer + ($piutangHariIni->piutang_transfer ?? 0);
        $this->totalPembayaran = $this->totalTunai + $this->totalTransfer;
        $this->totalPiutang = $transaksiHariIni->total_piutang;
    }

    public function render()
    {
        $this->handleSearchPagination();
        $data = $this->getTransaksiData();

        return view('livewire.kasir.transaksi.transaksi', compact('data'));
    }

    /**
     * Reset halaman jika search term berubah
     */
    private function handleSearchPagination()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }
        $this->previousSearchTerm = $this->searchTerm;
    }

    /**
     * Ambil data transaksi dengan filter dan pagination
     */
    private function getTransaksiData()
    {
        $search = '%' . $this->searchTerm . '%';
        $idKaryawan = $this->id_karyawan;

        return DB::table('transaksi')
            ->select([
                'transaksi.id',
                'transaksi.no_transaksi',
                'transaksi.tanggal',
                'daftar_pelanggan.nama_pelanggan',
                'daftar_pelanggan.no_telp',
                'transaksi.total_akhir',
                'transaksi.status',
                'detail.nama_item',
                'detail.deskripsi_item',
                'jumlah.jumlah_produk',
                'kategori_pembayaran.nama_kategori',
                'detail.id_karyawan'
            ])
            ->join('daftar_pelanggan', 'daftar_pelanggan.id', 'transaksi.id_pelanggan')
            ->join('kategori_pembayaran', 'kategori_pembayaran.id', 'transaksi.id_metode_pembayaran')
            ->leftJoin(DB::raw('(
                SELECT id_transaksi, nama_item, deskripsi_item, id_karyawan
                FROM detail_transaksi
                GROUP BY id_transaksi
            ) AS detail'), 'detail.id_transaksi', 'transaksi.id')
            ->leftJoin(DB::raw('(
                SELECT id_transaksi, COUNT(*) as jumlah_produk
                FROM detail_transaksi
                GROUP BY id_transaksi
            ) AS jumlah'), 'jumlah.id_transaksi', 'transaksi.id')
            ->selectRaw('
                EXISTS (
                    SELECT 1 FROM detail_transaksi dt2
                    WHERE dt2.id_transaksi = transaksi.id
                      AND dt2.id_karyawan = ?
                ) AS is_mine
            ', [$idKaryawan ?? -1])
            ->where(function ($query) use ($search) {
                $query->where('no_transaksi', 'LIKE', $search)
                    ->orWhere('nama_pelanggan', 'LIKE', $search);
            })
            ->when($this->filter_status, fn($q) => $q->where('transaksi.status', $this->filter_status))
            ->when($this->filter_pembayaran, fn($q) => $q->where('transaksi.id_metode_pembayaran', $this->filter_pembayaran))
            ->whereBetween('transaksi.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->where('transaksi.id_cabang', $this->filter_id_cabang)
            ->orderByRaw('(detail.id_karyawan = ?) DESC', [$this->id_karyawan])
            ->orderBy('transaksi.status', 'asc')
            ->orderBy('transaksi.tanggal', 'desc')
            ->orderBy('transaksi.id', 'desc')
            ->paginate($this->lengthData);
    }

    /**
     * Simpan transaksi baru
     */
    public function store()
    {
        $this->validate();

        if (empty($this->cartItems)) {
            $this->dispatchAlert('error', 'Keranjang Kosong', 'Silahkan isi items dulu ke keranjang!');
            return;
        }

        // Validasi stok sebelum menyimpan
        if (!$this->validateStock()) {
            return;
        }

        DB::beginTransaction();
        try {
            $transaksi = $this->createTransaksi();
            $this->createDetailTransaksi($transaksi->id);
            $this->updateStockBulk();
            $this->syncSetoranTransferHarian();

            DB::commit();

            $this->handleSuccessfulTransaction($transaksi->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Validasi stok produk sebelum menyimpan
     */
    private function validateStock(): bool
    {
        // Ambil ID produk yang perlu dicek stoknya
        $produkIdsToCheck = collect($this->cartItems)
            ->filter(fn($item) => in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum']))
            ->pluck('id_produk')
            ->unique()
            ->toArray();

        if (empty($produkIdsToCheck)) {
            return true;
        }

        // Ambil stok produk dari database
        $stokProduk = DB::table('produk')
            ->whereIn('id', $produkIdsToCheck)
            ->pluck('stock', 'id');

        // Hitung total quantity yang akan dibeli per produk
        $qtyPerProduk = [];
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $id_produk = $item['id_produk'];
                $qtyPerProduk[$id_produk] = ($qtyPerProduk[$id_produk] ?? 0) + $item['jumlah'];
            }
        }

        // Cek apakah ada stok yang kurang
        foreach ($qtyPerProduk as $id_produk => $qty_dibeli) {
            $stok_tersedia = $stokProduk[$id_produk] ?? 0;

            if ($qty_dibeli > $stok_tersedia) {
                $produkName = collect($this->cartItems)
                    ->firstWhere('id_produk', $id_produk)['nama_item'] ?? 'Produk';

                $this->dispatchAlert(
                    'error',
                    'Stok Tidak Cukup',
                    "Stok untuk '{$produkName}' hanya tersedia {$stok_tersedia}, tidak bisa membeli {$qty_dibeli}."
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Buat data transaksi utama
     */
    private function createTransaksi()
    {
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $status = $this->kembalian < 0 ? "2" : "3";
        $this->no_transaksi = $this->generateNoTransaksi($this->id_cabang);

        return ModelsTransaksi::create([
            'id_cabang' => $this->id_cabang,
            'id_user' => $this->id_user,
            'no_transaksi' => $this->no_transaksi,
            'tanggal' => now(),
            'id_pelanggan' => $this->id_pelanggan,
            'catatan' => $this->catatan,
            'total_pesanan' => $this->total_pesanan,
            'total_komisi_karyawan' => $this->total_komisi,
            'total_sub_total' => $this->total_sub_total,
            'total_diskon' => $this->total_diskon,
            'total_akhir' => $this->total_akhir,
            'total_hpp' => $this->total_hpp,
            'laba_bersih' => $this->laba_bersih,
            'id_metode_pembayaran' => $this->id_metode_pembayaran,
            'jumlah_dibayarkan' => $this->jumlah_dibayarkan,
            'kembalian' => $this->kembalian,
            'status' => $status,
        ]);
    }

    /**
     * Buat detail transaksi dan persediaan secara bulk
     */
    private function createDetailTransaksi($transaksiId)
    {
        $detailData = [];
        $persediaanData = [];

        foreach ($this->cartItems as $item) {
            // Data detail transaksi
            $detailData[] = [
                'id_transaksi' => $transaksiId,
                'id_produk' => $item['id_produk'],
                'nama_item' => $item['nama_item'],
                'kategori_item' => $item['kategori_item'],
                'deskripsi_item' => $item['deskripsi_item'],
                'harga' => $item['harga'],
                'harga_pokok' => $item['harga_pokok'],
                'jumlah' => $item['jumlah'],
                'sub_total' => $item['sub_total'],
                'diskon' => $item['diskon'],
                'total_harga' => $item['total_harga'],
                'id_karyawan' => $item['id_karyawan'],
                'nama_karyawan' => $item['nama_karyawan'],
                'komisi_persen' => $item['komisi_persen'],
                'komisi_nominal' => $item['komisi_nominal'],
            ];

            // Data persediaan jika produk memerlukan pengurangan stok
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $persediaanData[] = [
                    'id_cabang' => $this->id_cabang,
                    'id_user' => (int)$item['id_karyawan'] + 1,
                    'id_produk' => $item['id_produk'],
                    'tanggal' => now(),
                    'qty' => $item['jumlah'],
                    'keterangan' => 'Produk terjual dari ' . $this->no_transaksi,
                    'buku' => '-',
                    'fisik' => '-',
                    'selisih' => '-',
                    'opname' => 'no',
                    'status' => 'Out',
                ];
            }
        }

        // Insert bulk untuk performa
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

        if (!empty($persediaanData)) {
            DB::table('persediaan')->insert($persediaanData);
        }
    }

    /**
     * Update stok produk secara bulk menggunakan CASE WHEN
     */
    private function updateStockBulk()
    {
        // Hitung total quantity per produk
        $stockMap = [];
        foreach ($this->cartItems as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $stockMap[$item['id_produk']] = ($stockMap[$item['id_produk']] ?? 0) + $item['jumlah'];
            }
        }

        if (empty($stockMap)) {
            return;
        }

        // Update menggunakan raw SQL untuk performa optimal
        $updateQuery = "UPDATE produk SET stock = CASE";
        $bindings = [];

        foreach ($stockMap as $id_produk => $jumlah) {
            $updateQuery .= " WHEN id = ? THEN stock - ?";
            $bindings[] = $id_produk;
            $bindings[] = $jumlah;
        }

        $updateQuery .= " END WHERE id IN (" . implode(',', array_fill(0, count($stockMap), '?')) . ")";
        $bindings = array_merge($bindings, array_keys($stockMap));

        DB::statement($updateQuery, $bindings);
    }

    /**
     * Handle transaksi berhasil disimpan
     */
    private function handleSuccessfulTransaction($transaksiId)
    {
        $this->dispatchSwalTransaksi($transaksiId);
        $this->resetInputFields();
        $this->dispatch('closePembayaranModal');
        $this->dispatch('printNota', id: $transaksiId);
        $this->produks = $this->loadProdukAndKategori($this->id_cabang);
    }

    /**
     * Update transaksi existing
     */
    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Restore stok dari transaksi lama
            $this->restoreOldStock($transaksi->id);

            // Hapus data lama
            $this->deleteOldTransactionData($transaksi);

            // Buat data baru
            $this->createDetailTransaksi($transaksi->id);
            $this->updateStockBulk();

            // Update transaksi utama
            $this->updateMainTransaction($transaksi);

            $this->syncSetoranTransferHarian();
            DB::commit();

            $this->handleSuccessfulTransaction($transaksi->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kembalikan stok dari transaksi lama
     */
    private function restoreOldStock($transaksiId)
    {
        $oldDetails = DetailTransaksi::where('id_transaksi', $transaksiId)->get();
        $restoreStockMap = [];

        foreach ($oldDetails as $oldItem) {
            if (in_array($oldItem->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                $restoreStockMap[$oldItem->id_produk] = ($restoreStockMap[$oldItem->id_produk] ?? 0) + $oldItem->jumlah;
            }
        }

        if (empty($restoreStockMap)) {
            return;
        }

        // Update stok dengan menambahkan kembali
        $updateQuery = "UPDATE produk SET stock = CASE";
        $bindings = [];

        foreach ($restoreStockMap as $id_produk => $jumlah) {
            $updateQuery .= " WHEN id = ? THEN stock + ?";
            $bindings[] = $id_produk;
            $bindings[] = $jumlah;
        }

        $updateQuery .= " END WHERE id IN (" . implode(',', array_fill(0, count($restoreStockMap), '?')) . ")";
        $bindings = array_merge($bindings, array_keys($restoreStockMap));

        DB::statement($updateQuery, $bindings);
    }

    /**
     * Hapus data transaksi lama
     */
    private function deleteOldTransactionData($transaksi)
    {
        // Hapus detail transaksi lama
        DetailTransaksi::where('id_transaksi', $transaksi->id)->delete();

        // Hapus persediaan lama
        DB::table('persediaan')
            ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
            ->where('id_cabang', $this->id_cabang)
            ->delete();
    }

    /**
     * Update data transaksi utama
     */
    private function updateMainTransaction($transaksi)
    {
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $status = $this->kembalian < 0 ? "2" : "3";

        $transaksi->update([
            'id_user' => $this->id_user,
            'id_pelanggan' => $this->id_pelanggan,
            'catatan' => $this->catatan,
            'total_pesanan' => $this->total_pesanan,
            'total_komisi_karyawan' => $this->total_komisi,
            'total_sub_total' => $this->total_sub_total,
            'total_diskon' => $this->total_diskon,
            'total_akhir' => $this->total_akhir,
            'laba_bersih' => $this->total_akhir - $this->total_komisi,
            'id_metode_pembayaran' => $this->id_metode_pembayaran,
            'jumlah_dibayarkan' => $this->jumlah_dibayarkan,
            'kembalian' => $this->kembalian,
            'status' => $status,
        ]);
    }

    /**
     * Generate nomor transaksi unik
     */
    public function generateNoTransaksi($id_cabang)
    {
        return DB::transaction(function () use ($id_cabang) {
            // Tanggal hari ini untuk counter
            $tanggal = Carbon::now()->startOfDay();

            // Lock untuk mencegah race condition
            $counter = TransaksiCounter::where('id_cabang', $id_cabang)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // Buat counter baru jika belum ada
                $counter = TransaksiCounter::create([
                    'id_cabang' => $id_cabang,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                // Increment nomor terakhir
                $counter->increment('nomor_terakhir');
            }

            // Format nomor transaksi
            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            return "TRX/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }

    /**
     * Konfirmasi delete transaksi
     */
    public function deleteConfirm($id)
    {
        $this->dataId = $id;
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'If you delete the data, it cannot be restored!'
        ]);
    }

    /**
     * Delete transaksi dan restore stok
     */
    public function delete()
    {
        DB::beginTransaction();
        try {
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Restore stok produk
            $this->restoreStockFromTransaction($transaksi);

            // Hapus data terkait
            $this->deleteTransactionRelatedData($transaksi);

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

    /**
     * Batalkan transaksi dan restore stok
     */
    public function dibatalkan()
    {
        DB::beginTransaction();
        try {
            $transaksi = ModelsTransaksi::findOrFail($this->dataId);

            // Restore stok produk
            $this->restoreStockFromTransaction($transaksi);

            // Hapus persediaan terkait
            DB::table('persediaan')
                ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
                ->where('id_cabang', $this->id_cabang)
                ->delete();

            // Update status transaksi menjadi dibatalkan (status = 4)
            $transaksi->update(['status' => '4']);

            // Hapus piutang jika ada
            DB::table('piutang')
                ->where('id_transaksi', $transaksi->id)
                ->delete();

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

    /**
     * Restore stok dari transaksi
     */
    private function restoreStockFromTransaction($transaksi)
    {
        $details = DetailTransaksi::where('id_transaksi', $transaksi->id)->get();
        $restoreStock = [];

        foreach ($details as $detail) {
            if (in_array($detail->kategori_item, ['Produk Barbershop', 'Produk Umum'])) {
                $restoreStock[$detail->id_produk] = ($restoreStock[$detail->id_produk] ?? 0) + $detail->jumlah;
            }
        }

        if (empty($restoreStock)) {
            return;
        }

        // Update stok: tambah kembali
        $updateQuery = "UPDATE produk SET stock = CASE";
        $bindings = [];

        foreach ($restoreStock as $id => $qty) {
            $updateQuery .= " WHEN id = ? THEN stock + ?";
            $bindings[] = $id;
            $bindings[] = $qty;
        }

        $updateQuery .= " END WHERE id IN (" . implode(',', array_fill(0, count($restoreStock), '?')) . ")";
        $bindings = array_merge($bindings, array_keys($restoreStock));

        DB::statement($updateQuery, $bindings);
    }

    /**
     * Hapus data terkait transaksi
     */
    private function deleteTransactionRelatedData($transaksi)
    {
        // Hapus detail transaksi
        DetailTransaksi::where('id_transaksi', $transaksi->id)->delete();

        // Hapus persediaan
        DB::table('persediaan')
            ->where('keterangan', 'like', '%' . $transaksi->no_transaksi . '%')
            ->where('id_cabang', $this->id_cabang)
            ->delete();
    }

    // ==================== CART MANAGEMENT ====================

    /**
     * Cari produk untuk ditambahkan ke cart
     */
    public function updatedSearchProduk()
    {
        $search = '%' . $this->searchProduk . '%';

        $this->produks = DB::table('produk')
            ->select('produk.id', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id_cabang', $this->id_cabang)
            ->where(function ($query) use ($search) {
                $query->where('nama_item', 'LIKE', $search)
                    ->orWhere('produk.deskripsi', 'LIKE', $search);
            })
            ->get();
    }

    /**
     * Tambahkan produk ke form cart
     */
    public function cartProduk($id)
    {
        $data = DB::table('produk')
            ->select('produk.id', 'produk.id_kategori', 'nama_item', 'harga_jasa', 'harga_pokok', 'nama_kategori', 'produk.deskripsi')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id', $id)
            ->first();

        // Set data produk ke form
        $this->id_produk = $data->id;
        $this->check_id_kategori = $data->id_kategori;
        $this->nama_item = $data->nama_item;
        $this->deskripsi_item = $data->deskripsi;
        $this->kategori_item = $data->nama_kategori;
        $this->harga_item = $data->harga_jasa;
        $this->harga_pokok = $data->harga_pokok;
        $this->sub_total = $this->harga_item * $this->jumlah;
        $this->total_harga = $this->sub_total;

        // Set karyawan default ke user yang login
        $this->id_karyawan = Auth::user()->id - 1 ?? null;

        // Load data karyawan
        $this->karyawans = DB::table('daftar_karyawan')
            ->select('daftar_karyawan.id', 'users.name')
            ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
            ->where('daftar_karyawan.id', $this->id_karyawan)
            ->where('daftar_karyawan.id_cabang', $this->id_cabang)
            ->get();

        $this->calculateKomisi();
        $this->initSelect2();
    }

    /**
     * Hitung komisi berdasarkan karyawan dan produk
     */
    public function updatedIdKaryawan()
    {
        $this->calculateKomisi();
    }

    /**
     * Calculate komisi untuk karyawan
     */
    private function calculateKomisi()
    {
        if ($this->check_id_kategori == "2" || $this->check_id_kategori == "3") {
            // Komisi berdasarkan tabel komisi khusus
            $this->komisi_persen = DB::table('komisi')
                ->where('id_karyawan', $this->id_karyawan)
                ->where('id_produk', $this->id_produk)
                ->value('komisi_persen') ?? 0;
        } else {
            // Komisi berdasarkan produk
            $this->komisi_persen = DB::table('produk')
                ->where('id', $this->id_produk)
                ->value('komisi') ?? 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
    }

    /**
     * Tambah quantity produk
     */
    public function incrementJumlah()
    {
        $this->jumlah++;
        $this->recalculateItemTotals();
    }

    /**
     * Kurangi quantity produk
     */
    public function decrementJumlah()
    {
        if ($this->jumlah > 1) {
            $this->jumlah--;
        }
        $this->recalculateItemTotals();
    }

    /**
     * Recalculate totals setelah perubahan quantity atau diskon
     */
    private function recalculateItemTotals()
    {
        $this->sub_total = (int)$this->harga_item * (int)$this->jumlah;

        if ($this->input_diskon > 0) {
            $this->calculateDiscount();
        } else {
            $this->total_harga = $this->sub_total;
            $this->diskon = 0;
        }

        $this->komisi_nominal = $this->sub_total * $this->komisi_persen / 100;
        $this->initSelect2();
    }

    /**
     * Update jenis diskon (persentase/nominal)
     */
    public function updatedIsPersentase()
    {
        $this->input_diskon = 0;
        $this->diskon = 0;
        $this->total_harga = $this->sub_total;
    }

    /**
     * Hitung diskon berdasarkan input
     */
    public function updatedInputDiskon()
    {
        $this->calculateDiscount();
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount()
    {
        if ($this->isPersentase) {
            // Diskon dalam persen dari subtotal
            $this->diskon = (int)$this->sub_total * (int)$this->input_diskon / 100;
        } else {
            // Diskon dalam nominal langsung
            $this->diskon = (int)$this->input_diskon;
        }

        // Total akhir setelah diskon
        $this->total_harga = $this->sub_total - $this->diskon;
    }

    /**
     * Cancel form cart item
     */
    public function cancelCartItems()
    {
        $this->komisi_persen = 0;
        $this->komisi_nominal = 0;
        $this->jumlah = 1;
        $this->input_diskon = 0;
        $this->diskon = 0;
        $this->dispatch('initSelect2');
    }

    /**
     * Tambahkan item ke cart
     */
    public function addCartItems()
    {
        $this->validate([
            'id_karyawan' => 'required',
        ], [
            'id_karyawan.required' => 'Karyawan wajib dipilih.',
        ]);

        // Ambil nama karyawan
        $this->nama_karyawan = DB::table('daftar_karyawan')
            ->join('users', 'users.id', '=', 'daftar_karyawan.id_user')
            ->where('daftar_karyawan.id', $this->id_karyawan)
            ->value('users.name');

        // Tambahkan ke cart
        $this->cartItems[] = [
            'id_produk' => $this->id_produk,
            'nama_item' => $this->nama_item,
            'kategori_item' => $this->kategori_item,
            'deskripsi_item' => $this->deskripsi_item,
            'harga' => $this->harga_item,
            'harga_pokok' => $this->harga_pokok,
            'jumlah' => $this->jumlah,
            'sub_total' => $this->sub_total,
            'diskon' => $this->diskon,
            'total_harga' => $this->total_harga,
            'id_karyawan' => $this->id_karyawan,
            'nama_karyawan' => $this->nama_karyawan,
            'komisi_persen' => $this->komisi_persen,
            'komisi_nominal' => $this->komisi_nominal,
        ];

        $this->dispatch('closeCart');
        $this->cancelCartItems();
        $this->calculateTransactionSummary();
        $this->dispatch('initSelect2');
    }

    /**
     * Hapus item dari cart
     */
    public function deleteCartItems($index)
    {
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems); // Reindex array
        $this->calculateTransactionSummary();
        $this->dispatch('initSelect2');
    }

    /**
     * Hitung ringkasan transaksi dari cart items
     */
    public function calculateTransactionSummary()
    {
        $this->total_pesanan = 0;
        $this->total_sub_total = 0;
        $this->total_diskon = 0;
        $this->total_akhir = 0;
        $this->total_komisi = 0;
        $this->total_hpp = 0;

        foreach ($this->cartItems as $item) {
            $this->total_pesanan += $item['jumlah'] ?? 0;
            $this->total_sub_total += $item['sub_total'] ?? 0;
            $this->total_diskon += $item['diskon'] ?? 0;
            $this->total_komisi += $item['komisi_nominal'] ?? 0;
            $this->total_akhir += $item['total_harga'] ?? 0;
            $this->total_hpp += $item['harga_pokok'] ?? 0;
        }

        $this->laba_bersih = $this->total_sub_total - $this->total_diskon - $this->total_komisi - $this->total_hpp;
    }

    // ==================== EDIT FUNCTIONALITY ====================

    /**
     * Set mode editing
     */
    public function isEditingMode($mode)
    {
        $this->isEditing = $mode;
        $this->initSelect2();
    }

    /**
     * Load data untuk editing
     */
    public function edit(GlobalDataService $globalDataService, $id)
    {
        $this->isEditing = true;
        $this->dataId = $id;

        // Ambil data transaksi utama
        $transaksi = ModelsTransaksi::findOrFail($id);
        $this->populateTransactionData($transaksi);

        // Load master data untuk cabang terkait
        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks = $globalDataService->getProdukAndKategoriCustom($this->id_cabang);

        // Load cart items dari detail transaksi
        $this->loadCartItemsFromTransaction($id);
        $this->loadProdukAndKategori($this->id_cabang);
        $this->initSelect2();
    }

    /**
     * Populate data transaksi ke form
     */
    private function populateTransactionData($transaksi)
    {
        $this->id_cabang = $transaksi->id_cabang;
        $this->id_user = $transaksi->id_user;
        $this->no_transaksi = $transaksi->no_transaksi;
        $this->tanggal = $transaksi->tanggal;
        $this->id_pelanggan = $transaksi->id_pelanggan;
        $this->catatan = $transaksi->catatan;
        $this->total_pesanan = $transaksi->total_pesanan;
        $this->total_komisi = $transaksi->total_komisi_karyawan;
        $this->total_sub_total = $transaksi->total_sub_total;
        $this->total_diskon = $transaksi->total_diskon;
        $this->total_akhir = $transaksi->total_akhir;
        $this->total_hpp = $transaksi->total_hpp;
        $this->laba_bersih = $transaksi->laba_bersih;
        $this->id_metode_pembayaran = $transaksi->id_metode_pembayaran;
        $this->jumlah_dibayarkan = $transaksi->jumlah_dibayarkan;
        $this->kembalian = $transaksi->kembalian;
        $this->status = $transaksi->status;
    }

    /**
     * Load cart items dari detail transaksi
     */
    private function loadCartItemsFromTransaction($transaksiId)
    {
        $detail = DetailTransaksi::where('id_transaksi', $transaksiId)->get();
        $this->cartItems = [];

        foreach ($detail as $item) {
            $this->cartItems[] = [
                'id_produk' => $item->id_produk,
                'nama_item' => $item->nama_item,
                'kategori_item' => $item->kategori_item,
                'deskripsi_item' => $item->deskripsi_item,
                'harga' => $item->harga,
                'jumlah' => $item->jumlah,
                'sub_total' => $item->sub_total,
                'diskon' => $item->diskon,
                'total_harga' => $item->total_harga,
                'id_karyawan' => $item->id_karyawan,
                'nama_karyawan' => $item->nama_karyawan,
                'komisi_persen' => $item->komisi_persen,
                'komisi_nominal' => $item->komisi_nominal,
            ];
        }
    }

    // ==================== PAYMENT METHODS ====================

    /**
     * Handle form pembayaran
     */
    public function formPembayaran()
    {
        $this->kembalian = $this->jumlah_dibayarkan - $this->total_akhir;
        $this->dispatch('initSelect2');
    }

    /**
     * Set jumlah dibayarkan sama dengan total (uang pas)
     */
    public function uangPas()
    {
        $this->jumlah_dibayarkan = $this->total_akhir;
        $this->initSelect2();
    }

    /**
     * Update metode pembayaran untuk transaksi existing
     */
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
            $this->produks = $this->loadProdukAndKategori($this->id_cabang);
            $this->dispatchAlert('success', 'Success!', 'Metode pembayaran berhasil diperbarui.');
            $this->resetInputFields();
            $this->dispatch('closePembayaranModal');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Redirect ke halaman piutang
     */
    public function directPiutang()
    {
        return redirect()->to('/keuangan/piutang/' . Crypt::encrypt($this->dataId));
    }

    // ==================== REPORT MODAL METHODS ====================

    /**
     * Detail modal total omset
     */
    public function totalOmsetModal()
    {
        $this->detailOmset = DB::table('transaksi')
            ->select('no_transaksi', 'total_akhir as jumlah')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get();

        $this->totalDetailOmset = $this->detailOmset->sum('jumlah');
    }

    /**
     * Detail modal pembayaran cash
     */
    public function totalPembayaranCashModal()
    {
        $today = Carbon::today();

        // Data dari transaksi cash
        $transaksi = DB::table('transaksi')
            ->select('no_transaksi', DB::raw('LEAST(CAST(jumlah_dibayarkan AS UNSIGNED), CAST(total_akhir AS UNSIGNED)) as jumlah_dibayarkan'))
            ->where('id_metode_pembayaran', 1)
            ->whereDate('tanggal', $today)
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get();

        // Data dari pembayaran piutang cash
        $piutang = DB::table('piutang')
            ->select('no_referensi', 'jumlah_bayar')
            ->join('transaksi', 'piutang.id_transaksi', '=', 'transaksi.id')
            ->where('piutang.id_metode_pembayaran', '1')
            ->whereDate('piutang.tanggal_bayar', $today)
            ->where('transaksi.id_cabang', $this->filter_id_cabang)
            ->get();

        $this->detailPembayaranCash = $this->combinePaymentData($transaksi, $piutang);
        $this->totalDetailPembayaranCash = collect($this->detailPembayaranCash)->sum('jumlah');
    }

    /**
     * Detail modal pembayaran transfer
     */
    public function totalPembayaranTransferModal()
    {
        $today = Carbon::today();

        // Data dari transaksi transfer
        $transaksi = DB::table('transaksi')
            ->select('no_transaksi', DB::raw('LEAST(CAST(jumlah_dibayarkan AS UNSIGNED), CAST(total_akhir AS UNSIGNED)) as jumlah_dibayarkan'))
            ->where('id_metode_pembayaran', 2)
            ->whereDate('tanggal', $today)
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get();

        // Data dari pembayaran piutang transfer
        $piutang = DB::table('piutang')
            ->select('no_referensi', 'jumlah_bayar')
            ->join('transaksi', 'piutang.id_transaksi', '=', 'transaksi.id')
            ->where('piutang.id_metode_pembayaran', '2')
            ->whereDate('piutang.tanggal_bayar', $today)
            ->where('transaksi.id_cabang', $this->filter_id_cabang)
            ->get();

        $this->detailPembayaranTransfer = $this->combinePaymentData($transaksi, $piutang);
        $this->totalDetailPembayaranTransfer = collect($this->detailPembayaranTransfer)->sum('jumlah');
    }

    /**
     * Gabungkan data pembayaran dari transaksi dan piutang
     */
    private function combinePaymentData($transaksi, $piutang)
    {
        $gabungan = [];

        // Dari transaksi
        foreach ($transaksi as $item) {
            if ($item->jumlah_dibayarkan > 0) {
                $gabungan[] = [
                    'no_transaksi' => $item->no_transaksi,
                    'sumber' => 'Transaksi',
                    'jumlah' => $item->jumlah_dibayarkan
                ];
            }
        }

        // Dari piutang
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

    /**
     * Detail modal total piutang
     */
    public function totalPiutangModal()
    {
        $this->detailPiutang = DB::table('transaksi')
            ->select('no_transaksi', 'kembalian as jumlah')
            ->whereDate('tanggal', Carbon::today())
            ->whereIn('status', ["1", "2", "3"])
            ->where('id_cabang', $this->filter_id_cabang)
            ->get()
            ->filter(fn($item) => (int) $item->jumlah < 0) // Hanya ambil yang minus (piutang)
            ->values(); // Reset index untuk clean di Blade

        $this->totalDetailPiutang = $this->detailPiutang->sum('jumlah');
    }

    // ==================== SYNC METHODS ====================

    /**
     * Sinkronisasi setoran transfer harian ke kas dan bank
     */
    private function syncSetoranTransferHarian()
    {
        $this->loadReportHarian();

        // Lock records untuk prevent race condition
        $this->lockCashOnBankRecord();
        $this->lockKasRecord();

        // Update atau create Kas
        $kas = $this->updateOrCreateKasRecord();

        // Update atau create CashOnBank
        $this->updateOrCreateCashOnBankRecord($kas);
    }

    /**
     * Lock record cash_on_bank untuk hari ini
     */
    private function lockCashOnBankRecord()
    {
        DB::table('cash_on_bank')
            ->where('id_cabang', $this->id_cabang)
            ->where('tanggal', date('Y-m-d'))
            ->where('sumber_tabel', 'Setor Transfer')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Lock record kas untuk hari ini
     */
    private function lockKasRecord()
    {
        DB::table('kas')
            ->where('id_cabang', $this->id_cabang)
            ->where('no_referensi', 'STTF/' . $this->id_cabang . '/' . date('dmy'))
            ->lockForUpdate()
            ->first();
    }

    /**
     * Update atau create record kas
     */
    private function updateOrCreateKasRecord()
    {
        return Kas::updateOrCreate(
            [
                'id_cabang' => $this->id_cabang,
                'no_referensi' => 'STTF/' . $this->id_cabang . '/' . date('dmy'),
                'tanggal' => date('Y-m-d'),
            ],
            [
                'id_pembuat' => Auth::user()->id,
                'keterangan' => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
                'jumlah' => $this->totalTransfer,
                'id_kategori_keuangan' => 2,
                'status' => 'In',
            ]
        );
    }

    /**
     * Update atau create record cash_on_bank
     */
    private function updateOrCreateCashOnBankRecord($kas)
    {
        CashOnBank::updateOrCreate(
            [
                'id_cabang' => $this->id_cabang,
                'tanggal' => date('Y-m-d'),
                'sumber_tabel' => 'Setor Transfer',
            ],
            [
                'no_referensi' => $kas->no_referensi,
                'jenis' => 'In',
                'jumlah' => $this->totalTransfer,
                'keterangan' => 'Pemasukan Transfer tanggal ' . date('Y-m-d'),
                'id_sumber' => $kas->id,
            ]
        );
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Reset pagination ketika length data berubah
     */
    public function updatingLengthData()
    {
        $this->resetPage();
    }

    /**
     * Update data saat cabang berubah
     */
    public function updatedIdCabang(GlobalDataService $globalDataService)
    {
        $this->pelanggans = $globalDataService->getPelanggansCustom($this->id_cabang);
        $this->produks = $globalDataService->getProdukAndKategoriCustom($this->id_cabang);
        $this->karyawans = $globalDataService->getKaryawansCustom($this->id_cabang);

        $this->dispatch('refreshList');

        // Reset cart items ketika ganti cabang
        $this->cartItems = [];
        $this->resetInputFields();
    }

    /**
     * Reset filter pencarian
     */
    public function resetFilter()
    {
        $this->filter_status = '';
        $this->filter_pembayaran = '';
    }

    /**
     * Reset semua input fields
     */
    private function resetInputFields()
    {
        // Reset form pembayaran
        $this->id_metode_pembayaran = '';
        $this->id_pelanggan = '';
        // $this->id_pelanggan = $this->pelanggans->first()->id ?? null;

        // Reset cart
        $this->cartItems = [];

        // Reset transaksi fields
        $this->catatan = '-';
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

    /**
     * Cancel form dan reset fields
     */
    public function cancel()
    {
        $this->resetInputFields();
    }

    /**
     * Cancel list dan refresh select2
     */
    public function cancelList()
    {
        $this->initSelect2();
    }

    /**
     * Initialize list produk
     */
    public function listProduk()
    {
        $this->initSelect2();
    }

    /**
     * Refresh komponen saat ada update
     */
    public function updated()
    {
        $this->loadReportHarian();
        $this->dispatch('initSelect2');
    }

    // ==================== EVENT DISPATCHERS ====================

    public function updatedIdPelanggan()
    {
        $pelanggan = DaftarPelanggan::where('id', $this->id_pelanggan)
            ->first(['nama_pelanggan', 'total_kunjungan']);

        if ($pelanggan && $pelanggan->total_kunjungan == 10) {
            $this->dispatch(
                'swal:spesial',
                [
                    'type'  => 'info',
                    'title' => 'Pelanggan Spesial!',
                    'text'  => "Pelanggan {$pelanggan->nama_pelanggan} sudah 10x berkunjung. Mohon inputkan diskon 100% secara manual pada transaksi ini."
                ]
            );
            $this->flag_reset_kunjungan = true;
        } else {
            $this->flag_reset_kunjungan = false;
        }
    }

    /**
     * Dispatch alert notification
     */
    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type' => $type,
            'message' => $message,
            'text' => $text
        ]);
    }

    /**
     * Dispatch sweet alert untuk transaksi berhasil
     */
    private function dispatchSwalTransaksi($idTransaksi)
    {
        $this->dispatch('swal:transaksi', [
            'idTransaksi' => Crypt::encrypt($idTransaksi),
            'message' => 'Transaksi berhasil!',
            'text' => 'Apakah kamu ingin mencetak struk sekarang?',
        ]);
    }

    /**
     * Initialize Select2 components
     */
    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    // ==================== GETTER METHODS ====================

    /**
     * Get produk dengan kategori custom untuk cabang tertentu (alias method)
     */
    public function getProdukAndKategoriCustom($id_cabang)
    {
        return $this->loadProdukAndKategori($id_cabang);
    }

    /**
     * Get report harian (alias method untuk backward compatibility)
     */
    public function getReportHarian()
    {
        $this->loadReportHarian();
    }

    /**
     * Get ringkasan transaksi (alias method untuk backward compatibility)
     */
    public function getRingkasanTransaksi()
    {
        $this->calculateTransactionSummary();
    }
}

// ==================== ADDITIONAL HELPER TRAITS/METHODS ====================

/**
 * Helper trait untuk stock management
 * Bisa dipisah ke file terpisah jika diperlukan
 */
trait StockManagement
{
    /**
     * Validasi stok untuk multiple items
     */
    protected function validateStockForItems(array $items): bool
    {
        $stockRequirements = $this->calculateStockRequirements($items);
        $availableStock = $this->getAvailableStock(array_keys($stockRequirements));

        return $this->checkStockAvailability($stockRequirements, $availableStock);
    }

    /**
     * Hitung kebutuhan stok per produk
     */
    private function calculateStockRequirements(array $items): array
    {
        $requirements = [];

        foreach ($items as $item) {
            if (in_array($item['kategori_item'], ['Produk Barbershop', 'Produk Umum'])) {
                $id_produk = $item['id_produk'];
                $requirements[$id_produk] = ($requirements[$id_produk] ?? 0) + $item['jumlah'];
            }
        }

        return $requirements;
    }

    /**
     * Ambil stok yang tersedia
     */
    private function getAvailableStock(array $productIds): array
    {
        return DB::table('produk')
            ->whereIn('id', $productIds)
            ->pluck('stock', 'id')
            ->toArray();
    }

    /**
     * Cek ketersediaan stok
     */
    private function checkStockAvailability(array $requirements, array $available): bool
    {
        foreach ($requirements as $productId => $needed) {
            $stock = $available[$productId] ?? 0;
            if ($needed > $stock) {
                return false;
            }
        }

        return true;
    }
}

/**
 * Helper trait untuk transaction calculations
 * Bisa dipisah ke file terpisah jika diperlukan
 */
trait TransactionCalculations
{
    /**
     * Calculate total transaksi dari array items
     */
    protected function calculateTotalsFromItems(array $items): array
    {
        $totals = [
            'total_pesanan' => 0,
            'total_sub_total' => 0,
            'total_diskon' => 0,
            'total_akhir' => 0,
            'total_komisi' => 0,
            'total_hpp' => 0,
        ];

        foreach ($items as $item) {
            $totals['total_pesanan'] += $item['jumlah'] ?? 0;
            $totals['total_sub_total'] += $item['sub_total'] ?? 0;
            $totals['total_diskon'] += $item['diskon'] ?? 0;
            $totals['total_komisi'] += $item['komisi_nominal'] ?? 0;
            $totals['total_akhir'] += $item['total_harga'] ?? 0;
            $totals['total_hpp'] += ($item['harga_pokok'] ?? 0) * ($item['jumlah'] ?? 0);
        }

        $totals['laba_bersih'] = $totals['total_sub_total'] - $totals['total_diskon'] - $totals['total_komisi'] - $totals['total_hpp'];

        return $totals;
    }

    /**
     * Format currency untuk display
     */
    protected function formatCurrency($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Calculate kembalian dan status
     */
    protected function calculatePaymentStatus($totalAkhir, $jumlahDibayarkan): array
    {
        $kembalian = $jumlahDibayarkan - $totalAkhir;
        $status = $kembalian < 0 ? "2" : "3"; // 2 = Piutang, 3 = Lunas

        return [
            'kembalian' => $kembalian,
            'status' => $status
        ];
    }
}
