<?php

namespace App\Livewire\Pengaturan;

use Livewire\Component;
use App\Models\CabangLokasi;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Http;

class ProfileUsaha extends Component
{
    #[Title('Profile Usaha')]

    public $filter_id_cabang;
    public $cabangs;
    public $nama_cabang, $subtitle_cabang, $alamat, $no_telp, $email, $cred_id;
    public $template_pesan_booking, $template_pesan_belum_lunas, $template_pesan_lunas, $template_pesan_dibatalkan;
    public $qrcodeHtml, $waBase, $statusConnected;

    public function mount(GlobalDataService $globalDataService)
    {
        // Load global data for the component
        $this->cabangs = $globalDataService->getCabangs();
        $this->filter_id_cabang = $this->cabangs->first()->id ?? null;
        $this->waBase = $globalDataService->waBase();

        // dd($this->statusConnected);

        // dd($this->filter_id_cabang);
        $this->updatedFilterIdCabang();
        // dd($this->statusConnected);
    }

    public function checkConnectionWa()
    {
        $raw  = @file_get_contents("{$this->waBase}/get-state?cred_id={$this->cred_id}");
        $json = $raw !== false ? json_decode($raw, true) : null;
        $this->statusConnected = is_string($json) ? $json === 'connected'
            : (is_array($json) ? (($json['state'] ?? null) === 'connected') : false);
    }

    public function render()
    {
        return view('livewire.pengaturan.profile-usaha');
    }

    public function update()
    {
        try {
            $data = CabangLokasi::findOrFail($this->filter_id_cabang);

            $data->update([
                'nama_cabang' => $this->nama_cabang,
                'subtitle_cabang' => $this->subtitle_cabang,
                'alamat' => $this->alamat,
                'no_telp' => $this->no_telp,
                'email' => $this->email,
                'template_pesan_booking' => $this->template_pesan_booking,
                'template_pesan_belum_lunas' => $this->template_pesan_belum_lunas,
                'template_pesan_lunas' => $this->template_pesan_lunas,
                'template_pesan_dibatalkan' => $this->template_pesan_dibatalkan,
                'cred_id' => $this->cred_id,
            ]);

            $this->dispatchAlert('success', 'Berhasil!', 'Profile usaha berhasil diperbarui.');
        } catch (\Exception $e) {
            $this->dispatchAlert('error', 'Error!', 'Terjadi kesalahan: ' . $e->getMessage());
            return;
        }
    }

    public function updatedFilterIdCabang()
    {
        $data = DB::table('cabang_lokasi')
            ->where('id', $this->filter_id_cabang)
            ->first();

        $this->nama_cabang = $data->nama_cabang;
        $this->subtitle_cabang = $data->subtitle_cabang;
        $this->alamat = $data->alamat;
        $this->no_telp = $data->no_telp;
        $this->email = $data->email;
        $this->template_pesan_booking = $data->template_pesan_booking;
        $this->template_pesan_belum_lunas = $data->template_pesan_belum_lunas;
        $this->template_pesan_lunas = $data->template_pesan_lunas;
        $this->template_pesan_dibatalkan = $data->template_pesan_dibatalkan;
        $this->cred_id = $data->cred_id;

        $this->checkConnectionWa();
    }

    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type'      => $type,
            'message'   => $message,
            'text'      => $text
        ]);
    }

    public function generateQRCode()
    {
        $url = "http://0.0.0.0:5000/get-qrcode?cred_id={$this->cred_id}";
        $response = Http::get($url);

        if ($response->successful()) {
            // isi responsenya langsung <img ...> 
            $this->qrcodeHtml = $response->body();
        } else {
            $this->qrcodeHtml = '<p class="text-danger">Gagal ambil QR Code</p>';
        }
    }
}
