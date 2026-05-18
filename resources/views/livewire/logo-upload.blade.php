<div class="space-y-3">
    @if($currentLogo)
        <div class="flex items-center gap-3">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($currentLogo) }}" alt="Current logo"
                 style="max-height:72px; max-width:180px; object-fit:contain;
                        background:#fff; border:1px solid #d1d5db; border-radius:6px; padding:6px;">
            <button type="button" wire:click="removeLogo"
                    class="text-xs text-red-500 hover:text-red-700 underline">
                Remove
            </button>
        </div>
    @endif

    <div wire:loading wire:target="logo" class="flex items-center gap-2 text-blue-500 text-sm">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        Uploading…
    </div>

    @error('logo')
        <p class="text-red-500 text-sm">{{ $message }}</p>
    @enderror

    <label wire:loading.remove wire:target="logo"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold cursor-pointer
                  bg-blue-600 text-white hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        {{ $currentLogo ? 'Change Image' : 'Choose Image' }}
        <input type="file" wire:model="logo" accept="image/*" class="hidden">
    </label>

    <p class="text-xs text-gray-400">PNG, JPG, SVG — max 2 MB. Saved instantly on selection.</p>
</div>
