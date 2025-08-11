<div>
    <section class='section custom-section'>
        <div class='section-header tw-flex tw-w-full'>
            <h1>Slip Gaji </h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Slip Gaji</h3>
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
                                    <th class='tw-whitespace-nowrap'>Periode</th>
                                    <th class='tw-whitespace-nowrap'>No. Reference</th>
                                    <th class='tw-whitespace-nowrap'>Nama Karyawan</th>
                                    <th class='tw-whitespace-nowrap'>Total Gaji</th>
                                    <th width="5%" class='tw-whitespace-nowrap'>Status</th>
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
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->periode_mulai }} <span
                                            class="tw-text-gray-600">s/d</span>
                                        {{ $row->periode_selesai }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->no_referensi }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>{{ $row->nama_karyawan }}</td>
                                    <td class='tw-whitespace-nowrap text-left'>@money($row->total_gaji)</td>
                                    <td class='tw-whitespace-nowrap text-left'>
                                        @if ($row->status == "final")
                                        <p
                                            class="tw-bg-green-100 tw-text-green-600 tw-tracking-[0.5px] tw-font-semibold tw-py-0.5 tw-px-3 tw-text-center tw-rounded-md">
                                            Final</p>
                                        @else
                                        <p
                                            class="tw-bg-red-100 tw-text-red-600 tw-tracking-[0.5px] tw-font-semibold tw-text-sm tw-py-1 tw-px-3 tw-text-center tw-rounded-md">
                                            Draft</p>
                                        @endif
                                    </td>
                                    <td class='tw-whitespace-nowrap'>
                                        <a target="_BLANK" href="#" class='btn btn-info'>
                                            <i class='fas fa-print'></i>
                                        </a>
                                        <button wire:click.prevent="edit({{ $row->id }})" class="btn btn-primary"
                                            data-toggle="modal" data-target="#formDataModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click.prevent='deleteConfirm({{ $row->id }})'
                                            class='btn btn-danger'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                {{-- Tambahkan ini untuk total gaji per cabang --}}
                                <tr>
                                    <td colspan="4" class="tw-font-bold text-right text-black">Total Gaji Keseluruhan:
                                    </td>
                                    <td class="tw-font-bold text-left">@money($result->sum('total_gaji'))</td>
                                    <td colspan="2"></td>
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
        <button wire:click.prevent='isEditingMode(false)' class='btn-modal' data-toggle='modal' data-backdrop='static'
            data-keyboard='false' data-target='#formDataModal'>
            <i class='far fa-plus'></i>
        </button>
    </section>

    <div class="modal fade" data-backdrop="static" wire:ignore.self id="formDataModal"
        aria-labelledby="formDataModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title" id="formDataModalLabel">{{ $isEditing ? 'Edit Data' : 'Add Data' }}
                    </h5>
                    <button type="button" wire:click="cancel()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-4 lg:tw-px-6 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <div class="row no-gutters">
                            <div class="col-6 pr-1">
                                <div class='form-group'>
                                    <label for='periode_mulai'>Periode Mulai</label>
                                    <input type='date' wire:model='periode_mulai' id='periode_mulai'
                                        class='form-control'>
                                    @error('periode_mulai') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-6 pl-1">
                                <div class='form-group'>
                                    <label for='periode_selesai'>Periode Selesai</label>
                                    <input type='date' wire:model='periode_selesai' id='periode_selesai'
                                        class='form-control'>
                                    @error('periode_selesai') <span class='text-danger'>{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='id_karyawan'>Nama Karyawan</label>
                            <select wire:model='id_karyawan' id='id_karyawan' class='form-control select2'>
                                <option value='' disabled>-- Opsi Pilihan --</option>
                                @foreach ($karyawans as $karyawan)
                                <option value='{{ $karyawan->id }}'>{{ $karyawan->name }}</option>
                                @endforeach
                            </select>
                            @error('id_karyawan') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group tw-mt-1">
                            <label for="">Tunjangan</label>
                            @if ($tunjangans != [])
                            @foreach ($tunjangans as $index => $tunjangan)
                            <div class="row no-gutters tw-mt-1 align-items-center">
                                <div class="col-6 mb-2 pr-2">
                                    <input type="text" wire:model="tunjangans.{{ $index }}.nama_komponen"
                                        class="form-control" placeholder="Nama Tunjangan">
                                </div>
                                <div class="col-4 mb-2 pr-2">
                                    <input type="number" wire:model="tunjangans.{{ $index }}.jumlah"
                                        class="form-control" placeholder="Jumlah">
                                </div>
                                <div class="col-2 mb-2">
                                    <button type="button" class="btn btn-danger w-100"
                                        wire:click="removeTunjangan({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-center tw-mt-2">Belum ada tunjangan</p>
                            @endif
                            <center class="tw-mt-3">
                                <button wire:click.prevent="addTunjangan" class="btn btn-primary">Tambah
                                    Tunjangan</button>
                            </center>
                        </div>
                        <div class="form-group tw-mt-1">
                            <label for="">Potongan</label>
                            @if ($potongans != [])
                            @foreach ($potongans as $index => $potongan)
                            <div class="row no-gutters tw-mt-1 align-items-center">
                                <div class="col-6 mb-2 pr-2">
                                    <input type="text" wire:model="potongans.{{ $index }}.nama_komponen"
                                        class="form-control" placeholder="Nama Potongan">
                                </div>
                                <div class="col-4 mb-2 pr-2">
                                    <input type="number" wire:model="potongans.{{ $index }}.jumlah" class="form-control"
                                        placeholder="Jumlah">
                                </div>
                                <div class="col-2 mb-2">
                                    <button type="button" class="btn btn-danger w-100"
                                        wire:click="removePotongan({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-center tw-mt-2">Belum ada potongan</p>
                            @endif
                            <center class="tw-mt-3">
                                <button wire:click.prevent="addPotongan" class="btn btn-primary">Tambah
                                    Potongan</button>
                            </center>
                        </div>

                        <div class='form-group'>
                            <label for='status'>Status</label>
                            <select wire:model='status' id='status' class='form-control select2'>
                                <option value='draft'>Draft</option>
                                <option value='final'>Final</option>
                            </select>
                            @error('status') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div
                        class='modal-footer tw-sticky tw-bottom-[0] tw-bg-white tw-z-50 tw-px-2 lg:tw-px-4 tw-flex tw-justify-between tw-items-center'>
                        <button type='button' wire:click.prevent='review()'
                            class='btn btn-outline-primary form-control'>Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade !tw-pr-[0px] sm:tw-pr-[0px]" data-backdrop="static" wire:ignore.self id="reviewModal"
        aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[auto]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title tw-font-bold " id="reviewModalLabel">Slip Gaji</h5>
                    <button type="button" wire:click="cancelReview()" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-text-[#34395e] tw-px-2 lg:tw-px-4 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <div class="tw-text-center">
                            <h2 class="tw-text-xl tw-font-semibold">Barber shop</h2>
                            <p>{{ $nama_karyawan ?? "-" }}</p>
                            <p>Periode</p>
                            <p>{{ \Carbon\Carbon::parse($periode_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($periode_selesai)->format('d M Y') }}</p>
                        </div>
                        <hr class="tw-mt-2">
                        <div class="tw-mt-4">
                            <p class="tw-font-semibold">Komisi</p>
                            @forelse ($komisi_transaksi as $row_komisi)
                            @if ($row_komisi->komisi_nominal != 0)

                            <div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
                                <div class="tw-text-sm">
                                    <p class="tw-text-gray-500">
                                        {{ \Carbon\Carbon::parse($row_komisi->tanggal)->format('d M Y, H:i') }}</p>
                                    <p class="tw-text-blue-800">{{ $row_komisi->no_transaksi }}</p>
                                </div>
                                <div class="tw-text-sm tw-text-right">
                                    <p class="sm:tw-text-sm">@money($row_komisi->komisi_nominal)</p>
                                </div>
                            </div>
                            <hr>
                            @endif

                            @empty
                            <p>Tidak ada data riwayat komisi</p>
                            @endforelse
                            <div class="tw-flex tw-font-semibold tw-justify-between tw-py-1 tw-text-sm">
                                <p>Total Komisi</p>
                                <p class="tw-text-green-600">+@money($total_komisi)</p>
                            </div>
                            <hr>
                        </div>
                        <div class="tw-mt-4">
                            <p class="tw-font-semibold">Tunjangan</p>

                            @forelse ($tunjangans as $tunjangan)
                            <div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
                                <p class="tw-text-sm">{{ $tunjangan['nama_komponen'] ?: '-' }}</p>
                                <p class="tw-text-sm">@money($tunjangan['jumlah'])</p>
                            </div>
                            @empty
                            <p class="tw-text-sm tw-text-gray-500 tw-my-1">Tidak ada tunjangan</p>
                            @endforelse

                            <hr>
                            <div class="tw-flex tw-text-sm tw-my-1 tw-font-semibold tw-justify-between">
                                <p>Total Tunjangan</p>
                                <p class="tw-text-green-600">
                                    +@money($total_tunjangan)
                                </p>
                            </div>
                            <hr>
                        </div>
                        <div class="tw-mt-4">
                            <p class="tw-font-semibold">Potongan</p>

                            @forelse ($potongans as $potongan)
                            <div class="tw-flex tw-text-sm tw-items-center tw-justify-between tw-mt-1">
                                <p>{{ $potongan['nama_komponen'] ?: '-' }}</p>
                                <p>@money($potongan['jumlah'])</p>
                            </div>
                            @empty
                            <p class="tw-text-sm tw-text-gray-500 tw-my-1">Tidak ada potongan</p>
                            @endforelse
                            <hr>
                            <div class="tw-flex tw-text-sm tw-my-1 tw-font-semibold tw-justify-between">
                                <p>Total Potongan</p>
                                <p class="tw-text-red-600">
                                    -@money($total_potongan)
                                </p>
                            </div>
                            <hr>
                        </div>
                    </div>
                    <div
                        class="modal-footer tw-sticky tw-bottom-[0] tw-bg-white tw-z-50 tw-px-2 tw-flex tw-justify-between tw-items-center">
                        <div class="tw-text-sm tw-text-[#34395e] tw-tracking-[0.5px]">
                            <p class="">Total</p>
                            <p class="tw-font-semibold tw-text-lg">@money($total_gaji)</p>
                        </div>
                        <button wire:click.prevent="{{ $isEditing ? 'update()' : 'store()' }}"
                            class="btn btn-primary tw-bg-blue-500 form-control tw-py-2">Simpan</button>
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
    // $(document).ready(function () {
    //     $('#reviewModal').modal('show')
    // });
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
<script>
    let shouldReopenDataModal = true; // default: reopen ketika modal pembayaran ditutup

    $(document).ready(function () {
        // Saat modal pembayaran dibuka, sembunyikan modal data
        $('#reviewModal').on('show.bs.modal', function () {
            $('#formDataModal').modal('hide');
            shouldReopenDataModal = true; // modal utama akan dibuka lagi setelah ditutup
        });

        // Saat modal pembayaran ditutup, hanya munculkan lagi jika flag true
        $('#reviewModal').on('hidden.bs.modal', function () {
            if (shouldReopenDataModal) {
                $('#formDataModal').modal('show');
            }
        });
    });

    window.addEventListener('open-review-modal', event => {
        shouldReopenDataModal = false; // jangan munculkan kembali modal data
        $('#formDataModal').modal('hide');
        $('#reviewModal').modal({
            backdrop: 'static',
            keyboard: false
        }).modal('show');
    });

    window.addEventListener('swal:slipgaji', function (event) {
        const {
            idSlipGaji,
            message,
            text
        } = event.detail[0];

        Swal.fire({
            title: message,
            text: text,
            icon: 'success',
            showCancelButton: true,
            cancelButtonText: 'Tidak',
            confirmButtonText: 'Ya, Cetak Struk',
        }).then((result) => {
            shouldReopenDataModal = false; // modal utama akan dibuka lagi setelah ditutup
            $("#formDataModal").modal("hide");
            $("#reviewModal").modal("hide");
            if (result.isConfirmed) {
                window.open('/slip-gaji/print-nota/' + idSlipGaji, '_blank');
            }
        });
    });

</script>
@endpush
