<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\On;

class AddPermission extends Component
{
    public $showModal = false;
    public $permission_title;

    protected $rules = [
        'permission_title' => 'required|string|max:255|unique:permissions,permission_title',
    ];

    #[On('open-add-permission-modal')]
    public function openModal()
    {
        $this->reset();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function savePermission()
    {
        $this->validate();

        Permission::create([
            'permission_title' => $this->permission_title,
        ]);

        session()->flash('message', 'Permission added successfully.');
        $this->dispatch('permission-added');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.add-permission');
    }
}

