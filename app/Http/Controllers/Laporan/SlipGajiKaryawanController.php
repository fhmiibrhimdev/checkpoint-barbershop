<?php

namespace App\Http\Controllers\Laporan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SlipGajiKaryawanController extends Controller
{
    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        // Ambil parameter dari route
        $startInput = $request->query('start_date');
        $endInput   = $request->query('end_date');
        $idKaryawan   = $request->query('id_karyawan'); // bisa null
        $noReferensi = $request->query('no_referensi'); // bisa null

        // Default: hari ini
        $start = $startInput ? Carbon::parse($startInput)->startOfDay()
            : Carbon::today()->startOfDay();
        $end   = $endInput   ? Carbon::parse($endInput)->endOfDay()
            : Carbon::today()->endOfDay();

        // Swap kalau kebalik
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $karyawan = DB::table('daftar_karyawan')
            ->select('name', 'role_id', 'daftar_karyawan.created_at', 'daftar_karyawan.id_cabang')
            ->join('users', 'daftar_karyawan.id_user', '=', 'users.id')
            ->where('daftar_karyawan.id', $idKaryawan)
            ->first();

        $cabang_lokasi = DB::table('cabang_lokasi')
            ->select('nama_cabang', 'alamat')
            ->where('id', $karyawan->id_cabang)
            ->first();

        $komisi = DB::table('transaksi')
            ->select(
                DB::raw('SUM(transaksi.total_komisi_karyawan) as total_komisi')
            )
            ->join('detail_transaksi', 'transaksi.id', '=', 'detail_transaksi.id_transaksi')
            ->where('detail_transaksi.id_karyawan', $idKaryawan)
            ->whereIn('transaksi.status', ['2', '3'])
            ->whereBetween('transaksi.tanggal', [$start, $end])    // cover full hari
            ->value('total_komisi');

        $tunjangan = DB::table('slip_gaji as sg')
            ->select(
                'dsg.nama_komponen',
                'dsg.jumlah',
            )
            ->join('detail_slip_gaji as dsg', 'sg.id', '=', 'dsg.id_slip_gaji')
            ->where('sg.no_referensi', $noReferensi)
            ->where('dsg.tipe', 'tunjangan')
            ->get();

        $tunjangan->push((object)[
            'nama_komponen' => 'Komisi',
            'jumlah' => $komisi,
        ]);

        $potongan = DB::table('slip_gaji as sg')
            ->select(
                'dsg.nama_komponen',
                'dsg.jumlah',
            )
            ->join('detail_slip_gaji as dsg', 'sg.id', '=', 'dsg.id_slip_gaji')
            ->where('sg.no_referensi', $noReferensi)
            ->where('dsg.tipe', 'potongan')
            ->get();

        $data = [
            'judul'   => 'Laporan Slip Gaji',
            'tanggal' => now(),
            'komisi' => $komisi,
            'tunjangan' => $tunjangan,
            'potongan' => $potongan,
            'range'   => [$start->toDateString(), $end->toDateString()],
            'karyawan' => $karyawan,
            'cabang_lokasi' => $cabang_lokasi,
        ];

        $pdf = Pdf::loadView('pdf.laporan-slip-gaji-karyawan', $data) // pakai view punyamu sendiri
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan Slip Gaji %s s.d %s.pdf',
            $start->translatedFormat('d F Y'),
            $end->translatedFormat('d F Y')
        );

        return $pdf->stream($namaFile);
    }
}
