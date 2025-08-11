<?php

use App\Livewire\Example\Example;
use App\Livewire\Profile\Profile;
use App\Livewire\Dashboard\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\Control\User as ControlUser;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Livewire\Admin\DataPendukung\KategoriKeuangan as AdminKategoriKeuangan;
use App\Livewire\Admin\DataPendukung\KategoriSatuan as AdminKategoriSatuan;

use App\Livewire\Admin\DataMaster\Produk as AdminProduk;
use App\Livewire\Admin\DataMaster\DaftarKaryawan as AdminDaftarKaryawan;
use App\Livewire\Admin\DataMaster\DaftarPelanggan as AdminDaftarPelanggan;
use App\Livewire\Admin\DataMaster\DaftarSupplier as AdminDaftarSupplier;
use App\Livewire\Admin\Keuangan\CashOnBank as AdminCashOnBank;
use App\Livewire\Admin\Keuangan\Hutang as AdminHutang;
use App\Livewire\Admin\Keuangan\Kasbon as AdminKasbon;
use App\Livewire\Admin\Keuangan\KasKeluar as AdminKasKeluar;
use App\Livewire\Admin\Keuangan\KasMasuk as AdminKasMasuk;
use App\Livewire\Admin\Keuangan\Piutang as AdminPiutang;
use App\Livewire\Admin\Keuangan\SlipGaji as AdminSlipGaji;
use App\Livewire\Admin\Persediaan\SaldoAwalItem as AdminSaldoAwalItem;
use App\Livewire\Admin\Persediaan\StokMasuk as AdminStokMasuk;
use App\Livewire\Admin\Persediaan\StokKeluar as AdminStokKeluar;
use App\Livewire\Admin\Persediaan\StokOpname as AdminStokOpname;
use App\Livewire\Admin\Persediaan\KartuStok as AdminKartuStok;
use App\Livewire\Admin\Transaksi\JadwalBooking as AdminJadwalBooking;
use App\Livewire\Admin\Transaksi\Transaksi as AdminTransaksi;
use App\Livewire\Capster\Transaksi\RiwayatTransaksi as RiwayatTransaksi;
use App\Livewire\Caspter\Transaksi\JadwalBooking as CapsterJadwalBooking;
use App\Livewire\Caspter\Transaksi\Transaksi as CapsterTransaksi;
use App\Livewire\DataMaster\DaftarKaryawan;
use App\Livewire\DataMaster\DaftarPelanggan;
use App\Livewire\DataMaster\DaftarSupplier;
use App\Livewire\DataMaster\Produk;
use App\Livewire\DataPendukung\CabangLokasi;
use App\Livewire\DataPendukung\KategoriKeuangan;
use App\Livewire\DataPendukung\KategoriPembayaran;
use App\Livewire\DataPendukung\KategoriProduk;
use App\Livewire\DataPendukung\KategoriSatuan;
use App\Livewire\Kasir\Keuangan\CashOnBank as KasirCashOnBank;
use App\Livewire\Kasir\Keuangan\Hutang as KasirHutang;
use App\Livewire\Kasir\Keuangan\KasKeluar as KasirKasKeluar;
use App\Livewire\Kasir\Keuangan\KasMasuk as KasirKasMasuk;
use App\Livewire\Kasir\Keuangan\Piutang as KasirPiutang;
use App\Livewire\Kasir\Persediaan\KartuStok as KasirKartuStok;
use App\Livewire\Kasir\Persediaan\StokKeluar as KasirStokKeluar;
use App\Livewire\Kasir\Persediaan\StokMasuk as KasirStokMasuk;
use App\Livewire\Kasir\Transaksi\Transaksi as KasirTransaksi;
use App\Livewire\Keuangan\Piutang;
use App\Livewire\Keuangan\CashOnBank;
use App\Livewire\Keuangan\Hutang;
use App\Livewire\Keuangan\Kasbon;
use App\Livewire\Keuangan\KasKeluar;
use App\Livewire\Keuangan\KasMasuk;
use App\Livewire\Keuangan\SlipGaji;
use App\Livewire\Persediaan\KartuStok;
use App\Livewire\Persediaan\SaldoAwalItem;
use App\Livewire\Persediaan\StokKeluar;
use App\Livewire\Persediaan\StokMasuk;
use App\Livewire\Persediaan\StokOpname;
use App\Livewire\Test;
use App\Livewire\Transaksi\JadwalBooking;
use App\Livewire\Transaksi\PrintNota;
use App\Livewire\Transaksi\Transaksi;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::post('/', [AuthenticatedSessionController::class, 'store']);

Route::get('test', Test::class);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class);
    Route::get('/keuangan/hutang/{id_hutang}', Hutang::class);
    Route::get('/keuangan/piutang/{id_transaksi}', Piutang::class);
    Route::get('/transaksi/print-nota/{id_transaksi}', PrintNota::class);
    Route::get('/transaksi/riwayat-transaksi', RiwayatTransaksi::class);
});

Route::group(['middleware' => ['auth', 'role:direktur']], function () {
    Route::get('/cabang-lokasi', CabangLokasi::class);
    Route::get('/kategori/produk', KategoriProduk::class);
    Route::get('/kategori/keuangan', KategoriKeuangan::class);
    Route::get('/kategori/pembayaran', KategoriPembayaran::class);
    Route::get('/kategori/satuan', KategoriSatuan::class);
    Route::get('/master-data/produk', Produk::class);
    Route::get('/master-data/daftar-pelanggan', DaftarPelanggan::class);
    Route::get('/master-data/daftar-supplier', DaftarSupplier::class);
    Route::get('/master-data/daftar-karyawan', DaftarKaryawan::class);
    Route::get('/persediaan/saldo-awal-item', SaldoAwalItem::class);
    Route::get('/persediaan/stok-masuk', StokMasuk::class);
    Route::get('/persediaan/stok-keluar', StokKeluar::class);
    Route::get('/persediaan/stok-opname', StokOpname::class);
    Route::get('/persediaan/kartu-stok', KartuStok::class);

    Route::get('/keuangan/kas-masuk', KasMasuk::class);
    Route::get('/keuangan/kas-keluar', KasKeluar::class);
    Route::get('/keuangan/hutang', Hutang::class);
    Route::get('/keuangan/piutang', Piutang::class);
    Route::get('/keuangan/cash-on-bank', CashOnBank::class);
    Route::get('/keuangan/kasbon', Kasbon::class);
    Route::get('/keuangan/slip-gaji', SlipGaji::class);

    Route::get('/transaksi', Transaksi::class);
    Route::get('/transaksi/jadwal-booking', JadwalBooking::class);
    Route::get('/pengaturan/control-user', ControlUser::class);
});

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/admin/kategori/keuangan', AdminKategoriKeuangan::class);
    Route::get('/admin/kategori/satuan', AdminKategoriSatuan::class);
    Route::get('/admin/master-data/produk', AdminProduk::class);

    Route::get('/admin/master-data/daftar-pelanggan', AdminDaftarPelanggan::class);
    Route::get('/admin/master-data/daftar-supplier', AdminDaftarSupplier::class);
    Route::get('/admin/master-data/daftar-karyawan', AdminDaftarKaryawan::class);

    Route::get('/admin/persediaan/saldo-awal-item', AdminSaldoAwalItem::class);
    Route::get('/admin/persediaan/stok-masuk', AdminStokMasuk::class);
    Route::get('/admin/persediaan/stok-keluar', AdminStokKeluar::class);
    Route::get('/admin/persediaan/stok-opname', AdminStokOpname::class);
    Route::get('/admin/persediaan/kartu-stok', AdminKartuStok::class);

    Route::get('/admin/transaksi', AdminTransaksi::class);
    Route::get('/admin/transaksi/jadwal-booking', AdminJadwalBooking::class);

    Route::get('/admin/keuangan/kas-masuk', AdminKasMasuk::class);
    Route::get('/admin/keuangan/kas-keluar', AdminKasKeluar::class);
    Route::get('/admin/keuangan/hutang', AdminHutang::class);
    Route::get('/admin/keuangan/hutang/{id_hutang}', AdminHutang::class);
    Route::get('/admin/keuangan/piutang', AdminPiutang::class);
    Route::get('/admin/keuangan/piutang/{id_transaksi}', AdminPiutang::class);
    Route::get('/admin/keuangan/cash-on-bank', AdminCashOnBank::class);
    Route::get('/admin/keuangan/kasbon', AdminKasbon::class);
    Route::get('/admin/keuangan/slip-gaji', AdminSlipGaji::class);
});

Route::group(['middleware' => ['auth', 'role:kasir']], function () {
    // 
    Route::get('/kasir/persediaan/stok-masuk', KasirStokMasuk::class);
    Route::get('/kasir/persediaan/stok-keluar', KasirStokKeluar::class);
    Route::get('/kasir/persediaan/kartu-stok', KasirKartuStok::class);

    Route::get('/kasir/transaksi', KasirTransaksi::class);

    Route::get('/kasir/keuangan/kas-masuk', KasirKasMasuk::class);
    Route::get('/kasir/keuangan/kas-keluar', KasirKasKeluar::class);
    Route::get('/kasir/keuangan/hutang', KasirHutang::class);
    Route::get('/kasir/keuangan/hutang/{id_hutang}', KasirHutang::class);
    Route::get('/kasir/keuangan/piutang', KasirPiutang::class);
    Route::get('/kasir/keuangan/piutang/{id_transaksi}', KasirPiutang::class);
    Route::get('/kasir/keuangan/cash-on-bank', KasirCashOnBank::class);
});

Route::group(['middleware' => ['auth', 'role:capster']], function () {
    // 
    Route::get('/capster/transaksi', CapsterTransaksi::class);
    Route::get('/capster/transaksi/jadwal-booking', CapsterJadwalBooking::class);
});

require __DIR__ . '/auth.php';
