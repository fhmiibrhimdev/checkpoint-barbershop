<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Daftar Karyawan </h1>
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
                <h3>Tabel Daftar Karyawan</h3>
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
                                    <th class='tw-whitespace-nowrap'>Role</th>
                                    <th class='tw-whitespace-nowrap'>Nama Lengkap</th>
                                    <th class='tw-whitespace-nowrap'>Email</th>
                                    <th class='tw-whitespace-nowrap'>No. Telp</th>
                                    <th class='tw-whitespace-nowrap'>Sisa Kasbon</th>
                                    {{-- <th class='tw-whitespace-nowrap'>Deskripsi</th> --}}
                                    <th width="10%" class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $result)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result['role_id'] }}</td>
                                    <td class='tw-whitespace-nowrap text-left tw-flex tw-items-center'>
                                        <img src="{{ asset('assets/stisla/img/avatar/avatar-1.png') }}"
                                            class="tw-rounded-full tw-w-8 tw-h-8 tw-mr-3">
                                        {{ $result['name'] }}
                                    </td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result['email'] }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $result['no_telp'] }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>@money($result['saldo_kasbon'])</td>
                                    {{-- <td class='tw-whitespace-nowrap text-left'>{{ $result['deskripsi'] }}</td> --}}
                                    <td class='tw-whitespace-nowrap'>
                                        <button wire:click.prevent='edit({{ $result['id'] }})' class='btn btn-primary'
                                            data-toggle='modal' data-target='#formDataModal'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button wire:click.prevent='deleteConfirm({{ $result['id'] }})'
                                            class='btn btn-danger'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan='7' class='text-center'>No data available in the table</td>
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
                @forelse ($data as $result)
                <div
                    class="tw-bg-white tw-rounded-lg tw-shadow-md tw-shadow-gray-300 tw-h-full tw-px-3 tw-py-3 tw-mt-3 tw-text-[#34395e]">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div class="tw-flex tw-items-center">
                            <img src="{{ asset('assets/stisla/img/avatar/avatar-1.png') }}"
                                class="tw-rounded-full tw-w-12 tw-h-12 tw-object-cover tw-mr-3" alt="user-profile.png">
                            <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs">
                                <p class="tw-leading-5 tw-font-semibold tw-text-sm">{{ $result->name }}</p>
                                <p class="tw-leading-5">{{ $result->email }}</p>
                                <p class="tw-leading-5">{{ $result->no_telp }}</p>
                            </div>
                        </div>
                        <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs tw-border-l tw-border-gray-200">
                            <div class="tw-ml-3 tw-mr-1">
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
                    <div class="tw-text-xs tw-flex tw-justify-between">
                        <p class="tw-font-semibold">Sisa Kasbon: <span
                                class="tw-text-gray-500 tw-font-normal">@money($result->saldo_kasbon)</span>
                        </p>
                        <p class="tw-font-semibold">ROLE: <span
                                class="tw-text-gray-500 tw-font-normal">{{ $result->role_id }}</span></p>

                    </div>
                </div>
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
                        <div class="row">
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='name'>Nama Lengkap</label>
                                    <input type='text' wire:model='name' id='name' class='form-control'>
                                    @error('name') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='email'>Email</label>
                                    <input type='email' wire:model='email' id='email' class='form-control'>
                                    @error('email') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='tgl_lahir'>Tanggal Lahir</label>
                                    <input type='date' wire:model='tgl_lahir' id='tgl_lahir' class='form-control'>
                                    @error('tgl_lahir') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='no_telp'>No. Telepon</label>
                                    <input type='number' wire:model='no_telp' id='no_telp' class='form-control'>
                                    @error('no_telp') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="role_id">Role User</label>
                                    <div wire:ignore>
                                        <select id="role_id" wire:model="role_id" class="form-control select2">
                                            <option value="direktur">Direktur</option>
                                            <option value="admin">Admin</option>
                                            <option value="kasir">Kasir</option>
                                            <option value="capster">Capster</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='jk'>Jenis Kelamin</label>
                                    <div wire:ignore>
                                        <select wire:model='jk' id='jk' class='form-control select2'>
                                            <option value='-'>-- Opsi Pilihan --</option>
                                            <option value='Pria'>Pria</option>
                                            <option value='Wanita'>Wanita</option>
                                        </select>
                                    </div>
                                    @error('jk') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='alamat'>Alamat</label>
                                    <textarea wire:model='alamat' id='alamat' class='form-control'
                                        style='height: 100px !important;'></textarea>
                                    @error('alamat') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='deskripsi'>Deskripsi</label>
                                    <textarea wire:model='deskripsi' id='deskripsi' class='form-control'
                                        style='height: 100px !important;'></textarea>
                                    @error('deskripsi') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class='form-group'>
                            <label for='gambar'>Gambar</label>
                            <input type='file' wire:model='gambar' id='gambar' class='form-control'>
                            @error('gambar') <span class='text-danger'>{{ $message }}</span> @enderror
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
            // console.log('initselect2')
            $('.select2').select2({
                dropdownParent: $('#formDataModal')
            });

            $('.select2').on('change', function (e) {
                var id = $(this).attr('id');
                var data = $(this).select2("val");
                @this.set(id, data);
            });
        });
    })

</script>
@endpush
