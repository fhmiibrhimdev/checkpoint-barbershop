<?php

namespace App\Livewire\DataPendukung;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\KategoriProduk;
use App\Models\KategoriSatuan;
use Livewire\Attributes\Title;
use App\Models\DaftarPelanggan;
use App\Models\KategoriKeuangan;
use App\Models\KategoriPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CabangLokasi as ModelsCabangLokasi;
use App\Models\DaftarKaryawan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CabangLokasi extends Component
{
    use WithPagination;
    #[Title('Cabang Lokasi')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'nama_cabang'         => 'required',
        'alamat'              => '',
        'status'              => 'required',
        'no_telp'             => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;

    public $nama_cabang, $subtitle_cabang, $alamat, $email, $syarat_nota_1, $template_pesan_pembayaran, $status, $no_telp;

    public function mount()
    {
        $this->nama_cabang                  = NULL;
        $this->subtitle_cabang              = NULL;
        $this->alamat                       = NULL;
        $this->email                        = NULL;
        $this->syarat_nota_1                = NULL;
        $this->template_pesan_pembayaran    = NULL;
        $this->status                       = NULL;
        $this->no_telp                      = NULL;
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = ModelsCabangLokasi::select('cabang_lokasi.*')
            ->where(function ($query) use ($search) {
                $query->where('nama_cabang', 'LIKE', $search);
                $query->orWhere('alamat', 'LIKE', $search);
                $query->orWhere('no_telp', 'LIKE', $search);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.data-pendukung.cabang-lokasi', compact('data'));
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $cabang = ModelsCabangLokasi::create([
                'nama_cabang'         => $this->nama_cabang,
                'alamat'              => $this->alamat,
                'status'              => $this->status,
                'no_telp'             => $this->no_telp,
            ]);

            // $this->createKategoriProduk($cabang->id);
            // $this->createKategoriKeuangan($cabang->id);
            // $this->createKategoriPembayaran($cabang->id);
            // $this->createKategoriSatuan($cabang->id);
            $this->createDaftarPelanggan($cabang->id);
            $this->createDaftarKaryawan($cabang->id);

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function createKategoriProduk($id)
    {
        $kategori_produk = [
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Produk Barbershop',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Jasa Barbershop',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Treatment',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Produk Umum',
                'deskripsi'           => '',
            ],
        ];

        KategoriProduk::insert($kategori_produk);
    }

    private function createKategoriKeuangan($id)
    {
        $kategori_keuangan = [
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Pemasukan',
                'kategori'            => 'Pemasukan',
            ],

            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Biaya Operasional',
                'kategori'            => 'Pengeluaran',
            ],
        ];

        KategoriKeuangan::insert($kategori_keuangan);
    }

    private function createKategoriPembayaran($id)
    {
        $kategori_pembayaran = [
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Tunai',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'QRIS',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_kategori'       => 'Transfer',
                'deskripsi'           => '',
            ],
        ];

        KategoriPembayaran::insert($kategori_pembayaran);
    }

    private function createKategoriSatuan($id)
    {
        $kategori_satuan = [
            [
                'id_cabang'           => $id,
                'nama_satuan'         => 'Pcs',
                'deskripsi'           => '',
            ],
            [
                'id_cabang'           => $id,
                'nama_satuan'         => 'Kali',
                'deskripsi'           => '',
            ],
        ];

        KategoriSatuan::insert($kategori_satuan);
    }

    private function createDaftarPelanggan($id_cabang)
    {
        DaftarPelanggan::create([
            'id_user'        => Auth::user()->id,
            'id_cabang'      => $id_cabang,
            'nama_pelanggan' => "UMUM",
            'no_telp'        => "62",
            "deskripsi"      => "Pelanggan Umum",
            "gambar"         => '-',
        ]);
    }

    private function createDaftarKaryawan($id_cabang)
    {
        $users = [
            [
                'name' => 'Cabang ' . $id_cabang . ' - Admin',
                'email' => 'cabang' . $id_cabang . '@admin.com',
                'role_id' => 2, // ganti sesuai role_id aslinya
            ],
            [
                'name' => 'Cabang ' . $id_cabang . ' - Kasir',
                'email' => 'cabang' . $id_cabang . '@kasir.com',
                'role_id' => 3,
            ],
            [
                'name' => 'Cabang ' . $id_cabang . ' - Capster',
                'email' => 'cabang' . $id_cabang . '@capster.com',
                'role_id' => 4,
            ],
        ];

        $roleMapping = [
            "1"  => "direktur",
            "2"  => "admin",
            "3"  => "kasir",
            "4"  => "capster",
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'id_cabang' => $id_cabang,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('1'),
                'active' => 1,
            ]);

            // Assign role
            DB::table('role_user')->insert([
                'role_id' => $userData['role_id'],
                'user_id' => $user->id,
                'user_type' => 'App\Models\User',
            ]);

            // Insert ke daftar_karyawan
            DaftarKaryawan::create([
                'id_user'    => $user->id,
                'id_cabang'  => $id_cabang,
                'saldo_kasbon'  => '0',
                'role_id'    => $roleMapping[$userData['role_id']],
                'tgl_lahir'  => date('Y-m-d'),
                'jk'         => '-',
                'alamat'     => '-',
                'no_telp'    => '62',
                'deskripsi'  => '-',
                'gambar'     => '-',
            ]);
        }
    }

    public function edit($id)
    {
        $this->isEditing                    = true;
        $data = ModelsCabangLokasi::where('id', $id)->first();
        $this->dataId                       = $id;
        $this->nama_cabang                  = $data->nama_cabang;
        $this->subtitle_cabang              = $data->subtitle_cabang;
        $this->alamat                       = $data->alamat;
        $this->email                        = $data->email;
        $this->syarat_nota_1                = $data->syarat_nota_1;
        $this->template_pesan_pembayaran    = $data->template_pesan_pembayaran;
        $this->status                       = $data->status;
        $this->no_telp                      = $data->no_telp;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            ModelsCabangLokasi::findOrFail($this->dataId)->update([
                'nama_cabang'               => $this->nama_cabang,
                'subtitle_cabang'           => $this->subtitle_cabang,
                'alamat'                    => $this->alamat,
                'email'                     => $this->email,
                'syarat_nota_1'             => $this->syarat_nota_1,
                'template_pesan_pembayaran' => $this->template_pesan_pembayaran,
                'status'                    => $this->status,
                'no_telp'                   => $this->no_telp,
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
        DB::beginTransaction();
        try {
            ModelsCabangLokasi::findOrFail($this->dataId)->delete();
            // KategoriProduk::where('id_cabang', $this->dataId)->delete();
            // KategoriKeuangan::where('id_cabang', $this->dataId)->delete();
            // KategoriPembayaran::where('id_cabang', $this->dataId)->delete();
            // KategoriSatuan::where('id_cabang', $this->dataId)->delete();
            DaftarPelanggan::where('id_cabang', $this->dataId)->delete();
            DaftarKaryawan::where('id_cabang', $this->dataId)->delete();

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
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
    }

    private function resetInputFields()
    {
        $this->nama_cabang         = '';
        $this->alamat              = '';
        $this->status              = '';
        $this->no_telp             = '';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
