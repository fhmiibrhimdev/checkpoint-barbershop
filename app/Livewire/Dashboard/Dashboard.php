<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\DaftarKaryawan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    #[Title('Dashboard')]
    public $cabangs, $total_keseluruhan = [], $report_harian = [], $status_pesanan = [], $sales_overview = [], $overall_report = [], $top_produk = [], $pesanan_terbaru = [], $omset_harian = [];
    public $id_cabang, $id_karyawan;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs = $globalDataService->getCabangs();
        // dd($this->total_keseluruhan, $this->report_harian, $this->status_pesanan, $this->sales_overview, $this->overall_report, $this->top_produk);
    }

    private function getDashboardDirektur()
    {
        $idCabang = $this->filter_id_cabang ?? $this->cabangs->first()->id;

        $today          = Carbon::today();
        $startMonth     = Carbon::now()->startOfMonth();
        $endMonth       = Carbon::now()->endOfMonth();
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth   = Carbon::now()->subMonth()->endOfMonth();
        $start5 = now()->subDays(4)->startOfDay();
        $end5   = now()->endOfDay();

        /** -------------------------
         *  CASH, PIUTANG, HUTANG
         *  -------------------------
         */
        $totals = [
            'cash_on_bank' => DB::table('cash_on_bank')
                ->where('id_cabang', $idCabang)
                ->selectRaw("
                SUM(
                    CASE
                        WHEN jenis = 'In'  THEN jumlah
                        WHEN jenis = 'Out' THEN -jumlah
                        ELSE 0
                    END
                ) AS saldo_akhir
            ")
                ->value('saldo_akhir'),

            'piutang' => DB::table('transaksi')
                ->where('id_cabang', $idCabang)
                ->where('kembalian', '<', 0)
                ->sum('kembalian'),

            'hutang' => DB::table('daftar_supplier')
                ->where('id_cabang', $idCabang)
                ->where('sisa_hutang', '<', 0)
                ->sum('sisa_hutang'),
        ];

        /** -------------------------
         *  HARIAN
         *  -------------------------
         */
        $daily = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->whereDate('tanggal', $today)
            ->selectRaw('
            SUM(total_akhir) AS total_omset,
            SUM(CASE WHEN kembalian < 0 THEN kembalian ELSE 0 END) AS total_piutang,
            SUM(CASE WHEN status IN (1,2,3) THEN 1 ELSE 0 END) AS total_pesanan
        ')
            ->first();

        $totalPengeluaranHariIni = DB::table('kas')
            ->where('id_cabang', $idCabang)
            ->whereDate('tanggal', $today)
            ->where('status', 'Out')
            ->sum('jumlah');

        /** -------------------------
         *  STATUS PESANAN
         *  -------------------------
         */
        $statusPesanan = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->selectRaw("
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS booking,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS belum_lunas,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS lunas,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS dibatalkan
        ")
            ->first();

        /** -------------------------
         *  SALES OVERVIEW
         *  -------------------------
         */
        $pesananBulanIni = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->whereBetween('tanggal', [$startMonth, $endMonth])
            ->whereIn('status', [1, 2, 3])
            ->count();

        $totalOmsetBulanIni = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->whereBetween('tanggal', [$startMonth, $endMonth])
            ->whereIn('status', [2, 3])
            ->sum('total_akhir');

        $totalOmsetBulanLalu = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->whereBetween('tanggal', [$startLastMonth, $endLastMonth])
            ->whereIn('status', [2, 3])
            ->sum('total_akhir');

        $persentaseOmset = $totalOmsetBulanLalu != 0
            ? (($totalOmsetBulanIni - $totalOmsetBulanLalu) / $totalOmsetBulanLalu) * 100
            : 0;

        /** -------------------------
         *  OVERALL REPORT
         *  -------------------------
         */
        $overall = DB::table('transaksi')
            ->where('id_cabang', $idCabang)
            ->whereIn('status', [2, 3])
            ->selectRaw("
            COUNT(DISTINCT id_pelanggan) AS total_pelanggan,
            COUNT(*)                      AS total_pesanan,
            SUM(CASE WHEN status IN (2,3) THEN CAST(laba_bersih AS SIGNED) ELSE 0 END) AS total_profit,
            SUM(CAST(total_akhir AS SIGNED)) AS total_omset,
            SUM(CAST(total_hpp   AS SIGNED)) AS total_hpp
        ")
            ->first();

        $totalPengeluaran = DB::table('kas')
            ->where('id_cabang', $idCabang)
            ->where('status', 'Out')
            ->sum('jumlah');

        /** -------------------------
         *  TOP PRODUK
         *  -------------------------
         *  (pakai join ke transaksi untuk filter cabang)
         */
        $topProduk = DB::table('detail_transaksi as dt')
            ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
            ->join('produk as p', 'p.id', '=', 'dt.id_produk')
            ->where('t.id_cabang', $idCabang)
            ->select('p.nama_item as produk', DB::raw('COUNT(dt.id) as jumlah_pesanan'))
            ->groupBy('p.id', 'p.nama_item')
            ->orderByDesc('jumlah_pesanan')
            ->limit(5)
            ->get();

        /** -------------------------
         *  PESANAN TERBARU (5 terakhir)
         *  -------------------------
         */
        $pesananTerbaru = DB::table('transaksi as t')
            ->leftJoin('daftar_pelanggan as dp', function ($join) {
                $join->on('dp.id', '=', DB::raw('CAST(t.id_pelanggan AS UNSIGNED)'))
                    ->on('dp.id_cabang', '=', 't.id_cabang');
            })
            ->where('t.id_cabang', $idCabang)
            ->whereIn('t.status', ['2', '3'])
            ->orderBy('t.tanggal', 'desc')
            ->limit(5)
            ->get([
                't.no_transaksi',
                DB::raw('COALESCE(dp.nama_pelanggan, "-") as pelanggan'),
                DB::raw('CAST(REPLACE(t.total_akhir, ",", "") AS DECIMAL(18,2)) as total_akhir'),
                't.tanggal',
            ]);

        /** -------------------------
         *  OMSET HARIAN (5 hari terakhir)
         *  -------------------------
         *  Query agregat per hari, lalu isi nol untuk hari yang tidak ada transaksi.
         */
        $omsetHarianRaw = DB::table('transaksi as t')
            ->where('t.id_cabang', $idCabang)
            ->whereIn('t.status', ['2', '3'])
            ->whereBetween('t.tanggal', [$start5, $end5]) // 5 hari terakhir (hari ini termasuk)
            ->selectRaw("
                LEFT(t.tanggal, 10) as tgl,
                COALESCE(SUM(CAST(REPLACE(t.total_akhir, ',', '') AS DECIMAL(18,2))), 0) as pemasukan
            ")
            ->groupBy('tgl')
            ->orderBy('tgl', 'desc')
            ->get()
            ->keyBy('tgl');

        // bangun array 5 hari: hari ini, kemarin, dst (desc) dan isi 0 jika kosong
        $omsetHarian = [];
        for ($i = 0; $i < 5; $i++) {
            $d = now()->subDays($i)->toDateString(); // YYYY-MM-DD
            $omsetHarian[] = [
                'tanggal'   => $d,
                'pemasukan' => isset($omsetHarianRaw[$d]) ? (float) $omsetHarianRaw[$d]->pemasukan : 0,
            ];
        }

        /** -------------------------
         *  ASSIGN KE VARIABEL CLASS
         *  -------------------------
         */
        $this->total_keseluruhan = $totals;

        $this->report_harian = [
            'total_omset'       => (int) ($daily->total_omset ?? 0),
            'total_pengeluaran' => (int) $totalPengeluaranHariIni,
            'total_pesanan'     => (int) ($daily->total_pesanan ?? 0),
            'total_piutang'     => (int) ($daily->total_piutang ?? 0),
        ];

        $this->status_pesanan = (array) $statusPesanan;

        $this->sales_overview = [
            'pesanan_bulan_ini'     => $pesananBulanIni,
            'total_omset_bulan_ini' => $totalOmsetBulanIni,
            'persentase_omset'      => $persentaseOmset,
        ];

        $this->overall_report = [
            'total_pelanggan'  => (int) ($overall->total_pelanggan ?? 0),
            'total_pesanan'    => (int) ($overall->total_pesanan ?? 0),
            'total_profit'     => (int) ($overall->total_profit ?? 0),
            'total_omset'      => (int) ($overall->total_omset ?? 0),
            'total_pengeluaran' => (int) $totalPengeluaran,
            'total_hpp'        => (int) ($overall->total_hpp ?? 0),
        ];

        $this->top_produk = $topProduk;
        $this->pesanan_terbaru = $pesananTerbaru;
        $this->omset_harian    = $omsetHarian;

        // dd($this->total_keseluruhan, $this->report_harian, $this->status_pesanan, $this->sales_overview, $this->overall_report, $this->top_produk);
    }

    private function getDashboardAdmin()
    {
        // Implementasi khusus untuk dashboard admin jika diperlukan
    }

    private function getDashboardKasir()
    {
        // Implementasi khusus untuk dashboard kasir jika diperlukan
    }

    private function getDashboardKasirORCapster()
    {
        $idCabang = $this->filter_id_cabang ?? $this->cabangs->first()->id;

        $user = Auth::user();
        $this->id_karyawan = DaftarKaryawan::where('id_user', $user->id)->value('id');

        $today          = Carbon::today();
        $startMonth     = Carbon::now()->startOfMonth();
        $endMonth       = Carbon::now()->endOfMonth();
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth   = Carbon::now()->subMonth()->endOfMonth();
        $start5 = now()->subDays(4)->startOfDay();
        $end5   = now()->endOfDay();

        /** -------------------------
         *  KOMISI, KASBON, PIUTANG
         *  -------------------------
         */
        $totals = [
            'komisi' => DB::table('transaksi as t')
                ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 't.id')
                ->where('dt.id_karyawan', $this->id_karyawan)
                ->whereIn('t.status', [2, 3])
                ->sum('t.total_komisi_karyawan'),

            'piutang' => DB::table('transaksi as t')
                ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 't.id')
                ->where('dt.id_karyawan', $this->id_karyawan)
                ->where('t.kembalian', '<', 0)
                ->sum('t.kembalian'),

            'kasbon' => DB::table('daftar_karyawan as dk')
                ->where('dk.id', $this->id_karyawan)
                ->value('saldo_kasbon'),
        ];

        // dd($totals['komisi']);

        /** -------------------------
         *  HARIAN
         *  -------------------------
         */
        $daily = DB::table('transaksi')
            ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 'transaksi.id')
            ->where('dt.id_karyawan', $this->id_karyawan)
            ->whereDate('tanggal', $today)
            ->selectRaw('
                SUM(total_akhir) AS total_omset,
                SUM(CASE WHEN kembalian < 0 THEN kembalian ELSE 0 END) AS total_piutang,
                SUM(CASE WHEN status IN (1,2,3) THEN 1 ELSE 0 END) AS total_pesanan,
                SUM(CASE WHEN status IN (2,3) THEN total_komisi_karyawan ELSE 0 END) AS total_komisi
            ')
            ->first();

        /** -------------------------
         *  STATUS PESANAN
         *  -------------------------
         */
        $statusPesanan = DB::table('transaksi')
            ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 'transaksi.id')
            ->where('dt.id_karyawan', $this->id_karyawan)
            ->selectRaw("
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS booking,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS belum_lunas,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS lunas,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS dibatalkan
            ")
            ->first();

        /** -------------------------
         *  TOP PRODUK
         *  -------------------------
         *  (pakai join ke transaksi untuk filter cabang)
         */
        $topProduk = DB::table('transaksi as t')
            ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 't.id')
            ->join('produk as p', 'p.id', '=', 'dt.id_produk')
            ->whereIn('t.status', ['2', '3'])
            ->where('dt.id_karyawan', $this->id_karyawan)
            ->select('p.nama_item as produk', DB::raw('COUNT(dt.id) as jumlah_pesanan'))
            ->groupBy('p.id', 'p.nama_item')
            ->orderByDesc('jumlah_pesanan')
            ->limit(5)
            ->get();

        // dd($topProduk);

        /** -------------------------
         *  PESANAN TERBARU (5 terakhir)
         *  -------------------------
         */
        $pesananTerbaru = DB::table('transaksi as t')
            ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 't.id')
            ->leftJoin('daftar_pelanggan as dp', function ($join) {
                $join->on('dp.id', '=', DB::raw('CAST(t.id_pelanggan AS UNSIGNED)'))
                    ->on('dp.id_cabang', '=', 't.id_cabang');
            })
            ->where('dt.id_karyawan', $this->id_karyawan)
            ->whereIn('t.status', ['2', '3'])
            ->orderBy('t.tanggal', 'desc')
            ->limit(5)
            ->get([
                't.no_transaksi',
                DB::raw('COALESCE(dp.nama_pelanggan, "-") as pelanggan'),
                DB::raw('CAST(REPLACE(t.total_akhir, ",", "") AS DECIMAL(18,2)) as total_akhir'),
                't.tanggal',
            ]);

        /** -------------------------
         *  OMSET HARIAN (5 hari terakhir)
         *  -------------------------
         *  Query agregat per hari, lalu isi nol untuk hari yang tidak ada transaksi.
         */
        $omsetHarianRaw = DB::table('transaksi as t')
            ->join('detail_transaksi as dt', 'dt.id_transaksi', '=', 't.id')
            ->where('dt.id_karyawan', $this->id_karyawan)
            ->whereIn('t.status', ['2', '3'])
            ->whereBetween('t.tanggal', [$start5, $end5]) // 5 hari terakhir (hari ini termasuk)
            ->selectRaw("
                LEFT(t.tanggal, 10) as tgl,
                COALESCE(SUM(CAST(REPLACE(t.total_akhir, ',', '') AS DECIMAL(18,2))), 0) as pemasukan
            ")
            ->groupBy('tgl')
            ->orderBy('tgl', 'desc')
            ->get()
            ->keyBy('tgl');

        // bangun array 5 hari: hari ini, kemarin, dst (desc) dan isi 0 jika kosong
        $omsetHarian = [];
        for ($i = 0; $i < 5; $i++) {
            $d = now()->subDays($i)->toDateString(); // YYYY-MM-DD
            $omsetHarian[] = [
                'tanggal'   => $d,
                'pemasukan' => isset($omsetHarianRaw[$d]) ? (float) $omsetHarianRaw[$d]->pemasukan : 0,
            ];
        }

        /** -------------------------
         *  ASSIGN KE VARIABEL CLASS
         *  -------------------------
         */
        $this->total_keseluruhan = $totals;

        $this->report_harian = [
            'total_omset'       => (int) ($daily->total_omset ?? 0),
            'total_komisi'      => (int) ($daily->total_komisi ?? 0),
            'total_pesanan'     => (int) ($daily->total_pesanan ?? 0),
            'total_piutang'     => (int) ($daily->total_piutang ?? 0),
        ];

        $this->status_pesanan = (array) $statusPesanan;

        $this->top_produk = $topProduk;
        $this->pesanan_terbaru = $pesananTerbaru;
        $this->omset_harian    = $omsetHarian;

        // dd($this->total_keseluruhan, $this->report_harian, $this->status_pesanan, $this->sales_overview, $this->overall_report, $this->top_produk);
    }

    public function render()
    {
        $user = User::find(Auth::user()->id);

        if ($user->hasRole('direktur') || $user->hasRole('admin')) {
            $this->getDashboardDirektur();
            return view('livewire.dashboard.dashboard-direktur');
        } else if ($user->hasRole('kasir')) {
            $this->getDashboardKasirORCapster();
            return view('livewire.dashboard.dashboard-kasir');
        } else if ($user->hasRole('capster')) {
            $this->getDashboardKasirORCapster();
            return view('livewire.dashboard.dashboard-capster');
        }
    }
}
