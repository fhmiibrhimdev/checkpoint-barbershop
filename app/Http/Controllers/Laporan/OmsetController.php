<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OmsetController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        // Ambil & sanitasi parameter
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idCabang   = $request->query('id_cabang');

        // Default kalau kosong: hari ini
        $start = $startInput ? Carbon::parse($startInput)->startOfDay()
            : Carbon::today()->startOfDay();
        $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()
            : Carbon::today()->endOfDay();

        // Jika user kebalik (start > end), tukar
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $pesanan = DB::table('transaksi')
            ->select(
                'transaksi.no_transaksi',
                'transaksi.tanggal',
                'daftar_pelanggan.nama_pelanggan',
                'transaksi.total_akhir',
                'transaksi.jumlah_dibayarkan as uang_dp',
                'transaksi.kembalian as sisa_bayar',
                'transaksi.status',
                DB::raw("
                    CASE 
                        WHEN transaksi.status = '3' 
                             THEN COALESCE(NULLIF(SUM(piutang.jumlah_bayar), 0), transaksi.jumlah_dibayarkan)
                        ELSE COALESCE(SUM(piutang.jumlah_bayar), 0)
                    END as sudah_dibayar
                ")
            )
            ->join('daftar_pelanggan', 'transaksi.id_pelanggan', '=', 'daftar_pelanggan.id')
            ->leftJoin('piutang', 'piutang.id_transaksi', '=', 'transaksi.id')
            ->whereBetween('transaksi.tanggal', [$start, $end])
            ->whereIn('transaksi.status', ['2', '3'])
            ->when($idCabang, fn($q) => $q->where('transaksi.id_cabang', $idCabang))
            ->groupBy(
                'transaksi.no_transaksi',
                'transaksi.tanggal',
                'daftar_pelanggan.nama_pelanggan',
                'transaksi.total_akhir',
                'transaksi.jumlah_dibayarkan',
                'transaksi.kembalian',
                'transaksi.status'
            )
            ->orderBy('transaksi.tanggal', 'asc')
            ->get();

        $data = [
            'judul'   => 'Laporan Omset Pesanan',
            'tanggal' => now(),
            'pesanan' => $pesanan,
            'range'   => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-omset', $data)
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Omset %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
