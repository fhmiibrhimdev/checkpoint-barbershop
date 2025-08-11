<?php

namespace App\Livewire\Admin\DataMaster;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;
use App\Models\DaftarSupplier as ModelsDaftarSupplier;

class DaftarSupplier extends Component
{
    use WithPagination;
    #[Title('Daftar Supplier')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_cabang'           => 'required',
        'nama_supplier'       => 'required',
        'no_telp'             => '',
        'deskripsi'           => '',
        'gambar'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs;
    public $id_user, $id_cabang, $nama_supplier, $no_telp, $deskripsi, $gambar, $sisa_hutang;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        // Menyimpan instance dari service ke properti komponen
        $this->globalDataService = $globalDataService;

        // Ambil data global dari service
        $this->id_cabang  = Auth::user()->id_cabang;

        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = DB::table('daftar_supplier')->select('daftar_supplier.id', 'daftar_supplier.nama_supplier', 'daftar_supplier.sisa_hutang', 'daftar_supplier.no_telp', 'users.name')
            ->join('users', 'users.id', 'daftar_supplier.id_user')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', $search);
                $query->orWhere('nama_supplier', 'LIKE', $search);
                $query->orWhere('daftar_supplier.no_telp', 'LIKE', $search);
            })
            ->where('daftar_supplier.id_cabang', $this->id_cabang)
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.admin.data-master.daftar-supplier', compact('data'));
    }

    public function store()
    {
        $this->validate();
        try {
            ModelsDaftarSupplier::create([
                'id_user'             => Auth::user()->id,
                'id_cabang'           => $this->id_cabang,
                'nama_supplier'      => $this->nama_supplier,
                'no_telp'             => $this->no_telp,
                'deskripsi'           => $this->deskripsi,
                'gambar'              => $this->gambar,
                'sisa_hutang'              => $this->sisa_hutang,
            ]);

            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->isEditing        = true;
        $data = ModelsDaftarSupplier::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_cabang        = $data->id_cabang;
        $this->nama_supplier   = $data->nama_supplier;
        $this->no_telp          = $data->no_telp;
        $this->deskripsi        = $data->deskripsi;
        $this->gambar           = $data->gambar;
        $this->sisa_hutang      = $data->sisa_hutang;

        $this->dispatch('initSelect2');
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            ModelsDaftarSupplier::findOrFail($this->dataId)->update([
                'id_user'             => Auth::user()->id,
                'id_cabang'           => $this->id_cabang,
                'nama_supplier'       => $this->nama_supplier,
                'no_telp'             => $this->no_telp,
                'deskripsi'           => $this->deskripsi,
                'gambar'              => $this->gambar,
                'sisa_hutang'         => $this->sisa_hutang,
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
        ModelsDaftarSupplier::findOrFail($this->dataId)->delete();
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
        $this->dispatch('initSelect2');
    }

    private function resetInputFields()
    {
        $this->id_user             = Auth::user()->id;
        $this->nama_supplier       = '';
        $this->no_telp             = '62';
        $this->deskripsi           = '-';
        $this->gambar              = '-';
        $this->sisa_hutang         = 0;
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
