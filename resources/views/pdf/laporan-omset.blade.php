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
                <th style="text-align: center">No</th>
                <th style="text-align: center">No Transaksi</th>
                <th style="text-align: center">Pelanggan</th>
                <th style="text-align: center">Status</th>
                <th style="text-align: center">Total Akhir</th>
                <th style="text-align: center">Uang DP</th>
                <th style="text-align: center">Sudah Dibayar</th>
                <th style="text-align: center">Sisa Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanan as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_transaksi }}</td>
                <td>{{ $p->nama_pelanggan ?? '-' }}</td>
                <td>
                    @if ($p->status == '2')
                    <span class="text-danger">Belum Lunas</span>
                    @elseif ($p->status == '3')
                    <span class="text-danger">Lunas</span>
                    @endif
                </td>
                <td>@money($p->total_akhir)</td>
                <td>
                    @if ($p->status == '3')
                    @money(0)
                    @elseif ($p->status == '2')
                    @money($p->uang_dp)
                    @endif
                </td>
                <td>
                    @if ($p->status == '3' || ($p->status == '3' && ($p->total_akhir < $p->sudah_dibayar)))
                        @money($p->total_akhir)
                        @elseif ($p->status == '2')
                        @money($p->sudah_dibayar)
                        @endif
                </td>
                <td>
                    @if ($p->status == '3')
                    @money(0)
                    @else
                    @money($p->sisa_bayar)
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center">No data available on the table</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
