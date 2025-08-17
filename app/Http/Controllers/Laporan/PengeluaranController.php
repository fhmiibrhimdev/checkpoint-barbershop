<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PengeluaranController extends Controller
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

        $rows = DB::table('cash_on_bank as cob')
            ->select([
                'cob.tanggal',
                'cob.no_referensi',
                'cob.keterangan',
                'cob.sumber_tabel',
                'cob.jumlah',
            ])
            // ->whereDate('kas.tanggal', Carbon::today())
            ->where('cob.jenis', 'Out')
            ->whereBetween(DB::raw('DATE(cob.tanggal)'), [$start, $end])
            ->when($idCabang, fn($q) => $q->where('cob.id_cabang', $idCabang)) // pastikan kolom ada
            ->orderBy('cob.tanggal', 'asc')
            ->get();

        $data = [
            'judul'   => 'Laporan Pengeluaran',
            'tanggal' => now(),
            'data'    => $rows,
            'range'   => [$start->toDateString(), $end->toDateString()],
        ];

        $pdf = Pdf::loadView('pdf.laporan-pengeluaran', $data)
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Pengeluaran %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
