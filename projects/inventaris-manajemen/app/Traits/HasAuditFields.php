<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::created(function (Model $model) {
            self::logAuditTrail($model, 'CREATE');
        });

        static::updating(function (Model $model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::updated(function (Model $model) {
            self::logAuditTrail($model, 'UPDATE');
        });

        static::deleting(function (Model $model) {
            if (auth()->check() && method_exists($model, 'trashed')) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });

        static::deleted(function (Model $model) {
            self::logAuditTrail($model, 'DELETE');
        });
    }

    protected static function logAuditTrail(Model $model, string $action): void
    {
        if (!auth()->check()) return;

        // Skip recording for AuditTrail model itself to prevent infinite loop
        if ($model instanceof \App\Models\AuditTrail) return;

        $oldValues = $action === 'UPDATE' ? $model->getOriginal() : null;
        $newValues = $action !== 'DELETE' ? $model->getAttributes() : null;

        if ($action === 'UPDATE' && empty($model->getChanges())) {
            return;
        }

        \App\Models\AuditTrail::create([
            'table_name' => $model->getTable(),
            'record_id'  => (string) $model->getKey(),
            'action'     => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id'    => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
