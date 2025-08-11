<div>
    <section class='section custom-section'>
        <div class="section-header">
            <a href="{{ url('/keuangan/hutang') }}" class="btn btn-transparent" title="Kembali ke halaman transaksi">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Pelunasan Hutang</h1>
        </div>

        <div class='section-body'>
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-x-0 lg:tw-gap-x-6">
                <div class="tw-col-span-4">
                    <div class="card tw-px-6">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tanggal_beli">Tanggal Beli</label>
                                        <input type="text" class="form-control" id="tanggal_beli"
                                            value="{{ \Carbon\Carbon::parse($hutang['tanggal_beli'])->format('d M Y') }}"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="no_referensi">No. Referensi</label>
                                        <input type="text" class="form-control" id="no_referensi"
                                            value="{{ $hutang['no_referensi'] }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nama_supplier">Nama Supplier</label>
                                <input type="text" class="form-control" id="nama_supplier"
                                    value="{{ $hutang['nama_supplier'] }}" readonly>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="total_hutang">Total Hutang</label>
                                        <input type="text" class="form-control" id="total_hutang"
                                            value="@money($hutang['total_tagihan'])" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="total_dibayarkan">Sudah Dibayar</label>
                                        <input type="text" class="form-control" id="total_dibayarkan"
                                            value="@money($hutang['total_dibayarkan'])" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sisa_hutang">Sisa Hutang</label>
                                <input type="text" class="form-control" id="sisa_hutang"
                                    value="@money($hutang['sisa_hutang'])" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tw-col-span-8">
                    <div class='card'>
                        <h3>Status:
                            @if ($hutang['status'] == "Sudah Lunas")
                            <span
                                class="tw-bg-green-100 tw-text-green-600 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm">Lunas</span>
                            @elseif ($hutang['status'] == "Belum Lunas")
                            <span class="tw-bg-red-100 tw-text-red-600 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm">Belum
                                Lunas</span>
                            @endif
                        </h3>
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
                                <p>Search: </p><input type='search' wire:model.live.debounce.750ms='searchTerm'
                                    id='search-data' placeholder='Search here...' class='form-control'>
                            </div>
                            <div class='table-responsive tw-max-h-96 no-scrollbar'>
                                <table class='tw-w-full tw-table-auto'>
                                    <thead class='tw-sticky tw-top-0'>
                                        <tr class='tw-text-gray-700'>
                                            <th width='6%' class='text-center'>No</th>
                                            <th class='tw-whitespace-nowrap'>Tanggal Bayar</th>
                                            <th class='tw-whitespace-nowrap'>Jumlah</th>
                                            <th class='tw-whitespace-nowrap'>Keterangan</th>
                                            <th class='tw-whitespace-nowrap'>Metode Pembayaran</th>
                                            <th class='text-center'><i class='fas fa-cog'></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data as $row)
                                        <tr class='text-center'>
                                            <td class='tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td>
                                            <td class='tw-whitespace-nowrap text-left'>
                                                {{ \Carbon\Carbon::parse($row->tanggal_bayar)->format('d M Y') }}</td>
                                            <td class='tw-whitespace-nowrap text-left'>@money($row->jumlah_bayar)</td>
                                            <td class='tw-whitespace-nowrap text-left'>{{ $row->keterangan }}</td>
                                            <td class='tw-whitespace-nowrap text-left'>{{ $row->metode_pembayaran }}
                                            </td>
                                            <td class='tw-whitespace-nowrap'>
                                                <button wire:click.prevent='edit({{ $row->id }})'
                                                    class='btn btn-primary' data-toggle='modal'
                                                    data-target='#formDataModal'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button wire:click.prevent='deleteConfirm({{ $row->id }})'
                                                    class='btn btn-danger'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>
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
                        </div>
                    </div>
                </div>
            </div>
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
                            <label for='tanggal_bayar'>Tanggal Bayar</label>
                            <input type='date' wire:model='tanggal_bayar' id='tanggal_bayar' class='form-control'>
                            @error('tanggal_bayar') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='jumlah_bayar'>Jumlah Bayar</label>
                            <input type='number' wire:model='jumlah_bayar' id='jumlah_bayar' class='form-control'>
                            @error('jumlah_bayar') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='keterangan'>Keterangan</label>
                            <textarea wire:model='keterangan' id='keterangan' class='form-control'
                                style="height: 100px"></textarea>
                            @error('keterangan') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="id_metode_pembayaran">Metode Pembayaran</label>
                            <select wire:model="id_metode_pembayaran" id="id_metode_pembayaran" class="form-control">
                                <option value="">-- Opsi Pilihan --</option>
                                <option value="1">Tunai</option>
                                <option value="2">Transfer</option>
                            </select>
                            @error('id_metode_pembayaran') <span class='text-danger'>Metode Pembayaran wajib
                                diisi</span> @enderror
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
