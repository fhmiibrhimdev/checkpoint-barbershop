<?php

namespace App\Livewire\Admin\Keuangan;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class CashOnBank extends Component
{
    use WithPagination;
    #[Title('Admin | Cash On Bank')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_cabang'           => '',
        'id_user'             => '',
        'tanggal'             => 'required',
        'id_kategori_keuangan' => 'required',
        'jumlah'              => 'required',
        'status'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    /*
     * @var \Illuminate\Support\Collection
     */
    public $cabangs, $keuangans;
    public $id_cabang, $tanggal, $no_referensi, $jenis, $jumlah, $keterangan, $sumber_tabel, $id_sumber;
    public $filter_id_cabang = '';

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        // $this->cabangs           = $this->globalDataService->getCabangs();
        // $this->id_cabang         = $this->globalDataService->getCabangs()->first()->id ?? '';
        // $this->filter_id_cabang = $this->cabangs->first()->id ?? '';
        $this->filter_id_cabang = Auth::user()->id_cabang ?? '';
        $this->tanggal           = date('Y-m-d H:i:s');
        $this->no_referensi      = '';
        $this->jenis             = '';
        $this->jumlah            = '0';
        $this->keterangan        = '-';
        $this->sumber_tabel      = '';
        $this->id_sumber         = '';
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $running = DB::table('cash_on_bank as cob')
            ->select([
                'cob.id',
                'cob.tanggal',
                'cob.no_referensi',
                'cob.keterangan',
                'cob.sumber_tabel',
                'cob.jenis',
                'cob.jumlah',
                'cob.created_at',
                DB::raw("CASE WHEN cob.jenis = 'In'  THEN cob.jumlah ELSE 0 END AS pemasukan"),
                DB::raw("CASE WHEN cob.jenis = 'Out' THEN cob.jumlah ELSE 0 END AS pengeluaran"),
                DB::raw("
                SUM(
                    CASE WHEN cob.jenis = 'In' THEN cob.jumlah ELSE -cob.jumlah END
                ) OVER (ORDER BY cob.tanggal ASC, cob.id ASC)
                AS saldo_akhir
            "),
            ])
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('cob.id_cabang', $this->filter_id_cabang);
            })
            ->whereBetween('cob.tanggal', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString()
            ])
            ->where('cob.no_referensi', 'like', '%' . $this->searchTerm . '%');

        $data = DB::query()
            ->fromSub($running, 'r')
            ->orderBy('r.tanggal', 'DESC')
            ->orderBy('r.id', 'DESC')
            ->paginate($this->lengthData);

        return view('livewire.admin.keuangan.cash-on-bank', compact('data'));
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

    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type'      => $type,
            'message'   => $message,
            'text'      => $text
        ]);

        $this->resetInputFields();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function refreshData()
    {
        $this->render();
    }

    public function updated()
    {
        $this->dispatch('initSelect2');
    }
}
