<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            width: 80px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 6px 8px;
        }

        th {
            background: #ddd;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .box-total {
            border: 1px solid #000;
            padding: 8px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

    </style>
</head>

<body>

    <div class="header">
        <div class="title">Slip Gaji</div>
    </div>

    <p>
        {{ $cabang_lokasi->nama_cabang }}<br>
        {{ $cabang_lokasi->alamat }}<br>
    </p>
    @php
    use Carbon\Carbon;
    // Kalau controller ngirim $range = [startDate, endDate], tampilkan rentang
    $periode = isset($range) && is_array($range) && count($range)===2
    ? Carbon::parse($range[0])->translatedFormat('d F Y')
    .' s/d '.Carbon::parse($range[1])->translatedFormat('d F Y')
    : $tanggal->translatedFormat('d F Y');
    @endphp
    <table>
        <tr>
            <td>Nama</td>
            <td>: {{ $karyawan->name }}</td>
            <td>Jabatan</td>
            <td>: {{ $karyawan->role_id }}</td>
        </tr>
        <tr>
            <td>Tgl Mulai Bekerja</td>
            <td>: {{ Carbon::parse($karyawan->created_at)->translatedFormat('d F Y') }}</td>
            <td>Periode Gaji</td>
            <td>: {{ $periode }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Pendapatan</th>
                <th class="text-right">Jumlah</th>
                <th>Potongan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalPendapatan = 0;
            $totalPotongan = 0;
            $max = max(count($tunjangan), count($potongan));
            @endphp

            @for ($i = 0; $i < $max; $i++) <tr>
                <td>{{ $tunjangan[$i]->nama_komponen ?? '' }}</td>
                <td class="text-right">
                    @if(isset($tunjangan[$i]))
                    @money($tunjangan[$i]->jumlah)
                    @php $totalPendapatan += $tunjangan[$i]->jumlah; @endphp
                    @endif
                </td>
                <td>{{ $potongan[$i]->nama_komponen ?? '' }}</td>
                <td class="text-right">
                    @if(isset($potongan[$i]))
                    @money($potongan[$i]->jumlah)
                    @php $totalPotongan += $potongan[$i]->jumlah; @endphp
                    @endif
                </td>
                </tr>
                @endfor
        </tbody>
        <tfoot>
            <tr class="bold">
                <td>Total Pendapatan</td>
                <td class="text-right">@money($totalPendapatan)</td>
                <td>Total Potongan</td>
                <td class="text-right">@money($totalPotongan)</td>
            </tr>
        </tfoot>
    </table>

    <p class="box-total">
        Total Penerimaan Periode Ini <br>
        @money($totalPendapatan - $totalPotongan)
    </p>

    <p>
        Pembayaran gaji telah dilakukan oleh perusahaan
    </p>

</body>

</html>
