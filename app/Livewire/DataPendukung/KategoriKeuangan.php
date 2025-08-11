<?php

namespace App\Livewire\DataPendukung;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\KategoriKeuangan as ModelsKategoriKeuangan;
use App\Services\GlobalDataService;

class KategoriKeuangan extends Component
{
    use WithPagination;
    #[Title('Kategori Keuangan')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'nama_kategori'       => 'required',
        'kategori'            => 'required',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    // public $cabangs;
    public $id_cabang, $nama_kategori, $kategori, $deskripsi, $header, $can_update_delete;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->globalDataService   = $globalDataService;
        // $this->cabangs             = $this->globalDataService->getCabangs();
        // $this->id_cabang           = '';
        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('kategori_keuangan')->select('kategori_keuangan.*')
            // ->join('cabang_lokasi', 'kategori_keuangan.id_cabang', '=', 'cabang_lokasi.id')
            ->where(function ($query) use ($search) {
                $query->where('nama_kategori', 'LIKE', $search);
                $query->orWhere('kategori', 'LIKE', $search);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.data-pendukung.kategori-keuangan', compact('data'));
    }

    public function store()
    {
        try {
            $this->validate();
            ModelsKategoriKeuangan::create([
                // 'id_cabang'       => $this->id_cabang,
                'nama_kategori'     => $this->nama_kategori,
                'kategori'          => $this->kategori,
                'deskripsi'         => $this->deskripsi,
                'header'            => $this->header,
                'can_update_delete' => $this->can_update_delete,
            ]);

            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing        = true;
        $data = ModelsKategoriKeuangan::where('id', $id)->first();
        $this->dataId           = $id;
        // $this->id_cabang    = $data->id_cabang;
        $this->nama_kategori    = $data->nama_kategori;
        $this->kategori         = $data->kategori;
        $this->deskripsi        = $data->deskripsi;
    }

    public function update()
    {
        try {
            $this->validate();

            if ($this->dataId) {
                ModelsKategoriKeuangan::findOrFail($this->dataId)->update([
                    // 'id_cabang'       => $this->id_cabang,
                    'nama_kategori'     => $this->nama_kategori,
                    'kategori'          => $this->kategori,
                    'deskripsi'         => $this->deskripsi,
                    'header'            => $this->header,
                    'can_update_delete' => $this->can_update_delete,
                ]);

                $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
                $this->dataId = null;
            }
        } catch (\Exception $e) {
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
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
        ModelsKategoriKeuangan::findOrFail($this->dataId)->delete();
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
        $this->nama_kategori       = '';
        $this->kategori            = '';
        $this->deskripsi           = '';
        $this->header              = 'no';
        $this->can_update_delete   = '1';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
