@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
                <h4 class="page-title">System Settings</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-bordered" id="settings-tabs" role="tablist">
                        @foreach($groups as $group => $label)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                    id="{{ $group }}-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#{{ $group }}" 
                                    type="button" 
                                    role="tab">
                                {{ $label }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <div class="tab-content" id="settings-tab-content">
                            @foreach($groups as $group => $label)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                 id="{{ $group }}" 
                                 role="tabpanel">
                                
                                <div class="row mt-3">
                                    @foreach($settings[$group] as $setting)
                                    <div class="col-md-6 mb-3">
                                        <label for="{{ $setting->setting_key }}" class="form-label">
                                            {{ $setting->setting_label }}
                                            @if($setting->setting_description)
                                            <i class="fas fa-info-circle text-muted" 
                                               data-bs-toggle="tooltip" 
                                               title="{{ $setting->setting_description }}"></i>
                                            @endif
                                        </label>
                                        
                                        @if($setting->setting_type === 'boolean')
                                            <select class="form-select" 
                                                    id="{{ $setting->setting_key }}" 
                                                    name="settings[{{ $setting->setting_key }}]">
                                                <option value="true" {{ $setting->setting_value === 'true' ? 'selected' : '' }}>Yes</option>
                                                <option value="false" {{ $setting->setting_value === 'false' ? 'selected' : '' }}>No</option>
                                            </select>
                                        @elseif($setting->setting_type === 'integer')
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="{{ $setting->setting_key }}" 
                                                   name="settings[{{ $setting->setting_key }}]" 
                                                   value="{{ $setting->setting_value }}">
                                        @else
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="{{ $setting->setting_key }}" 
                                                   name="settings[{{ $setting->setting_key }}]" 
                                                   value="{{ $setting->setting_value }}">
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" 
                                                class="btn btn-info me-2" 
                                                onclick="testEfrisConnection()">
                                            <i class="fas fa-plug"></i> Test EFRIS Connection
                                        </button>
                                        <a href="{{ route('settings.clear-cache') }}" 
                                           class="btn btn-warning me-2"
                                           onclick="return confirm('Are you sure you want to clear the cache?')">
                                            <i class="fas fa-broom"></i> Clear Cache
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('settings.export') }}" 
                                           class="btn btn-secondary me-2">
                                            <i class="fas fa-download"></i> Export Settings
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Import Settings Modal -->
                    <div class="modal fade" id="importModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Import Settings</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('settings.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="settings_file" class="form-label">Settings File (JSON)</label>
                                            <input type="file" class="form-control" id="settings_file" name="settings_file" accept=".json" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Import</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testEfrisConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;

    fetch('{{ route("settings.test-efris") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: data.message,
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to test connection: ' + error.message,
        });
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush 