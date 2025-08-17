<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    {{-- <link rel="stylesheet" href="{{ public_path('assets/bootstrap-4.6/css/bootstrap.min.css') }}"> --}}
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
            border: none;
        }

    </style>
</head>

<body>
    <p style="font-family:'Courier New',monospace; font-size:12px">
        Dicetak pada {{ $tanggal->translatedFormat('l, d F Y H:i') }}
    </p>

    <div style="text-align:center; line-height:0.5">
        <h3>{{ $judul }}</h3>
        <h3 style="font-weight:400">
            Periode
            {{ \Carbon\Carbon::parse($range[0])->translatedFormat('d F Y') }}
            s/d
            {{ \Carbon\Carbon::parse($range[1])->translatedFormat('d F Y') }}
        </h3>
    </div>

    <hr>

    <p><strong>Penjualan</strong></p>
    <table>
        <tr>
            <td width="30%" style="padding-top:10px">Total Penjualan/Omset</td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px">@money($total->total_omset)</td>
        </tr>
        <tr>
            <td style="padding-top:10px">Total HPP Produk Terjual</td>
            <td style="padding-top:10px">:</td>
            <td style="padding-top:10px">@money($total->total_hpp_produk)</td>
        </tr>
        <tr>
            <td style="padding-top:10px"><strong>MARGIN</strong></td>
            <td style="padding-top:10px">:</td>
            <td style="padding-top:10px; font-size:13px"><strong>@money($total->margin)</strong></td>
        </tr>
    </table>

    <hr style="margin-top:20px;">

    @if($pemasukanKas->isNotEmpty())
    <p><strong>Pendapatan Lain-lain</strong></p>
    <table>
        @foreach ($pemasukanKas as $pl)
        <tr>
            <td width="30%" style="padding-top:10px">Pendapatan {{ $pl->keterangan }}</td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px">@money($pl->jumlah)</td>
        </tr>
        @endforeach
        <tr>
            <td width="30%" style="padding-top:10px"><strong>Total Pendapatan Lain-lain</strong></td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px; font-size:13px"><strong>@money($total_pendapatan_ll)</strong></td>
        </tr>
    </table>
    <hr style="margin-top:20px;">
    @endif

    <p><strong>Beban Pengeluaran</strong></p>
    <table>
        @foreach ($pengeluaran as $beban)
        <tr>
            <td width="30%" style="padding-top:10px">Beban {{ $beban->keterangan }}</td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px">@money($beban->jumlah)</td>
        </tr>
        @endforeach
        <tr>
            <td width="30%" style="padding-top:10px"><strong>Total Beban Pengeluaran</strong></td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px; font-size:13px"><strong>@money($total_beban)</strong></td>
        </tr>
    </table>

    <hr style="margin-top:20px;">

    <table>
        <tr>
            <td width="30%" style="padding-top:10px"><strong>LABA BERSIH</strong></td>
            <td width="2%" style="padding-top:10px">:</td>
            <td style="padding-top:10px; font-size:13px"><strong>@money($laba_bersih)</strong></td>
        </tr>
    </table>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
