<div>
    <section class='section custom-section'>
        <div class='section-header'>
            <h1>Cabang Lokasi</h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Cabang Lokasi</h3>
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
                    <div class='table-responsive tw-max-h-96 no-scrollbar'>
                        <table class='tw-w-full tw-table-auto '>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>Nama Cabang</th>
                                    <th class='tw-whitespace-nowrap' width="90%">Alamat</th>
                                    <th class='tw-whitespace-nowrap'>Status</th>
                                    <th class='tw-whitespace-nowrap'>No Telepon</th>
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->nama_cabang }}</td>
                                    <td class='text-left'>{{ $row->alamat }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->status }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->no_telp }}</td>
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
                                    <td colspan='6' class='text-center'>No data available in the table</td>
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

    <div class='modal fade' data-backdrop="static" wire:ignore.self id='formDataModal'
        aria-labelledby='formDataModalLabel' aria-hidden='true'>
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
                            <label for='nama_cabang'>Nama Cabang</label>
                            <input type='text' wire:model='nama_cabang' id='nama_cabang' class='form-control'>
                            @error('nama_cabang') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        @if ($isEditing)
                        <div class='form-group'>
                            <label for='subtitle_cabang'>Subtitle Cabang</label>
                            <input type='text' wire:model='subtitle_cabang' id='subtitle_cabang' class='form-control'>
                            @error('subtitle_cabang') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class='form-group'>
                            <label for='alamat'>Alamat</label>
                            <textarea wire:model='alamat' id='alamat' class='form-control'
                                style='height: 100px !important;'></textarea>
                            @error('alamat') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        @if ($isEditing)
                        <div class='form-group'>
                            <label for='email'>Email</label>
                            <input type='text' wire:model='email' id='email' class='form-control'>
                            @error('email') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='syarat_nota_1'>Syarat Nota 1</label>
                            <textarea wire:model='syarat_nota_1' id='syarat_nota_1' class='form-control'
                                style='height: 100px !important;'></textarea>
                            @error('syarat_nota_1') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='template_pesan_pembayaran'>Template Pesan Pembayaran</label>
                            <textarea wire:model='template_pesan_pembayaran' id='template_pesan_pembayaran'
                                class='form-control' style='height: 100px !important;'></textarea>
                            @error('template_pesan_pembayaran') <span class='text-danger'>{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                        <div class='form-group'>
                            <label for='status'>Status</label>
                            <select wire:model='status' id='status' class='form-control'>
                                <option value="">-- Opsi Pilihan --</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                            @error('status') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='no_telp'>No Telepon</label>
                            <input type='text' wire:model='no_telp' id='no_telp' class='form-control'>
                            @error('no_telp') <span class='text-danger'>{{ $message }}</span> @enderror
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

@endpush

@push('js-libraries')

@endpush

@push('scripts')

@endpush
