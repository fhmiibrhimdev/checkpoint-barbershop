<?php

namespace App\Livewire\DataMaster;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Hash;
use App\Models\DaftarKaryawan as ModelsDaftarKaryawan;

class DaftarKaryawan extends Component
{
    use WithPagination;
    #[Title('Daftar Karyawan')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_cabang'           => 'required',
        'role_id'             => 'required',
        'name'                => 'required',
        'email'               => 'required|email|unique:users',
        'password'            => '',
        'tgl_lahir'           => '',
        'jk'                  => '-',
        'alamat'              => '',
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
    public $id_user, $id_cabang, $role_id, $name, $email, $password, $tgl_lahir, $jk, $alamat, $no_telp, $deskripsi, $gambar;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        // Menyimpan instance dari service ke properti komponen
        $this->globalDataService = $globalDataService;

        // Ambil data global dari service
        $this->cabangs    = $this->globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id;

        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = ModelsDaftarKaryawan::select('daftar_karyawan.id', 'daftar_karyawan.role_id', 'users.name', 'users.email', 'daftar_karyawan.no_telp', 'daftar_karyawan.saldo_kasbon')
            ->leftJoin('users', 'users.id', 'daftar_karyawan.id_user')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', $search);
                $query->orWhere('role_id', 'LIKE', $search);
                $query->orWhere('users.email', 'LIKE', $search);
                $query->orWhere('daftar_karyawan.no_telp', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                $query->where('daftar_karyawan.id_cabang', $this->filter_id_cabang);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.data-master.daftar-karyawan', compact('data'));
    }

    public function store()
    {
        $this->validate();
        DB::beginTransaction();

        try {
            // Simpan user
            $user = User::create([
                'id_cabang'         => $this->filter_id_cabang,
                'name'              => $this->name,
                'email'             => $this->email,
                'email_verified_at' => null,
                'password'          => Hash::make('1'), // default password
                'active'            => '0',
                'remember_token'    => null,
            ]);

            $user->addRole($this->role_id);

            // Simpan data karyawan
            ModelsDaftarKaryawan::create([
                'id_user'    => $user->id,
                'id_cabang'  => $this->filter_id_cabang,
                'saldo_kasbon'  => '0',
                'role_id'    => $this->role_id,
                'tgl_lahir'  => $this->tgl_lahir,
                'jk'         => $this->jk,
                'alamat'     => $this->alamat,
                'no_telp'    => $this->no_telp,
                'deskripsi'  => $this->deskripsi,
                'gambar'     => $this->gambar,
            ]);

            DB::commit(); // Commit transaksi
            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Something went wrong while store data.');
        }
    }

    public function edit($id)
    {
        $this->isEditing        = true;
        $this->dispatch('initSelect2');

        $data = ModelsDaftarKaryawan::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_user          = $data->id_user;
        $this->id_cabang        = $data->id_cabang;
        $this->role_id          = $data->role_id;
        $this->tgl_lahir        = $data->tgl_lahir;
        $this->jk               = $data->jk;
        $this->alamat           = $data->alamat;
        $this->no_telp          = $data->no_telp;
        $this->deskripsi        = $data->deskripsi;
        $this->gambar           = $data->gambar;

        $user = User::findOrFail($data->id_user);
        $this->name             = $user->name;
        $this->email            = $user->email;
        $this->password         = $user->password;
    }

    public function update()
    {
        $data = ModelsDaftarKaryawan::findOrFail($this->dataId);
        $userId = $data->id_user;

        $this->validate([
            'id_cabang'           => 'required',
            'role_id'             => 'required',
            'name'                => 'required',
            'email'                => 'required|email|unique:users,email,' . $userId,
            'password'            => '',
            'tgl_lahir'           => '',
            'jk'                  => '-',
            'alamat'              => '',
            'no_telp'             => '',
            'deskripsi'           => '',
            'gambar'              => '',
        ]);

        DB::beginTransaction();

        try {
            $roleMapping = [
                "direktur"  => "1",
                "admin"     => "2",
                "kasir"     => "3",
                "capster"   => "4",
            ];

            if ($this->dataId) {
                $user = User::findOrFail($data->id_user);

                // Update user name & email saja
                $user->update([
                    'name'      => $this->name,
                    'email'     => $this->email,
                ]);

                DB::table('role_user')
                    ->where('user_id', $user->id) // Pastikan hanya yang sesuai dengan user_id
                    ->update([
                        'role_id' => $roleMapping[$this->role_id],
                    ]);

                // Update data karyawan
                $data->update([
                    'role_id'    => $this->role_id,
                    'tgl_lahir'  => $this->tgl_lahir,
                    'jk'         => $this->jk,
                    'alamat'     => $this->alamat,
                    'no_telp'    => $this->no_telp,
                    'deskripsi'  => $this->deskripsi,
                    'gambar'     => $this->gambar,
                ]);

                DB::commit(); // Commit transaksi
                $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
                $this->dataId = null;
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
        DB::beginTransaction();

        try {
            $data = ModelsDaftarKaryawan::findOrFail($this->dataId);
            // Hapus user terkait
            User::findOrFail($data->id_user)->delete();
            // Hapus role_user dengan where (bukan findOrFail)
            DB::table('role_user')->where('user_id', $data->id_user)->delete();
            // Hapus data karyawan
            $data->delete();
            DB::commit(); // Commit transaksi
            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Something went wrong while deleting data.');
        }
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
        // $this->id_cabang           = $this->cabangs->first()->id;
        $this->name                = '';
        $this->email               = '';
        $this->password            = '1';
        $this->tgl_lahir           = date('Y-m-d');
        $this->jk                  = '-';
        $this->alamat              = '-';
        $this->no_telp             = '62';
        $this->deskripsi           = '-';
        $this->gambar              = '-';
        $this->role_id             = 'admin';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
