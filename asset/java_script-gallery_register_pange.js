        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const backBtn = document.querySelector('.back-btn');
            const brandLogo = document.querySelector('.brand-logo');
            const strengthMeter = document.getElementById('strengthMeter');

            // Toggle password visibility
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Toggle confirm password visibility
            if (toggleConfirmPassword && confirmPasswordInput) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Password strength indicator
            if (passwordInput && strengthMeter) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 6) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                    
                    strengthMeter.style.width = strength + '%';
                    
                    if (strength < 50) {
                        strengthMeter.style.background = '#d32f2f';
                    } else if (strength < 75) {
                        strengthMeter.style.background = '#ff9800';
                    } else {
                        strengthMeter.style.background = '#4caf50';
                    }
                });
            }

            // Password confirmation validation
            if (confirmPasswordInput && passwordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    if (this.value === passwordInput.value && this.value !== '') {
                        this.style.borderColor = '#4caf50';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }

            // Form submission
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    // Basic validation
                    if (passwordInput.value.length < 6) {
                        e.preventDefault();
                        alert('Password must be at least 6 characters long!');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (passwordInput.value !== confirmPasswordInput.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        confirmPasswordInput.focus();
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('.register-btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner loading"></i><span>Creating Account...</span>';
                        submitBtn.disabled = true;
                        
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i><span>Create Account</span>';
                                submitBtn.disabled = false;
                            }
                        }, 5000);
                    }
                });
            }

            // Back button interaction
            if (backBtn) {
                backBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = this.getAttribute('href');
                });
            }

            // Brand logo click effect
            if (brandLogo) {
                brandLogo.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                });
            }

            // Auto-focus username
            setTimeout(() => {
                const usernameInput = document.getElementById('username');
                if (usernameInput) {
                    usernameInput.focus();
                }
            }, 100);

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + Enter to submit
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    if (registerForm) registerForm.requestSubmit();
                }
                
                // Escape to go back
                if (e.key === 'Escape') {
                    window.location.href = 'index.php';
                }
            });
        });