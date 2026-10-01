<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\On;

class EditRole extends Component
{
    public $showModal = false;
    public $roleId;
    public $role_title;
    public $permission_id;
    public $permissions;

    protected $rules = [
        'role_title' => 'required|string|max:255',
        'permission_id' => 'required|exists:permissions,id',
    ];

    #[On('open-edit-role-modal')]
    public function openModal($roleId = null)
    {
        // Handle event data - Livewire passes data as array
        if (is_array($roleId) && isset($roleId['roleId'])) {
            $roleId = $roleId['roleId'];
        }
        
        if (!$roleId) {
            return;
        }
        
        $this->roleId = $roleId;
        $role = Role::findOrFail($roleId);
        
        $this->role_title = $role->role_title;
        $this->permission_id = $role->permission_id;
        $this->permissions = Permission::all();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['roleId', 'role_title', 'permission_id']);
    }

    public function updateRole()
    {
        // Update validation rule to ignore current role
        $this->rules['role_title'] = 'required|string|max:255|unique:roles,role_title,' . $this->roleId;
        $this->validate();

        $role = Role::findOrFail($this->roleId);
        $role->update([
            'role_title' => $this->role_title,
            'permission_id' => $this->permission_id,
        ]);

        session()->flash('message', 'Role updated successfully.');
        $this->dispatch('role-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.edit-role');
    }
}

