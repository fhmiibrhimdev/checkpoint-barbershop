<div>
    <section class="section custom-section">
        @if ($agent->isDesktop())
        <div class='section-header tw-grid tw-grid-cols-3 tw-w-full'>
            <h1>Laporan Laba Rugi </h1>
            <div class="tw-text-center">
                <div class="tw-inline-flex tw-rounded-full tw-bg-gray-100 tw-p-1 tw-space-x-2 tw-text-center">
                    @foreach (['harian'=>'Harian','bulanan'=>'Bulanan','tahunan'=>'Tahunan','custom'=>'Custom'] as $key
                    => $label)
                    <button wire:click.prevent="setRange('{{ $key }}')" class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium transition
               {{ $option_filter === $key 
                  ? 'tw-bg-white tw-text-gray-700 tw-font-semibold tw-shadow' 
                  : 'tw-text-gray-500 hover:tw-bg-white/70' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
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
        @elseif ($agent->isMobile())
        <div class='section-header tw-w-full'>
            <h1>Dashboard </h1>
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
        <div class="tw-text-center tw-mt-0 lg:-tw-mt-3">
            <div class="tw-inline-flex tw-rounded-full tw-bg-gray-200 tw-px-1 tw-py-1  
             tw-space-x-4 tw-justify-center tw-items-center">
                <button wire:click.prevent="setRange('harian')"
                    class="{{ $option_filter == "harian" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">
                    Harian
                </button>
                <button wire:click.prevent="setRange('bulanan')"
                    class="{{ $option_filter == "bulanan" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Bulanan</button>
                <button wire:click.prevent="setRange('tahunan')"
                    class="{{ $option_filter == "tahunan" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Tahunan</button>
                <button wire:click.prevent="setRange('custom')"
                    class="{{ $option_filter == "custom" ? 'tw-bg-white tw-rounded-full tw-px-3 tw-py-2 tw-text-gray-700 tw-font-semibold' : '' }}">Custom</button>
            </div>
        </div>
        @endif

        <div class="section-body {{ $agent->isDesktop() ? '-tw-mt-[20px]' : 'tw-mt-[15px]' }}">
            <div class="card">
                <center>
                    @if ($agent->isDesktop())
                    <div class="card-body">
                        <div>
                            <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                                {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }}
                                <span class="tw-text-gray-400 tw-font-normal">s/d</span>
                                {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
                            <div class="tw-inline-flex tw-space-x-2 tw-items-center tw-mt-3">
                                <input wire:model.live="start_date" type="date" class="form-control">
                                <button wire:click.prevent="refreshToday()"
                                    class="btn btn-primary tw-rounded-full tw-w-1/2"><i
                                        class="fas fa-sync"></i></button>
                                <input wire:model.live="end_date" type="date" class="form-control">
                            </div>
                        </div>
                        <button wire:click.prevent="exportPDF()" class="btn btn-danger tw-whitespace-nowrap tw-mt-4"><i
                                class="fas fa-file-pdf"></i>
                            Export
                            PDF</button>
                    </div>
                    @elseif ($agent->isMobile())
                    <div class="card-body">
                        <p class="tw-tracking-wider tw-text-[#34395e] tw-text-base tw-font-semibold">
                            {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }}
                            <span class="tw-text-gray-400 tw-font-normal">s/d</span>
                            {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
                        <div class="tw-grid tw-grid-cols-3 tw-items-center tw-mt-3">
                            <div>
                                <input wire:model.live="start_date" type="date" class="form-control">
                            </div>
                            <div>
                                <button wire:click.prevent="refreshToday()"
                                    class="btn btn-primary tw-rounded-full tw-w-1/2"><i
                                        class="fas fa-sync"></i></button>
                            </div>
                            <div>
                                <input wire:model.live="end_date" type="date" class="form-control">
                            </div>
                        </div>
                        <button class="btn btn-danger tw-mt-4 tw-whitespace-nowrap"><i class="fas fa-file-pdf"></i>
                            Export
                            PDF</button>
                    </div>
                    @endif
                </center>
            </div>
        </div>
    </section>
</div>
