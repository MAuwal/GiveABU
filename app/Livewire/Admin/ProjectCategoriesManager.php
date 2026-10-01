<?php

namespace App\Livewire\Admin;

use App\Models\ProjectCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectCategoriesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = ['category-added' => 'refreshCategories', 'category-updated' => 'refreshCategories'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function refreshCategories()
    {
        $this->resetPage();
    }

    public function deleteCategory($categoryId)
    {
        $category = ProjectCategory::findOrFail($categoryId);
        
        // Check if category has projects
        if ($category->projects()->count() > 0) {
            session()->flash('error', 'Cannot delete category. It is assigned to ' . $category->projects()->count() . ' project(s).');
            return;
        }

        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
        $this->refreshCategories();
    }

    public function render()
    {
        $categories = ProjectCategory::query()
            ->with(['department', 'faculty'])
            ->withCount('projects')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('department', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('faculty', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.project-categories-manager', [
            'categories' => $categories,
        ]);
    }
}

