<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionsManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = ['permission-added' => 'refreshPermissions', 'permission-updated' => 'refreshPermissions'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function refreshPermissions()
    {
        $this->resetPage();
    }

    public function deletePermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        // Check if permission has roles
        if ($permission->roles()->count() > 0) {
            session()->flash('error', 'Cannot delete permission. It is assigned to ' . $permission->roles()->count() . ' role(s).');
            return;
        }

        $permission->delete();
        session()->flash('message', 'Permission deleted successfully.');
        $this->refreshPermissions();
    }

    public function render()
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->when($this->search, function ($query) {
                $query->where('permission_title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.permissions-manager', [
            'permissions' => $permissions,
        ]);
    }
}

