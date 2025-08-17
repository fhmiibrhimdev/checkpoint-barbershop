<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;

class LaporanPengeluaran extends Component
{
    use WithPagination;
    #[Title('Laporan Pengeluaran')]

    protected $listeners = [
        'delete'
    ];
    protected $rules = [
        'title' => 'required',
    ];
    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;
    public $cabangs, $filter_id_cabang;
    public $dataId, $title;
    public $option_filter; // 'harian', 'bulanan', 'tahunan', 'custom'
    public $start_date, $end_date;

    public function exportPDF()
    {
        return redirect()->route('laporan.pengeluaran.pdf', [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'id_cabang' => $this->filter_id_cabang,
        ]);
    }

    public function setRange(string $option, ?string $pivotDate = null): void
    {
        $this->option_filter = $option;

        // pakai tanggal acuan: dari input user kalau ada, else today
        $d = $pivotDate ? Carbon::parse($pivotDate) : Carbon::today();

        switch ($option) {
            case 'harian':
                $this->start_date = $d->toDateString();
                $this->end_date   = $d->toDateString();
                break;

            case 'bulanan':
                $this->start_date = $d->copy()->startOfMonth()->toDateString();
                $this->end_date   = $d->copy()->endOfMonth()->toDateString();
                break;

            case 'tahunan':
                $this->start_date = $d->copy()->startOfYear()->toDateString();
                $this->end_date   = $d->copy()->endOfYear()->toDateString();
                break;

            case 'custom':
            default:
                // biarkan user atur manual; jaga default aman
                $this->start_date ??= Carbon::today()->toDateString();
                $this->end_date   ??= Carbon::today()->toDateString();
                break;
        }
    }

    /** Dipanggil otomatis saat start_date berubah lewat input */
    public function updatedStartDate($value): void
    {
        if (!$value) return;

        // Saat mode bulanan/tahunan, snap end_date ke bulan/tahun yang sama dengan start_date
        if ($this->option_filter === 'bulanan') {
            $this->setRange('bulanan', $value);
        } elseif ($this->option_filter === 'tahunan') {
            $this->setRange('tahunan', $value);
        } elseif ($this->option_filter === 'harian') {
            $this->end_date = Carbon::parse($value)->toDateString();
        } else {
            // custom: jaga agar end_date >= start_date
            if (Carbon::parse($this->end_date)->lt(Carbon::parse($value))) {
                $this->end_date = $value;
            }
        }
    }

    /** Supaya kalau user ganti tab filter, ikut menghitung dari start_date yang ada */
    public function updatedOptionFilter($value): void
    {
        $this->setRange($value, $this->start_date ?: Carbon::today()->toDateString());
    }

    public function refreshToday()
    {
        $this->setRange('harian');
    }

    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs = $globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
        $this->setRange('harian');
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('cash_on_bank as cob')
            ->select([
                'cob.tanggal',
                'cob.no_referensi',
                'cob.keterangan',
                'cob.sumber_tabel',
                'cob.jumlah',
            ])
            // ->whereDate('kas.tanggal', Carbon::today())
            ->where('cob.jenis', 'Out')
            ->whereBetween(DB::raw('DATE(cob.tanggal)'), [$this->start_date, $this->end_date])
            ->where('cob.id_cabang', $this->filter_id_cabang)
            ->paginate($this->lengthData);

        return view('livewire.laporan.laporan-pengeluaran', compact('data'));
    }

    public function updatingLengthData()
    {
        $this->resetPage();
    }

    private function searchResetPage()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }

        $this->previousSearchTerm = $this->searchTerm;
    }
}
