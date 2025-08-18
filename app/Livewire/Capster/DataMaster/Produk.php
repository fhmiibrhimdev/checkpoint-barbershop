<?php

namespace App\Livewire\Capster\DataMaster;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Produk as ModelsProduk;

class Produk extends Component
{
    use WithPagination;
    #[Title('Capster |Produk')]

    protected $paginationTheme = 'bootstrap';
    protected $globalDataService;

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_cabang'           => 'required',
        'id_user'             => 'required',
        'id_kategori'         => 'required',
        'id_satuan'           => 'required',
        'kode_item'           => '',
        'nama_item'           => 'required',
        'harga_jasa'          => '',
        'komisi'              => 'required|integer',
        'harga_pokok'         => '',
        'harga_jual'          => '',
        'stock'               => '',
        'deskripsi'           => '',
        'gambar'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $cabangs, $kategoris, $satuans, $karyawans;
    public $dataKategoris, $komisi_karyawan, $komisi_karyawan_awal = [];
    public $isKomisi, $isProdukUmum = false, $total_karyawan;
    public $id_cabang, $id_user, $id_kategori, $id_satuan, $kode_item, $nama_item, $harga_jasa, $komisi, $harga_pokok, $harga_jual, $stock, $deskripsi, $gambar;

    // Menggunakan mount untuk inject service
    public function mount(GlobalDataService $globalDataService)
    {
        // Menyimpan instance dari service ke properti komponen
        $this->globalDataService = $globalDataService;

        // Ambil data global dari service
        $this->id_cabang =  Auth::user()->id_cabang;
        $this->kategoris  = $this->globalDataService->getKategorisCustom()->whereIn('id', ['1', '2', '3'])->get();
        $this->satuans    = $this->globalDataService->getSatuans();

        $this->karyawans = $this->globalDataService->getKaryawansCustom($this->id_cabang ?? $this->cabangs->first()->id);
        // dd($this->karyawans);

        // Init komisi_karyawan default kosong
        foreach ($this->karyawans as $karyawan) {
            $this->komisi_karyawan[$karyawan->id] = null;
        }

        // dd($this->komisi_karyawan);

        // dd($this->karyawans);

        $this->resetInputFields();
    }

    public function updatedIdCabang(GlobalDataService $globalDataService)
    {
        $this->karyawans = $globalDataService->getKaryawansCustom($this->id_cabang);
    }

    public function updatedIdKategori()
    {
        if (in_array($this->id_kategori, ['1', '2', '3'])) {
            $this->id_satuan = 1;
            $this->isKomisi = true;
        } else {
            $this->id_satuan = 2;
            $this->isKomisi = false;
        }

        $this->id_kategori == "4" ? $this->isProdukUmum = true : $this->isProdukUmum = false;
        // dd($this->isKomisi);
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $this->total_karyawan = DB::table('daftar_karyawan')
            ->select('id_cabang', DB::raw('COUNT(*) as total'))
            ->where('role_id', 'capster')
            ->where('id_cabang', $this->id_cabang)
            ->groupBy('id_cabang')
            ->pluck('total', 'id_cabang') // hasil: [id_cabang => total]
            ->toArray();

        $komisi_data = DB::table('komisi')
            ->select('id_produk', DB::raw('count(*) as jumlah_komisi'))
            ->where(function ($q) {
                $q->whereNotNull('komisi_persen')
                    ->where('komisi_persen', '!=', 0)
                    ->where('komisi_persen', '!=', '');
            })
            ->groupBy('id_produk')
            ->pluck('jumlah_komisi', 'id_produk')
            ->toArray();

        $data = DB::table('produk')->select('produk.id', 'produk.id_cabang', 'produk.nama_item', 'produk.harga_pokok', 'produk.harga_jasa', 'produk.komisi', 'produk.stock', 'produk.deskripsi', 'produk.gambar', 'kategori_produk.nama_kategori', 'kategori_satuan.nama_satuan')
            ->leftJoin('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->leftJoin('kategori_satuan', 'kategori_satuan.id', 'produk.id_satuan')
            ->whereIn('id_kategori', ['1', '2', '3'])
            ->where(function ($query) use ($search) {
                // $query->where('nama_kategori', 'LIKE', $search);
                $query->where('nama_item', 'LIKE', $search);
                $query->orWhere('produk.deskripsi', 'LIKE', $search);
            })
            ->where('produk.id_cabang', $this->id_cabang)
            ->orderBy('nama_kategori', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.capster.data-master.produk', compact('data', 'komisi_data'));
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Simpan data produk
            $produk = ModelsProduk::create([
                'id_cabang'    => $this->id_cabang,
                'id_user'      => $this->id_user,
                'id_kategori'  => $this->id_kategori,
                'id_satuan'    => $this->id_satuan,
                'kode_item'    => $this->kode_item,
                'nama_item'    => $this->nama_item,
                'harga_jasa'   => $this->harga_jasa,
                'komisi'       => $this->komisi,
                'harga_pokok'  => $this->harga_pokok,
                'harga_jual'   => $this->harga_jual,
                'stock'        => $this->stock,
                'deskripsi'    => $this->deskripsi,
                'gambar'       => $this->gambar,
            ]);

            // Siapkan data untuk insert ke pivot
            $pivotData = [];

            foreach ($this->komisi_karyawan as $idKaryawan => $persen) {
                if (!is_null($persen)) {
                    $pivotData[] = [
                        'id_karyawan' => $idKaryawan,
                        'id_produk' => $produk->id,
                        'komisi_persen' => $persen,
                    ];
                }
            }

            // Insert langsung ke tabel pivot
            DB::table('komisi')->insert($pivotData);

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Produk dan komisi berhasil disimpan.');
            $this->dispatch('setBackNavs');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(GlobalDataService $globalDataService, $id)
    {
        $this->isEditing        = true;
        $this->dispatch('initSelect2');
        $data = ModelsProduk::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_cabang        = $data->id_cabang;
        $this->id_user          = $data->id_user;
        $this->id_kategori      = $data->id_kategori;
        $this->id_satuan        = $data->id_satuan;
        $this->kode_item        = $data->kode_item;
        $this->nama_item        = $data->nama_item;
        $this->harga_jasa       = $data->harga_jasa;
        $this->komisi           = $data->komisi;
        $this->harga_pokok      = $data->harga_pokok;
        $this->harga_jual       = $data->harga_jual;
        $this->stock            = $data->stock;
        $this->deskripsi        = $data->deskripsi;
        $this->gambar           = $data->gambar;
        $this->updatedIdKategori();

        $this->karyawans = $globalDataService->getKaryawansCustom($this->id_cabang);

        // 🟡 Ambil komisi dari pivot dan isi ke $komisi_karyawan
        $komisi = DB::table('komisi')
            ->join('daftar_karyawan', 'komisi.id_karyawan', '=', 'daftar_karyawan.id')
            ->where('id_cabang', $this->id_cabang)
            ->select('komisi.*')
            ->where('id_produk', $id)
            ->get();

        foreach ($komisi as $row) {
            $this->komisi_karyawan[$row->id_karyawan] = $row->komisi_persen;
            $this->komisi_karyawan_awal[$row->id_karyawan] = $row->komisi_persen;
        }
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            DB::beginTransaction();

            try {
                // 1. Update data produk
                ModelsProduk::findOrFail($this->dataId)->update([
                    // 'id_cabang'           => $this->id_cabang,
                    'id_user'             => Auth::user()->id,
                    'id_satuan'           => $this->id_satuan,
                    // 'kode_item'           => $this->kode_item,
                    'nama_item'           => $this->nama_item,
                    'harga_jasa'          => $this->harga_jasa,
                    'komisi'              => $this->komisi,
                    'harga_pokok'         => $this->harga_pokok,
                    // 'harga_jual'          => $this->harga_jual,
                    // 'stock'               => $this->stock,
                    'deskripsi'           => $this->deskripsi,
                    // 'gambar'              => $this->gambar,
                ]);

                // Ambil dari properti yang sudah diisi saat edit
                $komisiLama = $this->komisi_karyawan_awal ?? [];
                $komisiBaru = $this->komisi_karyawan ?? [];

                if ($komisiBaru !== $komisiLama) {
                    DB::table('komisi')->where('id_produk', $this->dataId)->delete();

                    $dataInsert = [];
                    foreach ($komisiBaru as $id_karyawan => $persen) {
                        $dataInsert[] = [
                            'id_produk'      => $this->dataId,
                            'id_karyawan'    => $id_karyawan,
                            'komisi_persen'  => $persen,
                        ];
                    }

                    if (!empty($dataInsert)) {
                        DB::table('komisi')->insert($dataInsert);
                    }
                }

                DB::commit();

                $this->dispatchAlert('success', 'Success!', 'Produk & komisi berhasil diperbarui.');
                $this->dispatch('setBackNavs');
                $this->dataId = null;
            } catch (\Exception $e) {
                DB::rollBack();
                // Optional: kirim ke log kalau perlu
                Log::error('Gagal update produk: ' . $e->getMessage());
                $this->dispatchAlert('error', 'Gagal!', 'Terjadi kesalahan: ' . $e->getMessage());
            }
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
            // 1. Hapus komisi dulu
            DB::table('komisi')->where('id_produk', $this->dataId)->delete();

            // 2. Hapus produk
            ModelsProduk::findOrFail($this->dataId)->delete();

            DB::commit();

            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal hapus produk: ' . $e->getMessage());
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
        $this->dispatch('initSelect2');
        $this->updatedIdKategori();
    }

    private function resetInputFields()
    {
        $this->id_user             = Auth::user()->id;
        $this->id_kategori         = $this->kategoris->first()->id;
        $this->id_satuan           = $this->satuans->first()->id;
        $this->kode_item           = NULL;
        $this->nama_item           = '';
        $this->harga_jasa          = '0';
        $this->komisi              = '0';
        $this->harga_pokok         = NULL;
        $this->harga_jual          = NULL;
        $this->stock               = '0';
        $this->deskripsi           = '-';
        $this->gambar              = NULL;

        $this->komisi_karyawan     = [];
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
        $this->dispatch('setBackNavs');
    }
}
