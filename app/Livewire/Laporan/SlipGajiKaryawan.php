<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DaftarKaryawan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class SlipGajiKaryawan extends Component
{
    use WithPagination;
    #[Title('Laporan Slip Gaji Karyawan')]

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
    public $dataId, $id_karyawan;
    public $option_filter; // 'harian', 'bulanan', 'tahunan', 'custom'
    public $start_date, $end_date;

    public function exportPDF($no_referensi, $periode_mulai, $periode_selesai)
    {
        return redirect()->route('laporan.slip-gaji-karyawan.pdf', [
            'no_referensi' => $no_referensi,
            'start_date' => $periode_mulai,
            'end_date' => $periode_selesai,
            'id_karyawan' => $this->id_karyawan,
        ]);
    }

    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs = $globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
        $user = Auth::user();
        $this->id_karyawan = DaftarKaryawan::where('id_user', $user->id)->value('id');
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('slip_gaji')
            ->select(
                'no_referensi',
                'periode_mulai',
                'periode_selesai',
                'total_tunjangan',
                'total_potongan',
                'total_gaji',
            )
            ->where('slip_gaji.id_karyawan', $this->id_karyawan)
            ->paginate($this->lengthData);

        return view('livewire.laporan.slip-gaji-karyawan', compact('data'));
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
