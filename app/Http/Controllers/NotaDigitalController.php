<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaDigitalController extends Controller
{
    public function printNota(Request $request, string $key)
    {
        // jika pakai base64url di atas, kembalikan dulu ke base64 biasa:
        $b64 = strtr($key, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $noTransaksi = base64_decode($b64, true);
        // kalau pakai base64 biasa + urlencode, cukup:
        // $noTransaksi = base64_decode($request->key, true);

        if ($noTransaksi === false || $noTransaksi === '') {
            abort(404, 'Kode nota tidak valid');
        }

        $transaksi = DB::table('transaksi AS t')->select('t.id', 't.id_cabang', 't.tanggal', 't.no_transaksi', 'p.nama_pelanggan', 't.total_sub_total', 't.total_diskon', 't.total_akhir', 't.jumlah_dibayarkan', 't.kembalian')
            ->join('daftar_pelanggan AS p', 'p.id', '=', 't.id_pelanggan')
            ->where('t.no_transaksi', $noTransaksi)
            ->get();

        $cabang = DB::table('cabang_lokasi')->select('id', 'nama_cabang', 'alamat', 'no_telp', 'email')->where('id', $transaksi->first()->id_cabang)->first();

        $detailTransaksis = DB::table('detail_transaksi AS dt')->select('dt.nama_item', 'dt.deskripsi_item', 'dt.jumlah', 'dt.harga', 'dt.diskon', 'dt.sub_total', 'dt.total_harga')
            ->where('dt.id_transaksi', $transaksi->first()->id)
            ->get();

        if (!$transaksi) abort(404, 'Transaksi tidak ditemukan');

        // render view / pdf / dsb.
        return view('nota.digital', compact('cabang', 'transaksi', 'detailTransaksis'));
    }
}
