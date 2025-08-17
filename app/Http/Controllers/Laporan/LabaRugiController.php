<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LabaRugiController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idCabang   = $request->query('id_cabang'); // opsional

        // Default: hari ini
        $start = $startInput ? Carbon::parse($startInput)->startOfDay()
            : Carbon::today()->startOfDay();
        $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()
            : Carbon::today()->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start]; // tukar kalau user kebalik
        }

        // ========= RINGKASAN PENJUALAN =========
        $total = DB::table('transaksi')
            ->selectRaw("
                COALESCE(SUM(total_akhir),0) as total_omset,
                COALESCE(SUM(total_hpp),0)   as total_hpp_produk,
                (COALESCE(SUM(total_akhir),0) - COALESCE(SUM(total_hpp),0)) as margin
            ")
            ->whereIn('status', ['2', '3'])
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start, $end])
            ->when($idCabang, fn($q) => $q->where('id_cabang', $idCabang))
            ->first();

        // ========= PENDAPATAN LAIN-LAIN (Kas In) =========
        $pemasukanKas = DB::table('kas')
            ->select('keterangan', 'jumlah')
            ->where('status', 'In')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start, $end])
            ->when($idCabang, fn($q) => $q->where('id_cabang', $idCabang))
            // (Opsional) hindari mutasi yang bukan pendapatan riil:
            ->where('keterangan', 'not like', 'Pemasukan Transfer%')
            ->where('keterangan', 'not like', 'SETOR TUNAI%')
            ->orderBy('tanggal', 'asc')
            ->get();

        // ========= BEBAN (Kas Out) =========
        $pengeluaran = DB::table('kas')
            ->select('keterangan', 'jumlah')
            ->where('status', 'Out')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start, $end])
            ->when($idCabang, fn($q) => $q->where('id_cabang', $idCabang))
            ->orderBy('tanggal', 'asc')
            ->get();

        // ========= HITUNGAN =========
        $total_beban         = (float) $pengeluaran->sum('jumlah');
        $total_pendapatan_ll = (float) $pemasukanKas->sum('jumlah');
        $margin              = (float) ($total->margin ?? 0);
        $laba_bersih         = $margin + $total_pendapatan_ll - $total_beban;

        // ========= RENDER PDF =========
        $data = [
            'judul'               => 'Laporan Laba Rugi',
            'tanggal'             => now(),
            'total'               => $total,
            'pemasukanKas'        => $pemasukanKas,
            'pengeluaran'         => $pengeluaran,
            'total_pendapatan_ll' => $total_pendapatan_ll,
            'total_beban'         => $total_beban,
            'laba_bersih'         => $laba_bersih,
            'range'               => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-laba-rugi', $data)->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Laba Rugi %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
