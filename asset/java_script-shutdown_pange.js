    // Spawn falling pixel particles
    const container = document.getElementById('particles');
    for (let i = 0; i < 30; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = `
            left: ${Math.random() * 100}%;
            width: ${Math.random() * 5 + 3}px;
            height: ${Math.random() * 5 + 3}px;
            opacity: ${Math.random() * 0.5 + 0.1};
            animation-duration: ${Math.random() * 10 + 6}s;
            animation-delay: ${Math.random() * 8}s;
        `;
        container.appendChild(p);
    }

    // Replace logo placeholder with actual logo if available
    document.addEventListener('DOMContentLoaded', function () {
        const logoContainer = document.querySelector('.logo-container');
        const img = new Image();
        img.src = 'asset/logo.jpg';
        img.onload = () => {
            logoContainer.innerHTML = `<img src="asset/logo.jpg" alt="Warlord Network" style="width:99%;height:99%;border-radius:50%;object-fit:cover;">`;
        };
    });