<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Kas Keluar </h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Kas Keluar</h3>
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
                            placeholder='no.reference, nama pembuat' class='form-control'>
                    </div>
                    <div class='table-responsive tw-max-h-96 no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>No. Reference</th>
                                    <th class='tw-whitespace-nowrap'>Tanggal</th>
                                    <th class='tw-whitespace-nowrap'>Keterangan</th>
                                    <th class='tw-whitespace-nowrap'>Kategori Pengeluaran</th>
                                    <th class='tw-whitespace-nowrap'>Jumlah</th>
                                    <th class='tw-whitespace-nowrap'>Dibuat Oleh</th>
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->no_referensi }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>
                                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->keterangan }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->nama_kategori }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>@money($row->jumlah)</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->nama_pembuat }}</td>
                                    <td class='tw-whitespace-nowrap'>
                                        <button wire:click.prevent='edit({{ $row->id }})' class='btn btn-primary'
                                            data-toggle='modal' data-target='#formDataModal'>
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
                                    <td colspan='9' class='text-center'>No data available in the table</td>
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
                            <label for='id_kategori_keuangan'>Kategori Pengeluaran</label>
                            <select wire:model='id_kategori_keuangan' id='id_kategori_keuangan'
                                class='form-control select2'>
                                <option value="">-- Opsi Pilihan --</option>
                                @foreach ($kategori_keuangans as $kategori_keuangan)
                                <option value='{{ $kategori_keuangan->id }}'>{{ $kategori_keuangan->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_kategori_keuangan') <span class='text-danger'>Kategori Pengeluaran wajib
                                dipilih</span>
                            @enderror
                        </div>
                        <div class='form-group'>
                            <label for='tanggal'>Tanggal</label>
                            <input type='datetime-local' wire:model='tanggal' id='tanggal' class='form-control'>
                            @error('tanggal') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='jumlah'>Jumlah</label>
                            <input type='number' wire:model='jumlah' id='jumlah' class='form-control'>
                            @error('jumlah') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='keterangan'>Keterangan</label>
                            <textarea wire:model='keterangan' id='keterangan' class='form-control'
                                style='height: 100px !important;'></textarea>
                            @error('keterangan') <span class='text-danger'>Keterangan wajib diisi</span> @enderror
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
