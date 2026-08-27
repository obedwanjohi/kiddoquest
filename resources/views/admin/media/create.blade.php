@extends('admin.layouts.app')
@section('title', 'Upload Media')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>📤 Upload Media</h3>
        <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- File Upload --}}
            <div class="form-group">
                <label class="form-label">File(s) <span style="color:#dc3545;">*</span></label>
                <input type="file" name="files[]" id="fileInput" class="form-control" required multiple
                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
                    style="padding:12px;border:2px dashed #cbd5e1;border-radius:8px;"
                    onchange="previewFile(this)">
                <div style="font-size:12px;color:#999;margin-top:4px;">Max 100MB. Images, videos, audio, and documents supported.</div>
            </div>

            {{-- Preview Area / Bulk Upload Status --}}
            <div id="preview-area" style="display:none;margin:16px 0;border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#f8fafc;">
                <div id="single-preview" style="text-align:center;">
                    <img id="preview-img" src="" style="max-width:300px;max-height:200px;border-radius:8px;display:none;">
                    <video id="preview-video" controls style="max-width:300px;max-height:200px;border-radius:8px;display:none;"></video>
                    <audio id="preview-audio" controls style="display:none;"></audio>
                    <div id="preview-icon" style="font-size:48px;display:none;">📄</div>
                </div>
                <div id="bulk-preview" style="display:none;">
                    <h4 style="margin-top:0;margin-bottom:12px;">Bulk Upload Files (<span id="bulk-count">0</span>)</h4>
                    <div style="max-height:200px;overflow-y:auto;border:1px solid #cbd5e1;background:#fff;border-radius:4px;">
                        <ul id="bulk-list" style="margin:0;padding:0;list-style:none;"></ul>
                    </div>
                </div>
                
                {{-- Progress Bar --}}
                <div id="upload-progress-container" style="display:none;margin-top:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:14px;font-weight:bold;">
                        <span id="upload-status-text">Uploading...</span>
                        <span id="upload-percentage">0%</span>
                    </div>
                    <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                        <div id="upload-progress-bar" style="height:100%;background:#3b82f6;width:0%;transition:width 0.2s;"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Display Name <span style="color:#999;font-weight:normal;">(optional — uses file name if blank)</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Letter A Teaching Video">
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-control">
                    <option value="">— None —</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->icon }} {{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tags <span style="color:#999;font-weight:normal;">(comma-separated)</span></label>
                <input type="text" name="tags" class="form-control" placeholder="e.g., alphabet, letters, english">
            </div>

            <div class="form-group">
                <label class="form-label">Alt Text <span style="color:#999;font-weight:normal;">(for accessibility)</span></label>
                <input type="text" name="alt_text" class="form-control" placeholder="e.g., The letter A in uppercase">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="What is this media used for?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" id="uploadBtn">📤 Upload</button>
        </form>
    </div>
</div>

<script>
function previewFile(input) {
    const files = input.files;
    if (files.length === 0) return;

    const area = document.getElementById('preview-area');
    const singlePreview = document.getElementById('single-preview');
    const bulkPreview = document.getElementById('bulk-preview');

    area.style.display = 'block';

    if (files.length === 1) {
        // Single file preview
        singlePreview.style.display = 'block';
        bulkPreview.style.display = 'none';

        const file = files[0];
        const img = document.getElementById('preview-img');
        const video = document.getElementById('preview-video');
        const audio = document.getElementById('preview-audio');
        const icon = document.getElementById('preview-icon');

        img.style.display = 'none';
        video.style.display = 'none';
        audio.style.display = 'none';
        icon.style.display = 'none';

        if (file.type.startsWith('image/')) {
            img.src = URL.createObjectURL(file);
            img.style.display = 'inline';
        } else if (file.type.startsWith('video/')) {
            video.src = URL.createObjectURL(file);
            video.style.display = 'inline';
        } else if (file.type.startsWith('audio/')) {
            audio.src = URL.createObjectURL(file);
            audio.style.display = 'inline';
        } else {
            icon.style.display = 'block';
        }
    } else {
        // Bulk file preview
        singlePreview.style.display = 'none';
        bulkPreview.style.display = 'block';
        
        document.getElementById('bulk-count').textContent = files.length;
        const list = document.getElementById('bulk-list');
        list.innerHTML = '';
        
        for(let i = 0; i < files.length; i++) {
            const li = document.createElement('li');
            li.style.padding = '8px 12px';
            li.style.borderBottom = '1px solid #e2e8f0';
            li.style.fontSize = '14px';
            
            const nameSpan = document.createElement('span');
            nameSpan.textContent = files[i].name;
            
            li.appendChild(nameSpan);
            list.appendChild(li);
        }
    }
}
</script>
@endsection