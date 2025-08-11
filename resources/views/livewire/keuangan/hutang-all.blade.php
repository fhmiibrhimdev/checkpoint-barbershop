<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Hutang </h1>
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

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Hutang</h3>
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
                    @if (!$agent->isMobile())
                    <div class='table-responsive tw-max-h-96 no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>Tanggal Beli</th>
                                    <th class='tw-whitespace-nowrap'>No. Reference</th>
                                    <th class='tw-whitespace-nowrap'>Nama Supplier</th>
                                    <th class='tw-whitespace-nowrap'>Total Hutang</th>
                                    <th class='tw-whitespace-nowrap'>Sudah Dibayar</th>
                                    <th class='tw-whitespace-nowrap'>Sisa Hutang</th>
                                    {{-- <th class='tw-whitespace-nowrap'>Status</th> --}}
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data->groupBy('nama_cabang') as $result)
                                <tr>
                                    <td class="tw-text-sm tw-tracking-wider" colspan="10">
                                        <b>Lokasi: {{ $result[0]->nama_cabang }}</b>
                                    </td>
                                </tr>
                                @foreach ($result as $row)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>
                                        {{ \Carbon\Carbon::parse($row->tanggal_beli)->format('d M Y') }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->no_referensi }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->nama_supplier }}</td>
                                    <td class='tw-whitespace-nowrap text-right'>@money($row->total_tagihan)</td>
                                    <td class='tw-whitespace-nowrap text-right'>@money($row->total_dibayarkan)</td>
                                    <td class='tw-whitespace-nowrap text-right'>@money($row->sisa_hutang)</td>
                                    {{-- <td class='tw-whitespace-nowrap text-left'>
                                        @if ($row->status == "Sudah Lunas")
                                        <span
                                            class="tw-bg-green-100 tw-text-green-600 tw-px-2 tw-py-1 tw-rounded-full tw-font-semibold tw-tracking-[0.5px] tw-text-xs">Lunas</span>
                                        @elseif ($row->status == "Belum Lunas")
                                        <span
                                            class="tw-bg-red-100 tw-text-red-600 tw-px-2 tw-py-1 tw-rounded-full tw-font-semibold tw-tracking-[0.5px] tw-text-xs">Belum
                                            Lunas</span>
                                        @endif
                                    </td> --}}
                                    <td class='tw-whitespace-nowrap'>
                                        <a href="{{ url('/keuangan/hutang/' . \Crypt::encrypt($row->id)) }}"
                                            class="btn btn-primary" title="Klik untuk membayar hutang">
                                            <i class="fas fa-money-bill-transfer"></i>
                                        </a>
                                        <button wire:click.prevent='edit({{ $row->id }})' class='btn btn-warning'
                                            data-toggle='modal' data-target='#formDataModal'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button wire:click.prevent='deleteConfirm({{ $row->id }})'
                                            class='btn btn-danger'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan='10' class='text-center'>No data available in the table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class='mt-5 px-3'>
                        {{ $data->links() }}
                    </div>
                    @endif
                </div>
            </div>
            @if ($agent->isMobile())
            <div class="tw-px-3 -tw-mt-1">
                @forelse ($data->groupBy('nama_cabang') as $row)
                <div
                    class="tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px] tw-text-base tw-mt-6 tw-mb-4 lg:tw-px-0">
                    <p>{{ $row[0]->nama_cabang }}</p>
                </div>
                @foreach ($row as $result)
                <div
                    class="tw-bg-white tw-rounded-lg tw-shadow-md tw-shadow-gray-300 tw-h-full tw-px-3 tw-py-3 tw-mt-3 tw-text-[#34395e]">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mt-1">
                        <p class="tw-text-sm tw-text-[#34395e] tw-font-semibold tw-tracking-[0.5px]">
                            {{ \Carbon\Carbon::parse($result->tanggal_beli)->format('d M Y') }}
                        </p>
                        <div>
                            <button wire:click.prevent='edit({{ $result->id }})'
                                class='btn btn-sm tw-px-3 tw-py-1 btn-warning' data-toggle='modal'
                                data-target='#formDataModal'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button wire:click.prevent='deleteConfirm({{ $result->id }})'
                                class='btn btn-sm tw-px-3 tw-py-1 btn-danger'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                    </div>
                    <hr class="tw-my-3">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div class="tw-flex tw-items-center">
                            <img src="{{ asset('assets/stisla/img/example-image-50.jpg') }}"
                                class="tw-rounded-lg tw-w-12 tw-h-12 tw-object-cover tw-mr-3">
                            <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs">
                                <p class="tw-leading-5 tw-text-xs"><b>No. Referensi:</b> {{ $result->no_referensi }}
                                    dari <span class="tw-leading-5 tw-text-xs text-primary"><i
                                            class="fas fa-arrow-right"></i>
                                        {{ $result->nama_supplier }}</span></p>

                            </div>
                        </div>
                        <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs tw-border-l tw-border-gray-200">
                            <div class="tw-ml-3 tw-mr-1 tw-flex tw-space-x-1">
                                <a href="{{ url('/keuangan/hutang/' . \Crypt::encrypt($result->id)) }}"
                                    class="btn btn-primary" title="Klik untuk membayar hutang">
                                    <i class="fas fa-money-bill-transfer"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr class="tw-mt-3 tw-mb-1.5">
                    <div class="tw-text-xs tw-flex tw-text-center tw-whitespace-nowrap tw-justify-between">
                        <p class="tw-font-semibold">Total Hutang</p>
                        <p class="tw-font-semibold">Sudah Dibayar</p>
                        <p class="tw-font-semibold">Sisa Piutang</p>
                    </div>
                    <div class="tw-text-sm tw-flex tw-justify-between tw-text-center tw-whitespace-nowrap tw-gap-x-4">
                        <p class="tw-font-normal tw-text-sm tw-text-yellow-600">@money($result->total_tagihan)</p>
                        <p class="tw-font-normal tw-text-sm tw-text-green-600">@money($result->total_dibayarkan)</p>
                        <p class="tw-font-normal tw-text-sm tw-text-red-600">@money($result->sisa_hutang)</p>
                    </div>
                </div>
                @endforeach
                @empty
                No data available
                @endforelse

            </div>

            <div class="card tw-mt-6">
                <div class="card-body tw-py-0 tw-mb-6 tw-px-4 tw-items-center">
                    {{ $data->links() }}
                </div>
            </div>
            @endif
        </div>
        <button wire:click.prevent='isEditingMode(false)' class='btn-modal' data-toggle='modal' data-backdrop='static'
            data-keyboard='false' data-target='#formDataModal'>
            <i class='far fa-plus'></i>
        </button>
    </section>

    <div class='modal fade' wire:ignore.self id='formDataModal' aria-labelledby='formDataModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='formDataModalLabel'>{{ $isEditing ? 'Edit Data' : 'Add Data' }}</h5>
                    <button type='button' wire:click='cancel()' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <form>
                    <div class='modal-body'>
                        <div class='form-group'>
                            <label for='tanggal_beli'>Tanggal Beli</label>
                            <input type='date' wire:model='tanggal_beli' id='tanggal_beli' class='form-control'>
                            @error('tanggal_beli') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='id_supplier'>Nama Supplier</label>
                            <select wire:model='id_supplier' id='id_supplier' class='form-control select2'>
                                <option value=''>-- Opsi Pilihan --</option>
                                @foreach($suppliers as $supplier)
                                <option value='{{ $supplier->id }}'>{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                            @error('id_supplier') <small class='text-danger'>Nama Supplier wajib diisi</small> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='total_tagihan'>Jumlah Hutang</label>
                            <input type='number' wire:model='total_tagihan' id='total_tagihan' class='form-control'>
                            @error('total_tagihan') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' wire:click='cancel()' class='btn btn-secondary tw-bg-gray-300'
                            data-dismiss='modal'>Close</button>
                        <button type='submit' wire:click.prevent='{{ $isEditing ? 'update()' : 'store()' }}'
                            wire:loading.attr='disabled' class='btn btn-primary tw-bg-blue-500'>Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
