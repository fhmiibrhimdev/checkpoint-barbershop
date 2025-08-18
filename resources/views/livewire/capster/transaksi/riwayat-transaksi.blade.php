<div>
    <section class="section custom-section">
        <div class="section-header">
            <h1>Riwayat Transaksi</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <h3>Table Riwayat Transaksi</h3>
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
                            placeholder="Search here..." class="form-control" value="">
                    </div>
                    <div class="table-responsive">
                        <table class='tw-w-full tw-table-auto'>
                            <thead class="tw-sticky tw-top-0">
                                <tr class="tw-text-gray-700">
                                    <th width="6%" class="text-center">No</th>
                                    <th class="tw-whitespace-nowrap">No Transaksi</th>
                                    <th class="tw-whitespace-nowrap">Pelanggan</th>
                                    <th class="tw-whitespace-nowrap">KOMISI</th>
                                    <th class="text-center"><i class="fas fa-cog"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data->groupBy('tanggal') as $row)
                                @php
                                $totalTanggal = $row->sum('total_komisi_karyawan');
                                @endphp
                                <tr>
                                    <td class="tw-text-sm tw-tracking-wider tw-font-semibold" colspan="10">
                                        Tanggal: {{ \Carbon\Carbon::parse($row[0]->tanggal)->format('Y-m-d') }}
                                    </td>
                                </tr>
                                @foreach ($row as $detail)
                                <tr>
                                    <td class="text-center">{{ $loop->index + 1 }}</td>
                                    <td class="tw-whitespace-nowrap">{{ $detail->no_transaksi }}</td>
                                    <td class="tw-whitespace-nowrap">{{ $detail->nama_pelanggan }}</td>
                                    <td class="tw-whitespace-nowrap">@money($detail->total_komisi_karyawan)</td>
                                    <td class="text-center">
                                        <button wire:click.prevent="edit({{ $detail->id }})" class="btn btn-primary"
                                            data-toggle="modal" data-target="#formDataModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="tw-bg-gray-100">
                                    <td colspan="3" class="text-right"></td>
                                    <td class="tw-text-sm"><b>@money($row->sum('total_komisi_karyawan'))</b></td>
                                    <td></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Not data available in the table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5 px-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        class="modal-body tw-px-4 lg:tw-px-6 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto tw-overflow-x-hidden no-scrollbar">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='id_pelanggan'>No Transaksi</label>
                                    <input type="text" class="form-control" value="{{ $no_transaksi }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class='form-group'>
                                    <label for='id_pelanggan'>Nama Pelanggan</label>
                                    <input type="text" class="form-control" value="{{ $nama_pelanggan }}" readonly>
                                </div>
                            </div>
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
                            <textarea wire:model="catatan" id="catatan" class="form-control" style="height: 100px"
                                readonly></textarea>
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
