<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Document Management') }}
        </h2>
    </x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Document Management</h3>
                    <div>
                        <a href="{{ route('documents.create') }}" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Document
                        </a>
                        <a href="{{ route('documents.stats') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Storage Stats
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-file"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Documents</span>
                                    <span class="info-box-number">{{ number_format($stats['total_documents']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-hdd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Size</span>
                                    <span class="info-box-number">{{ number_format($stats['total_size'] / 1024 / 1024, 1) }} MB</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending Approval</span>
                                    <span class="info-box-number">{{ $stats['pending_approval'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Expired</span>
                                    <span class="info-box-number">{{ $stats['expired'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-download"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Downloads</span>
                                    <span class="info-box-number">{{ number_format($stats['total_downloads'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-archive"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Archived</span>
                                    <span class="info-box-number">{{ $stats['archived'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="statement" {{ request('type') == 'statement' ? 'selected' : '' }}>Statement</option>
                                    <option value="receipt" {{ request('type') == 'receipt' ? 'selected' : '' }}>Receipt</option>
                                    <option value="contract" {{ request('type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="invoice" {{ request('type') == 'invoice' ? 'selected' : '' }}>Invoice</option>
                                    <option value="report" {{ request('type') == 'report' ? 'selected' : '' }}>Report</option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <option value="investment" {{ request('category') == 'investment' ? 'selected' : '' }}>Investment</option>
                                    <option value="client" {{ request('category') == 'client' ? 'selected' : '' }}>Client</option>
                                    <option value="system" {{ request('category') == 'system' ? 'selected' : '' }}>System</option>
                                    <option value="legal" {{ request('category') == 'legal' ? 'selected' : '' }}>Legal</option>
                                    <option value="financial" {{ request('category') == 'financial' ? 'selected' : '' }}>Financial</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="visibility" class="form-control">
                                    <option value="">All Visibility</option>
                                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                                    <option value="restricted" {{ request('visibility') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search documents...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('documents.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <form method="POST" action="{{ route('documents.bulk-action') }}" class="mb-4" id="bulkActionForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <select name="action" class="form-control" id="bulkAction">
                                    <option value="">Bulk Actions</option>
                                    <option value="archive">Archive</option>
                                    <option value="restore">Restore</option>
                                    <option value="delete">Delete</option>
                                    <option value="approve">Approve</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning" id="bulkActionBtn" disabled>
                                    <i class="fas fa-tasks"></i> Apply Action
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Documents Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Size</th>
                                    <th>Status</th>
                                    <th>Visibility</th>
                                    <th>Downloads</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="document_ids[]" value="{{ $document->id }}" class="form-check-input document-checkbox">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="{{ $document->type_icon }} fa-2x text-primary mr-3"></i>
                                            <div>
                                                <strong>{{ $document->title }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $document->document_number }}</small>
                                                @if($document->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($document->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($document->type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ ucfirst($document->category) }}</span>
                                    </td>
                                    <td>{{ $document->file_size_human }}</td>
                                    <td>
                                        <span class="badge badge-{{ $document->status_badge_color }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                        @if($document->isExpired())
                                        <br><small class="text-danger">Expired</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $document->visibility_badge_color }}">
                                            {{ ucfirst($document->visibility) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $document->download_count }}</span>
                                        @if($document->last_downloaded_at)
                                        <br><small class="text-muted">{{ $document->last_downloaded_at->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $document->uploadedBy->name }}</strong>
                                        <br><small class="text-muted">{{ $document->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($document->canBeDownloadedBy(auth()->user()))
                                                <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            @if($document->canBeEditedBy(auth()->user()))
                                                <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($document->canBeDeletedBy(auth()->user()))
                                                <form method="POST" action="{{ route('documents.destroy', $document) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this document?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No documents found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const documentCheckboxes = document.querySelectorAll('.document-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    const bulkActionSelect = document.getElementById('bulkAction');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        documentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });

    // Individual checkbox change
    documentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionButton();
            updateSelectAllCheckbox();
        });
    });

    // Bulk action select change
    bulkActionSelect.addEventListener('change', function() {
        updateBulkActionButton();
    });

    function updateBulkActionButton() {
        const checkedBoxes = document.querySelectorAll('.document-checkbox:checked');
        const hasAction = bulkActionSelect.value !== '';
        bulkActionBtn.disabled = checkedBoxes.length === 0 || !hasAction;
    }

    function updateSelectAllCheckbox() {
        const checkedBoxes = document.querySelectorAll('.document-checkbox:checked');
        const totalBoxes = documentCheckboxes.length;
        selectAllCheckbox.checked = checkedBoxes.length === totalBoxes;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalBoxes;
    }

    // Bulk action form submission
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.document-checkbox:checked');
        const action = bulkActionSelect.value;
        
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one document.');
            return;
        }

        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return;
        }

        const actionText = action.charAt(0).toUpperCase() + action.slice(1);
        if (!confirm(`Are you sure you want to ${actionText.toLowerCase()} ${checkedBoxes.length} document(s)?`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
</x-app-layout>
