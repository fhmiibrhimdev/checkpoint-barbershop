<div>
    <section class="section custom-section">
        <div class='section-header tw-flex tw-w-full'>
            <h1>Piutang </h1>
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
        <div class="section-body">
            <div class="card">
                <h3>Table Piutang</h3>
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
                    @if (!$agent->isMobile())
                    <div class="table-responsive tw-max-h-96 no-scrollbar">
                        <table class='tw-w-full tw-table-auto'>
                            <thead class="tw-sticky tw-top-0">
                                <tr class="tw-text-gray-700">
                                    <th width="6%" class="text-center">No</th>
                                    <th class="tw-whitespace-nowrap">Tanggal</th>
                                    <th class="tw-whitespace-nowrap">No. Reference</th>
                                    <th class="tw-whitespace-nowrap">Pelanggan</th>
                                    <th class="tw-whitespace-nowrap tw-text-right">Total Tagihan</th>
                                    <th class="tw-whitespace-nowrap tw-text-right">Sudah Dibayar</th>
                                    <th class="tw-whitespace-nowrap tw-text-right">Sisa Piutang</th>
                                    <th class="text-center"><i class="fas fa-cog"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                <tr>
                                    <td class="text-center">{{ $loop->index + 1 }}</td>
                                    <td class="tw-whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y, H:i') }}</td>
                                    <td class="tw-whitespace-nowrap tw-tracking-wide">
                                        {{ $row->no_referensi }} <br><i class="fas fa-arrow-right"></i> <span
                                            class="tw-text-gray-500 tw-font-normal">{{ $row->no_transaksi }}</span></td>
                                    <td class="tw-whitespace-nowrap">{{ $row->nama_pelanggan }}</td>
                                    <td class="tw-whitespace-nowrap tw-text-right">@money($row->total_akhir)</td>
                                    <td class="tw-whitespace-nowrap tw-text-right">@money($row->jumlah_dibayarkan +
                                        $row->total_bayar)
                                    </td>
                                    <td class="tw-whitespace-nowrap tw-text-right">@money($row->kembalian)</td>
                                    <td class="text-center">
                                        <a href="{{ url('/keuangan/piutang/' . \Crypt::encrypt($row->id)) }}"
                                            class="btn btn-primary" title="Klik untuk membayar piutang">
                                            <i class="fas fa-money-bill-transfer"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">Not data available in the table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5 px-3">
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
                    <div class="tw-flex tw-justify-between tw-items-center tw-mt-1">
                        <p class="tw-text-xs tw-text-[#34395e] tw-font-semibold tw-tracking-[0.5px]">
                            {{ \Carbon\Carbon::parse($result->tanggal)->format('d M Y, H:i') }}
                        </p>
                        <p class="tw-text-xs tw-text-[#34395e] tw-font-semibold tw-tracking-[0.5px]">
                            {{ $result->nama_pelanggan }}
                        </p>
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
                                        {{ $result->no_transaksi }}</span></p>

                            </div>
                        </div>
                        <div class="tw-text-[#34395e] tw-tracking-[0.5px] tw-text-xs tw-border-l tw-border-gray-200">
                            <div class="tw-ml-3 tw-mr-1 tw-flex tw-space-x-1">
                                <a href="{{ url('/keuangan/piutang/' . \Crypt::encrypt($result->id)) }}"
                                    class="btn btn-primary" title="Klik untuk membayar piutang">
                                    <i class="fas fa-money-bill-transfer"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr class="tw-mt-3 tw-mb-1.5">
                    <div class="tw-text-xs tw-flex tw-text-center tw-whitespace-nowrap tw-justify-between">
                        <p class="tw-font-semibold">Total Tagihan</p>
                        <p class="tw-font-semibold">Sudah Dibayar</p>
                        <p class="tw-font-semibold">Sisa Piutang</p>
                    </div>
                    <div class="tw-text-sm tw-flex tw-justify-between tw-text-center tw-whitespace-nowrap tw-gap-x-4">
                        <p class="tw-font-normal tw-text-yellow-600">@money($result->total_akhir)</p>
                        <p class="tw-font-normal tw-text-green-600">@money($result->jumlah_dibayarkan +
                            $result->total_bayar)</p>
                        <p class="tw-font-normal tw-text-red-600">@money($result->kembalian)</p>
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
        {{-- <button wire:click.prevent="isEditingMode(false)" class="btn-modal" data-toggle="modal" data-backdrop="static"
            data-keyboard="false" data-target="#formDataModal">
            <i class="far fa-plus"></i>
        </button> --}}
    </section>
    <div class="modal fade" data-backdrop="static" wire:ignore.self id="formDataModal"
        aria-labelledby="formDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formDataModalLabel">{{ $isEditing ? 'Edit Data' : 'Add Data' }}</h5>
                    <button type="button" wire:click="cancel()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" wire:model="title" id="title" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancel()" class="btn btn-secondary tw-bg-gray-300"
                            data-dismiss="modal">Close</button>
                        <button type="submit" wire:click.prevent="{{ $isEditing ? 'update()' : 'store()' }}"
                            wire:loading.attr="disabled" class="btn btn-primary tw-bg-blue-500">Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
