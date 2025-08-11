<div class="tw-inline-block">
    <div class=" text-center mt-2 text-uppercase">
        <p class="tw-text-[13px] tw-font-bold">{{ $cabang->nama_cabang }}</p>
        <div class="tw-mt-2">
            <p class="tw-text-[10px]">{{ $cabang->alamat }}</p>
            <p class="tw-text-[10px]">Telp/WA {{ $cabang->no_telp }}</p>
            <p class="tw-text-[10px]">Email: {{ $cabang->email }}</p>
        </div>
    </div>
    <hr class=" tw-bg-black tw-border-2 tw-border-black tw-mt-2 tw-black" />
    <div class=" mt-2 tw-text-[10px]">
        <div class="row">
            <div class="col-12">
                <table>
                    <tbody>
                        <tr>
                            <td width="35%">TANGGAL</td>
                            <td width="5%">:</td>
                            <td>{{ $transaksi[0]->tanggal }}</td>
                        </tr>
                        <tr>
                            <td>NO.TRX</td>
                            <td>:</td>
                            <td>{{ $transaksi[0]->no_transaksi }}</td>
                        </tr>
                        <tr>
                            <td>PLG.</td>
                            <td>:</td>
                            <td>{{ $transaksi[0]->nama_pelanggan }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <hr class=" tw-bg-black tw-border-2 tw-border-black tw-mt-2 tw-black" />
    <div class="mt-2.5 tw-uppercase tw-text-[10px]">
        @foreach ($detailTransaksis as $detailTransaksi)
        <div class="tw-mt-2.5">
            <p class="tw-font-bold">{{ $detailTransaksi->nama_item }}</p>
            <div class="row">
                <div class="col-7">
                    <p class="tw-text-right">{{ $detailTransaksi->jumlah }} x
                        {{ number_format($detailTransaksi->harga, 0, ',', '.') }}</p>
                    @if ($detailTransaksi->diskon > 0)
                    <p class="tw-text-right">DISKON</p>
                    @endif
                </div>
                <div class="col-5">
                    <p class="tw-text-right">{{ number_format($detailTransaksi->sub_total, 0, ',', '.') }}</p>
                    @if ($detailTransaksi->diskon > 0)
                    <p class="tw-text-right">-{{ number_format($detailTransaksi->diskon, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @endforeach
        <hr class="tw-bg-black tw-border-2 tw-border-black tw-mt-2 tw-black" />
        <div class="tw-text-[10px] tw-mt-2">
            <div class="tw-flex tw-justify-between tw-text-[10px]">
                <p>SUB TOTAL</p>
                <p>@price($transaksi[0]->total_sub_total)</p>
            </div>
            <div class="tw-flex tw-justify-between tw-text-[10px]">
                <p>DISKON</p>
                <p>@price($transaksi[0]->total_diskon)</p>
            </div>
            <div class="tw-flex tw-justify-between tw-text-[10px] tw-font-bold">
                <p>TOTAL</p>
                <p>@price($transaksi[0]->total_akhir)</p>
            </div>
            <div class="tw-flex tw-justify-between tw-text-[10px] tw-font-bold">
                <p>DIBAYAR</p>
                <p>@price($transaksi[0]->jumlah_dibayarkan)</p>
            </div>
            <div class="tw-flex tw-justify-between tw-text-[10px] tw-font-bold">
                <p>KEMBALI</p>
                <p>@price($transaksi[0]->kembalian)</p>
            </div>
        </div>
    </div>
    <hr class=" tw-bg-black tw-border-2 tw-border-black tw-mt-2 tw-black" />
    <div class="tw-text-[10px] tw-mt-2 tw-text-center">
        <p>Terima kasih telah berbelanja di tempat kami. Kepuasan Anda adalah tujuan kami</p>
    </div>
    {{-- <center class="tw-mt-2 tw-text-[10px]">
        <p>Link nota digital: </p>
        <img class="tw-mt-2" id='barcode'
            src="https://api.qrserver.com/v1/create-qr-code/?data=https://midragondev.my.id/&amp;size=100x100" alt=""
            title="HELLO" width="75" height="75" />
    </center> --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous">
    </script>
    <script>
        window.print();

    </script>

</div>
