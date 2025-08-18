<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KasbonKaryawanController extends Controller
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

        $pesanan = DB::table('kasbon')
            ->select(
                'kasbon.no_referensi',
                'kasbon.tgl_disetujui',
                'kasbon.jumlah',
                'kasbon.keterangan',
                'kasbon.kategori',
            )
            ->where('kasbon.id_karyawan', $idKaryawan)
            ->whereBetween('kasbon.tgl_disetujui', [$start, $end])    // cover full hari
            ->orderBy('kasbon.tgl_disetujui', 'asc')
            ->get();

        $data = [
            'judul'   => 'Laporan Kasbon',
            'tanggal' => now(),
            'pesanan' => $pesanan,
            'range'   => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-kasbon-karyawan', $data) // pakai view punyamu sendiri
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Kasbon %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
