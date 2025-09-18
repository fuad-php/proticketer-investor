@extends('layouts.app')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Setup Two-Factor Authentication</h3>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> What is Two-Factor Authentication?</h5>
                        <p>Two-factor authentication (2FA) adds an extra layer of security to your account. You'll need to enter a code from your authenticator app in addition to your password when logging in.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Step 1: Install an Authenticator App</h5>
                            <p>Download and install one of these authenticator apps on your mobile device:</p>
                            <ul>
                                <li><strong>Google Authenticator</strong> (iOS/Android)</li>
                                <li><strong>Microsoft Authenticator</strong> (iOS/Android)</li>
                                <li><strong>Authy</strong> (iOS/Android)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Step 2: Scan QR Code</h5>
                            <p>Open your authenticator app and scan this QR code:</p>
                            <div class="text-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Step 3: Enter Verification Code</h5>
                            <p>After scanning the QR code, enter the 6-digit code from your authenticator app:</p>
                            
                            <form method="POST" action="{{ route('2fa.enable') }}">
                                @csrf
                                <input type="hidden" name="secret" value="{{ $secretKey }}">
                                
                                <div class="form-group">
                                    <label for="code">Verification Code</label>
                                    <input type="text" 
                                           id="code" 
                                           name="code" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           placeholder="Enter 6-digit code"
                                           maxlength="6"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-shield-alt"></i> Enable 2FA
                                    </button>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Security Notes:</h6>
                        <ul class="mb-0">
                            <li>Keep your authenticator app secure and don't share it with anyone</li>
                            <li>Save your backup codes in a safe place - you'll need them if you lose access to your authenticator app</li>
                            <li>If you lose your device, you can use backup codes to regain access</li>
                        </ul>
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
    
    // Auto-format the code input
    codeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 6) {
            value = value.substring(0, 6);
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
