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
    <table style="table-layout: auto">
        <thead>
            <tr>
                <th style="text-align:center; white-space: nowrap">No</th>
                <th style="text-align:center; white-space: nowrap">No Referensi</th>
                <th style="text-align:center; white-space: nowrap">Waktu</th>
                <th style="text-align:center; white-space: nowrap" width="30%">Keterangan</th>
                <th style="text-align:center; white-space: nowrap">Pengajuan</th>
                <th style="text-align:center; white-space: nowrap">Pelunasan</th>
                <th style="text-align:center; white-space: nowrap">Sisa Kasbon</th>
            </tr>
        </thead>
        <tbody>
            @php
            use Carbon\Carbon;

            // Kelompokkan per tanggal (tanpa jam)
            $groups = $pesanan->groupBy(fn($r) => Carbon::parse($r->tgl_disetujui)->toDateString());
            $saldo = 0;
            $no = 1; // nomor jalan terus lintas tanggal
            @endphp

            @forelse ($groups as $tgl => $rows)
            {{-- Header tanggal --}}
            <tr style="background:#f2f2f2; font-weight:bold;">
                <td colspan="7" style="padding:6px 8px;">
                    {{ Carbon::parse($tgl)->translatedFormat('l, d F Y') }}
                </td>
            </tr>

            @foreach ($rows as $row)
            @php
            $pengajuan = $row->kategori === 'pengajuan' ? $row->jumlah : 0;
            $pelunasan = $row->kategori === 'pelunasan' ? $row->jumlah : 0;

            // hitung saldo berjalan
            $saldo += $pelunasan - $pengajuan;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td style="text-align: center">{{ $row->no_referensi }}</td>
                <td style="text-align: center">{{ Carbon::parse($row->tgl_disetujui)->format('H:i') }}</td>
                <td>{{ $row->keterangan }}</td>
                <td class="text-right">@money($pengajuan)</td>
                <td class="text-right">@money($pelunasan)</td>
                <td class="text-right">@money(abs($saldo))</td>
            </tr>
            @endforeach
            @empty
            <tr>
                <td colspan="5" style="text-align:center">No data available on the table</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>
