<div>
    <section class="section custom-section">
        <div class='section-header tw-flex tw-w-full'>
            <h1>Dashboard </h1>
            <div class="ml-auto">
                <select wire:model.live='filter_id_cabang' id='filter_id_cabang'
                    class='tw-w-full tw-border tw-border-gray-300 tw-rounded-full tw-text-sm'>
                    <option value='' disabled>-- Pilih Cabang --</option>
                    @foreach ($cabangs as $cabang)
                    <option value='{{ $cabang->id }}'>{{ $cabang->nama_cabang }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="section-body tw-mt-4">
            <div class="tw-px-4 -tw-mt-0 lg:-tw-mt-0 lg:tw-px-1">
                <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">Total Keseluruhan</h3>
            </div>
            <div class="tw-px-4 lg:tw-px-0 tw-mt-3">
                <div class="tw-overflow-x-auto no-scrollbar">
                    <div class="tw-grid tw-grid-flow-col tw-auto-cols-max tw-gap-4">
                        <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                            <div class="card-body tw-p-0">
                                <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3">
                                    <div>
                                        <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                            <i class="fa-sharp fa-solid fa-money-bill-trend-up tw-text-lg"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="tw-text-gray-300">Cash On Bank</h3>
                                        <p class="tw-text-xl">Rp14.267.787</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                            <div class="card-body tw-p-0">
                                <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3">
                                    <div>
                                        <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                            <i class="fa-sharp fa-solid fa-money-bill tw-text-lg"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="tw-text-gray-300">Piutang</h3>
                                        <p class="tw-text-xl">Rp965.408</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card tw-bg-gray-800 tw-rounded-lg tw-text-white tw-min-w-[250px]">
                            <div class="card-body tw-p-0">
                                <div class="tw-flex tw-px-4 tw-py-5 tw-space-x-3">
                                    <div>
                                        <div class="tw-px-4 tw-py-2 tw-border tw-border-gray-700 tw-rounded-lg">
                                            <i class="fa-sharp fa-solid fa-money-bill-1-wave tw-text-lg"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="tw-text-gray-300">Hutang</h3>
                                        <p class="tw-text-xl">@money(rand(0, 10000000))</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tambah kartu lagi -->
                    </div>
                </div>
            </div>
            <div class="tw-px-4 -tw-mt-0 lg:-tw-mt-0 lg:tw-px-1">
                <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                    Report Harian</h3>
                <p class="tw-leading-5 tw-mt-2">Cek pemasukan dari transaksi di tokomu.</p>
            </div>
            <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-y-0 tw-gap-x-4 tw-px-4 lg:tw-px-0 tw-mt-4">
                <div
                    class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]">
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        omset</p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money(23000230)</p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]"">
                    <p class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                    pengeluaran</p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money(0)</p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]
                    -tw-mt-4 lg:tw-mt-0">
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        pesanan
                    </p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money(0)</p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[75px] lg:tw-h-[75px]
                    -tw-mt-4 lg:tw-mt-0">
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        Piutang
                    </p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money(0)</p>
                </div>
            </div>
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
                                <i class="fa-regular fa-calendar-clock"></i>
                            </div>
                            <div>
                                <p
                                    class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                    Booking</p>
                                <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">0
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                        <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                            <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                <i class="fa-regular fa-receipt"></i>
                            </div>
                            <div>
                                <p
                                    class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                    Belum Lunas</p>
                                <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">0
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                        <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                            <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                <i class="fa-regular fa-badge-check"></i>
                            </div>
                            <div>
                                <p
                                    class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                    Lunas</p>
                                <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">0
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card card-info tw-rounded-lg tw-px-3 tw-py-4 tw-min-w-[150px]">
                        <div class="card-body tw-flex tw-items-center tw-p-0 tw-space-x-2">
                            <div class="tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-rounded-lg">
                                <i class="fa-sharp fa-regular fa-shield-xmark"></i>
                            </div>
                            <div>
                                <p
                                    class=" tw-tracking-wider tw-text-[#34395e] tw-text-xs lg:tw-text-sm tw-font-semibold">
                                    Dibatalkan</p>
                                <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">0
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card ">
                <h3>Sales Overview</h3>
                <div class="card-body">
                    <div class="tw-pb-4 lg:tw-py-4 tw-gap-y-10 tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-text-center">
                        <div class="tw-text-4xl">
                            <p class="text-warning tw-font-semibold">98</p>
                            <p class="tw-mt-4 tw-text-base tw-tracking-wider tw-text-[#34395e] tw-font-semibold">
                                Pesanan bulan ini</p>
                        </div>
                        <div class="tw-text-4xl">
                            <p class="text-info tw-font-semibold">@money(rand(0, 100000000))</p>
                            <p class="tw-mt-4 tw-text-base tw-tracking-wider tw-text-[#34395e] tw-font-semibold">
                                Omset bulan ini</p>
                        </div>
                        <div class="tw-text-4xl">
                            <p class="text-danger tw-font-semibold">-16.69 %</p>
                            <p class="tw-mt-4 tw-text-base tw-tracking-wider tw-text-[#34395e] tw-font-semibold">
                                Menurun : -16.69 % dibanding bulan lalu.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-x-4">
                <div class="card">
                    <h3>Overall Report</h3>
                    <div class="card-body -tw-mt-4 lg:-tw-mt-0">
                        <div
                            class="tw-grid tw-grid-cols-2 tw-space-x-0 lg:tw-space-x-2 tw-px-6 lg:tw-px-4 tw-gap-y-4 tw-mt-1">
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <div class="tw-px-3 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded-lg">
                                    <i class="far fa-users tw-text-lg"></i>
                                </div>
                                <div class="tw-tracking-wider tw-text-[#34395e]">
                                    <p class="tw-font-semibold tw-text-base">16</p>
                                    <p class="tw-text-sm">Pelanggan</p>
                                </div>
                            </div>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <div class="tw-px-3 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded-lg">
                                    <i class="far fa-bag-shopping tw-text-lg"></i>
                                </div>
                                <div class="tw-tracking-wider tw-text-[#34395e]">
                                    <p class="tw-font-semibold tw-text-base">16</p>
                                    <p class="tw-text-sm">Total Pesanan</p>
                                </div>
                            </div>
                        </div>
                        <hr class="tw-border tw-my-4 tw-bg-gray-50">
                        <div class="tw-grid tw-grid-cols-2 tw-space-x-2 tw-gap-y-8 tw-px-4 tw-text-center">
                            <div class="tw-text-[#34395e] tw-tracking-wider">
                                <p class="tw-font-semibold tw-text-lg">@money(rand(0, 100000000))</p>
                                <p>Total Profit</p>
                            </div>
                            <div class="tw-text-[#34395e] tw-tracking-wider">
                                <p class="tw-font-semibold tw-text-lg">@money(rand(0, 100000000))</p>
                                <p>Total Omset</p>
                            </div>
                            <div class="tw-text-[#34395e] tw-tracking-wider">
                                <p class="tw-font-semibold tw-text-lg">@money(rand(0, 100000000))</p>
                                <p>Total Pengeluaran</p>
                            </div>
                            <div class="tw-text-[#34395e] tw-tracking-wider">
                                <p class="tw-font-semibold tw-text-lg">@money(rand(0, 100000000))</p>
                                <p>Total HPP</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <h3>Top 5 Produk Terlaris</h3>
                    <div class="card-body -tw-mt-4 lg:-tw-mt-0">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 5; $i++) <tr>
                                    <td>Produk {{ $i + 1 }}</td>
                                    <td>{{ rand(0,100) }} pesanan</td>
                                    </tr>
                                    @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </section>
</div>
