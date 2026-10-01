<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RolesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = ['role-added' => 'refreshRoles', 'role-updated' => 'refreshRoles'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function refreshRoles()
    {
        $this->resetPage();
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Cannot delete role. It is assigned to ' . $role->users()->count() . ' user(s).');
            return;
        }

        $role->delete();
        session()->flash('message', 'Role deleted successfully.');
        $this->refreshRoles();
    }

    public function render()
    {
        $roles = Role::query()
            ->with('users')
            ->with('permission')
            ->when($this->search, function ($query) {
                $query->where('role_title', 'like', '%' . $this->search . '%')
                      ->orWhereHas('permission', function ($q) {
                          $q->where('permission_title', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.roles-manager', [
            'roles' => $roles,
        ]);
    }
}

