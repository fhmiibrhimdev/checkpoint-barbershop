<div>
    <section class="section custom-section">
        <div class='section-header'>
            <h1>Transaksi </h1>
        </div>
        <div class="tw-px-4 -tw-mt-0 lg:-tw-mt-4 lg:tw-px-1">
            <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                Report Harian</h3>
            <p class="tw-leading-5 tw-mt-2">Cek pemasukan dari transaksi di tokomu.</p>
        </div>
        <div class="section-body tw-mt-4">
            <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-y-0 tw-gap-x-4 tw-px-4 lg:tw-px-0">
                <div
                    class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[100px] lg:tw-h-[100px]">
                    <p class="tw-tracking-wider tw-text-gray-500 tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        omset</p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money($totalOmset)</p>
                    <p class="tw-tracking-wider text-primary tw-text-xs tw-font-semibold tw-cursor-pointer"
                        wire:click.prevent="totalOmsetModal()" data-toggle="modal" data-backdrop="static"
                        data-keyboard="false" data-target="#totalOmsetModal">
                        Klik Detail
                    </p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[100px] lg:tw-h-[100px]"">
                    <p class=" tw-tracking-wider tw-text-gray-500 tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                    pembayaran cash</p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money($totalTunai)</p>
                    <p class="tw-tracking-wider text-primary tw-text-xs tw-font-semibold tw-cursor-pointer"
                        wire:click.prevent="totalPembayaranCashModal()" data-toggle="modal" data-backdrop="static"
                        data-keyboard="false" data-target="#totalPembayaranCashModal">
                        Klik Detail
                    </p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[100px] lg:tw-h-[100px]
                    -tw-mt-4 lg:tw-mt-0">
                    <p class="tw-tracking-wider tw-text-gray-500 tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        pembayaran transfer
                    </p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money($totalTransfer)</p>
                    <p class="tw-tracking-wider text-primary tw-text-xs tw-font-semibold tw-cursor-pointer"
                        wire:click.prevent="totalPembayaranTransferModal()" data-toggle="modal" data-backdrop="static"
                        data-keyboard="false" data-target="#totalPembayaranTransferModal">
                        Klik Detail
                    </p>
                </div>
                <div class="card card-primary tw-rounded-lg tw-px-4 tw-py-2 tw-flex tw-flex-col tw-justify-between tw-h-[100px] lg:tw-h-[100px]
                    -tw-mt-4 lg:tw-mt-0">
                    <p class="tw-tracking-wider tw-text-gray-500 tw-text-xs lg:tw-text-sm tw-font-semibold">Total
                        Piutang
                    </p>
                    <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base lg:tw-text-lg tw-font-bold">
                        @money($totalPiutang)</p>
                    <p class="tw-tracking-wider text-primary tw-text-xs tw-font-semibold tw-cursor-pointer"
                        wire:click.prevent="totalPiutangModal()" data-toggle="modal" data-backdrop="static"
                        data-keyboard="false" data-target="#totalPiutangModal">
                        Klik Detail
                    </p>
                </div>
            </div>

            <div class="card -tw-mt-2">
                <h3>Table Transaksi</h3>
                <div class="card-body">
                    <div class="show-entries">
                        <p class="show-entries-show">Show</p>
                        <select wire:model.live="lengthData" id="length-data">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                            <option value="500">500</option>
                        </select>
                        <p class="show-entries-entries">Entries</p>
                    </div>
                    <div class="search-column">
                        <p>Search: </p><input type="search" wire:model.live.debounce.750ms="searchTerm" id="search-data"
                            placeholder="no transaksi, nama pelanggan" class="form-control" value="">
                    </div>
                    <div class="tw-overflow-x-auto tw-w-full no-scrollbar">
                        <div class="tw-flex tw-px-4 -tw-mb-6 tw-w-max tw-space-x-1 tw-ml-auto">
                            <button wire:click.prevent="resetFilter()"
                                class="btn btn-outline-secondary tw-rounded-full tw-px-4 tw-py-2 tw-h-10">
                                Reset Filter
                            </button>
                            <div class="form-group">
                                <select wire:model.live="filter_status" id="filter_status"
                                    class="form-control tw-rounded-full tw-bg-gray-100 tw-border tw-border-gray-100 tw-pr-8">
                                    <option value="">Semua Status</option>
                                    <option value="1">Booking</option>
                                    <option value="2">Belum Lunas</option>
                                    <option value="3">Lunas</option>
                                    <option value="4">Dibatalkan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select wire:model.live="filter_pembayaran" id="filter_pembayaran"
                                    class="form-control tw-rounded-full tw-bg-gray-100 tw-border tw-border-gray-100 tw-pr-8">
                                    <option value="">Metode Pembayaran</option>
                                    @foreach ($pembayarans as $pembayaran)
                                    <option value="{{ $pembayaran->id }}">{{ $pembayaran->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-x-4 tw-gap-y-4 tw-px-4 lg:tw-px-0 ">
                @forelse ($data as $row)
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-shadow-gray-300 tw-flex tw-flex-col tw-h-full">

                    {{-- HEADER --}}
                    <div class="tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px] tw-px-4 tw-pt-4">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <p class="tw-text-sm">{{ Str::limit($row->nama_pelanggan ?? '-', 15) }}</p>
                            <span class="tw-bg-gray-50 tw-px-2 tw-py-2 tw-rounded-md tw-text-xs">
                                {{ $row->no_transaksi }}
                            </span>
                        </div>
                        <div class="tw-flex tw-justify-between tw-items-center tw-mt-2">
                            <p class="tw-text-xs tw-font-normal tw-text-gray-500">
                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y, H:i') }}</p>
                            <div class="tw-flex tw-space-x-1">
                                @if ($row->status == "3")
                                <div
                                    class="{{ $row->id_karyawan == $id_karyawan ? 'tw-bg-green-100 tw-text-green-600' : 'tw-bg-gray-100 tw-text-gray-600' }} tw-tracking-[0.5px] tw-text-xs tw-py-1 tw-px-2 tw-rounded-md">
                                    Lunas</div>
                                @elseif ($row->status == "2")
                                <div
                                    class="{{ $row->id_karyawan == $id_karyawan ? 'tw-bg-orange-100 tw-text-orange-600' : 'tw-bg-gray-100 tw-text-gray-600' }} tw-tracking-[0.5px] tw-text-xs tw-py-1 tw-px-2 tw-rounded-md">
                                    Belum Lunas</div>
                                @elseif ($row->status == "1")
                                <div
                                    class="{{ $row->id_karyawan == $id_karyawan ? 'tw-bg-yellow-100 tw-text-yellow-600' : 'tw-bg-gray-100 tw-text-gray-600' }} tw-tracking-[0.5px] tw-text-xs tw-py-1 tw-px-2 tw-rounded-md">
                                    Booking</div>
                                @elseif ($row->status == "4")
                                <div
                                    class="{{ $row->id_karyawan == $id_karyawan ? 'tw-bg-red-100 tw-text-red-600' : 'tw-bg-gray-100 tw-text-gray-600' }} tw-tracking-[0.5px] tw-text-xs tw-py-1 tw-px-2 tw-rounded-md">
                                    Dibatalkan</div>
                                @endif
                                <div
                                    class="{{ $row->id_karyawan == $id_karyawan ? 'tw-bg-blue-100 tw-text-blue-600' : 'tw-bg-gray-100 tw-text-gray-600' }} tw-tracking-[0.5px] tw-text-xs tw-py-1 tw-px-2 tw-rounded-md">
                                    {{ $row->nama_kategori }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- <hr class="tw-bg-gray-50 tw-my-4"> --}}

                    {{-- CONTENT TENGAH yang flexible --}}
                    <div class="tw-px-4 tw-py-0 tw-flex-grow tw-my-4">
                        <div class="tw-flex tw-text-[#34395e] tw-tracking-[0.5px] tw-items-start">
                            <img src="{{ asset('assets/stisla/img/example-image-50.jpg') }}"
                                class="tw-rounded-lg tw-w-12 tw-h-12 tw-object-cover tw-mr-3 {{ $row->id_karyawan == $id_karyawan ? '' : 'tw-grayscale' }}">
                            <div class="tw-text-xs -tw-mt-1">
                                <p class="tw-font-semibold">{{ Str::limit($row->nama_item ?? '-', 30) }}</p>
                                <p class="tw-text-gray-600 tw-leading-5">
                                    {{ Str::limit($row->deskripsi_item ?? '-', 30) }}</p>
                            </div>
                        </div>
                        @if (($row->jumlah_produk ?? 0) > 1)
                        <p class="tw-mt-2 -tw-mb-2">+{{ $row->jumlah_produk - 1 }} produk lainnya</p>
                        @endif
                    </div>

                    {{-- <hr class="tw-bg-gray-50 tw-mt-4 tw-mb-4"> --}}

                    {{-- FOOTER --}}
                    <div
                        class="tw-flex tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px] tw-justify-between tw-items-center tw-px-4 tw-mb-4">
                        <p class="tw-text-base">@money($row->total_akhir)</p>
                        <div>
                            @if ($row->id_karyawan == $id_karyawan)
                            <button wire:click.prevent='edit({{ $row->id }})' class='btn btn-primary'
                                data-toggle='modal' data-target='#formDataModal'><i class="fas fa-eye"></i></button>
                            @endif
                            @if ($row->status != "4")
                            {{-- <button wire:click.prevent='deleteConfirm({{ $row->id }})' class="btn btn-danger"><i
                                class="fas fa-trash"></i></button> --}}
                            <a target="_BLANK" href="{{ url('/transaksi/print-nota/'.\Crypt::encrypt($row->id)) }}"
                                class="btn {{ $row->id_karyawan == $id_karyawan ? 'btn-info' : 'tw-bg-gray-100 tw-text-gray-600' }}"
                                title="Cetak Struk"><i class="fas fa-print"></i></a>

                            <a wire:click.prevent="kirimWA({{ $row->id }})" data-toggle="modal" data-backdrop="static"
                                data-keyboard="false" data-target="#kirimWAModal"
                                class="btn {{ $row->id_karyawan == $id_karyawan ? 'btn-warning' : 'tw-bg-gray-100 tw-text-gray-600' }}"><i
                                    class="fab fa-whatsapp"></i></a>

                            @endif
                        </div>
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
        </div>
        <button wire:click.prevent="isEditingMode(false)" class="btn-modal" data-toggle="modal" data-backdrop="static"
            data-keyboard="false" data-target="#formDataModal">
            <i class="far fa-plus"></i>
        </button>
    </section>

    <div class="modal fade" data-backdrop="static" wire:ignore.self id="kirimWAModal"
        aria-labelledby="kirimWAModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kirimWAModalLabel">Kirim Pesan WA</h5>
                    <button type="button" wire:click="cancel()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="tw-flex tw-justify-between">
                            <p class="tw-tracking-wider tw-text-[#34395e] tw-font-semibold">Status Wa: </p>

                            <div>
                                {{-- Saat loading --}}
                                <span wire:loading>
                                    <p class="tw-tracking-wider text-warning tw-font-semibold">Loading...</p>
                                </span>

                                {{-- Saat sudah selesai --}}
                                <span wire:loading.remove>
                                    @if ($statusConnected)
                                    <p class="tw-tracking-wider text-success tw-font-semibold">Connected!</p>
                                    @else
                                    <p class="tw-tracking-wider text-danger tw-font-semibold">Disconnected!</p>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="row tw-mt-5">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="wa_no_transaksi">No. Transaksi</label>
                                    <input type="text" wire:model="wa_no_transaksi" id="wa_no_transaksi"
                                        class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="wa_nama_pelanggan">Nama Pelangan</label>
                                    <input type="text" wire:model="wa_nama_pelanggan" id="wa_nama_pelanggan"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="wa_no_telp">Nomor WhatsApp</label>
                            <input type="text" wire:model="wa_no_telp" id="wa_no_telp" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label for="template_pesan">Template Pesan Pembayaran</label>
                            <textarea wire:model="template_pesan" id="template_pesan" style="height: 100px"
                                class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancel()" class="btn btn-secondary tw-bg-gray-300"
                            data-dismiss="modal">Close</button>
                        <button type="submit" wire:click.prevent="sendWA()" wire:loading.attr="disabled"
                            class="btn btn-primary tw-bg-blue-500"><i class="fas fa-paper-plane"></i> Kirim WA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" data-backdrop="static" wire:ignore.self id="formDataModal"
        aria-labelledby="formDataModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title" id="formDataModalLabel">{{ $isEditing ? 'View Data' : 'Tambah Transaksi' }}
                    </h5>
                    <button type="button" wire:click="cancel()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-4 lg:tw-px-6 tw-max-h-[calc(100vh-300px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <div class='form-group'>
                            <label for='id_pelanggan'>Pelanggan (Opsional)</label>
                            {{-- <span>{{ $id_pelanggan }}</span> --}}
                            {{-- <div wire:ignore> --}}
                            <select wire:model='id_pelanggan' id='id_pelanggan' class='form-control select2'>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach ($pelanggans as $pelanggan)
                                <option value='{{ $pelanggan->id }}'>{{ $pelanggan->nama_pelanggan }}
                                </option>
                                @endforeach
                            </select>
                            {{-- </div> --}}
                            @error('id_pelanggan') <span class='text-danger'>{{ $message }}</span>
                            @enderror
                        </div>
                        <label
                            class="tw-text-base tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px] -tw-mt-4">Items</label>
                        {{-- @for ($i = 1; $i <= 5; $i++) --}}
                        @forelse ($cartItems as $index => $item)
                        <div id="table-produk" class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs tw-mt-2">
                            <div class="tw-flex tw-justify-between tw-font-semibold tw-items-start">
                                <p>{{ $item['nama_item'] == "-" ? "" : $item['nama_item'] }}
                                    {{ $item['deskripsi_item'] == "-" ? "" : " - ". $item['deskripsi_item'] }}</p>
                                <p class="text-primary tw-text-sm tw-ml-2 tw-mt-1 tw-whitespace-nowrap">
                                    @money($item['total_harga'])</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-mb-2">
                                <div class="tw-whitespace-nowrap">
                                    <p>{{ $item['jumlah'] }} x @money($item['harga'])</p>
                                    <p>{{ $item['kategori_item'] }}</p>
                                </div>
                                <div class="tw-flex tw-items-center tw-space-x-3">
                                    @if (!$isEditing)
                                    <button wire:click.prevent="deleteCartItems({{ $index }})" class="btn btn-danger">
                                        <i class="fas fa-trash tw-text-base tw-ml-auto"></i>
                                    </button>
                                    @endif
                                    {{-- <input type="number" class="form-control tw-w-1/4 tw-text-center tw-ml-auto">
                                    <i class="far fa-plus-circle tw-text-lg text-primary tw-ml-auto"></i> --}}
                                </div>
                            </div>
                            <div id="accordion">
                                <div class="accordion">
                                    <div class="accordion-header tw-py-1 tw-text-center" role="button"
                                        data-toggle="collapse" data-target="#panel-body-{{ $index }}"
                                        wire:key={{ rand() }}>
                                        <i class="fas fa-angle-down"></i>
                                    </div>
                                    <div class="accordion-body collapse tw-px-0" id="panel-body-{{ $index }}"
                                        data-parent="#accordion">
                                        <div class="tw-flex tw-justify-between">
                                            <p>Diskon</p>
                                            <p class="text-danger">-@money($item['diskon'])</p>
                                        </div>
                                        <div class="tw-flex tw-justify-between">
                                            <p>Karyawan</p>
                                            <p>{{ $item['nama_karyawan'] }}</p>
                                        </div>
                                        <div class="tw-flex tw-justify-between">
                                            <p>Komisi ({{ $item['komisi_persen'] }}%)</p>
                                            <p>@money($item['komisi_nominal'])</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="tw-bg-gray-50">
                        </div>
                        @empty
                        <p class="tw-text-center tw-text-gray-400 ">Belum ada items</p>
                        @endforelse
                        {{-- @endfor --}}
                        @if (!$isEditing)
                        <center class="tw-py-5">
                            <button wire:click.prevent="listProduk()" class="btn btn-primary tw-text-center"
                                data-toggle="modal" data-target="#formListItemsModal">Pilih Items</button>
                        </center>
                        @endif
                        <div class="form-group tw-mt-3">
                            <label for="catatan">Catatan (Opsional)</label>
                            <textarea wire:model="catatan" id="catatan" class="form-control"
                                style="height: 100px"></textarea>
                        </div>
                        <hr class="tw-bg-gray-200">
                        <div class="tw-mt-4">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Total
                                    Pesanan</p>
                                <p>{{ $total_pesanan ?? 0 }}</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Total
                                    Komisi Karyawan
                                </p>
                                <p>@money($total_komisi)</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Total HPP
                                </p>
                                <p>@money($total_hpp)</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Total Sub
                                    Total
                                </p>
                                <p>@money($total_sub_total)</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Total
                                    Diskon</p>
                                <p class="text-danger">-@money($total_diskon)</p>
                            </div>
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <p class="tw-text-[#34395e] tw-tracking-[0.5px] tw-font-semibold tw-text-xs">Pendapatan
                                    Bersih
                                </p>
                                <p>@money($laba_bersih)</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer tw-sticky tw-bottom-[0] tw-bg-white tw-z-50 tw-pl-4 tw-pr-6 tw-block">
                        <div class="tw-text-sm tw-text-[#34395e] tw-tracking-[0.5px]">
                            <p class="">Total</p>
                            <p class="tw-font-semibold tw-text-lg">@money($total_akhir)</p>
                        </div>
                        @if ($isEditing)
                        @if ($status == "1" && $jumlah_dibayarkan == 0) {{-- Kalau status: booking, dan belum lunas --}}
                        <div class="row no-gutters">
                            <div class="col-6 pr-2">
                                <button wire:click.prevent="dibatalkan()"
                                    class="btn btn-danger form-control tw-py-2">Dibatalkan</button>
                            </div>
                            <div class="col-6 pl-2">
                                <button wire:click.prevent="formPembayaran()"
                                    class="btn btn-primary tw-bg-blue-500 form-control tw-py-2" data-toggle="modal"
                                    data-target="#formPembayaranModal">Bayar</button>
                            </div>
                        </div>
                        @elseif ($status == "1" && $jumlah_dibayarkan > 0) <div class="row no-gutters">
                            {{-- Kalau status: booking, dan bayar dp --}}
                            <div class="col-6 pr-2">
                                <button wire:click.prevent="dibatalkan()"
                                    class="btn btn-danger form-control tw-py-2">Dibatalkan</button>
                            </div>
                            <div class="col-6 pl-2">
                                <button wire:click.prevent="directPiutang()"
                                    class="btn btn-primary tw-bg-blue-500 form-control tw-py-2">Bayar Piutang</button>
                            </div>
                        </div>
                        @elseif ($status == "2") {{-- Kalau status: belum lunas --}}
                        <div class="row no-gutters">
                            <div class="col-6 pr-2">
                                <button wire:click.prevent="dibatalkan()"
                                    class="btn btn-danger form-control tw-py-2">Dibatalkan</button>
                            </div>
                            <div class="col-6 pl-2">
                                <button wire:click.prevent="directPiutang()"
                                    class="btn btn-primary tw-bg-blue-500 form-control tw-py-2" data-toggle="modal"
                                    data-target="#formPembayaranModal">Bayar Piutang</button>
                            </div>
                        </div>
                        @elseif($status == "3") {{-- Kalau status: lunas --}}
                        <div class="row no-gutters">
                            <div class="col-6 pr-2">
                                <button wire:click.prevent="dibatalkan()"
                                    class="btn btn-danger form-control tw-py-2">Dibatalkan</button>
                            </div>
                            <div class="col-6 pl-2">
                                <button wire:click.prevent="formPembayaran()"
                                    class="btn btn-primary tw-bg-blue-500 form-control" data-toggle="modal"
                                    data-target="#formPembayaranModal">Ganti Pembayaran</button>
                            </div>
                        </div>
                        @endif
                        @else
                        <button wire:click.prevent="formPembayaran()"
                            class="btn btn-primary tw-bg-blue-500 form-control tw-py-2" data-toggle="modal"
                            data-target="#formPembayaranModal">Bayar</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade tw-pr-[0px] sm:tw-pr-[15px]" data-backdrop="static" wire:ignore.self id="formListItemsModal"
        aria-labelledby="formListItemsModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title" id="formListItemsModalLabel">Pilih Produk</h5>
                    <button type="button" wire:click="cancelList()" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body tw-px-4 lg:tw-px-6">
                        <input type="search" wire:model.live.debounce.750ms="searchProduk" class="form-control tw-mb-5"
                            placeholder="Cari nama items">
                        @foreach ($produks as $produk)
                        <button wire:click.prevent="cartProduk({{ $produk->id }})" data-toggle="modal"
                            data-target="#formCartItemsModal" class="tw-w-full  tw-text-[#34395e] tw-tracking-[0.5px]">
                            <div class="tw-flex tw-justify-between tw-text-sm tw-items-start">
                                <p class="tw-font-semibold tw-text-xs tw-text-left tw-leading-5">
                                    {{ $produk->nama_item }}</p>
                                <p class="tw-whitespace-nowrap tw-font-semibold tw-ml-5 -tw-mt-1 text-primary">
                                    @money($produk->harga_jasa)</p>
                            </div>
                            <div class="tw-text-left tw-flex tw-justify-between">
                                <p class="tw-text-gray-500 tw-text-xs">
                                    @if ($produk->nama_kategori == "Jasa Barbershop" || $produk->nama_kategori ==
                                    "Treatment")
                                    {{ $produk->deskripsi }}
                                    @else
                                    Sisa Stock: {{ $produk->stock }}
                                    @endif
                                </p>
                                <p class="tw-text-gray-500 tw-text-xs">{{ $produk->nama_kategori }}</p>
                            </div>
                        </button>
                        <hr class="tw-bg-gray-50 tw-my-3">
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade tw-pr-[0px] sm:tw-pr-[15px]" data-backdrop="static" wire:ignore.self id="formCartItemsModal"
        aria-labelledby="formCartItemsModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title" id="formCartItemsModalLabel">Add Cart Produk</h5>
                    <button type="button" wire:click="cancelCartItems()" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body tw-px-4 lg:tw-px-6 tw-text-sm tw-text-[#34395e] tw-tracking-[0.5px]">
                        <div class="form-group">
                            <label for="id_karyawan">Karyawan</label>
                            {{-- <div wire:ignore> --}}
                            <select wire:model="id_karyawan" id="id_karyawan" class="form-control select2">
                                <option value="" disabled>-- Opsi Pilihan --</option>
                                @foreach ($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}">{{ $karyawan->name }}</option>
                                @endforeach
                            </select>
                            <small>@error('id_karyawan') <span
                                    class='text-danger'>{{ $message }}</span>@enderror</small>
                            {{-- </div> --}}
                        </div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <p class="tw-font-semibold">{{ $nama_item ?? "" }}</p>
                            <p class="tw-font-semibold text-primary tw-ml-2 tw-whitespace-nowrap">@money($harga_item)
                            </p>
                        </div>
                        <div class="tw-flex tw-justify-between tw-text-sm">
                            <p class="tw-text-gray-500">{{ $deskripsi_item ?? "" }}</p>
                            <p class="tw-text-gray-500">{{ $kategori_item ?? "" }}</p>
                        </div>
                        <div class="tw-flex tw-space-x-10 tw-justify-center tw-my-10">
                            <button wire:click.prevent="decrementJumlah()" class="btn btn-danger">-</button>
                            <p class="tw-text-2xl tw-font-semibold">{{ $jumlah }}</p>
                            <button wire:click.prevent="incrementJumlah()" class="btn btn-primary">+</button>
                        </div>
                        <div class="form-group">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-2 form-group">
                                <label>Diskon</label>
                                <label class="custom-switch">
                                    <input type="checkbox" wire:key="{{ rand() }}" wire:model.live="isPersentase"
                                        name="custom-switch-checkbox" class="custom-switch-input">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">Persentase</span>
                                </label>
                            </div>
                            {{-- {{ $input_diskon }} --}}
                            @if ($isPersentase)
                            <div class="input-group">
                                <input type="number" wire:model.lazy="input_diskon"
                                    class="form-control phone-number tw-text-right">
                                <div class="input-group-prepend">
                                    <div class="input-group-text tw-rounded-r-md">
                                        %
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        Rp
                                    </div>
                                </div>
                                <input type="number" wire:model.lazy="input_diskon" class="form-control phone-number">
                            </div>
                            @endif
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <p>Sub Total</p>
                            <p>@money($sub_total)</p>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <p>Diskon</p>
                            <p class="text-danger">-@money($diskon)</p>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <p>Total Harga</p>
                            <b>@money($total_harga)</b>
                        </div>
                        <div class="tw-flex tw-justify-between text-success">
                            <p>Komisi ({{ $this->komisi_persen ?? "0" }}%)</p>
                            <p>@money($komisi_nominal)</p>
                        </div>
                    </div>
                    <div class="tw-px-6 tw-pb-6">
                        <div class="row">
                            <div class="col-6">
                                <button type="button" wire:click="cancelCartItems()"
                                    class="btn btn-secondary form-control tw-bg-gray-300"
                                    data-dismiss="modal">Cancel</button>
                            </div>
                            <div class="col-6">
                                <button type="submit" wire:click.prevent="addCartItems()" wire:loading.attr="disabled"
                                    class="btn btn-primary form-control tw-bg-blue-500"><i
                                        class="fas fa-cart-plus text-lg"></i> Add Cart</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade tw-pr-[0px] sm:tw-pr-[15px]" data-backdrop="static" wire:ignore.self id="formPembayaranModal"
        aria-labelledby="formPembayaranModalLabel" aria-hidden="true">
        <div class='modal-dialog modal-dialog-centered tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title" id="formPembayaranModalLabel">Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body tw-px-4 lg:tw-px-6">
                        <div class="form-group tw-flex tw-justify-between">
                            <label class="tw-text-base">Total Akhir</label>
                            <label class="tw-text-base">@money($total_akhir)</label>
                        </div>
                        <div class="form-group -tw-mt-3">
                            <label for="id_metode_pembayaran">Metode Pembayaran</label>
                            <select wire:model="id_metode_pembayaran" id="id_metode_pembayaran"
                                class="form-control select2">
                                <option value="">-- Opsi Pilihan</option>
                                @foreach ($pembayarans as $pembayaran)
                                <option value="{{ $pembayaran->id }}">{{ $pembayaran->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('id_metode_pembayaran')
                            <span class='text-danger'>Metode Pembayaran wajib dipilih.</span>
                            @enderror
                        </div>
                        @if ($isEditing && $status == "3")
                        @else
                        <div class="form-group">
                            <label>Jumlah yang Dibayarkan</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text tw-text-xl tw-h-full">
                                        Rp
                                    </div>
                                </div>
                                <input type="number" wire:model.lazy="jumlah_dibayarkan"
                                    class="form-control form-control-lg tw-text-2xl tw-font-bold tw-text-right tw-text-black phone-number">
                            </div>
                        </div>
                        <div class="form-group tw-flex tw-justify-between">
                            <label>Kembalian</label>
                            <label>@money($jumlah_dibayarkan - $total_akhir)</label>
                        </div>
                        @endif
                        @if ($isEditing && $status == "3")
                        <button wire:click.prevent='updateMetodePembayaran()' wire:loading.attr='disabled'
                            class="btn btn-primary form-control tw-flex tw-items-center tw-justify-center">
                            Ya, Simpan
                        </button>
                        @else
                        <div class="tw-grid tw-grid-cols-2 tw-gap-x-4">
                            <button wire:click.prevent="uangPas()" class="btn btn-outline-primary form-control">Uang
                                Pas</button>
                            <button wire:click.prevent='{{ $isEditing ? 'update()' : 'store()' }}'
                                wire:loading.attr='disabled'
                                class="btn btn-primary form-control tw-flex tw-items-center tw-justify-center">
                                <i class="fas fa-badge-check tw-text-lg tw-mr-1" id="finish-transaksi"></i>
                                Bayar
                            </button>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" data-backdrop="static" wire:ignore.self id="totalOmsetModal"
        aria-labelledby="totalOmsetModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px]"
                        id="totalOmsetModalLabel">Detail Omset &dash;
                        {{ \Carbon\Carbon::parse(now())->format('d M Y') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-0 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <i class="tw-text-gray-400 tw-px-2">*) Sumber hanya berasal dari transaksi</i>
                        <div class="tw-max-h-96 tw-mt-2">
                            <table class='tw-w-full tw-table-auto'>
                                <thead class="tw-sticky tw-top-0">
                                    <tr class="tw-text-gray-700">
                                        <th width="6%" class="text-center">No</th>
                                        <th>No. Transaksi</th>
                                        <th class="tw-text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detailOmset as $omset)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $omset->no_transaksi }}</td>
                                        <td class="tw-text-right">@money($omset->jumlah)</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Not data available in the table</td>
                                    </tr>
                                    @endforelse
                                    @if (count($detailOmset))
                                    <tr>
                                        <td colspan="2"><strong>Total</strong></td>
                                        <td class="tw-text-right"><strong>@money($totalDetailOmset)</strong></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" data-backdrop="static" wire:ignore.self id="totalPembayaranCashModal"
        aria-labelledby="totalPembayaranCashModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px]"
                        id="totalPembayaranCashModalLabel">Detail Pembayaran Cash &dash;
                        {{ \Carbon\Carbon::parse(now())->format('d M Y') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-0 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <i class="tw-text-gray-400 tw-px-2">*) Sumber bisa berasal dari transaksi atau piutang</i>
                        <div class="tw-overflow-auto tw-max-h-96 no-scrollbar tw-mt-2">
                            <table class='tw-w-full tw-table-auto'>
                                <thead class="tw-sticky tw-top-0">
                                    <tr class="tw-text-gray-700">
                                        <th width="6%" class="text-center">No</th>
                                        <th>No. Transaksi</th>
                                        <th>Sumber</th>
                                        <th class="tw-text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detailPembayaranCash as $pembayaranCash)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $pembayaranCash['no_transaksi'] }}</td>
                                        <td>{{ $pembayaranCash['sumber'] }}</td>
                                        <td class="tw-text-right">@money($pembayaranCash['jumlah'])</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Not data available in the table</td>
                                    </tr>
                                    @endforelse
                                    @if (count($detailPembayaranCash))
                                    <tr>
                                        <td class="tw-text-right" colspan="3"><strong>Total</strong></td>
                                        <td class="tw-text-right"><strong>@money($totalDetailPembayaranCash)</strong>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" data-backdrop="static" wire:ignore.self id="totalPembayaranTransferModal"
        aria-labelledby="totalPembayaranTransferModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px]"
                        id="totalPembayaranTransferModalLabel">Detail Pembayaran Transfer &dash;
                        {{ \Carbon\Carbon::parse(now())->format('d M Y') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-0 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <i class="tw-text-gray-400 tw-px-2">*) Sumber bisa berasal dari transaksi atau piutang</i>
                        <div class="tw-overflow-auto tw-max-h-96 no-scrollbar tw-mt-2">
                            <table class='tw-w-full tw-table-auto'>
                                <thead class="tw-sticky tw-top-0">
                                    <tr class="tw-text-gray-700">
                                        <th width="6%" class="text-center">No</th>
                                        <th>No. Transaksi</th>
                                        <th>Sumber</th>
                                        <th class="tw-text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detailPembayaranTransfer as $pembayaranTransfer)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $pembayaranTransfer['no_transaksi'] }}</td>
                                        <td>{{ $pembayaranTransfer['sumber'] }}</td>
                                        <td class="tw-text-right">@money($pembayaranTransfer['jumlah'])</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Not data available in the table</td>
                                    </tr>
                                    @endforelse
                                    @if (count($detailPembayaranTransfer))
                                    <tr>
                                        <td class="tw-text-right" colspan="3"><strong>Total</strong></td>
                                        <td class="tw-text-right">
                                            <strong>@money($totalDetailPembayaranTransfer)</strong>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" data-backdrop="static" wire:ignore.self id="totalPiutangModal"
        aria-labelledby="totalPiutangModalLabel" aria-hidden="true">
        <div class='modal-dialog tw-w-full tw-m-0 sm:tw-w-auto sm:tw-m-[1.75rem_auto] tw-overflow-y-[initial]'>
            <div class='modal-content tw-rounded-none lg:tw-rounded-md'>
                <div class="modal-header tw-px-4 lg:tw-px-6 tw-sticky tw-top-[0] tw-bg-white tw-z-50">
                    <h5 class="modal-title tw-font-semibold tw-text-[#34395e] tw-tracking-[0.5px]"
                        id="totalPiutangModalLabel">Detail Piutang &dash;
                        {{ \Carbon\Carbon::parse(now())->format('d M Y') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div
                        class="modal-body tw-px-0 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <i class="tw-text-gray-400 tw-px-2">*) Sumber hanya berasal dari transaksi</i>
                        <div class="tw-max-h-96 tw-mt-2">
                            <table class='tw-w-full tw-table-auto'>
                                <thead class="tw-sticky tw-top-0">
                                    <tr class="tw-text-gray-700">
                                        <th width="6%" class="text-center">No</th>
                                        <th>No. Transaksi</th>
                                        <th class="tw-text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detailPiutang as $piutang)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $piutang->no_transaksi }}</td>
                                        <td class="tw-text-right">@money($piutang->jumlah)</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Not data available in the table</td>
                                    </tr>
                                    @endforelse
                                    @if (count($detailPiutang))
                                    <tr>
                                        <td colspan="2"><strong>Total</strong></td>
                                        <td class="tw-text-right"><strong>@money($totalDetailPiutang)</strong></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
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
    let shouldReopenDataModal = true; // default: reopen ketika modal pembayaran ditutup

    $(document).ready(function () {
        // Saat modal pembayaran dibuka, sembunyikan modal data
        $('#formPembayaranModal').on('show.bs.modal', function () {
            $('#formDataModal').modal('hide');
            shouldReopenDataModal = true; // modal utama akan dibuka lagi setelah ditutup
        });

        // Saat modal pembayaran ditutup, hanya munculkan lagi jika flag true
        $('#formPembayaranModal').on('hidden.bs.modal', function () {
            if (shouldReopenDataModal) {
                $('#formDataModal').modal('show');
            }
        });
    });

    // Event khusus dari Livewire / JS untuk menutup semua modal
    window.addEventListener('closePembayaranModal', event => {
        shouldReopenDataModal = false; // jangan munculkan kembali modal data
        $('#formPembayaranModal').modal('hide');
        $('#formDataModal').modal('hide');
    });

    window.addEventListener('refreshList', event => {
        setTimeout(() => {
            $('.select2').each(function () {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal')
                });
            });
        }, 500);
    });

</script>
<script>
    // $(document).ready(function () {
    //     $('#totalOmsetModal').modal('show')
    // });
    window.addEventListener('initSelect2', event => {
        $(document).ready(function () {
            $('.select2').each(function () {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal')
                });
            });

            $('.select2').on('change', function (e) {
                var id = $(this).attr('id');
                var data = $(this).select2("val");
                @this.set(id, data);
            });
        });
    })
    window.addEventListener('closeCart', event => {
        $(document).ready(function () {
            $('#formListItemsModal').modal('hide')
            $('#formCartItemsModal').modal('hide')
        });
    })
    // window.addEventListener('printNota', function (idTransaksi) {
    //     console.log('Print Nota:', event.detail);
    //     const id = event.detail.id; // ambil dari event.detail

    //     window.open('/transaksi/print-nota/' + id, '_blank');
    // });

</script>
@endpush
