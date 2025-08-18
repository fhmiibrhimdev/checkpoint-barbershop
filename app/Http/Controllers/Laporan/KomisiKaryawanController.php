<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KomisiKaryawanController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        // Ambil parameter dari route
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idKaryawan   = $request->query('id_karyawan'); // bisa null

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
                'transaksi.total_komisi_karyawan',
            )
            ->join('detail_transaksi', 'transaksi.id', '=', 'detail_transaksi.id_transaksi')
            ->join('daftar_pelanggan', 'transaksi.id_pelanggan', '=', 'daftar_pelanggan.id')
            ->whereNot('transaksi.total_komisi_karyawan', 0)
            ->whereIn('status', ['2', '3'])
            ->where('detail_transaksi.id_karyawan', $idKaryawan)
            ->whereBetween('transaksi.tanggal', [$start, $end])    // cover full hari
            ->orderBy('transaksi.tanggal', 'asc')
            ->get();

        $data = [
            'judul'   => 'Laporan Komisi',
            'tanggal' => now(),
            'pesanan' => $pesanan,
            'range'   => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-komisi-karyawan', $data) // pakai view punyamu sendiri
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Komisi %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
