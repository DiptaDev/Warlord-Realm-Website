document.addEventListener('DOMContentLoaded', function () {
    // Ganti placeholder logo dengan logo server asli
    const logoImg = document.getElementById('server-logo');
    // Uncomment dan ganti dengan URL logo server Anda
    // logoImg.src = "./asset/logo.jpg";

    // Efek hover pada tombol Discord
    const discordButton = document.querySelector('.discord-button');
    discordButton.addEventListener('mouseenter', function () {
        this.querySelector('i').style.transform = 'rotate(15deg) scale(1.2)';
    });

    discordButton.addEventListener('mouseleave', function () {
        this.querySelector('i').style.transform = 'rotate(0deg) scale(1)';
    });

    // Efek glow pada judul maintenance
    const maintenanceTitle = document.querySelector('.maintenance-title');
    let glowIntensity = 0.4;
    let increasing = true;

    setInterval(() => {
        if (increasing) {
            glowIntensity += 0.01;
            if (glowIntensity >= 0.6) increasing = false;
        } else {
            glowIntensity -= 0.01;
            if (glowIntensity <= 0.3) increasing = true;
        }

        maintenanceTitle.style.textShadow = `0 0 20px rgba(255, 51, 51, ${glowIntensity})`;
    }, 100);

    // Logo hover effect
    const logoContainer = document.querySelector('.logo-container');
    logoContainer.addEventListener('mouseenter', function () {
        this.style.transform = 'scale(1.05)';
        this.style.boxShadow = '0 15px 40px rgba(255, 0, 0, 0.4)';
    });

    logoContainer.addEventListener('mouseleave', function () {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = '0 10px 30px rgba(255, 0, 0, 0.3)';
    });
});