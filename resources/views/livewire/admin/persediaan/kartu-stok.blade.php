<div>
    <section class='section custom-section'>
        <div class='section-header'>
            <h1>Kartu Stok </h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Kartu Stok</h3>
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
                    <div class='table-responsive tw-max-h-full no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width="10%" rowspan="2" class='tw-whitespace-nowrap text-center'>Tanggal</th>
                                    <th rowspan="2" class='tw-whitespace-nowrap'>Keterangan</th>
                                    <th rowspan="2" class='tw-text-center tw-whitespace-nowrap'>Stok Awal</th>
                                    <th colspan="2" class='tw-text-center tw-whitespace-nowrap'>Mutasi</th>
                                    <th rowspan="2" class='tw-text-center tw-whitespace-nowrap'>Stok Akhir</th>
                                </tr>
                                <tr class='tw-text-gray-700'>
                                    <th class='tw-whitespace-nowrap tw-text-center'>In</th>
                                    <th class='tw-whitespace-nowrap tw-text-center'>Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $amountIn = 0;
                                $amountOut = 0;
                                $amountBalance = 0;
                                $amountBalanceLast = 0;
                                @endphp
                                @forelse ($data->groupBy('nama_item') as $result)
                                <tr>
                                    <td class="tw-text-sm tw-tracking-wider" colspan="10">
                                        <b>Produk: <span class="text-primary">{{ $result[0]->nama_item }}</span></b>
                                    </td>
                                </tr>
                                @foreach ($result as $results)
                                <tr class='text-center'>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-left tw-pl-10'>{{ $results->tanggal }}
                                    </td>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-left'>{{ $results->keterangan }}</td>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-center'>
                                        @if ($results->status == 'Balance')
                                        @php
                                        $amountBalance += $results->qty;
                                        @endphp
                                        @stock($results->qty)
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-center'>
                                        @if ($results->status == 'In')
                                        @php
                                        $amountIn += $results->qty;
                                        @endphp
                                        @stock($results->qty)
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-center'>
                                        @if ($results->status == 'Out')
                                        @php
                                        $amountOut += $results->qty;
                                        @endphp
                                        @stock($results->qty)
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class='tw-p-3 tw-whitespace-nowrap text-center'>
                                        @php
                                        $amountBalanceLast = last((array)$results->balancing);
                                        @endphp
                                        @stock($results->balancing)
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan='13' class='text-center'>No data available in the table</td>
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
