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
    <ul style="font-size: 14px; margin-left: -10px">
        <li>Total Cash : @money($totalCash)</li>
        <li>Total Transfer : @money($totalTransfer)</li>
        <li>Saldo Akhir : @money($totalCash + $totalTransfer)</li>
    </ul>

    @php
    use Carbon\Carbon;

    // Group per tanggal (tanpa jam)
    $cashGroups = $cash->groupBy(fn($r) => Carbon::parse($r->waktu)->toDateString());
    $transferGroups = $transfer->groupBy(fn($r) => Carbon::parse($r->waktu)->toDateString());

    // Nomor baris jalan terus per seksi
    $noCash = 1;
    $noTf = 1;
    @endphp

    <div style="margin-top: 10px">
        <b style=" font-size:14px">Tipe Pembayaran Cash</b>
        <table>
            <thead>
                <tr>
                    <th style="text-align:center">No</th>
                    <th style="text-align:center">Waktu</th>
                    <th style="text-align:center">Keterangan</th>
                    <th style="text-align:center">Masuk</th>
                    <th style="text-align:center">Keluar</th>
                    <th style="text-align:center">User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashGroups as $tgl => $rows)
                {{-- Header tanggal --}}
                <tr style="background:#f2f2f2; font-weight:bold;">
                    <td colspan="6">
                        {{ Carbon::parse($tgl)->translatedFormat('l, d F Y') }}
                    </td>
                </tr>

                {{-- Detail baris --}}
                @foreach($rows as $tunai)
                <tr>
                    <td style="text-align:center">{{ $noCash++ }}</td>
                    <td style="text-align: center">{{ Carbon::parse($tunai->waktu)->format('H:i:s') }}</td>
                    <td>{{ $tunai->keterangan }}</td>
                    <td style="text-align:right">@money($tunai->masuk)</td>
                    <td style="text-align:right">@money($tunai->keluar)</td>
                    <td>{{ $tunai->admin }}</td>
                </tr>
                @endforeach

                {{-- Subtotal per tanggal --}}
                <tr style="font-weight:bold; background:#fafafa;">
                    <td colspan="3" style="text-align:right">Subtotal
                        {{ Carbon::parse($tgl)->translatedFormat('d F Y') }}</td>
                    <td style="text-align:right">@money($rows->sum('masuk'))</td>
                    <td style="text-align:right">@money($rows->sum('keluar'))</td>
                    <td></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center">No data available on the table</td>
                </tr>
                @endforelse

                {{-- Total seluruh Cash --}}
                @if($cash->isNotEmpty())
                <tr>
                    <td colspan="3" style="text-align:right; font-size:14px"><strong>Total Cash</strong>
                    </td>
                    <td style="text-align:right; font-size:14px">
                        <strong>@money($cash->sum('masuk'))</strong></td>
                    <td style="text-align:right; font-size:14px">
                        <strong>@money($cash->sum('keluar'))</strong></td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px">
        <b style="font-size:14px">Tipe Pembayaran Transfer</b>
        <table>
            <thead>
                <tr>
                    <th style="text-align:center">No</th>
                    <th style="text-align:center">Waktu</th>
                    <th style="text-align:center">Keterangan</th>
                    <th style="text-align:center">Masuk</th>
                    <th style="text-align:center">Keluar</th>
                    <th style="text-align:center">User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferGroups as $tgl => $rows)
                {{-- Header tanggal --}}
                <tr style="background:#f2f2f2; font-weight:bold;">
                    <td colspan="6">
                        {{ Carbon::parse($tgl)->translatedFormat('l, d F Y') }}
                    </td>
                </tr>

                {{-- Detail baris --}}
                @foreach($rows as $tunai)
                <tr>
                    <td style="text-align: center">{{ $noTf++ }}</td>
                    <td style="text-align: center">{{ Carbon::parse($tunai->waktu)->format('H:i:s') }}
                    </td>
                    <td>{{ $tunai->keterangan }}</td>
                    <td style="text-align:right">@money($tunai->masuk)</td>
                    <td style="text-align:right">@money($tunai->keluar)</td>
                    <td>{{ $tunai->admin }}</td>
                </tr>
                @endforeach

                {{-- Subtotal per tanggal --}}
                <tr style="font-weight:bold; background:#fafafa;">
                    <td colspan="3" style="text-align:right">Subtotal
                        {{ Carbon::parse($tgl)->translatedFormat('d F Y') }}</td>
                    <td style="text-align:right">@money($rows->sum('masuk'))</td>
                    <td style="text-align:right">@money($rows->sum('keluar'))</td>
                    <td></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center">No data available on the table</td>
                </tr>
                @endforelse

                {{-- Total seluruh Transfer --}}
                @if($transfer->isNotEmpty())
                <tr>
                    <td colspan="3" style="text-align:right; font-size:14px"><strong>Total
                            Transfer</strong></td>
                    <td style="text-align:right; font-size:14px">
                        <strong>@money($transfer->sum('masuk'))</strong></td>
                    <td style="text-align:right; font-size:14px">
                        <strong>@money($transfer->sum('keluar'))</strong></td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
