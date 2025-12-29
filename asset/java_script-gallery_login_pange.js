        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const backBtn = document.querySelector('.back-btn');
            const brandLogo = document.querySelector('.brand-logo');

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

            // Form submission
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.login-btn');
                    if (submitBtn) {
                        const originalHTML = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner loading"></i><span>Authenticating...</span>';
                        submitBtn.disabled = true;
                        
                        // Reset after 3 seconds if still disabled
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.innerHTML = originalHTML;
                                submitBtn.disabled = false;
                            }
                        }, 3000);
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

            // Input focus effects
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--accent-primary)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--text-muted)';
                });
            });

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
                    if (loginForm) loginForm.requestSubmit();
                }
                
                // Escape to go back
                if (e.key === 'Escape') {
                    window.location.href = 'index.php';
                }
            });
        });