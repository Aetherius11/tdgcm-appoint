<?php

namespace App\Livewire;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class LogoUpload extends Component
{
    use WithFileUploads;

    public int $companyId;
    public ?string $currentLogo = null;
    public $logo;

    public function mount(int $companyId, ?string $currentLogo = null): void
    {
        $this->companyId = $companyId;
        $this->currentLogo = $currentLogo;
    }

    public function updatedLogo(): void
    {
        $this->validate(['logo' => 'image|max:2048']);

        $path = $this->logo->store('branding', 'public');

        Company::findOrFail($this->companyId)->update(['branding_logo' => $path]);

        $this->currentLogo = $path;
        $this->logo = null;
    }

    public function removeLogo(): void
    {
        $company = Company::findOrFail($this->companyId);

        if ($company->branding_logo) {
            Storage::disk('public')->delete($company->branding_logo);
            $company->update(['branding_logo' => null]);
        }

        $this->currentLogo = null;
    }

    public function render()
    {
        return view('livewire.logo-upload');
    }
}
