<div>
    <section class='section custom-section'>
        <div class='section-header'>
            <h1>Kategori Satuan</h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Kategori Satuan</h3>
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
                    <div class='tw-max-h-96 no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>Nama Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                <tr class='text-center'>
                                    <td class='tw-py-3 tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td>
                                    <td class='tw-py-3 tw-whitespace-nowrap text-left'>{{ $row->nama_satuan }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan='4' class='text-center'>No data available in the table</td>
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
                            <label for='nama_satuan'>Nama Satuan</label>
                            <input type='text' wire:model='nama_satuan' id='nama_satuan' class='form-control'>
                            @error('nama_satuan') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        {{-- <div class='form-group'>
                            <label for='deskripsi'>Deskripsi</label>
                            <textarea wire:model='deskripsi' id='deskripsi' class='form-control'
                                style='height: 100px !important;'></textarea>
                            @error('deskripsi') <span class='text-danger'>{{ $message }}</span> @enderror
                    </div> --}}
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
