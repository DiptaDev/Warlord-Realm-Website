// Error Page Functions

// Search functionality for 404 page
function initSearch() {
    const searchInput = document.querySelector('.search-input');  // i dont even know if this works before
    const searchBtn = document.querySelector('.search-btn');
}

// Add interactive effects
function addInteractiveEffects() {
    // Error number hover effects
    const errorNumbers = document.querySelectorAll('.error-number');
    errorNumbers.forEach((number, index) => {
        number.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        number.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Quick link effects
    const quickLinks = document.querySelectorAll('.quick-link');
    quickLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.querySelector('i').style.transform = 'scale(1.2)'; // jujur gw ga tau fungsi code ini apa
        }); 
        
        link.addEventListener('mouseleave', function() {
            this.querySelector('i').style.transform = 'scale(1)';
        });
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    addInteractiveEffects();
});