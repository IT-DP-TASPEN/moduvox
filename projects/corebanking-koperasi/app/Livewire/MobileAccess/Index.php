<?php

namespace App\Livewire\MobileAccess;

use App\Models\Cif;
use App\Models\MobileAccess;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, LogsActivity;

    // ── Search & Filter ─────────────────────────────────────────────
    public string $search  = '';
    public string $filterStatus = ''; // '', '1' = aktif, '0' = blokir

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Manajemen Akses Mobile');
    }

    // ── Modal: Registrasi Baru ───────────────────────────────────────
    public bool $showRegisterModal = false;
    public string $reg_cif_no   = '';

    // ── Modal: Reset PIN ────────────────────────────────────────────
    public bool $showResetPinModal   = false;
    public int|null $resetTargetId   = null;
    public string $resetTargetName   = '';
    public string $resetNewPin       = '';

    // ── Modal: Reset Password ───────────────────────────────────────
    public bool $showResetPasswordModal = false;
    public int|null $resetPassTargetId = null;
    public string $resetPassTargetName = '';
    public string $resetNewPassword = '';

    // ── Modal: Ganti Status ─────────────────────────────────────────
    public bool $showStatusModal    = false;
    public int|null $statusTargetId = null;
    public string $statusTargetName = '';
    public bool $newStatus          = false;

    protected function rules(): array
    {
        return [
            'reg_cif_no'   => 'required|string|exists:cifs,cif_no',
        ];
    }

    protected array $validationAttributes = [
        'reg_cif_no'   => 'CIF No',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    // ─────────────────────────────────────────────────────────────────
    //  Registrasi akun mobile baru (admin hanya daftarkan CIF)
    // ─────────────────────────────────────────────────────────────────

    public function openRegisterModal(): void
    {
        $this->reset(['reg_cif_no']);
        $this->resetErrorBag();
        $this->showRegisterModal = true;
    }

    public function register(): void
    {
        $this->validate();

        $cif = Cif::where('cif_no', $this->reg_cif_no)->firstOrFail();

        if (MobileAccess::where('cif_id', $cif->id)->exists()) {
            $this->addError('reg_cif_no', 'CIF ini sudah memiliki akun mobile banking.');
            return;
        }

        MobileAccess::create([
            'cif_id'          => $cif->id,
            'cif_no'          => $cif->cif_no,
            'username'        => null,
            'password_hash'   => null,
            'pin_hash'        => null,
            'is_active'       => true,
            'wrong_pin_count' => 0,
            'created_by'      => auth()->id(),
            'updated_by'      => auth()->id(),
        ]);

        $this->showRegisterModal = false;
        $this->logActivity('CREATE', "Mendaftarkan akses mobile banking (pending aktivasi) untuk CIF: {$cif->cif_no}");
        session()->flash('success', "Akses mobile untuk nasabah {$cif->name} berhasil didaftarkan. Nasabah dapat melakukan aktivasi mandiri.");
        $this->resetPage();
    }

    // ─────────────────────────────────────────────────────────────────
    //  Reset PIN
    // ─────────────────────────────────────────────────────────────────

    public function openResetPin(int $id): void
    {
        $mobile = MobileAccess::with('cif')->findOrFail($id);
        $this->resetTargetId   = $id;
        $this->resetTargetName = $mobile->cif?->name ?? ($mobile->username ?: $mobile->cif_no);
        $this->resetNewPin     = '';
        $this->resetErrorBag();
        $this->showResetPinModal = true;
    }

    public function resetPin(): void
    {
        $this->validateOnly('resetNewPin', [
            'resetNewPin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'resetNewPin.size'  => 'PIN baru harus tepat 6 digit angka.',
            'resetNewPin.regex' => 'PIN baru hanya boleh berisi angka.',
        ]);

        $mobile = MobileAccess::findOrFail($this->resetTargetId);
        $mobile->setPin($this->resetNewPin);
        $mobile->updated_by = auth()->id();
        $mobile->resetPinLock();

        $this->showResetPinModal = false;
        $this->logActivity('UPDATE', "Mereset PIN akun mobile banking nasabah: {$this->resetTargetName}");
        session()->flash('success', "PIN nasabah {$this->resetTargetName} berhasil direset. Akun diaktifkan kembali.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  Reset Password
    // ─────────────────────────────────────────────────────────────────

    public function openResetPassword(int $id): void
    {
        $mobile = MobileAccess::with('cif')->findOrFail($id);
        $this->resetPassTargetId = $id;
        $this->resetPassTargetName = $mobile->cif?->name ?? ($mobile->username ?: $mobile->cif_no);
        $this->resetNewPassword = '';
        $this->resetErrorBag();
        $this->showResetPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validateOnly('resetNewPassword', [
            'resetNewPassword' => ['required', 'string', 'min:8'],
        ], [
            'resetNewPassword.min' => 'Password baru minimal 8 karakter.',
        ]);

        $mobile = MobileAccess::findOrFail($this->resetPassTargetId);
        $mobile->setPassword($this->resetNewPassword);
        $mobile->updated_by = auth()->id();
        $mobile->save();

        $this->showResetPasswordModal = false;
        $this->logActivity('UPDATE', "Mereset password akun mobile banking nasabah: {$this->resetPassTargetName}");
        session()->flash('success', "Password nasabah {$this->resetPassTargetName} berhasil direset.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  Toggle blokir / aktifkan akses
    // ─────────────────────────────────────────────────────────────────

    public function openToggleStatus(int $id): void
    {
        $mobile = MobileAccess::with('cif')->findOrFail($id);
        $this->statusTargetId   = $id;
        $this->statusTargetName = $mobile->cif?->name ?? ($mobile->username ?: $mobile->cif_no);
        $this->newStatus        = ! $mobile->is_active; // kebalikan dari status saat ini
        $this->showStatusModal  = true;
    }

    public function toggleStatus(): void
    {
        $mobile = MobileAccess::findOrFail($this->statusTargetId);
        $mobile->is_active = $this->newStatus;

        // Jika diaktifkan kembali, reset PIN lock juga
        if ($this->newStatus) {
            $mobile->wrong_pin_count = 0;
            $mobile->pin_blocked_at  = null;
        } else {
            // Blokir → hapus semua token sesi
            $mobile->revokeAllTokens();
        }

        $mobile->updated_by = auth()->id();
        $mobile->save();

        $action = $this->newStatus ? 'diaktifkan' : 'diblokir';
        $this->showStatusModal = false;
        $this->logActivity('UPDATE', "Status akun mobile banking nasabah {$this->statusTargetName} berhasil {$action}");
        session()->flash('success', "Akun mobile nasabah {$this->statusTargetName} berhasil {$action}.");
    }

    // ─────────────────────────────────────────────────────────────────
    //  Render
    // ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = MobileAccess::with(['cif'])
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('cif_no', 'like', "%{$this->search}%")
                          ->orWhere('username', 'like', "%{$this->search}%")
                          ->orWhereHas('cif', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('is_active', (bool) $this->filterStatus))
            ->latest()
            ->paginate(15);

        return view('livewire.mobile-access.index', [
            'records' => $query,
        ]);
    }
}
