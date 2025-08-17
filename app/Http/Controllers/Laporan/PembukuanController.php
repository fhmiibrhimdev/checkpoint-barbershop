<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PembukuanController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        // Ambil parameter dari query
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idCabang   = $request->query('id_cabang'); // opsional

        // Default: hari ini
        $start = $startInput ? Carbon::parse($startInput)->startOfDay()
            : Carbon::today()->startOfDay();
        $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()
            : Carbon::today()->endOfDay();

        // Jika terbalik, tukar
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        // ===== 1) Sumber data =====

        // Transaksi (IN dari penjualan)
        $transaksi = DB::table('transaksi as t')
            ->select([
                't.tanggal as waktu',
                DB::raw("CONCAT('Pembayaran Pesanan ', t.no_transaksi) as keterangan"),
                't.jumlah_dibayarkan as masuk',
                DB::raw('0 as keluar'),
                'u.name as admin',
                't.id_metode_pembayaran',
                't.id_cabang as id_cabang',
            ])
            ->leftJoin('users as u', 'u.id', '=', 't.id_user')
            ->where('t.jumlah_dibayarkan', '>', 0)
            ->whereIn('t.status', ['2', '3']);

        // Piutang (pelunasan)
        $piutang = DB::table('piutang as p')
            ->select([
                'p.tanggal_bayar as waktu',
                DB::raw("CONCAT('Pelunasan Piutang ', p.no_referensi) as keterangan"),
                'p.jumlah_bayar as masuk',
                DB::raw('0 as keluar'),
                'u.name as admin',
                'p.id_metode_pembayaran',
                't.id_cabang as id_cabang', // ikut cabang transaksi
            ])
            ->leftJoin('transaksi as t', 't.id', '=', 'p.id_transaksi')
            ->leftJoin('users as u', 'u.id', '=', 't.id_user');

        // Kas (CASH in/out lain-lain)
        // Catatan: pastikan kolom k.id_cabang ada. Kalau belum ada, ganti jadi DB::raw('NULL as id_cabang')
        $kas = DB::table('kas as k')
            ->select([
                'k.tanggal as waktu',
                'k.keterangan',
                DB::raw("CASE WHEN k.status = 'In'  THEN k.jumlah ELSE 0 END as masuk"),
                DB::raw("CASE WHEN k.status = 'Out' THEN k.jumlah ELSE 0 END as keluar"),
                'u.name as admin',
                DB::raw('1 as id_metode_pembayaran'), // anggap kas = CASH
                'k.id_cabang as id_cabang',
            ])
            ->leftJoin('users as u', 'u.id', '=', 'k.id_pembuat')
            // Kecualikan pemasukan transfer/setor tunai bila ingin pembukuan kas harian bersih
            ->where('k.keterangan', 'not like', 'Pemasukan Transfer%')
            ->where('k.keterangan', 'not like', 'SETOR TUNAI%');

        $cob = DB::table('cash_on_bank as cob')
            ->select([
                'cob.tanggal as waktu',
                'cob.keterangan',
                DB::raw("CASE WHEN cob.jenis = 'In'  THEN cob.jumlah ELSE 0 END as masuk"),
                DB::raw("CASE WHEN cob.jenis = 'Out' THEN cob.jumlah ELSE 0 END as keluar"),
                DB::raw("'' as admin"),                 // jika tidak ada kolom user
                DB::raw('2 as id_metode_pembayaran'),   // 2 = Transfer/Bank
                'cob.id_cabang as id_cabang',
            ])
            ->whereIn('sumber_tabel', ['Hutang', 'Piutang', 'Kasbon (Pengajuan)', 'Kasbon (Pelunasan)', 'Slip Gaji']);

        // ===== 2) UNION dan filter luar =====
        $sub = $transaksi->unionAll($piutang)->unionAll($kas)->unionAll($cob);

        $query = DB::query()
            ->fromSub($sub, 'x')
            ->whereBetween(DB::raw('DATE(x.waktu)'), [$start, $end])        // filter rentang datetime
            ->when($idCabang, fn($q) => $q->where('x.id_cabang', $idCabang))
            ->orderBy('x.waktu', 'asc');

        $rows = $query->get();

        // ===== 3) Kelompok & total per metode pembayaran =====
        $grouped = $rows->groupBy('id_metode_pembayaran');

        // Konvensi umum: 1 = cash, 2 = transfer. Sesuaikan dengan data Anda.
        $cash     = $grouped->get(1, collect());
        $transfer = $grouped->get(2, collect());

        $totalCash     = $cash->sum('masuk') - $cash->sum('keluar');
        $totalTransfer = $transfer->sum('masuk') - $transfer->sum('keluar');

        // (Opsional) Kalau ada metode lain, bisa total dinamis:
        // $totalsByMethod = $grouped->map(fn($c) => $c->sum('masuk') - $c->sum('keluar'));

        // ===== 4) Render PDF =====
        $data = [
            'judul'         => 'Laporan Pembukuan',
            'tanggal'       => now(),
            'cash'          => $cash,
            'transfer'      => $transfer,
            'totalCash'     => $totalCash,
            'totalTransfer' => $totalTransfer,
            'range'         => [$start->toDateString(), $end->toDateString()],
            // 'totalsByMethod' => $totalsByMethod, // kalau mau dipakai di view
        ];

        $pdf = Pdf::loadView('pdf.laporan-pembukuan', $data)
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Pembukuan %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
