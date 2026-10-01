<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class UsersManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    #[On('user-added')]
    #[On('role-added')]
    public function refreshUsersList()
    {
        // This method is intentionally empty.
        // The #[On] attributes will trigger a re-render.
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with('role')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.users-manager', [
            'users' => $users
        ]);
    }
} 