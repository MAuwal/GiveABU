<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\On;

class EditPermission extends Component
{
    public $showModal = false;
    public $permissionId;
    public $permission_title;

    protected $rules = [
        'permission_title' => 'required|string|max:255',
    ];

    #[On('open-edit-permission-modal')]
    public function openModal($permissionId = null)
    {
        // Handle event data - Livewire passes data as array
        if (is_array($permissionId) && isset($permissionId['permissionId'])) {
            $permissionId = $permissionId['permissionId'];
        }
        
        if (!$permissionId) {
            return;
        }
        
        $this->permissionId = $permissionId;
        $permission = Permission::findOrFail($permissionId);
        
        $this->permission_title = $permission->permission_title;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['permissionId', 'permission_title']);
    }

    public function updatePermission()
    {
        // Update validation rule to ignore current permission
        $this->rules['permission_title'] = 'required|string|max:255|unique:permissions,permission_title,' . $this->permissionId;
        $this->validate();

        $permission = Permission::findOrFail($this->permissionId);
        $permission->update([
            'permission_title' => $this->permission_title,
        ]);

        session()->flash('message', 'Permission updated successfully.');
        $this->dispatch('permission-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.edit-permission');
    }
}

