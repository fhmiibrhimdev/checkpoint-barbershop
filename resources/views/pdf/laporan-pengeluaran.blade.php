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
                <th style="text-align:center">No Referensi</th>
                <th style="text-align:center">Keterangan</th>
                <th style="text-align:center">Kategori</th>
                <th style="text-align:center">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php
            use Carbon\Carbon;

            $no = 1;
            // Kelompokkan per tanggal (YYYY-MM-DD)
            $groups = $data->groupBy(fn($r) => Carbon::parse($r->tanggal)->toDateString());
            $grandTotal = $data->sum('jumlah');
            @endphp

            @forelse ($groups as $tgl => $items)
            {{-- Header per tanggal --}}
            <tr style="background:#f2f2f2; font-weight:bold;">
                <td colspan="5">
                    {{ Carbon::parse($tgl)->translatedFormat('l, d F Y') }}
                </td>
            </tr>

            {{-- Detail baris --}}
            @foreach ($items as $p)
            <tr>
                <td style="text-align:center">{{ $no++ }}</td>
                <td>{{ $p->no_referensi }}</td>
                <td>{{ $p->keterangan }}</td>
                <td>{{ $p->sumber_tabel }}</td>
                <td style="text-align:right">@money($p->jumlah)</td>
            </tr>
            @endforeach

            {{-- Subtotal per tanggal --}}
            <tr style="font-weight:bold; background:#fafafa;">
                <td colspan="4" style="text-align:right">
                    Subtotal {{ Carbon::parse($tgl)->translatedFormat('d F Y') }}
                </td>
                <td style="text-align:right">@money($items->sum('jumlah'))</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center">No data available on the table</td>
            </tr>
            @endforelse

            {{-- Grand total seluruh tanggal --}}
            @if($data->isNotEmpty())
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold; padding-top:10px;">
                    Total Pengeluaran (Semua Tanggal)
                </td>
                <td style="text-align:right; font-weight:bold; padding-top:10px; font-size:13px;">
                    @money($grandTotal)
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
