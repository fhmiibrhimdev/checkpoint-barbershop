<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Cash On Bank </h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Cash On Bank</h3>
                {{-- <div class="tw-flex ml-auto mr-3">
                    <button class="btn btn-primary" wire:click.prevent='refreshData()' wire:loading.attr="disabled">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div> --}}
                <div class='card-body'>
                    <div class='show-entries'>
                        <p class='show-entries-show'>Show</p>
                        <select wire:model.live='lengthData' id='length-data'>
                            <option value='25'>25</option>
                            <option value='50'>50</option>
                            <option value='100'>100</option>
                            <option value='250'>250</option>
                            <option value='500'>500</option>
                        </select>
                        <p class='show-entries-entries'>Entries</p>
                    </div>
                    <div class='search-column'>
                        <p>Search: </p><input type='search' wire:model.live.debounce.750ms='searchTerm' id='search-data'
                            placeholder='Search here...' class='form-control'>
                    </div>
                    <div class='table-responsive no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>No. Reference</th>
                                    {{-- <th class='tw-whitespace-nowrap'>Tanggal</th> --}}
                                    {{-- <th class='tw-whitespace-nowrap'>Sumber</th> --}}
                                    <th class='tw-whitespace-nowrap'>Keterangan</th>
                                    <th class='tw-whitespace-nowrap'>Pemasukan</th>
                                    <th class='tw-whitespace-nowrap'>Pengeluaran</th>
                                    <th class='tw-whitespace-nowrap'>Saldo Akhir</th>
                                    {{-- <th class='text-center'><i class='fas fa-cog'></i></th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                // Grouping per tanggal, format d-m-Y
                                $grouped = $data->groupBy(function($item) {
                                return \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y');
                                });
                                @endphp
                                @forelse ($grouped as $result)
                                <tr>
                                    <td class="tw-text-sm tw-tracking-wider" colspan="10">
                                        <b>Tanggal:
                                            {{ \Carbon\Carbon::parse($result[0]->tanggal)->format('d-m-Y') }}</b>
                                    </td>
                                </tr>
                                @foreach ($result as $row)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>{{ $loop->iteration }}</td>
                                    <td class='tw-whitespace-nowrap text-primary text-left'>{{ $row->no_referensi }}
                                    </td>
                                    {{-- <td class=' tw-whitespace-nowrap text-left'>
                                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td> --}}
                                    <td class=' tw-whitespace-nowrap lg:tw-whitespace-normal text-left'>
                                        <p class="tw-leading-5">{{ $row->keterangan }}</p>
                                        <p class="tw-text-gray-500 tw-text-sm">Sumber:
                                            <span
                                                class="{{ $row->jenis == "In" ? "tw-text-green-600" : "tw-text-red-600" }}">{{ $row->sumber_tabel }}</span>
                                        </p>
                                    </td>
                                    <td class=" tw-whitespace-nowrap text-right">@money($row->pemasukan)</td>
                                    <td class=" tw-whitespace-nowrap text-right">@money(-$row->pengeluaran)</td>
                                    <td
                                        class=" tw-whitespace-nowrap text-right @if($loop->first && $loop->parent->first) tw-font-extrabold tw-text-base @endif">
                                        @money($row->saldo_akhir)</td>
                                    {{-- <td class='tw-whitespace-nowrap'>
                                        <button wire:click.prevent='edit({{ $row->id }})' class='btn btn-primary'
                                    data-toggle='modal' data-target='#formDataModal'>
                                    <i class='fas fa-edit'></i>
                                    </button>
                                    <button wire:click.prevent='deleteConfirm({{ $row->id }})' class='btn btn-danger'>
                                        <i class='fas fa-trash'></i>
                                    </button>
                                    </td> --}}
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan='8' class='text-center'>No data available in the table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class='mt-5 px-3'>
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('general-css')
<link href="{{ asset('assets/midragon/select2/select2.min.css') }}" rel="stylesheet" />
@endpush

@push('js-libraries')
<script src="{{ asset('/assets/midragon/select2/select2.full.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    window.addEventListener('initSelect2', event => {
        $(document).ready(function () {
            $('.select2').select2();

            $('.select2').on('change', function (e) {
                var id = $(this).attr('id');
                var data = $(this).select2("val");
                @this.set(id, data);
            });
        });
    })

</script>
@endpush
