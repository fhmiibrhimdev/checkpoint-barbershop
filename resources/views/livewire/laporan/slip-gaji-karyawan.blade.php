<div>
    <section class="section custom-section">
        <div class='section-header tw-w-full'>
            <h1>Slip Gaji Karyawan </h1>
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

        <div class="section-body {{ $agent->isDesktop() ? '-tw-mt-[20px]' : 'tw-mt-[15px]' }}">
            <div class="card -tw-mt-[20px]">
                <h3>Table Slip Gaji Karyawan</h3>
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
                        <table class='tw-w-full tw-table-auto'>
                            <thead class="tw-sticky tw-top-0">
                                <tr class="tw-text-gray-700">
                                    <th width="6%" class="text-center">No</th>
                                    <th class="tw-whitespace-nowrap">No Reference</th>
                                    <th class="tw-whitespace-nowrap">Periode</th>
                                    <th class="tw-whitespace-nowrap">Total Tunjangan</th>
                                    <th class="tw-whitespace-nowrap">Total Potongan</th>
                                    <th class="tw-whitespace-nowrap">Total Gaji</th>
                                    <th class="text-center"><i class="fas fa-cogs"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                <tr>
                                    <td class="tw-text-center">{{ $loop->index + 1 }}</td>
                                    <td>{{ $row->no_referensi }}</td>
                                    <td>{{ $row->periode_mulai }} s/d {{ $row->periode_selesai }}</td>
                                    <td>@money($row->total_tunjangan)</td>
                                    <td>@money($row->total_potongan)</td>
                                    <td>@money($row->total_gaji)</td>
                                    <td>
                                        <button
                                            wire:click.prevent="exportPDF('{{ $row->no_referensi }}', '{{ $row->periode_mulai }}', '{{ $row->periode_selesai }}')"
                                            class="btn btn-danger" title="Export PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="tw-text-center">
                                    <td colspan="10">No data available in the table</td>
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
