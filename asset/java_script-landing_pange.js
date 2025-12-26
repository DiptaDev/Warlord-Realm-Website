function showServerInfo() {
    document.getElementById('serverInfo').style.display = 'block';
    document.getElementById('joinInfo').style.display = 'none';
    // Scroll ke informasi server
    document.getElementById('serverInfo').scrollIntoView({ behavior: 'smooth' });
}

function showJoinInfo() {
    document.getElementById('joinInfo').style.display = 'block';
    document.getElementById('serverInfo').style.display = 'none';
    // Scroll ke informasi bergabung
    document.getElementById('joinInfo').scrollIntoView({ behavior: 'smooth' });
}

function hideServerInfo() {
    document.getElementById('serverInfo').style.display = 'none';
    // Scroll kembali ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideJoinInfo() {
    document.getElementById('joinInfo').style.display = 'none';
    // Scroll kembali ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Ganti logo dengan gambar
document.addEventListener('DOMContentLoaded', function () {
    // Anda bisa mengganti ini dengan URL gambar logo Anda
    const logoContainer = document.querySelector('.logo-container');
    logoContainer.innerHTML = '<img src="asset/logo.jpg" alt="Warlord Network" style="width:99%;height:99%;border-radius:55%;object-fit:cover;">';
});

console.log("gw bingung milih kinara atau kayla")