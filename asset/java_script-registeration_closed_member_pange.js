        // Handle logo error
        function handleLogoError(img) {
            const container = img.parentElement;
            container.innerHTML = `
                <div class="logo-glow"></div>
                <div class="logo-image" style="display: flex; align-items: center; justify-content: center; 
                     background: linear-gradient(135deg, #1a1a1a 0%, #330000 100%);">
                    <i class="fas fa-crown" style="font-size: 4rem; color: #cc0000;"></i>
                </div>
            `;
        }
        
        // Fungsi interaktif untuk status badge
        function toggleStatusMessage() {
            const badge = document.querySelector('.status-badge');
            const originalText = 'REGISTRATION CLOSED';
            const tempText = 'Will Reopen Soon!';
            
            badge.innerHTML = badge.innerHTML.includes(originalText) 
                ? `<i class="fas fa-clock"></i> ${tempText}`
                : `<i class="fas fa-lock"></i> ${originalText}`;
        }
        
        // Fungsi untuk menampilkan info registrasi
        function showRegistrationInfo() {
            alert('Registration will reopen when server optimization is complete. Check Discord for updates!');
        }
        
        // Fungsi untuk toggle info box
        function toggleBoxInfo(box) {
            const icon = box.querySelector('.info-icon i');
            const p = box.querySelector('p');
            const originalText = p.textContent;
            
            // Toggle class aktif
            box.classList.toggle('active');
            
            if (box.classList.contains('active')) {
                // Ubah ke mode expanded
                icon.style.transform = 'scale(1.3) rotate(180deg)';
                p.innerHTML = `<strong>ℹ️ More Info: </strong><br>Check our Discord for real-time updates and community discussions about when registrations will reopen.`;
            } else {
                // Kembali ke normal
                icon.style.transform = '';
                p.textContent = originalText;
            }
        }
        
        // Inisialisasi sederhana tanpa animasi bertahap
        document.addEventListener('DOMContentLoaded', function() {
            // Preload gambar untuk performa
            const logo = document.getElementById('serverLogo');
            if (logo) {
                const img = new Image();
                img.src = logo.src;
            }
            
            // Tambahkan efek klik sederhana
            const clickableElements = document.querySelectorAll('.info-box, .btn, .status-badge, .footer-link, .highlight');
            
            clickableElements.forEach(el => {
                el.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });