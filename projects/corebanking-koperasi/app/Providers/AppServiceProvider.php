<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    private array $auditColumns = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen('eloquent.creating: App\Models\*', function ($eventName, array $data) {
            $this->fillAuditUser($data[0] ?? null, true);
        });

        Event::listen('eloquent.updating: App\Models\*', function ($eventName, array $data) {
            $this->fillAuditUser($data[0] ?? null, false);
        });

        Event::listen('eloquent.saving: App\Models\*', function ($eventName, array $data) {
            $model = $data[0] ?? null;
            if (!$model instanceof Model) return;

            // Excluded models where uppercasing is dangerous or unnecessary
            $excludedModels = [
                \App\Models\User::class,
                \App\Models\Menu::class,
                \App\Models\ActivityLog::class,
                \App\Models\ApprovalConfig::class,
                \App\Models\ApprovalRequest::class,
            ];

            if (in_array(get_class($model), $excludedModels)) return;

            $excludedFields = [
                'email', 'password', 'remember_token', 'username', 
                'token', 'secret', 'file', 'path', 'url', 'photo', 
                'signature', 'icon', 'route', 'permission', 'module_key', 
                'action', 'device_id', 'contact_person', 'notes', 'remarks'
            ];

            foreach ($model->getAttributes() as $key => $value) {
                if (is_string($value) && !in_array($key, $excludedFields)) {
                    if (str_starts_with($value, '$2y$') || str_starts_with($value, '$argon2')) continue;
                    if ($model->hasCast($key, ['array', 'json', 'object', 'collection'])) continue;
                    
                    $trimVal = trim($value);
                    if (str_starts_with($trimVal, '{') || str_starts_with($trimVal, '[')) {
                        json_decode($value);
                        if (json_last_error() === JSON_ERROR_NONE) continue;
                    }
                    
                    $model->setAttribute($key, mb_strtoupper($value, 'UTF-8'));
                }
            }
        });
    }

    private function fillAuditUser($model, bool $creating): void
    {
        if (!$model instanceof Model) return;

        $userId = Auth::id() ?? \App\Models\User::getSystemUserId();
        if (!$userId) return;

        if ($creating && $this->modelHasColumn($model, 'created_by') && !$model->getAttribute('created_by')) {
            $model->setAttribute('created_by', $userId);
        }

        if ($this->modelHasColumn($model, 'updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }

        if ($creating && $this->shouldAutoApprovePostedModel($model)) {
            if ($this->modelHasColumn($model, 'approved_by') && !$model->getAttribute('approved_by')) {
                $model->setAttribute('approved_by', Auth::id() ?? $userId);
            }

            if ($this->modelHasColumn($model, 'approved_at') && !$model->getAttribute('approved_at')) {
                $model->setAttribute('approved_at', now());
            }
        }
    }

    private function modelHasColumn(Model $model, string $column): bool
    {
        $table = $model->getTable();
        $key = "{$table}.{$column}";

        return $this->auditColumns[$key] ??= Schema::hasColumn($table, $column);
    }

    private function shouldAutoApprovePostedModel(Model $model): bool
    {
        return in_array(get_class($model), [
            \App\Models\DepositTransaction::class,
            \App\Models\Journal::class,
            \App\Models\LoanTransaction::class,
            \App\Models\SavingTransaction::class,
        ], true);
    }
}
