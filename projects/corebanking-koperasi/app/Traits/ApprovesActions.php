<?php

namespace App\Traits;

use App\Models\ApprovalConfig;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Auth;

trait ApprovesActions
{
    private function approvalModuleAliases(string $module): array
    {
        $aliases = [$module];

        if (str_contains($module, '-')) {
            $aliases[] = str_replace('-', '_', $module);
        }
        if (str_contains($module, '_')) {
            $aliases[] = str_replace('_', '-', $module);
        }

        return array_values(array_unique($aliases));
    }

    /**
     * Intercept action and check if it requires approval.
     * 
     * @param string $module Module key (e.g. 'provinces')
     * @param string $action Action type (CREATE, UPDATE, DELETE)
     * @param array|null $dataAfter New data payload
     * @param mixed $modelId ID of the record (for UPDATE/DELETE)
     * @param array|null $dataBefore Original data (for UPDATE/DELETE)
     * @return string 'APPROVED'|'PENDING'
     */
    public function interceptAction($module, $action, $dataAfter = null, $modelId = null, $dataBefore = null)
    {
        $config = ApprovalConfig::whereIn('module_key', $this->approvalModuleAliases((string) $module))
            ->where('action', $action)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return 'APPROVED';
        }

        // Create approval request
        ApprovalRequest::create([
            'module_key' => $module,
            'model_id' => $modelId,
            'action' => $action,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'requested_by' => Auth::id(),
            'status' => 'PENDING',
        ]);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('approval-created');
        }

        return 'PENDING';
    }
}
