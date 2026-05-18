@php
    $previewUrl = $currentLogo ? \Illuminate\Support\Facades\Storage::url($currentLogo) : null;
@endphp

<div x-data="{
    preview: @js($previewUrl),
    uploading: false,
    error: null,

    upload(event) {
        const file = event.target.files[0];
        if (!file) return;

        this.preview = URL.createObjectURL(file);
        this.uploading = true;
        this.error = null;

        const fd = new FormData();
        fd.append('logo', file);
        fd.append('company_id', {{ $companyId ?? 'null' }});
        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

        fetch('/admin/upload-logo', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    $wire.set('data.branding_logo', d.path);
                } else {
                    this.error = d.message ?? 'Upload failed.';
                }
            })
            .catch(() => { this.error = 'Upload failed. Please try again.'; })
            .finally(() => { this.uploading = false; });
    }
}" class="space-y-3">

    {{-- Current / new logo preview --}}
    <div x-show="preview">
        <img :src="preview" alt="Logo preview"
             style="max-height:80px; max-width:200px; object-fit:contain;
                    background:#fff; border:1px solid #d1d5db; border-radius:6px; padding:6px;">
    </div>

    {{-- File picker button --}}
    <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold cursor-pointer transition
                  bg-blue-600 text-white hover:bg-blue-700"
           :class="{ 'opacity-50 cursor-not-allowed': uploading }">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span x-text="uploading ? 'Uploading…' : (preview ? 'Change Image' : 'Choose Image')"></span>
        <input type="file" accept="image/*" class="hidden"
               @change="upload($event)" :disabled="uploading">
    </label>

    <p class="text-xs text-gray-400">PNG, JPG, SVG — max 2 MB. Saved immediately on selection.</p>

    <p x-show="error" x-text="error" class="text-red-500 text-sm"></p>
</div>
