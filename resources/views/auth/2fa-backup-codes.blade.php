@extends('layouts.app')

@section('title', 'Backup Codes')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Backup Codes</h3>
                </div>

                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Important!</h5>
                        <p>Please save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Your Backup Codes:</h5>
                            <div class="list-group">
                                @foreach($backupCodes as $index => $code)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="font-monospace">{{ $code }}</span>
                                    <span class="badge badge-secondary">{{ $index + 1 }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>How to Use Backup Codes:</h5>
                            <ol>
                                <li>When logging in, click "Use Backup Code Instead"</li>
                                <li>Enter one of the codes above</li>
                                <li>Each code can only be used once</li>
                                <li>Generate new codes when you run low</li>
                            </ol>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> Security Tips:</h6>
                        <ul class="mb-0">
                            <li>Store these codes in a secure location (password manager, safe, etc.)</li>
                            <li>Don't store them in your email or cloud storage</li>
                            <li>Each code can only be used once</li>
                            <li>Generate new codes when you have fewer than 3 remaining</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="printBackupCodes()">
                            <i class="fas fa-print"></i> Print Codes
                        </button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-success">
                            <i class="fas fa-check"></i> I've Saved These Codes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .card-header, .btn, .alert {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .list-group-item {
        border: 1px solid #000 !important;
        margin-bottom: 5px !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
function printBackupCodes() {
    window.print();
}
</script>
@endpush
