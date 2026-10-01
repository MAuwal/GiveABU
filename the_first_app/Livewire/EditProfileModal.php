<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInfo;

class EditProfileModal extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $name;
    public $email;
    public $password;
    public $donor_type;
    public $role_id;
    public $address;
    public $phone;
    public $states = [];
    public $lgas = [];
    public $selectedState = '';
    public $selectedCity = '';
    public $profile_photo;
    public $current_photo;
    public $successMessage;
    public $asCard = false;

    protected $rules = [
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'password' => 'nullable|string|min:8',
        'donor_type' => 'nullable|string|max:255',
        'role_id' => 'nullable|integer',
        'address' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:255',
        'selectedState' => 'nullable|string|max:255',
        'selectedCity' => 'nullable|string|max:255',
        'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
    ];

    public function mount()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            // Not logged in, set defaults or skip loading user info
            $this->name = '';
            $this->email = '';
            $this->password = '';
            $this->donor_type = '';
            $this->role_id = null;
            $this->address = '';
            $this->phone = '';
            $this->selectedState = '';
            $this->selectedCity = '';
            $this->current_photo = null;
            return;
        }
        
        // Load user data
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->password = '';
        $this->donor_type = $user->donor_type ?? '';
        $this->role_id = $user->role_id ?? null;
        
        // Load LGA.json
        $lgaJson = file_get_contents(public_path('LGA.json'));
        $lgaData = json_decode($lgaJson, true);
        $this->states = array_keys($lgaData);
        $this->lgas = $lgaData;
        $userInfo = $user->userInfo;
        $this->address = $userInfo->address ?? '';
        $this->phone = $userInfo->phone ?? '';
        $this->selectedState = $userInfo->state ?? '';
        $this->selectedCity = $userInfo->city ?? '';
        $this->current_photo = $userInfo->profile_photo ?? null;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetErrorBag();
        $this->successMessage = null;
    }

    public function updatedSelectedState($value)
    {
        $this->selectedCity = '';
    }

    public function updateProfile()
    {
        $this->validate();
        $user = Auth::user();
        
        // Update user data
        $userData = [];
        if ($this->name) {
            $userData['name'] = $this->name;
        }
        if ($this->email) {
            $userData['email'] = $this->email;
        }
        if ($this->password) {
            $userData['password'] = $this->password;
        }
        if ($this->donor_type !== null) {
            $userData['donor_type'] = $this->donor_type;
        }
        if ($this->role_id !== null) {
            $userData['role_id'] = $this->role_id;
        }
        if (!empty($userData)) {
            $user->update($userData);
        }
        
        // Update user info
        $userInfo = $user->userInfo;
        $data = [
            'address' => $this->address,
            'phone' => $this->phone,
            'state' => $this->selectedState,
            'city' => $this->selectedCity,
        ];
        if ($this->profile_photo) {
            $path = $this->profile_photo->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
            $this->current_photo = $path;
        }
        if ($userInfo) {
            $userInfo->update($data);
        } else {
            $data['user_id'] = $user->id;
            UserInfo::create($data);
        }
        $this->successMessage = 'Profile updated successfully!';
        $this->reset('profile_photo');
        $this->dispatch('profile-updated');
    }

    public function render()
    {
        return view('livewire.edit-profile-modal', [
            'asCard' => $this->asCard,
        ]);
    }
}
