@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Two-Factor Authentication</h3>
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        <h5 class="mt-3">Enter Verification Code</h5>
                        <p class="text-muted">Please enter the 6-digit code from your authenticator app</p>
                    </div>

                    <form method="POST" action="{{ route('2fa.verify') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="code">Verification Code</label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                   placeholder="000000"
                                   maxlength="6"
                                   required
                                   autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-check"></i> Verify Code
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#backupCodeForm">
                            <i class="fas fa-key"></i> Use Backup Code Instead
                        </button>
                    </div>

                    <div class="collapse mt-3" id="backupCodeForm">
                        <div class="card card-body">
                            <h6>Enter Backup Code</h6>
                            <form method="POST" action="{{ route('2fa.verify-backup') }}">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="backup_code">Backup Code</label>
                                    <input type="text" 
                                           id="backup_code" 
                                           name="backup_code" 
                                           class="form-control @error('backup_code') is-invalid @enderror" 
                                           placeholder="Enter 8-character backup code"
                                           maxlength="8"
                                           required>
                                    @error('backup_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fas fa-key"></i> Use Backup Code
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('logout') }}" class="btn btn-link text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const backupCodeInput = document.getElementById('backup_code');
    
    // Auto-format the code input
    codeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 6) {
            value = value.substring(0, 6);
        }
        e.target.value = value;
    });
    
    // Auto-format the backup code input
    backupCodeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^A-Z0-9]/g, '').toUpperCase(); // Only alphanumeric
        if (value.length > 8) {
            value = value.substring(0, 8);
        }
        e.target.value = value;
    });
    
    // Auto-submit when 6 digits are entered
    codeInput.addEventListener('input', function(e) {
        if (e.target.value.length === 6) {
            // Small delay to let user see the complete code
            setTimeout(() => {
                e.target.form.submit();
            }, 500);
        }
    });
});
</script>
@endpush
