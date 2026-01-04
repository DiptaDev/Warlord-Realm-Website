        // Form navigation
        let currentPage = 0;
        const totalPages = 5;
        const formPages = 4; // Number of form pages (excluding welcome and success)

        function nextPage() {
            if (currentPage === 0) {
                // Welcome page, no validation needed
                switchPage(true);
            } else if (validatePage(currentPage)) {
                // Form pages, validate before proceeding
                switchPage(true);
            }
        }

        function prevPage() {
            if (currentPage > 0) {
                switchPage(false);
            }
        }

        function switchPage(forward) {
            // Animate page transition
            const currentPageElement = document.getElementById(`page${currentPage}`);
            currentPageElement.style.animation = 'fadeOut 0.3s ease';

            setTimeout(() => {
                currentPageElement.classList.remove('active');
                currentPageElement.style.animation = '';

                if (forward) {
                    currentPage++;
                } else {
                    currentPage--;
                }

                const nextPageElement = document.getElementById(`page${currentPage}`);
                nextPageElement.classList.add('active');

                // Focus first input on new page (if it's a form page)
                if (currentPage > 0 && currentPage <= formPages) {
                    const firstInput = nextPageElement.querySelector('input, textarea, select');
                    if (firstInput) {
                        setTimeout(() => firstInput.focus(), 300);
                    }
                }
            }, 300);
        }

        function validatePage(pageNum) {
            let isValid = true;
            let errorMessage = '';
            let firstErrorElement = null;

            if (pageNum === 1) {
                const requiredFields = ['mc_username', 'age', 'discord', 'email', 'active_hours'];
                requiredFields.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        element.style.borderColor = 'var(--primary-red)';
                        element.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                        isValid = false;
                        errorMessage = 'Please fill in all required fields';
                        if (!firstErrorElement) firstErrorElement = element;
                    }
                });

                // Age validation
                const age = document.getElementById('age').value;
                if (age && (age < 10 || age > 99)) {
                    const ageInput = document.getElementById('age');
                    ageInput.style.borderColor = 'var(--primary-red)';
                    ageInput.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                    isValid = false;
                    errorMessage = 'Age must be between 10 and 99';
                    if (!firstErrorElement) firstErrorElement = ageInput;
                }

                // Email validation
                const email = document.getElementById('email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && !emailRegex.test(email)) {
                    const emailInput = document.getElementById('email');
                    emailInput.style.borderColor = 'var(--primary-red)';
                    emailInput.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                    if (!firstErrorElement) firstErrorElement = emailInput;
                }

                const playDuration = document.querySelector('input[name="play_duration"]:checked');
                const playTime = document.querySelector('input[name="play_time"]:checked');

                if (!playDuration || !playTime) {
                    errorMessage = 'Please answer all questions';
                    isValid = false;
                }
            }

            if (pageNum === 2) {
                const requiredTextareas = ['server_experience', 'main_target', 'consistency'];
                requiredTextareas.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        element.style.borderColor = 'var(--primary-red)';
                        element.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                        isValid = false;
                        errorMessage = 'Please answer all questions';
                        if (!firstErrorElement) firstErrorElement = element;
                    }
                });

                const skillLevel = document.querySelector('input[name="skill_level"]:checked');
                if (!skillLevel) {
                    errorMessage = 'Please select your skill level';
                    isValid = false;

                    // Highlight all radio options
                    document.querySelectorAll('input[name="skill_level"]').forEach(radio => {
                        radio.parentElement.style.borderColor = 'var(--primary-red)';
                        radio.parentElement.style.background = 'rgba(255, 51, 51, 0.1)';
                    });
                }
            }

            if (pageNum === 3) {
                const requiredTextareas = ['base_stolen', 'attitude_newbies', 'past_conflict', 'reaction_loss', 'fair_play', 'rule_violation', 'bug_response', 'admin_disagreement', 'strength', 'weakness'];
                requiredTextareas.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        element.style.borderColor = 'var(--primary-red)';
                        element.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                        isValid = false;
                        errorMessage = 'Please answer all questions';
                        if (!firstErrorElement) firstErrorElement = element;
                    }
                });

                const personalityType = document.querySelector('input[name="personality_type"]:checked');
                if (!personalityType) {
                    errorMessage = 'Please select which player type describes you';
                    isValid = false;

                    // Highlight all radio options
                    document.querySelectorAll('input[name="personality_type"]').forEach(radio => {
                        radio.parentElement.style.borderColor = 'var(--primary-red)';
                        radio.parentElement.style.background = 'rgba(255, 51, 51, 0.1)';
                    });
                }
            }

            if (pageNum === 4) {
                const requiredTextareas = ['important_rule', 'commitment_reason', 'why_accept'];
                requiredTextareas.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        element.style.borderColor = 'var(--primary-red)';
                        element.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                        isValid = false;
                        errorMessage = 'Please answer all questions';
                        if (!firstErrorElement) firstErrorElement = element;
                    }
                });

                const checkboxes = ['agree_rules', 'agree_sanctions', 'agree_trial'];
                checkboxes.forEach(checkboxId => {
                    const checkbox = document.getElementById(checkboxId);
                    if (!checkbox.checked) {
                        checkbox.parentElement.style.color = 'var(--primary-red)';
                        checkbox.parentElement.style.borderColor = 'var(--primary-red)';
                        isValid = false;
                        errorMessage = 'Please agree to all requirements';
                        if (!firstErrorElement) firstErrorElement = checkbox;
                    }
                });
            }

            if (!isValid) {
                // Show error with animation
                const errorDiv = document.createElement('div');
                errorDiv.innerHTML = `
                    <div style="position: fixed; top: 20px; right: 20px; background: var(--primary-red); color: white; padding: 15px 20px; border-radius: 6px; z-index: 10000; box-shadow: 0 4px 15px rgba(255, 51, 51, 0.3); animation: slideInRight 0.3s ease;">
                        <i class="fas fa-exclamation-circle"></i> ${errorMessage}
                    </div>
                `;

                // Remove existing error
                const existingError = document.querySelector('.error-notification');
                if (existingError) existingError.remove();

                errorDiv.className = 'error-notification';
                document.body.appendChild(errorDiv);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);

                // Scroll to first error
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorElement.focus();
                }
            }

            return isValid;
        }

        // Form submission
        document.getElementById('whitelistForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validatePage(4)) {
                return;
            }

            // Collect all form data
            const formData = new FormData(this);
            const data = {};

            // Handle checkboxes (expertise array)
            const expertise = [];
            document.querySelectorAll('input[name="expertise[]"]:checked').forEach(cb => {
                expertise.push(cb.value);
            });

            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key === 'expertise[]') {
                    if (!data.expertise) data.expertise = [];
                    data.expertise.push(value);
                } else {
                    data[key] = value;
                }
            }

            // Convert expertise array to string
            if (data.expertise) {
                data.expertise = data.expertise.join(', ');
            }

            // Show loading with animation
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Application...';
            submitBtn.disabled = true;

            // Add loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 10, 10, 0.9); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white;">
                    <div class="spinner" style="width: 50px; height: 50px; border: 4px solid rgba(255, 51, 51, 0.3); border-top: 4px solid var(--primary-red); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
                    <p style="font-size: 1.2rem; color: var(--light-red);">Submitting your application...</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);

            // Add spinner animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateX(0); }
                    to { opacity: 0; transform: translateX(-20px); }
                }
            `;
            document.head.appendChild(style);

            // Send to server
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    // Remove loading overlay
                    loadingOverlay.remove();
                    style.remove();

                    if (result.success) {
                        // Show success page with celebration effect
                        document.getElementById('page4').classList.remove('active');
                        document.getElementById('page5').classList.add('active');
                        document.getElementById('applicationId').textContent = result.application_id;

                        // Add confetti effect
                        setTimeout(() => {
                            createConfetti();
                        }, 500);
                    } else {
                        // Show error
                        const errorDiv = document.createElement('div');
                        errorDiv.innerHTML = `
                        <div style="position: fixed; top: 20px; right: 20px; background: var(--primary-red); color: white; padding: 15px 20px; border-radius: 6px; z-index: 10000; box-shadow: 0 4px 15px rgba(255, 51, 51, 0.3);">
                            <i class="fas fa-exclamation-circle"></i> Error: ${result.message}
                        </div>
                    `;
                        document.body.appendChild(errorDiv);

                        setTimeout(() => {
                            errorDiv.remove();
                        }, 5000);

                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Remove loading overlay
                    loadingOverlay.remove();
                    style.remove();

                    // Show error
                    const errorDiv = document.createElement('div');
                    errorDiv.innerHTML = `
                    <div style="position: fixed; top: 20px; right: 20px; background: var(--primary-red); color: white; padding: 15px 20px; border-radius: 6px; z-index: 10000; box-shadow: 0 4px 15px rgba(255, 51, 51, 0.3);">
                        <i class="fas fa-exclamation-circle"></i> An error occurred while submitting. Please try again.
                    </div>
                `;
                    document.body.appendChild(errorDiv);

                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);

                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        function goToHome() {
            // Add transition effect
            document.body.style.opacity = '0.7';
            document.body.style.transition = 'opacity 0.3s ease';

            setTimeout(() => {
                window.location.href = '../index.html';
            }, 300);
        }


        // Add real-time validation and interactive effects
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('input', function () {
                this.style.borderColor = '';
                this.style.boxShadow = '';

                // Remove error from parent if radio/checkbox
                if (this.type === 'radio' || this.type === 'checkbox') {
                    this.parentElement.style.borderColor = '';
                    this.parentElement.style.background = '';
                }
            });

            element.addEventListener('focus', function () {
                this.style.boxShadow = '0 0 0 2px rgba(255, 51, 51, 0.2)';
                this.parentElement.style.borderColor = 'var(--primary-red)';
            });

            element.addEventListener('blur', function () {
                this.style.boxShadow = '';
                this.parentElement.style.borderColor = '';
            });
        });

        // Confetti effect function
        function createConfetti() {
            const colors = ['#ff3333', '#ff6666', '#cc0000', '#ffcc00', '#ffffff'];
            const confettiCount = 100;

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-20px';
                confetti.style.opacity = '0.8';
                confetti.style.zIndex = '9998';
                confetti.style.pointerEvents = 'none';

                document.body.appendChild(confetti);

                // Animation
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight + 20}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                });

                animation.onfinish = () => confetti.remove();
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                if (currentPage < formPages) {
                    nextPage();
                }
            }

            if (e.key === 'Escape') {
                if (currentPage > 0) {
                    prevPage();
                }
            }
        });

        // Add smooth scroll to top when page changes
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('active')) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            });
        });

        // Observe all form pages
        document.querySelectorAll('.form-page').forEach(page => {
            observer.observe(page, { attributes: true });
        });