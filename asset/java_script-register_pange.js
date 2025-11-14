// Form Navigation
function nextPage() {
    const email = document.getElementById('email').value;
    
    // Validasi email
    if (!email || !isValidEmail(email)) {
        alert('Harap masukkan email yang valid!');
        return;
    }
    
    document.getElementById('page1').classList.remove('active');
    document.getElementById('page2').classList.add('active');
    
    // Scroll ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevPage() {
    document.getElementById('page2').classList.remove('active');
    document.getElementById('page1').classList.add('active');
    
    // Scroll ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Form submission
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validasi semua field
    const requiredFields = [
        'username', 'minecraftType', 'discord', 'skills', 
        'experience', 'reason', 'diamond'
    ];
    
    let isValid = true;
    let errorMessage = '';
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field) || document.querySelector(`[name="${field}"]:checked`);
        if (!element || !element.value) {
            isValid = false;
            errorMessage = 'Harap lengkapi semua field yang wajib diisi!';
        }
    });
    
    if (!isValid) {
        alert(errorMessage);
        return;
    }
    
    // Kumpulkan data form
    const formData = {
        email: document.getElementById('email').value,
        username: document.getElementById('username').value,
        minecraftType: document.getElementById('minecraftType').value,
        discord: document.getElementById('discord').value,
        socialMedia: document.getElementById('socialMedia').value,
        skills: document.getElementById('skills').value,
        experience: document.getElementById('experience').value,
        reason: document.getElementById('reason').value,
        adminMessage: document.getElementById('adminMessage').value,
        diamond: document.querySelector('input[name="diamond"]:checked').value
    };
    
    // Simulasi pengiriman form
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    submitBtn.disabled = true;
    
    // Kirim data ke backend PHP
    fetch('process_registration.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessPage(data.registration_id);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim data. Silakan coba lagi.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    
    emailInput.addEventListener('blur', function() {
        if (this.value && !isValidEmail(this.value)) {
            this.style.borderColor = '#ff3333';
            this.style.boxShadow = '0 0 10px rgba(255, 51, 51, 0.5)';
        } else {
            this.style.borderColor = '#444';
            this.style.boxShadow = 'none';
        }
    });
});

// Auto-focus on email input
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.focus();
    }
});

// Additional functions for success page
function showSuccessPage(registrationId) {
    document.getElementById('page2').classList.remove('active');
    document.getElementById('page3').classList.add('active');
    if (registrationId) {
        document.getElementById('registrationId').textContent = registrationId;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goToHome() {
    window.location.href = '../index.html';
}

function newRegistration() {
    document.getElementById('page3').classList.remove('active');
    document.getElementById('page1').classList.add('active');
    document.getElementById('registrationForm').reset();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}