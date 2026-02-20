<?php
/**
 * Login Page - src/Views/pages/auth/login.php
 * 
 * User login form with email/password
 */
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-white border-0 pt-4">
                    <h2 class="text-center mb-0">
                        <i class="bi bi-graph-up text-gradient"></i> Login
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/auth/login" id="loginForm">
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
                                placeholder="Enter your password"
                                data-validate="required|min:8"
                                autocomplete="current-password"
                            >
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                >
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="/auth/forgot-password" class="text-decoration-none">
                                Forgot password?
                            </a>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </form>
                </div>
                
                <!-- Footer with Registration Link -->
                <div class="card-footer bg-white border-0 pb-4">
                    <p class="text-center mb-0">
                        Don't have an account? 
                        <a href="/auth/register" class="fw-bold">Sign up</a>
                    </p>
                </div>
                
                <!-- Social Login (Optional Future Feature) -->
                <div class="card-footer bg-light border-top">
                    <div class="text-center mb-3">
                        <small class="text-muted">Or continue with</small>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="/auth/instagram/redirect" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-instagram"></i> Instagram
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Terms & Privacy Links -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    By signing in, you agree to our
                    <a href="/terms" class="text-decoration-none">Terms of Service</a>
                    and
                    <a href="/privacy" class="text-decoration-none">Privacy Policy</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('htmx:responseError', function(evt) {
        const response = evt.detail.xhr.response;
        try {
            const json = JSON.parse(response);
            Notification.error(json.error || 'Login failed');
        } catch (e) {
            Notification.error('Login failed. Please try again.');
        }
    });
    
    document.getElementById('loginForm').addEventListener('htmx:sendError', function() {
        Notification.error('Network error. Please check your connection.');
    });
</script>
