@props([
    'name',           // Form field name, e.g. 'video_media_id'
    'label' => 'Select Media',
    'type' => 'all',  // all|image|audio|video
    'value' => null,  // Currently selected media ID (or URL if output=url)
    'media' => null,  // Pre-loaded Media model (optional, for existing value)
    'help' => null,
    'output' => 'id', // 'id' outputs media ID | 'url' outputs the file URL string
    'compact' => false, // Smaller layout for inline forms
])

<div x-data="mediaPicker({
    field: '{{ $name }}',
    typeFilter: '{{ $type }}',
    endpoint: '{{ route('admin.media.search') }}',
    uploadUrl: '{{ route('admin.media.store') }}',
    outputMode: '{{ $output }}',
    currentId: {{ $output === 'url' ? 'null' : ($value ?? 'null') }},
    currentUrl: @if($output === 'url' && $value) {{ Illuminate\Support\Js::from($value) }} @else null @endif,
    currentPreview: @if($media) {
        id: {{ $media->id }},
        name: {{ Illuminate\Support\Js::from($media->name) }},
        icon: '{{ $media->icon }}',
        url: '{{ $media->url }}',
        thumbUrl: '{{ $media->thumbnail_url ?: $media->url }}',
        type: '{{ $media->type }}',
        size: '{{ $media->size_formatted }}',
        duration: '{{ $media->duration_formatted }}',
    } @else null @endif,
})" class="media-picker-wrapper @if($compact) media-picker-compact @endif">

    {{-- Hidden field that actually gets submitted (outputs ID or URL depending on outputMode) --}}
    <input type="hidden" :value="outputMode === 'url' ? (selectedPreview?.url ?? currentUrl ?? '') : selectedId" name="{{ $name }}" id="{{ $name }}">

    {{-- Label --}}
    @if($label)
        <label class="form-label">{{ $label }}</label>
    @endif

    {{-- Selected media display --}}
    <div x-show="selectedPreview" x-cloak class="media-selected-card">
        <div class="media-selected-thumb">
            <img x-show="selectedPreview?.type === 'image'" :src="selectedPreview?.thumbUrl" :alt="selectedPreview?.name">
            <span x-show="selectedPreview?.type !== 'image'" class="media-icon-large" x-text="selectedPreview?.icon"></span>
        </div>
        <div class="media-selected-info">
            <div class="media-selected-name" x-text="selectedPreview?.name"></div>
            <div class="media-selected-meta">
                <span x-text="selectedPreview?.type?.toUpperCase()"></span>
                <span x-show="selectedPreview?.duration"> · <span x-text="selectedPreview?.duration"></span></span>
                <span x-show="selectedPreview?.size"> · <span x-text="selectedPreview?.size"></span></span>
            </div>
            {{-- Audio preview player --}}
            <audio x-show="selectedPreview?.type === 'audio'" :src="selectedPreview?.url" controls preload="none" style="margin-top:6px;width:100%;"></audio>
        </div>
        <div class="media-selected-actions">
            <button type="button" @click="openModal()" class="btn btn-sm btn-secondary">Change</button>
            <button type="button" @click="clearSelection()" class="btn btn-sm btn-danger">Remove</button>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!selectedPreview" class="media-empty-card">
        <button type="button" @click="openModal()" class="btn btn-primary">
            📁 Choose from Library
        </button>
        @if($help)
            <p class="form-help">{{ $help }}</p>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak @keydown.escape.window="closeModal()" class="media-modal-overlay" @click.self="closeModal()">
        <div class="media-modal" @click.stop>
            <div class="media-modal-header">
                <h3>Select {{ ucfirst($type) !== 'All' ? ucfirst($type) : 'Media' }}</h3>
                <button type="button" @click="closeModal()" class="modal-close">×</button>
            </div>

            <div class="media-modal-toolbar">
                <input type="text" x-model="searchTerm" @input.debounce.300ms="search()" placeholder="🔍 Search media..." class="media-search-input">
                <div class="media-filter-tabs">
                    @if($type === 'all')
                        <button type="button" @click="setType('all')" :class="{ active: type === 'all' }">All</button>
                        <button type="button" @click="setType('image')" :class="{ active: type === 'image' }">🖼️ Images</button>
                        <button type="button" @click="setType('audio')" :class="{ active: type === 'audio' }">🔊 Audio</button>
                        <button type="button" @click="setType('video')" :class="{ active: type === 'video' }">🎬 Video</button>
                    @endif
                </div>
            </div>

            <div class="media-modal-body">
                <div x-show="loading" class="media-loading">Loading...</div>
                <div x-show="!loading && results.length === 0" class="media-empty">
                    No media found. Upload a new file below.
                </div>
                <div class="media-grid" x-show="!loading && results.length > 0">
                    <template x-for="item in results" :key="item.id">
                        <div class="media-grid-item" @click="selectItem(item)" :class="{ selected: selectedId === item.id }">
                            <div class="media-grid-thumb">
                                <img x-show="item.type === 'image'" :src="item.thumb_url" :alt="item.name">
                                <span x-show="item.type !== 'image'" class="media-icon" x-text="item.icon"></span>
                            </div>
                            <div class="media-grid-name" x-text="item.name"></div>
                            <div class="media-grid-type" x-text="item.type"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Upload zone (using div, NOT form, to avoid nested-form HTML corruption) --}}
            <div class="media-modal-upload">
                <div class="upload-row">
                    <input type="file" x-ref="fileInput" @change="onFileSelected()" accept="@if($type === 'image')image/*@elseif($type === 'audio')audio/*@elseif($type === 'video')video/*@else*/*@endif" class="upload-file-input">
                    <input type="text" x-model="uploadName" placeholder="Name (optional)" class="upload-name-input">
                    <button type="button" class="btn btn-primary" x-show="uploadFile" :disabled="uploading" @click="upload()">
                        <span x-show="!uploading">⬆ Upload</span>
                        <span x-show="uploading">Uploading...</span>
                    </button>
                </div>
                <p x-show="uploadError" class="upload-error" x-text="uploadError"></p>
            </div>

            <div class="media-modal-footer">
                <button type="button" @click="closeModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function mediaPicker(config) {
    return {
        ...config,
        showModal: false,
        loading: false,
        results: [],
        searchTerm: '',
        type: config.typeFilter,
        selectedId: config.currentId,
        selectedPreview: config.currentPreview,
        // Upload state
        uploadFile: null,
        uploadName: '',
        uploading: false,
        uploadError: '',

        init() {
            // If we have an ID but no preview, try to load it
            if (this.selectedId && !this.selectedPreview) {
                this.loadCurrent();
            }
        },

        openModal() {
            this.showModal = true;
            this.search();
        },

        closeModal() {
            this.showModal = false;
        },

        setType(newType) {
            this.type = newType;
            this.search();
        },

        async search() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.type && this.type !== 'all') params.set('type', this.type);
                if (this.searchTerm) params.set('search', this.searchTerm);

                const resp = await fetch(`${this.endpoint}?${params}`);
                const data = await resp.json();
                this.results = data.items || [];
            } catch (e) {
                console.error('Media search failed:', e);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        async loadCurrent() {
            // Fetch details for currently selected media ID
            try {
                const params = new URLSearchParams();
                params.set('search', '');
                if (this.type && this.type !== 'all') params.set('type', this.type);
                const resp = await fetch(`${this.endpoint}?${params}`);
                const data = await resp.json();
                const found = (data.items || []).find(i => i.id === this.selectedId);
                if (found) this.selectedPreview = found;
            } catch (e) {}
        },

        selectItem(item) {
            this.selectedId = item.id;
            this.selectedPreview = {
                id: item.id,
                name: item.name,
                icon: item.icon,
                url: item.url,
                thumbUrl: item.thumb_url,
                type: item.type,
                size: item.size,
                duration: item.duration,
            };
            this.closeModal();
        },

        clearSelection() {
            this.selectedId = null;
            this.selectedPreview = null;
        },

        onFileSelected() {
            this.uploadFile = this.$refs.fileInput.files[0];
            if (this.uploadFile && !this.uploadName) {
                this.uploadName = this.uploadFile.name.replace(/\.[^/.]+$/, '');
            }
        },

        async upload() {
            if (!this.uploadFile) return;
            this.uploading = true;
            this.uploadError = '';

            const formData = new FormData();
            formData.append('file', this.uploadFile);
            if (this.uploadName) formData.append('name', this.uploadName);

            try {
                const resp = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    },
                });

                if (resp.redirected || resp.ok) {
                    // After upload, search to find the new item
                    this.searchTerm = this.uploadName;
                    await this.search();
                    this.uploadFile = null;
                    this.uploadName = '';
                    this.$refs.fileInput.value = '';
                } else {
                    this.uploadError = 'Upload failed. Check file size/type.';
                }
            } catch (e) {
                this.uploadError = 'Upload error: ' + e.message;
            } finally {
                this.uploading = false;
            }
        },
    };
}
</script>

<style>
[x-cloak] { display: none !important; }

.media-picker-wrapper { margin-bottom: 16px; }

.media-selected-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
}
.media-selected-thumb {
    width: 64px; height: 64px;
    border-radius: 8px;
    overflow: hidden;
    background: #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.media-selected-thumb img { width: 100%; height: 100%; object-fit: cover; }
.media-icon-large { font-size: 32px; }
.media-selected-info { flex: 1; min-width: 0; }
.media-selected-name { font-weight: 600; color: #1e293b; margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.media-selected-meta { font-size: 12px; color: #64748b; }
.media-selected-actions { display: flex; gap: 6px; flex-shrink: 0; }

.media-empty-card { display: flex; flex-direction: column; gap: 6px; }

/* Modal */
.media-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.media-modal {
    background: white;
    border-radius: 12px;
    width: 100%; max-width: 800px;
    max-height: 85vh;
    display: flex; flex-direction: column;
    overflow: hidden;
}
.media-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}
.media-modal-header h3 { margin: 0; font-size: 18px; }
.modal-close {
    background: none; border: none; font-size: 28px;
    cursor: pointer; color: #64748b; line-height: 1;
}
.media-modal-toolbar {
    display: flex; gap: 12px; padding: 12px 20px;
    border-bottom: 1px solid #e2e8f0; align-items: center;
}
.media-search-input {
    flex: 1; padding: 8px 12px;
    border: 1px solid #cbd5e1; border-radius: 6px;
    font-size: 14px;
}
.media-filter-tabs { display: flex; gap: 4px; }
.media-filter-tabs button {
    padding: 6px 12px; border: 1px solid #cbd5e1;
    background: white; border-radius: 6px; cursor: pointer; font-size: 13px;
}
.media-filter-tabs button.active { background: #3b82f6; color: white; border-color: #3b82f6; }

.media-modal-body { flex: 1; overflow-y: auto; padding: 16px 20px; }
.media-loading, .media-empty { text-align: center; padding: 40px; color: #64748b; }
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
}
.media-grid-item {
    border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;
    padding: 8px; transition: all 0.15s; text-align: center;
}
.media-grid-item:hover { border-color: #93c5fd; background: #eff6ff; }
.media-grid-item.selected { border-color: #3b82f6; background: #dbeafe; }
.media-grid-thumb {
    width: 100%; aspect-ratio: 1; border-radius: 6px;
    overflow: hidden; background: #f1f5f9;
    display: flex; align-items: center; justify-content: center; margin-bottom: 6px;
}
.media-grid-thumb img { width: 100%; height: 100%; object-fit: cover; }
.media-icon { font-size: 36px; }
.media-grid-name { font-size: 12px; font-weight: 500; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.media-grid-type { font-size: 10px; color: #94a3b8; text-transform: uppercase; }

.media-modal-upload {
    padding: 12px 20px; border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.upload-row { display: flex; gap: 8px; align-items: center; }
.upload-file-input { flex: 1; font-size: 13px; }
.upload-name-input { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; width: 200px; }
.upload-error { color: #ef4444; font-size: 12px; margin-top: 4px; }

.media-modal-footer {
    padding: 12px 20px; border-top: 1px solid #e2e8f0;
    display: flex; justify-content: flex-end; gap: 8px;
}

/* Buttons */
.btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; }
.btn-sm { padding: 4px 10px; font-size: 12px; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-secondary { background: #e2e8f0; color: #475569; }
.btn-secondary:hover { background: #cbd5e1; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

.form-label { display: block; font-weight: 600; color: #334155; margin-bottom: 6px; }
.form-help { font-size: 12px; color: #64748b; margin: 4px 0 0; }
</style>