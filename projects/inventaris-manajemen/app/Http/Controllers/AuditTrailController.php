<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index()
    {
        return view('system.audit.index');
    }

    public function data(Request $request)
    {
        $query = AuditTrail::with('user');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('table_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qUser) use ($search) {
                      $qUser->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $columns = ['created_at', 'user_id', 'action', 'table_name', 'record_id'];
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            if (isset($columns[$columnIdx])) {
                $query->orderBy($columns[$columnIdx], $order['dir']);
            }
        } else {
            $query->latest();
        }

        return $this->datatableResponse($query, $request, function($item) {
            $item->user_name = $item->user ? $item->user->name : 'Sistem';
            $item->waktu = $item->created_at->format('d/m/Y H:i:s');
            
            $color = 'gray';
            if ($item->action === 'CREATE') $color = 'emerald';
            if ($item->action === 'UPDATE') $color = 'blue';
            if ($item->action === 'DELETE') $color = 'red';
            
            $item->action_badge = '<span class="px-2 py-1 text-[10px] font-bold rounded bg-'.$color.'-100 text-'.$color.'-700">'.$item->action.'</span>';

            // Map table_name to a friendly name for frontend
            $tableMap = [
                'inventaris' => 'Inventaris',
                'inv_mutasi' => 'Mutasi',
                'inv_improvements' => 'Improvement',
                'mst_kantor' => 'Kantor',
                'mst_golongan' => 'Golongan',
                'mst_jenis' => 'Jenis',
                'mst_lokasi' => 'Lokasi',
                'mst_ruangan' => 'Ruangan',
                'mst_sumber_dana' => 'Sumber Dana',
                'penyusutan_batches' => 'Penyusutan Batch',
                'penyusutan_details' => 'Penyusutan Detail',
                'users' => 'User',
            ];
            $item->model_type = $tableMap[$item->table_name] ?? $item->table_name;
            $item->model_id = $item->record_id;

            // Build changes summary
            if ($item->action === 'UPDATE' && $item->old_values && $item->new_values) {
                $changes = [];
                foreach ($item->new_values as $key => $newVal) {
                    $oldVal = $item->old_values[$key] ?? null;
                    if ($oldVal != $newVal && !in_array($key, ['updated_at', 'updated_by'])) {
                        $changes[$key] = ['dari' => $oldVal, 'ke' => $newVal];
                    }
                }
                $item->changes = !empty($changes) ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null;
            } elseif ($item->action === 'CREATE' && $item->new_values) {
                $filtered = collect($item->new_values)->except(['created_at', 'updated_at', 'created_by', 'updated_by'])->toArray();
                $item->changes = json_encode($filtered, JSON_UNESCAPED_UNICODE);
            } else {
                $item->changes = null;
            }

            return $item;
        });
    }
}
