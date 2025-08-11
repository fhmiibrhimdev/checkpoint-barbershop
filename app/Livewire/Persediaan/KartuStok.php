<?php

namespace App\Livewire\Persediaan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Persediaan;
use App\Services\GlobalDataService;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

class KartuStok extends Component
{
    use WithPagination;
    #[Title('Kartu Stok')]

    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $cabangs;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs    = $globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('persediaan')->select('tanggal', 'nama_item',  'persediaan.keterangan', 'persediaan.status', 'qty', DB::raw("SUM(CASE WHEN persediaan.status = 'Out' THEN -qty ELSE qty END) OVER(PARTITION BY persediaan.id_cabang, persediaan.id_produk ORDER BY tanggal ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS balancing"))
            ->join('produk', 'produk.id', 'persediaan.id_produk')
            ->where(function ($query) use ($search) {
                $query->where('tanggal', 'LIKE', $search);
                $query->orWhere('produk.nama_item', 'LIKE', $search);
                $query->orWhere('persediaan.keterangan', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('persediaan.id_cabang', $this->filter_id_cabang);
            })
            ->whereBetween('persediaan.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->orderBy('tanggal', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.persediaan.kartu-stok', compact('data'));
    }

    private function searchResetPage()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }

        $this->previousSearchTerm = $this->searchTerm;
    }
}
