<?php

namespace App\Livewire\Kasir\Persediaan;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KartuStok extends Component
{
    use WithPagination;
    #[Title('Kasir | Kartu Stok')]

    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $cabangs;
    public $id_cabang;

    public function mount()
    {
        $this->id_cabang = Auth::user()->id_cabang ?? null;
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
            ->where('persediaan.id_cabang', $this->id_cabang)
            ->whereBetween('persediaan.tanggal', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->orderBy('tanggal', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.kasir.persediaan.kartu-stok', compact('data'));
    }

    private function searchResetPage()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }

        $this->previousSearchTerm = $this->searchTerm;
    }
}
