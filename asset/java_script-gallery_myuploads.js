        document.addEventListener('DOMContentLoaded', function() {
            // Image Modal functionality
            const imageModal = document.getElementById('imageModal');
            const modalOverlay = document.getElementById('modalOverlay');
            const modalClose = document.getElementById('modalClose');
            const modalImage = document.getElementById('modalImage');
            const imageLoader = document.getElementById('imageLoader');
            const modalTitle = document.getElementById('modalTitle');
            const modalDate = document.getElementById('modalDate');
            const modalStatus = document.getElementById('modalStatus');
            const modalDescription = document.getElementById('modalDescription');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const currentImageSpan = document.getElementById('currentImage');
            const totalImagesSpan = document.getElementById('totalImages');
            const downloadBtn = document.getElementById('downloadBtn');
            
            let currentImageIndex = 0;
            let images = [];
            let touchStartX = 0;
            let touchEndX = 0;
            const swipeThreshold = 50;
            
            // Collect all gallery images
            function collectImages() {
                const galleryCards = document.querySelectorAll('.upload-card');
                images = [];
                
                galleryCards.forEach((card, index) => {
                    const img = card.querySelector('.upload-image');
                    const title = card.querySelector('.upload-title').textContent;
                    const dateElement = card.querySelector('.upload-date');
                    const statusElement = card.querySelector('.status-badge');
                    const description = card.getAttribute('data-description') || '';
                    
                    // Extract date from the date element
                    let dateText = dateElement.textContent;
                    // Get just the uploaded date (first line)
                    const dateMatch = dateText.match(/[A-Za-z]+ \d{1,2}, \d{4}/);
                    const date = dateMatch ? dateMatch[0] : 'Unknown date';
                    
                    // Get status text and color
                    let statusText = 'Unknown';
                    let statusColor = '#9e9e9e';
                    
                    if (statusElement) {
                        statusText = statusElement.textContent.trim();
                        // Check which status class is present
                        if (statusElement.classList.contains('status-pending')) {
                            statusColor = '#FF9800';
                        } else if (statusElement.classList.contains('status-approved')) {
                            statusColor = '#4CAF50';
                        } else if (statusElement.classList.contains('status-rejected')) {
                            statusColor = '#d32f2f';
                        }
                    }
                    
                    images.push({
                        src: img.src,
                        title: title,
                        description: description,
                        date: date,
                        status: statusText,
                        statusColor: statusColor,
                        index: index
                    });
                });
                
                totalImagesSpan.textContent = images.length;
            }
            
            // Open modal with image
            function openModal(index) {
                if (images.length === 0) return;
                
                currentImageIndex = index;
                const image = images[currentImageIndex];
                
                // Show modal
                imageModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Set image info
                modalTitle.textContent = image.title;
                modalDate.textContent = image.date;
                modalStatus.textContent = image.status;
                modalStatus.style.color = image.statusColor;
                
                // Show/hide description
                if (image.description) {
                    modalDescription.style.display = 'block';
                    modalDescription.querySelector('p').textContent = image.description;
                } else {
                    modalDescription.style.display = 'none';
                }
                
                // Show loader and hide image
                imageLoader.classList.add('active');
                modalImage.style.opacity = '0';
                
                // Load image with size check
                const img = new Image();
                img.onload = function() {
                    // Check if image is very large
                    if (this.naturalWidth > 2000 || this.naturalHeight > 2000) {
                        // For very large images, apply additional constraints
                        modalImage.style.maxWidth = '90%';
                        modalImage.style.maxHeight = '55vh';
                    } else {
                        // Reset to default
                        modalImage.style.maxWidth = '';
                        modalImage.style.maxHeight = '';
                    }
                    
                    modalImage.src = image.src;
                    modalImage.alt = image.title;
                    modalImage.style.opacity = '1';
                    imageLoader.classList.remove('active');
                    
                    // Update counter
                    currentImageSpan.textContent = currentImageIndex + 1;
                };
                
                img.onerror = function() {
                    imageLoader.classList.remove('active');
                    modalImage.style.opacity = '1';
                    modalImage.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="%23333"/><text x="200" y="150" font-family="Arial" font-size="16" fill="%23fff" text-anchor="middle">Image failed to load</text></svg>';
                };
                
                img.src = image.src;
                
                // Update navigation buttons
                updateNavigation();
            }
            
            // Close modal
            function closeModal() {
                imageModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            // Update navigation buttons state
            function updateNavigation() {
                prevBtn.style.display = currentImageIndex > 0 ? 'flex' : 'none';
                nextBtn.style.display = currentImageIndex < images.length - 1 ? 'flex' : 'none';
            }
            
            // Navigate to previous image
            function prevImage() {
                if (currentImageIndex > 0) {
                    currentImageIndex--;
                    openModal(currentImageIndex);
                }
            }
            
            // Navigate to next image
            function nextImage() {
                if (currentImageIndex < images.length - 1) {
                    currentImageIndex++;
                    openModal(currentImageIndex);
                }
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (imageModal.style.display === 'block') {
                    if (e.key === 'Escape') {
                        closeModal();
                    } else if (e.key === 'ArrowLeft') {
                        prevImage();
                    } else if (e.key === 'ArrowRight') {
                        nextImage();
                    }
                }
            });
            
            // Touch events for swipe
            modalOverlay.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            modalOverlay.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
            
            function handleSwipe() {
                const diff = touchStartX - touchEndX;
                
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Swipe left
                        nextImage();
                    } else {
                        // Swipe right
                        prevImage();
                    }
                }
            }
            
            // Download image
            downloadBtn.addEventListener('click', function() {
                const image = images[currentImageIndex];
                const link = document.createElement('a');
                link.href = image.src;
                link.download = `warlord-realm-${image.title.replace(/\s+/g, '-').toLowerCase()}.jpg`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Event listeners
            modalOverlay.addEventListener('click', closeModal);
            modalClose.addEventListener('click', closeModal);
            prevBtn.addEventListener('click', prevImage);
            nextBtn.addEventListener('click', nextImage);
            
            // Initialize
            collectImages();
            
            // Add click events to gallery images
            document.querySelectorAll('.upload-card').forEach((card, index) => {
                card.addEventListener('click', function() {
                    openModal(index);
                });
            });
            
            // Prevent modal close when clicking inside modal container
            document.querySelector('.modal-container').addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Auto-hide alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.animation = 'slideOutRight 0.5s ease forwards';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                });
            }, 4500);
        });