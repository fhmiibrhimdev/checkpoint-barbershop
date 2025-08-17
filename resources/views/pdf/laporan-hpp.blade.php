<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    {{-- <link rel="stylesheet" href="{{ public_path('assets/bootstrap-4.6/css/bootstrap.min.css') }}"> --}}
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

    </style>
</head>

<body>
    <p style="font-family: 'Courier New', Courier, monospace; font-size: 12px">Dicetak pada
        {{ $tanggal->translatedFormat('l, d F Y H:i:s') }}</p>
    <div style="text-align: center; line-height: 0.5">
        <h3>{{ $judul }}</h3>
        @php
        // Kalau controller ngirim $range = [startDate, endDate], tampilkan rentang
        $periode = isset($range) && is_array($range) && count($range)===2
        ? \Carbon\Carbon::parse($range[0])->translatedFormat('l, d F Y')
        .' s/d '.\Carbon\Carbon::parse($range[1])->translatedFormat('l, d F Y')
        : $tanggal->translatedFormat('l, d F Y');
        @endphp
        <h4 style="margin:0; font-weight:400;">Periode {{ $periode }}</h4>
    </div>
    <hr>
    <table>
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">No Transaksi</th>
                <th style="text-align:center">Waktu</th>
                <th style="text-align:center">Pelanggan</th>
                <th style="text-align:center">Total HPP</th>
            </tr>
        </thead>
        <tbody>
            @php
            use Carbon\Carbon;

            // Kelompokkan per tanggal (tanpa jam)
            $groups = $pesanan->groupBy(fn($r) => Carbon::parse($r->tanggal)->toDateString());
            $grandTotal = $pesanan->sum('total_hpp');
            $no = 1; // nomor jalan terus lintas tanggal
            @endphp

            @forelse ($groups as $tgl => $rows)
            {{-- Header tanggal --}}
            <tr style="background:#f2f2f2; font-weight:bold;">
                <td colspan="5" style="padding:6px 8px;">
                    {{ Carbon::parse($tgl)->translatedFormat('l, d F Y') }}
                </td>
            </tr>

            {{-- Detail baris per tanggal --}}
            @foreach ($rows as $p)
            <tr>
                <td style="text-align:center">{{ $no++ }}</td>
                <td style="text-align:center">{{ $p->no_transaksi }}</td>
                <td style="text-align:center">
                    {{ Carbon::parse($p->tanggal)->translatedFormat('d-m-Y H:i:s') }}
                </td>
                <td style="text-align: center">{{ $p->nama_pelanggan ?? '-' }}</td>
                <td style="text-align:right">@money($p->total_hpp)</td>
            </tr>
            @endforeach

            {{-- Subtotal tanggal --}}
            <tr style="font-weight:bold; background:#fafafa;">
                <td colspan="4" style="text-align:right; padding:6px 8px;">
                    Subtotal {{ Carbon::parse($tgl)->translatedFormat('d F Y') }}
                </td>
                <td style="text-align:right; padding:6px 8px;">
                    @money($rows->sum('total_hpp'))
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center">No data available on the table</td>
            </tr>
            @endforelse

            {{-- Grand total semua tanggal --}}
            @if($pesanan->isNotEmpty())
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold; padding-top:10px;">
                    Total HPP (Semua Tanggal)
                </td>
                <td style="text-align:right; font-weight:bold; font-size:13px; padding-top:10px;">
                    @money($grandTotal)
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
