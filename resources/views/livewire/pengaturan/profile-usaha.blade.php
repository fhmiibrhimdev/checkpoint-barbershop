<div>
    <section class="section custom-section">
        <div class='section-header tw-flex tw-w-full'>
            <h1>Profile Usaha </h1>
            @if (Auth::user()->hasRole('direktur'))
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
            @endif
        </div>

        <div class="section-body">
            <div class="card">
                <div class="tw-flex tw-ml-6 tw-mt-6 tw-mb-5 lg:tw-mb-1">
                    <h3 class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                        {{ $nama_cabang }}
                    </h3>
                    <div class="ml-auto tw-mr-4">
                        <button wire:click.prevent="update()" wire:loading.attr="disabled" class="btn btn-primary">
                            <i class="fas fa-file-excel"></i> Save Data
                        </button>
                    </div>
                </div>
                <div class="card-body tw-px-4 lg:tw-px-6">
                    <div class="form-group row">
                        <label for="nama_cabang" class="col-sm-2 col-form-label">Nama Cabang</label>
                        <div class="col-sm-10">
                            <input type="text" wire:model="nama_cabang" class="form-control" id="nama_cabang">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="subtitle_cabang" class="col-sm-2 col-form-label">Subtitle Cabang</label>
                        <div class="col-sm-10">
                            <input type="text" wire:model="subtitle_cabang" class="form-control" id="subtitle_cabang">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="alamat" class="col-sm-2 col-form-label">Alamat</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" wire:model="alamat" id="alamat"
                                style="height: 75px"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="no_telp" class="col-sm-2 col-form-label">No Telp/WA</label>
                        <div class="col-sm-10">
                            <input type="text" wire:model="no_telp" class="form-control" id="no_telp">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" wire:model="email" class="form-control" id="email">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="template_pesan_booking" class="col-sm-2 col-form-label">Template Pesan
                            Booking</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" wire:model="template_pesan_booking"
                                id="template_pesan_booking" style="height: 100px"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="template_pesan_belum_lunas" class="col-sm-2 col-form-label">Template Pesan
                            Belum Lunas</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" wire:model="template_pesan_belum_lunas"
                                id="template_pesan_belum_lunas" style="height: 100px"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="template_pesan_lunas" class="col-sm-2 col-form-label">Template Pesan
                            Lunas</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" wire:model="template_pesan_lunas" id="template_pesan_lunas"
                                style="height: 100px"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="template_pesan_dibatalkan" class="col-sm-2 col-form-label">Template Pesan
                            Dibatalkan</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" wire:model="template_pesan_dibatalkan"
                                id="template_pesan_dibatalkan" style="height: 100px"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
