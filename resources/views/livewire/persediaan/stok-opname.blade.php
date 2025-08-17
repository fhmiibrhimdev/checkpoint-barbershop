<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Stok Opname </h1>
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
                <h3>Tabel Stok Opname</h3>
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
                    <div class='table-responsive no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>Tanggal</th>
                                    <th class='tw-whitespace-nowrap'>Nama Produk</th>
                                    <th class='tw-whitespace-nowrap'>Buku</th>
                                    <th class='tw-whitespace-nowrap'>Fisik</th>
                                    <th class='tw-whitespace-nowrap'>Selisih</th>
                                    <th class='tw-whitespace-nowrap'>Keterangan</th>
                                    <th class='tw-whitespace-nowrap'>User Created</th>
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $result)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result->tanggal }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result->nama_item }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>@stock($result->buku)</td>
                                    <td class='tw-whitespace-nowrap text-left'>@stock($result->fisik)</td>
                                    <td class='tw-whitespace-nowrap text-left'>@stock($result->selisih)</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result->keterangan }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result->name }}</td>
                                    <td class='tw-whitespace-nowrap'>
                                        <button wire:click.prevent='deleteConfirm({{ $result->id }})'
                                            class='btn btn-danger'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>
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
                        <p class="tw-text-xs tw-text-[#34395e] tw-font-semibold tw-tracking-[0.5px]">
                            {{ \Carbon\Carbon::parse($result->tanggal)->format('d M Y') }}
                        </p>
                        <p class="tw-text-xs tw-text-[#34395e] tw-font-semibold tw-tracking-[0.5px]">
                            {{ $result->name }}
                        </p>
                    </div>
                    <hr class="tw-my-3">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div class="tw-flex tw-items-center">
                            <img src="{{ asset('assets/stisla/img/example-image-50.jpg') }}"
                                class="tw-rounded-lg tw-w-12 tw-h-12 tw-object-cover tw-mr-3">
                            <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs">
                                <p class="tw-leading-5 text-primary">{{ $result->nama_item }}</p>
                                <p class="tw-leading-5 tw-text-gray-500">{{ $result->keterangan }}</p>
                            </div>
                        </div>
                        <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs tw-border-l tw-border-gray-200">
                            <div class="tw-ml-3 tw-mr-1 tw-flex tw-space-x-1">
                                <button wire:click.prevent='edit({{ $result->id }})' class='btn btn-primary'
                                    data-toggle='modal' data-target='#formDataModal'>
                                    <i class='fas fa-edit'></i>
                                </button>
                                <button wire:click.prevent='deleteConfirm({{ $result->id }})' class='btn btn-danger'>
                                    <i class='fas fa-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr class="tw-mt-3 tw-mb-1.5">
                    <div class="tw-text-xs tw-flex tw-justify-between tw-items-center">
                        <p class="tw-font-semibold">Buku: <span
                                class="tw-text-gray-500 tw-font-normal">@stock($result->buku)</span></p>
                        <p class="tw-font-semibold">Fisik: <span
                                class="tw-text-gray-500 tw-font-normal">@stock($result->fisik)</span>
                        </p>
                        <p class="tw-font-semibold">Selisih <span
                                class="tw-text-gray-500 tw-font-normal">@stock($result->selisih)</span></p>

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

    <div class='modal fade' data-backdrop="static" wire:ignore.self id='formDataModal'
        aria-labelledby='formDataModalLabel' aria-hidden='true'>
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class='modal-header tw-px-4 lg:tw-px-6'>
                    <h5 class='modal-title' id='formDataModalLabel'>{{ $isEditing ? 'Edit Data' : 'Add Data' }}</h5>
                    <button type='button' wire:click='cancel()' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <form>
                    <div class='modal-body tw-px-4 lg:tw-px-6'>
                        <div class='form-group'>
                            <label for='id_produk'>Nama Produk</label>
                            <select wire:model='id_produk' id='id_produk' class='form-control select2'>
                                @foreach ($produks as $detailProduk)
                                <option value="{{ $detailProduk->id }}">{{ $detailProduk->nama_item }}</option>
                                @endforeach
                            </select>
                            @error('id_produk') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='tanggal'>Tanggal</label>
                            <input type='datetime-local' wire:model='tanggal' id='tanggal' class='form-control'>
                            @error('tanggal') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class='form-group'>
                                    <label for='buku'>Buku</label>
                                    <input type='number' wire:model.lazy='buku' id='buku' class='form-control' readonly>
                                    @error('buku') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class='form-group'>
                                    <label for='fisik'>Fisik</label>
                                    <input type='number' wire:model.lazy='fisik' id='fisik' class='form-control'>
                                    @error('fisik') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class='form-group'>
                                    <label for='selisih'>Selisih</label>
                                    <input type='number' value="{{ (int)$this->fisik - (int)$this->buku }}"
                                        name="selisih" id='selisih' class='form-control' readonly>
                                    @error('selisih') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='keterangan'>Keterangan</label>
                            <textarea wire:model='keterangan' id='keterangan' class='form-control'
                                style='height: 100px !important;'></textarea>
                            @error('keterangan') <span class='text-danger'>{{ $message }}</span> @enderror
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
