<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HppController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        // Ambil parameter dari route
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idCabang   = $request->query('id_cabang'); // bisa null

        // Default: hari ini
        $start = $startInput ? Carbon::parse($startInput)->startOfDay()
            : Carbon::today()->startOfDay();
        $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()
            : Carbon::today()->endOfDay();

        // Swap kalau kebalik
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $pesanan = DB::table('transaksi')
            ->select(
                'transaksi.no_transaksi',
                'transaksi.tanggal',
                'daftar_pelanggan.nama_pelanggan',
                'transaksi.total_hpp'
            )
            ->join('daftar_pelanggan', 'transaksi.id_pelanggan', '=', 'daftar_pelanggan.id')
            ->where('transaksi.total_hpp', '<>', 0)                // hindari whereNot utk kompatibilitas
            ->whereIn('transaksi.status', ['2', '3'])
            ->whereBetween('transaksi.tanggal', [$start, $end])    // cover full hari
            ->when($idCabang, fn($q) => $q->where('transaksi.id_cabang', $idCabang))
            ->orderBy('transaksi.tanggal', 'asc')
            ->get();

        $data = [
            'judul'   => 'Laporan HPP',
            'tanggal' => now(),
            'pesanan' => $pesanan,
            'range'   => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-hpp', $data) // pakai view punyamu sendiri
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan HPP %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
