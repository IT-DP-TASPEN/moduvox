<div x-data="{ showCreate: @entangle('showCreateModal').live, showEdit: @entangle('showEditModal').live, showDelete: @entangle('confirmingDeletion') }">
    <!-- Header -->
    <x-header title="Log Audit & Aktivitas" subtitle="Jejak digital seluruh pengguna sistem" :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="filter_action" class="pl-3 pr-8 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold text-outline hover:border-primary/50">
                        <option value="">Semua Action</option>
                        @foreach($actions as $a)
                            <option value="{{ $a->action }}">{{ $a->action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <select wire:model.live="filter_user" class="pl-3 pr-8 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold text-outline hover:border-primary/50">
                        <option value="">Semua Pengguna</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari deskripsi..." class="pl-10 pr-4 py-2 bg-surface border border-surface-dim rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-48 font-medium">
                </div>
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-8">
        <div class="bg-white rounded-3xl shadow-sm border border-surface-dim overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface border-b border-surface-dim uppercase text-[10px] tracking-widest font-bold text-outline text-right">
                        <th class="px-6 py-4 text-left">Pengguna</th>
                        <th class="px-6 py-4 text-left">Aksi & Modul</th>
                        <th class="px-6 py-4 text-left">Deskripsi Kejadian</th>
                        <th class="px-6 py-4 text-left">IP & Perangkat</th>
                        <th class="px-6 py-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-dim">
                    @foreach($logsList as $log)
                    <tr class="hover:bg-surface/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                    {{ substr($log->user->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-primary leading-tight">{{ $log->user->name ?? 'System' }}</p>
                                    <p class="text-[9px] text-outline font-medium">{{ $log->user->username ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase w-fit tracking-wider
                                    @if($log->action == 'CREATE') bg-green-100 text-green-700
                                    @elseif($log->action == 'UPDATE') bg-blue-100 text-blue-700
                                    @elseif($log->action == 'DELETE') bg-red-100 text-red-700
                                    @elseif($log->action == 'LOGIN') bg-purple-100 text-purple-700
                                    @else bg-surface-dim text-outline @endif">
                                    {{ $log->action }}
                                </span>
                                <span class="text-[9px] text-outline font-mono opacity-60">{{ class_basename($log->model_type) ?: 'External Action' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-medium text-primary leading-relaxed max-w-sm">{{ $log->description }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <div class="flex items-center space-x-1 text-primary">
                                    <span class="material-symbols-outlined text-xs">public</span>
                                    <span class="text-[10px] font-bold">{{ $log->ip_address }}</span>
                                </div>
                                <p class="text-[9px] text-outline truncate w-32 font-medium" title="{{ $log->user_agent }}">{{ $log->user_agent }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-xs font-bold text-primary">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-outline font-medium">{{ $log->created_at->format('H:i') }} WIB</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="px-6 py-4 bg-surface border-t border-surface-dim">
                {{ $logsList->links() }}
            </div>
        </div>
    </div>
</div>
