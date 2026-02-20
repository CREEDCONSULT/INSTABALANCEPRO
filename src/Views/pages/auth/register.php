<?php
/**
 * Registration Page - src/Views/pages/auth/register.php
 * 
 * User registration form with email/password
 */
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-white border-0 pt-4">
                    <h2 class="text-center mb-0">
                        <i class="bi bi-person-plus text-gradient"></i> Create Account
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/auth/register" id="registerForm">
                        <!-- CSRF Token Hidden Input -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                required
                                placeholder="you@example.com"
                                data-validate="required|email"
                                autocomplete="email"
                            >
                            <small class="form-text">We'll never share your email.</small>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Min. 8 characters"
                                data-validate="required|min:8"
                                autocomplete="new-password"
                            >
                            <small class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                At least 8 characters, mix of upper/lower case, numbers and symbols recommended
                            </small>
                        </div>
                        
                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password_confirm" 
                                name="password_confirm" 
                                required
                                placeholder="Confirm your password"
                                data-validate="required|match:#password"
                                autocomplete="new-password"
                            >
                        </div>
                        
                        <!-- Terms Checkbox -->
                        <div class="form-check mb-4">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="terms" 
                                name="terms_agreed" 
                                required
                            >
                            <label class="form-check-label" for="terms">
                                I agree to the 
                                <a href="/terms" class="text-decoration-none">Terms of Service</a>
                                and
                                <a href="/privacy" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-person-plus"></i> Create Account
                        </button>
                    </form>
                </div>
                
                <!-- Footer with Login Link -->
                <div class="card-footer bg-white border-0 pb-4">
                    <p class="text-center mb-0">
                        Already have an account? 
                        <a href="/auth/login" class="fw-bold">Sign in</a>
                    </p>
                </div>
                
                <!-- Social Registration (Optional Future Feature) -->
                <div class="card-footer bg-light border-top">
                    <div class="text-center mb-3">
                        <small class="text-muted">Or register with</small>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="/auth/instagram/redirect" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-instagram"></i> Instagram
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('htmx:responseError', function(evt) {
        const response = evt.detail.xhr.response;
        try {
            const json = JSON.parse(response);
            Notification.error(json.error || 'Registration failed');
        } catch (e) {
            Notification.error('Registration failed. Please try again.');
        }
    });
    
    document.getElementById('registerForm').addEventListener('htmx:sendError', function() {
        Notification.error('Network error. Please check your connection.');
    });
</script>
