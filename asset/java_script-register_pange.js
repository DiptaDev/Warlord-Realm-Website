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
    
    // Simulasi pengiriman form
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    submitBtn.disabled = true;
    
    // Simulasi proses pengiriman
    setTimeout(() => {
        alert('Pendaftaran berhasil dikirim! kami menghubungi Anda melalui email dan Discord atau Whatsapp.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Reset form atau redirect
        document.getElementById('registrationForm').reset();
        prevPage(); // Kembali ke halaman pertama
    }, 2000);
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