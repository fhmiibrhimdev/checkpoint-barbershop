<div>
    <section class="section custom-section">
        <div class="section-header">
            <h1>Dashboard</h1>
        </div>

        <div class="section-body tw-mt-4">
            <div id="total-keseluruhan">
                <div class="tw-px-4 -tw-mt-0 lg:-tw-mt-0 lg:tw-px-1">
                    <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">Total Keseluruhan</h3>
                </div>
                <div class="tw-px-4 lg:tw-px-0 tw-mt-3">
                    <div class="tw-overflow-x-auto no-scrollbar">
                        <div class="tw-grid tw-grid-flow-col tw-auto-cols-max tw-gap-4">
                            <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                                <div class="card-body tw-p-0">
                                    <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3 tw-items-center">
                                        <div>
                                            <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                                <i class="fa-sharp fa-solid fa-money-bill-trend-up tw-text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="tw-text-gray-50">Pendapatan Komisi</h3>
                                            <p class="tw-text-xl">@money($total_keseluruhan['komisi_bulanan'] ?? 0)</p>
                                            <p class="tw-leading-4 tw-text-gray-300">Bulan Ini</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                                <div class="card-body tw-p-0">
                                    <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3 tw-items-center">
                                        <div>
                                            <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                                <i class="fa-sharp fa-solid fa-money-bill-trend-up tw-text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="tw-text-gray-50">Pendapatan Komisi</h3>
                                            <p class="tw-text-xl">@money($total_keseluruhan['komisi'] ?? 0)</p>
                                            <p class="tw-leading-4 tw-text-gray-300">Keseluruhan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                                <div class="card-body tw-p-0">
                                    <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3 tw-items-center">
                                        <div>
                                            <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                                <i class="fa-sharp fa-solid fa-money-bill tw-text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="tw-text-gray-50">Kasbon</h3>
                                            <p class="tw-text-xl">@money($total_keseluruhan['kasbon'] ?? 0)</p>
                                            <p class="tw-leading-4 tw-text-gray-300">Keseluruhan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                                <div class="card-body tw-p-0">
                                    <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3 tw-items-center">
                                        <div>
                                            <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                                <i class="fa-sharp fa-solid fa-money-bill tw-text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="tw-text-gray-50">Piutang</h3>
                                            <p class="tw-text-xl">@money($total_keseluruhan['piutang'] ?? 0)</p>
                                            <p class="tw-leading-4 tw-text-gray-300">Keseluruhan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tambah kartu lagi -->
                        </div>
                    </div>
                </div>
            </div>

            <div id="report-harian">
                <div class="tw-px-4 -tw-mt-0 lg:-tw-mt-0 lg:tw-px-1">
                    <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                        Report Harian</h3>
                    <p class="tw-leading-5 tw-mt-2">Cek pemasukan dari transaksi di tokomu.</p>
                </div>
                <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-y-0 tw-gap-x-4 tw-px-4 lg:tw-px-0 tw-mt-4">
                    <div
                        class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]">
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                            Omset</p>
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                            @money($report_harian['total_omset'] ?? 0)</p>
                    </div>
                    <div
                        class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]">
                        <p class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                            Komisi</p>
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                            @money($report_harian['total_komisi'] ?? 0)</p>
                    </div>
                    <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]
                    -tw-mt-4 lg:tw-mt-0">
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                            Pesanan
                        </p>
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                            {{ $report_harian['total_pesanan'] ?? 0 }}</p>
                    </div>
                    <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]
                    -tw-mt-4 lg:tw-mt-0">
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                            Piutang
                        </p>
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                            @money($report_harian['total_piutang'] ?? 0)</p>
                    </div>
                </div>
            </div>

            <div id="status-pesanan">
                <div class="tw-px-4 -tw-mt-0 lg:tw-px-1">
                    <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                        Status Pesanan</h3>
                    <p class="tw-leading-5 tw-mt-2">Cek status pesanan di tokomu.</p>
                </div>
                <div class="tw-overflow-x-auto no-scrollbar">
                    <div
                        class="tw-grid tw-grid-flow-col lg:tw-grid-cols-4 tw-auto-cols-max tw-gap-4 tw-px-4 lg:tw-px-0 tw-mt-4">
                        <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                            <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                                <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                    <i class="fa-regular fa-calendar-clock text-primary"></i>
                                </div>
                                <div>
                                    <p
                                        class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                        Booking</p>
                                    <p
                                        class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                                        {{ $status_pesanan['booking'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                            <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                                <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                    <i class="fa-regular fa-receipt text-warning"></i>
                                </div>
                                <div>
                                    <p
                                        class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                        Belum Lunas</p>
                                    <p
                                        class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                                        {{ $status_pesanan['belum_lunas'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                            <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                                <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                    <i class="fa-regular fa-badge-check text-success"></i>
                                </div>
                                <div>
                                    <p
                                        class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                        Lunas</p>
                                    <p
                                        class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                                        {{ $status_pesanan['lunas'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                            <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                                <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                    <i class="fa-sharp fa-regular fa-shield-xmark text-danger"></i>
                                </div>
                                <div>
                                    <p
                                        class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                        Dibatalkan</p>
                                    <p
                                        class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                                        {{ $status_pesanan['dibatalkan'] ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="table-sales">
                <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-7 tw-gap-x-0 lg:tw-gap-x-4">
                    <div class="tw-col-span-3">
                        <div class="card">
                            <h3>Pesanan Terbaru</h3>
                            <div class="card-body -tw-mt-4 lg:-tw-mt-0">
                                <div class="table-responsive no-scrollbar">
                                    <table class="tw-w-full tw-table-auto">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Pelanggan</th>
                                                <th>Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pesanan_terbaru as $psn_terbaru)
                                            {{-- @dd($produk->produk) --}}
                                            <tr>
                                                <td class="tw-whitespace-nowrap">{{ $psn_terbaru->no_transaksi }}</td>
                                                <td class="tw-whitespace-nowrap">{{ $psn_terbaru->tanggal }}</td>
                                                <td class="tw-whitespace-nowrap">{{ $psn_terbaru->pelanggan }}</td>
                                                <td class="tw-whitespace-nowrap">@money($psn_terbaru->total_akhir)</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tw-col-span-2">
                        <div class="card">
                            <h3>Omset Harian</h3>
                            <div class="card-body -tw-mt-4 lg:-tw-mt-0">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="tw-text-center">Tanggal</th>
                                            <th class="tw-text-right">Pemasukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($omset_harian as $omset_hari)
                                        <tr>
                                            <td class="tw-whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($omset_hari['tanggal'])->isoFormat('dddd, D MMMM Y') }}
                                            </td>
                                            <td class="tw-text-right">@money($omset_hari['pemasukan'])</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tw-col-span-2">
                        <div class="card">
                            <h3>Top 5 Produk Terlaris</h3>
                            <div class="card-body -tw-mt-4 lg:-tw-mt-0">
                                <table>
                                    <thead>
                                        <tr>
                                            <th width="60%">Produk</th>
                                            <th width="40%">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($top_produk as $produk)
                                        {{-- @dd($produk->produk) --}}
                                        <tr>
                                            <td>{{ $produk->produk }}</td>
                                            <td>{{ $produk->jumlah_pesanan }} pesanan</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
