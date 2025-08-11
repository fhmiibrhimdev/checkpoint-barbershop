<div>
    <section class="section custom-section">
        <div class="section-header">
            <a href="{{ url('/keuangan/piutang') }}" class="btn btn-transparent" title="Kembali ke halaman transaksi">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Pelunasan Piutang</h1>
        </div>

        <div class="section-body">
            <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-12 tw-gap-x-0 lg:tw-gap-x-6">
                <div class="tw-col-span-4">
                    <div class="card">
                        <div class="card-body tw-px-6">
                            <div class="form-group">
                                <label for="nama_pelanggan">Nama Pelanggan</label>
                                <input type="text" class="form-control" id="nama_pelanggan"
                                    value="{{ $transaksis->nama_pelanggan ?? "" }}" readonly>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="no_transaksi">No. Transaksi</label>
                                        <input type="text" class="form-control" id="no_transaksi"
                                            value="{{ $transaksis->no_transaksi ?? "" }}" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal</label>
                                        <input type="datetime-local" class="form-control" id="tanggal"
                                            value="{{ $transaksis->tanggal ?? "" }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="total_tagihan">Total Tagihan</label>
                                        <input type="text" class="form-control" id="total_tagihan"
                                            value="@money($transaksis->total_akhir ?? " 0")" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="total_tagihan">Uang DP</label>
                                        <input type="text" class="form-control" id="c"
                                            value="@money($transaksis->jumlah_dibayarkan ?? " 0")" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sisa_piutang">Sisa Piutang <i class="tw-text-gray-400">*)= Total Tagihan -
                                        (Uang DP + Pelunasan)</i></label>
                                <input type="text" class="form-control" id="sisa_piutang"
                                    value="@money($sisa_piutang->kembalian ?? " 0")" readonly>
                            </div>
                            <div class="form-group">
                                <label for="catatan">Catatan</label>
                                <textarea class="form-control" style="height: 75px" id="catatan"
                                    readonly>{{ $transaksis->catatan ?? "" }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tw-col-span-8">
                    <div class="card">
                        <h3>Status:
                            @if ($sisa_piutang->status == 3)
                            <span
                                class="tw-bg-green-100 tw-text-green-600 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm">Lunas</span>
                            @elseif ($sisa_piutang->status == 2)
                            <span class="tw-bg-red-100 tw-text-red-600 tw-px-3 tw-py-1 tw-rounded-lg tw-text-sm">Belum
                                Lunas</span>
                            @endif
                        </h3>
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
                                <p>Search: </p><input type="search" wire:model.live.debounce.750ms="searchTerm"
                                    id="search-data" placeholder="Search here..." class="form-control" value="">
                            </div>
                            <div class="table-responsive tw-w-full tw-max-h-96">
                                <table>
                                    <thead class="tw-sticky tw-top-0">
                                        <tr class="tw-text-gray-700">
                                            <th width="6%" class="text-center tw-whitespace-nowrap">No</th>
                                            <th width="15%" class="tw-whitespace-nowrap">Tanggal</th>
                                            <th width="15%" class="tw-whitespace-nowrap">Jumlah</th>
                                            <th width="35%" class="tw-whitespace-nowrap">Keterangan</th>
                                            <th class="tw-whitespace-nowrap">Pembayaran</th>
                                            <th class="text-center tw-whitespace-nowrap"><i class="fas fa-cog"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data as $row)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="tw-whitespace-nowrap">{{ $row->tanggal_bayar }}</td>
                                            <td class="tw-whitespace-nowrap">@money($row->jumlah_bayar)</td>
                                            <td>{{ $row->keterangan }}</td>
                                            <td class="tw-whitespace-nowrap">{{ $row->nama_kategori }}</td>
                                            <td class="text-center tw-whitespace-nowrap">
                                                <button wire:click.prevent="edit({{ $row->id }})"
                                                    class="btn btn-primary" data-toggle="modal"
                                                    data-target="#formDataModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click.prevent="deleteConfirm({{ $row->id }})"
                                                    class="btn btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Not data available in the table</td>
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
            </div>

        </div>
        <button wire:click.prevent="isEditingMode(false)" class="btn-modal" data-toggle="modal" data-backdrop="static"
            data-keyboard="false" data-target="#formDataModal">
            <i class="far fa-plus"></i>
        </button>
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
                            <label for="tanggal_bayar">Tanggal Dibayar</label>
                            <input type="date" wire:model="tanggal_bayar" id="tanggal_bayar" class="form-control">
                            @error('tanggal_bayar')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="jumlah_bayar">Jumlah Dibayar</label>
                            <input type="number" wire:model="jumlah_bayar" id="jumlah_bayar" class="form-control">
                            @error('jumlah_bayar')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea wire:model="keterangan" id="keterangan" class="form-control"
                                style="height: 100px"></textarea>
                            @error('keterangan')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="id_metode_pembayaran">Metode Pembayaran</label>
                            <select wire:model="id_metode_pembayaran" id="id_metode_pembayaran" class="form-control">
                                <option value="">-- Opsi Pilihan --</option>
                                @foreach ($pembayarans as $pembayaran)
                                <option value="{{ $pembayaran->id }}">{{ $pembayaran->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('id_metode_pembayaran')<small class="text-danger">Metode Pembayaran field is
                                required</small>@enderror
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
