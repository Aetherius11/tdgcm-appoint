<div x-data="{
    uploading: false,
    error: null,
    success: false,
    preview: @js($currentLogo ? \Illuminate\Support\Facades\Storage::url($currentLogo) : null),

    upload(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.preview = URL.createObjectURL(file);

        const fd = new FormData();
        fd.append('logo', file);
        fd.append('company_id', {{ $companyId }});
        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

        this.uploading = true;
        this.error = null;
        this.success = false;

        fetch('/admin/upload-logo', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    this.success = true;
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    this.error = d.message ?? 'Upload failed.';
                }
            })
            .catch(() => { this.error = 'Upload failed. Please try again.'; })
            .finally(() => { this.uploading = false; });
    }
}" class="p-2 space-y-4">

    {{-- Current logo preview --}}
    <div x-show="preview" class="flex justify-center">
        <img :src="preview" alt="Logo Preview"
            class="max-h-24 max-w-xs object-contain rounded border border-gray-200 bg-white p-2">
    </div>

    {{-- File input --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Choose image file (PNG, JPG, SVG, GIF)
        </label>
        <input type="file" accept="image/*"
            @change="upload($event)"
            :disabled="uploading"
            class="block w-full text-sm text-gray-700 dark:text-gray-300
                   file:mr-4 file:py-2 file:px-4
                   file:rounded-lg file:border-0
                   file:text-sm file:font-semibold
                   file:bg-blue-600 file:text-white
                   hover:file:bg-blue-700 cursor-pointer">
    </div>

    {{-- Status messages --}}
    <div x-show="uploading" class="flex items-center gap-2 text-blue-600 text-sm">
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        Uploading…
    </div>
    <div x-show="success" class="text-green-600 text-sm font-semibold">
        ✓ Logo uploaded! Reloading…
    </div>
    <div x-show="error" x-text="error" class="text-red-600 text-sm"></div>
</div>
