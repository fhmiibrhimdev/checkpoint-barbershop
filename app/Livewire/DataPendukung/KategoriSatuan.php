<?php

namespace App\Livewire\DataPendukung;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use App\Models\KategoriSatuan as ModelsKategoriSatuan;

class KategoriSatuan extends Component
{
    use WithPagination;
    #[Title('Kategori Satuan')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_cabang'         => 'required',
        'nama_satuan'         => 'required',
        'deskripsi'           => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs;
    public $id_cabang, $nama_satuan, $deskripsi;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService   = $globalDataService;
        // $this->cabangs             = $this->globalDataService->getCabangs();
        // $this->id_cabang           = '';
        $this->nama_satuan         = '';
        $this->deskripsi           = '';
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('kategori_satuan')->select('kategori_satuan.id', 'kategori_satuan.nama_satuan')
            // ->join('cabang_lokasi', 'kategori_satuan.id_cabang', '=', 'cabang_lokasi.id')
            ->where(function ($query) use ($search) {
                $query->where('nama_satuan', 'LIKE', $search);
                // $query->orWhere('deskripsi', 'LIKE', $search);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.data-pendukung.kategori-satuan', compact('data'));
    }

    public function store()
    {
        $this->validate();

        ModelsKategoriSatuan::create([
            // 'id_cabang'       => $this->id_cabang,
            'nama_satuan'         => $this->nama_satuan,
            'deskripsi'           => $this->deskripsi,
        ]);

        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing        = true;
        $data = ModelsKategoriSatuan::where('id', $id)->first();
        $this->dataId           = $id;
        // $this->id_cabang    = $data->id_cabang;
        $this->nama_satuan      = $data->nama_satuan;
        // $this->deskripsi        = $data->deskripsi;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            ModelsKategoriSatuan::findOrFail($this->dataId)->update([
                // 'id_cabang'       => $this->id_cabang,
                'nama_satuan'         => $this->nama_satuan,
                // 'deskripsi'           => $this->deskripsi,
            ]);

            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
            $this->dataId = null;
        }
    }

    public function deleteConfirm($id)
    {
        $this->dataId = $id;
        $this->dispatch('swal:confirm', [
            'type'      => 'warning',
            'message'   => 'Are you sure?',
            'text'      => 'If you delete the data, it cannot be restored!'
        ]);
    }

    public function delete()
    {
        ModelsKategoriSatuan::findOrFail($this->dataId)->delete();
        $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
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

    public function isEditingMode($mode)
    {
        $this->isEditing = $mode;
        $this->initSelect2();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function updated()
    {
        $this->initSelect2();
    }

    private function resetInputFields()
    {
        // $this->id_cabang           = '';
        $this->nama_satuan         = '';
        $this->deskripsi           = '';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
