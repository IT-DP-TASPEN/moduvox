<?php

namespace App\Livewire\BusinessDate;

use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class Index extends Component
{
    use LogsActivity, WithLogout;

    public string $business_date = '';
    public string $system_date_display = '';
    public string $application_date_display = '';

    public function mount(): void
    {
        $this->business_date = (string) (config('app.business_date') ?? '');
        $this->refreshClockLabels();
        $this->logActivity('NAVIGATE', 'Tanggal Operasional');
    }

    public function saveBusinessDate()
    {
        $this->business_date = trim($this->business_date);
        $this->validate([
            'business_date' => 'nullable|date_format:Y-m-d',
        ]);

        $this->writeEnvValue('APP_BUSINESS_DATE', $this->business_date !== '' ? $this->business_date : null);
        Artisan::call('config:clear');

        $this->logActivity('UPDATE', $this->business_date !== ''
            ? "Mengubah tanggal operasional aplikasi ke {$this->business_date}"
            : 'Mengembalikan tanggal operasional aplikasi ke tanggal sistem');

        session()->flash('success', 'Tanggal operasional berhasil diperbarui.');
        return redirect()->route('system.business-date');
    }

    public function resetBusinessDate()
    {
        $this->business_date = '';
        return $this->saveBusinessDate();
    }

    public function render()
    {
        return view('livewire.business-date.index')->layout('layouts.app');
    }

    private function refreshClockLabels(): void
    {
        $timezone = config('app.timezone');
        $this->system_date_display = (new \DateTimeImmutable('now', new \DateTimeZone($timezone)))->format('d/m/Y H:i:s');
        $this->application_date_display = now()->format('d/m/Y H:i:s');
    }

    private function writeEnvValue(string $key, ?string $value): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';
        $line = $value === null ? "# {$key}=" : "{$key}={$value}";
        $pattern = "/^#?\\s*{$key}=.*$/m";

        $contents = preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents)
            : rtrim($contents) . PHP_EOL . $line . PHP_EOL;

        file_put_contents($path, $contents);
    }
}
