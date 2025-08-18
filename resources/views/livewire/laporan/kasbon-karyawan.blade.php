<div>
    <section class="section custom-section">
        @if ($agent->isDesktop())
        <div class='section-header tw-flex tw-justify-between tw-w-full'>
            <h1>Laporan Kasbon Karyawan </h1>
            <div class="tw-text-center">
                <div class="tw-inline-flex tw-rounded-full tw-bg-gray-100 tw-p-1 tw-space-x-2 tw-text-center">
                    @foreach (['harian'=>'Harian','bulanan'=>'Bulanan','tahunan'=>'Tahunan','custom'=>'Custom'] as $key
                    => $label)
                    <button wire:click.prevent="setRange('{{ $key }}')" class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium transition
               {{ $option_filter === $key 
                  ? 'tw-bg-white tw-text-gray-700 tw-font-semibold tw-shadow' 
                  : 'tw-text-gray-500 hover:tw-bg-white/70' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        @elseif ($agent->isMobile())
        <div class='section-header tw-w-full'>
            <h1>Laporan Kasbon Karyawan </h1>
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
        <div class="tw-text-center tw-mt-0 lg:-tw-mt-3">
            <div class="tw-inline-flex tw-rounded-full tw-bg-gray-200 tw-px-1 tw-py-1  
             tw-space-x-4 tw-justify-center tw-items-center">
                <button wire:click.prevent="setRange('harian')"
                    class="{{ $option_filter == "harian" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">
                    Harian
                </button>
                <button wire:click.prevent="setRange('bulanan')"
                    class="{{ $option_filter == "bulanan" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Bulanan</button>
                <button wire:click.prevent="setRange('tahunan')"
                    class="{{ $option_filter == "tahunan" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Tahunan</button>
                <button wire:click.prevent="setRange('custom')"
                    class="{{ $option_filter == "custom" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Custom</button>
            </div>
        </div>
        @endif

        <div class="section-body {{ $agent->isDesktop() ? '-tw-mt-[20px]' : 'tw-mt-[15px]' }}">
            <div class="card">
                <center>
                    @if ($agent->isDesktop())
                    <div class="card-body">
                        <div>
                            <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                                {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }}
                                <span class="tw-text-gray-400 tw-font-normal">s/d</span>
                                {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
                            <div class="tw-inline-flex tw-space-x-2 tw-items-center tw-mt-3">
                                <input wire:model.live="start_date" type="date" class="form-control">
                                <button wire:click.prevent="refreshToday()"
                                    class="btn btn-primary tw-rounded-full tw-w-1/2"><i
                                        class="fas fa-sync"></i></button>
                                <input wire:model.live="end_date" type="date" class="form-control">
                            </div>
                        </div>
                        <button wire:click.prevent="exportPDF()" class="btn btn-danger tw-whitespace-nowrap tw-mt-4"><i
                                class="fas fa-file-pdf"></i>
                            Export
                            PDF</button>
                    </div>
                    @elseif ($agent->isMobile())
                    <div class="card-body">
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                            {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }}
                            <span class="tw-text-gray-400 tw-font-normal">s/d</span>
                            {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
                        <div class="tw-grid tw-grid-cols-3 tw-items-center tw-mt-3">
                            <div>
                                <input wire:model.live="start_date" type="date" class="form-control">
                            </div>
                            <div>
                                <button wire:click.prevent="refreshToday()"
                                    class="btn btn-primary tw-rounded-full tw-w-1/2"><i
                                        class="fas fa-sync"></i></button>
                            </div>
                            <div>
                                <input wire:model.live="end_date" type="date" class="form-control">
                            </div>
                        </div>
                        <button class="btn btn-danger tw-mt-4 tw-whitespace-nowrap"><i class="fas fa-file-pdf"></i>
                            Export
                            PDF</button>
                    </div>
                    @endif
                </center>
            </div>
            <div class="card -tw-mt-[20px]">
                <h3>Table Kasbon Karyawan</h3>
                {{-- <div class="tw-flex tw-ml-6 tw-mt-6 tw-mb-5 lg:tw-mb-1">
                    <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">Table Omset Pesanan
                    </h3>
                    <div class="ml-auto tw-mr-4">
                        <button wire:click="exportExcel" class="btn btn-primary">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div> --}}
                <div class="card-body">
                    <div class="show-entries">
                        <p class="show-entries-show">Show</p>
                        <select wire:model.live="lengthData" id="length-data">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                            <option value="500">500</option>
                        </select>
                        <p class="show-entries-entries">Entries</p>
                    </div>
                    <div class="search-column">
                        <p>Search: </p><input type="search" wire:model.live.debounce.750ms="searchTerm" id="search-data"
                            placeholder="Search here..." class="form-control" value="">
                    </div>
                    <div class="table-responsive tw-max-h-96">
                        @php
                        use Carbon\Carbon;
                        $no = ($data->currentPage() - 1) * $data->perPage() + 1;

                        // saldo berjalan
                        $saldo = 0;

                        // group by tanggal
                        $groups = $data->getCollection()->groupBy(function ($item) {
                        return Carbon::parse($item->tgl_disetujui)->toDateString();
                        });
                        @endphp

                        <table class="tw-w-full tw-table-auto">
                            <thead class="tw-sticky tw-top-0">
                                <tr class="tw-text-gray-700">
                                    <th class="text-center">No</th>
                                    <th>No Referensi</th>
                                    <th>Waktu</th>
                                    <th>Keterangan</th>
                                    <th>Pengajuan</th>
                                    <th>Pelunasan</th>
                                    <th>Sisa Kasbon</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groups as $tgl => $rows)
                                {{-- Header tanggal --}}
                                <tr>
                                    <td colspan="7" class="tw-font-semibold tw-text-gray-700">
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
                                    <td>{{ $row->no_referensi }}</td>
                                    <td>{{ Carbon::parse($row->tgl_disetujui)->format('H:i') }}</td>
                                    <td>{{ $row->keterangan }}</td>
                                    <td class="text-right">@money($pengajuan)</td>
                                    <td class="text-right">@money($pelunasan)</td>
                                    <td class="text-right">@money($saldo)</td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5 px-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
