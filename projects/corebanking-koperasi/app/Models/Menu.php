<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Menu extends Model
{
    protected $fillable = ['name', 'code', 'icon', 'route', 'permission', 'category', 'order', 'is_active', 'table_name', 'parent_id', 'schema'];

    protected $casts = [
        'schema' => 'array',
        'is_active' => 'boolean'
    ];

    protected static function booted()
    {
        static::saved(function ($menu) {
            if ($menu->permission) {
                \Spatie\Permission\Models\Permission::findOrCreate($menu->permission);
                
                // Disable auto-assign to allow system:sync-permissions command to handle permissions

                // Force clear permission cache
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        });

        static::deleted(function ($menu) {
            // Force clear permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        });
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    public function roleAccesses()
    {
        return $this->hasMany(RoleMenuAccess::class);
    }
}
