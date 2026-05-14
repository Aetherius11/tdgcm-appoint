<?php


namespace App\Livewire;

use Livewire\Component;
use App\Models\Principal;
use App\Models\Purpose;

class AppointmentForm extends Component
{
    public $departments;
    public $purposes = []; // This starts empty

    public $selectedDepartment = null;
    public $selectedPurpose = null;

    public function mount()
    {
        // Load all departments on initial page load
        $this->departments = Principal::all();
    }

    // This function runs automatically whenever $selectedDepartment changes
    public function updatedSelectedDepartment($value)
    {
        // Fetch only purposes linked to the chosen department
        $this->purposes = Purpose::where('principal_id', $value)->get();
        
        // Reset purpose selection if they change departments
        $this->selectedPurpose = null;
    }

    public function render()
    {
        return view('livewire.appointment-form');
    }
}