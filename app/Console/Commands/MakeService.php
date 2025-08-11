<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    // Nama command yang akan digunakan
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class in app/Services';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $serviceName = $this->argument('name');  // Dapatkan nama service dari argumen
        $servicePath = app_path("Services/{$serviceName}.php"); // Tentukan path untuk file service

        // Jika service sudah ada
        if (File::exists($servicePath)) {
            $this->error("Service {$serviceName} already exists!");
            return;
        }

        // Template kode dasar untuk service
        $serviceTemplate = "<?php

namespace App\Services;

class {$serviceName}
{
    // Tambahkan fungsi service di sini jika perlu
}
";

        // Buat file service baru
        File::put($servicePath, $serviceTemplate);

        // Output ke terminal
        $this->info("Service {$serviceName} has been created at {$servicePath}");
    }
}
