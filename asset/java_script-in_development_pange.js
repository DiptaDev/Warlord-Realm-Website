// Progress bar dengan nilai tetap
function setProgressBar(percentage) {
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    
    // Pastikan percentage antara 0-100
    percentage = Math.max(0, Math.min(100, percentage));
    
    // Set width progress bar
    progressFill.style.width = percentage + '%';
    
    // Update text
    progressText.textContent = `Progress: ${percentage}%`;
    
    // Jika sudah 100%, tambah efek khusus
    if (percentage === 100) {
        progressFill.style.background = 'linear-gradient(90deg, #00ff00, #00cc00)';
        progressText.style.color = '#00ff00';
        progressText.textContent = 'Progress: 100% - Selesai! Released SOON!';
        
        // Tambah animasi berkedip untuk progress 100%
        progressFill.style.animation = 'completePulse 1.5s infinite';
    }
}

// Animasi halus untuk progress bar (tanpa mengubah nilai)
function animateProgressBar() {
    const progressFill = document.querySelector('.progress-fill');
    
    // Hanya animasi glow, tidak mengubah width
    setInterval(() => {
        progressFill.style.opacity = progressFill.style.opacity === '0.9' ? '1' : '0.9';
    }, 1000);
}

// Update progress secara bertahap (contoh: dari 0% ke target)
function animateToTargetPercentage(targetPercentage, duration = 2000) {
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    
    const startPercentage = 0;
    const startTime = performance.now();
    
    function updateProgress(currentTime) {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1);
        
        // Easing function untuk animasi smooth
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentPercentage = startPercentage + (targetPercentage - startPercentage) * easeOutQuart;
        
        progressFill.style.width = currentPercentage + '%';
        progressText.textContent = `Progress: ${Math.round(currentPercentage)}%`;
        
        if (progress < 1) {
            requestAnimationFrame(updateProgress);
        } else {
            // Set nilai akhir yang tepat
            setProgressBar(targetPercentage);
        }
    }
    
    requestAnimationFrame(updateProgress);
}

// Experimental Access Functions
function accessExperimental() {
    const confirmAccess = confirm(
        "⚠️ PERINGATAN BETA ACCESS ⚠️\n\n" +
        "Kamu akan mengakses website yang masih dalam tahap pengembangan.\n" +
        "Fitur mungkin tidak stabil dan dapat berubah sewaktu-waktu.\n\n" +
        "Apakah Kamu ingin melanjutkan?"
    );
    
    if (confirmAccess) {
        // Redirect ke halaman beta/experimental
        // Ganti URL dengan halaman experimental Anda
        window.location.href = "../index.html"; // atau halaman beta lainnya
    }
}

function showExperimentalDetails() {
    document.getElementById('betaModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('betaModal').style.display = 'none';
}

// Close modal jika klik di luar konten
window.onclick = function(event) {
    const modal = document.getElementById('betaModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Keyboard escape to close modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // HAPUS: updateCountdown() - countdown dihilangkan
    
    // Set progress bar ke nilai yang diinginkan
    const currentProgress = 95; // Nilai bisa diubah dari 0-100
    setProgressBar(currentProgress);
    
    // Animasi ke target progress
    animateToTargetPercentage(95, 4000);
    
    // Animasi halus tanpa mengubah nilai
    animateProgressBar();
    
    // Add some interactive effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Tambah efek khusus untuk tombol experimental
    const experimentalBtn = document.querySelector('.btn-experimental');
    if (experimentalBtn) {
        experimentalBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        experimentalBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }
});

// Fungsi untuk update progress dari luar (jika diperlukan)
function updateDevelopmentProgress(newPercentage) {
    setProgressBar(newPercentage);
}