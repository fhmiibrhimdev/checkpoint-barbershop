<?php

namespace App\Livewire\Kasir\Keuangan;

use Carbon\Carbon;
use App\Models\Kas;
use Livewire\Component;
use App\Models\CashOnBank;
use App\Models\KasCounter;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

class KasMasuk extends Component
{
    use WithPagination;
    #[Title('Kasir | Kas Masuk')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'no_referensi'        => '',
        'id_pembuat'          => '',
        'tanggal'             => 'required',
        'keterangan'          => 'required',
        'jumlah'              => 'required',
        'id_kategori_keuangan'     => 'required',
        'status'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $kategori_keuangans, $cabangs;
    public $id_cabang, $no_referensi, $id_pembuat, $tanggal, $keterangan, $jumlah, $id_kategori_keuangan, $status;
    public $filter_id_cabang;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->kategori_keuangans = $globalDataService->getKategoriKeuangan('Pemasukan');
        // $this->cabangs = $globalDataService->getCabangs();
        // dd($this->kategori_keuangans);
        $user = Auth::user();
        $this->filter_id_cabang    = $user->id_cabang;
        $this->id_pembuat          = $user->id;
        // $this->id_cabang           = $user->id_cabang;
        $this->id_kategori_keuangan = $this->kategori_keuangans->first()->id ?? '';
        $this->status              = 'In';
        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = Kas::select('kas.id', 'kas.no_referensi', 'kas.tanggal', 'kas.keterangan', 'kas.jumlah', 'kategori_keuangan.nama_kategori', 'kas.id_kategori_keuangan', 'users.name as nama_pembuat')
            ->join('kategori_keuangan', 'kas.id_kategori_keuangan', '=', 'kategori_keuangan.id')
            ->join('users', 'kas.id_pembuat', '=', 'users.id')
            ->where(function ($query) use ($search) {
                $query->where('no_referensi', 'LIKE', $search);
                $query->orWhere('users.name', 'LIKE', $search);
            })
            ->when($this->filter_id_cabang, function ($query) {
                return $query->where('kas.id_cabang', $this->filter_id_cabang);
            })
            ->where('status', 'In')
            ->orderBy('id', 'DESC')
            ->paginate($this->lengthData);

        return view('livewire.kasir.keuangan.kas-masuk', compact('data'));
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $no_referensi = $this->generateNoKas($this->filter_id_cabang, $this->status);

            $this->no_referensi = $this->id_kategori_keuangan == "1" ? 'STTN/' . $this->filter_id_cabang . '/' . date('dmy') : $no_referensi;

            // Insert kas
            $kas = Kas::create([
                'id_cabang'             => $this->filter_id_cabang,
                'no_referensi'          => $this->no_referensi,
                'id_pembuat'            => $this->id_pembuat,
                'tanggal'               => $this->tanggal,
                'keterangan'            => $this->keterangan,
                'jumlah'                => $this->jumlah,
                'id_kategori_keuangan'  => $this->id_kategori_keuangan,
                'status'                => $this->status,
            ]);

            $this->id_kategori_keuangan == "1" ? $sumber_tabel = 'Setor Tunai' : $sumber_tabel = 'Kas Masuk';

            CashOnBank::create([
                'id_cabang'     => $this->filter_id_cabang,
                'tanggal'       => $this->tanggal,
                'sumber_tabel'  => $sumber_tabel,
                'no_referensi'  => $kas->no_referensi,
                'jenis'         => 'In',
                'jumlah'        => $this->jumlah,
                'keterangan'    => $this->keterangan,
                'id_sumber'     => $kas->id,
            ]);

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->initSelect2();
        $this->isEditing        = true;
        $data = Kas::where('id', $id)->first();
        $this->dataId           = $id;
        $this->tanggal          = $data->tanggal;
        $this->keterangan       = $data->keterangan;
        $this->jumlah           = $data->jumlah;
        $this->id_kategori_keuangan  = $data->id_kategori_keuangan;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            DB::beginTransaction();
            try {
                // Ambil data kas yang mau diupdate dengan lock
                $kas = Kas::lockForUpdate()->findOrFail($this->dataId);

                // Update data kas
                $kas->update([
                    'tanggal'               => $this->tanggal,
                    'keterangan'            => $this->keterangan,
                    'jumlah'                => $this->jumlah,
                    'id_kategori_keuangan'  => $this->id_kategori_keuangan,
                ]);

                CashOnBank::where([
                    'no_referensi' => $kas->no_referensi,
                    'id_sumber' => $kas->id,
                ])->update([
                    'tanggal'       => $this->tanggal,
                    'jumlah'        => $this->jumlah,
                    'keterangan'    => $this->keterangan,
                ]);

                DB::commit();
                $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
                $this->dataId = null;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
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
            // Lock data kas yang mau dihapus
            $kas = Kas::lockForUpdate()->findOrFail($this->dataId);

            // Kalau setor tunai
            if ($kas->id_kategori_keuangan == 1) {
                // Lock cash_on_bank dengan sumber_tabel 'Setor Tunai'
                DB::table('cash_on_bank')
                    ->where([
                        'no_referensi' => $kas->no_referensi,
                        'id_sumber' => $kas->id,
                        'sumber_tabel' => 'Setor Tunai',
                    ])
                    ->lockForUpdate()
                    ->get();

                // Hapus cash_on_bank terkait
                CashOnBank::where([
                    'no_referensi' => $kas->no_referensi,
                    'id_sumber' => $kas->id,
                    'sumber_tabel' => 'Setor Tunai',
                ])->delete();
            } else {
                // Lock cash_on_bank dengan sumber_tabel 'Kas Masuk'
                DB::table('cash_on_bank')
                    ->where([
                        'no_referensi' => $kas->no_referensi,
                        'id_sumber' => $kas->id,
                        'sumber_tabel' => 'Kas Masuk',
                    ])
                    ->lockForUpdate()
                    ->get();

                // Hapus cash_on_bank terkait
                CashOnBank::where([
                    'no_referensi' => $kas->no_referensi,
                    'id_sumber' => $kas->id,
                    'sumber_tabel' => 'Kas Masuk',
                ])->delete();
            }

            // Hapus kas
            $kas->delete();

            DB::commit();
            $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
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
        $this->initSelect2();
        $this->isEditing = $mode;
    }

    private function resetInputFields()
    {
        $this->no_referensi        = '';
        $this->tanggal             = date('Y-m-d');
        if ($this->id_kategori_keuangan == 1) {
            $this->keterangan      = 'SETOR TUNAI';
        } else {
            $this->keterangan      = '';
        }
        $this->jumlah              = '0';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }

    public function updatedIdKategoriKeuangan()
    {
        if ($this->id_kategori_keuangan == 1) {
            $this->keterangan      = 'SETOR TUNAI';
        } else {
            $this->keterangan      = '';
        }
        $this->initSelect2();
    }

    public function updated()
    {
        $this->initSelect2();
    }

    public function initSelect2()
    {
        $this->dispatch('initSelect2');
    }

    public function generateNoKas($id_cabang, $status)
    {
        return DB::transaction(function () use ($id_cabang, $status) {
            $tanggal = Carbon::now()->startOfDay();

            $counter = KasCounter::where('id_cabang', $id_cabang)
                ->where('status', $status)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = KasCounter::create([
                    'id_cabang' => $id_cabang,
                    'status' => $status,
                    'tanggal' => $tanggal,
                    'nomor_terakhir' => 1,
                ]);
            } else {
                $counter->increment('nomor_terakhir');
            }

            $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);
            $tglFormat = $tanggal->format('dmy');

            // Tentukan prefix berdasarkan status
            $prefix = $status === 'In' ? 'KSIN' : 'KASO';

            return "{$prefix}/{$id_cabang}/{$tglFormat}/{$nomorUrut}";
        });
    }
}
