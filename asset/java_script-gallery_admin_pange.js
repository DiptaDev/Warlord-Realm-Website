        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);

        // Edit Modal Functionality
        const editModal = document.getElementById('editModal');
        const editTriggers = document.querySelectorAll('.edit-trigger');
        const cancelEdit = document.getElementById('cancelEdit');
        const editImageId = document.getElementById('editImageId');
        const editTitle = document.getElementById('editTitle');
        const editDescription = document.getElementById('editDescription');

        editTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                editImageId.value = this.getAttribute('data-id');
                editTitle.value = this.getAttribute('data-title');
                editDescription.value = this.getAttribute('data-description');
                editModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        cancelEdit.addEventListener('click', function() {
            editModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Close edit modal when clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                editModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Preview Modal Functionality
        const previewModal = document.getElementById('previewModal');
        const previewImage = document.getElementById('previewImage');
        const closePreview = document.getElementById('closePreview');
        const prevPreview = document.getElementById('prevPreview');
        const nextPreview = document.getElementById('nextPreview');
        const previewTriggers = document.querySelectorAll('.preview-trigger');
        
        let previewImages = [];
        let currentPreviewIndex = 0;
        
        // Collect all images for preview
        function collectPreviewImages() {
            previewImages = [];
            document.querySelectorAll('.preview-trigger').forEach((img, index) => {
                previewImages.push({
                    src: img.getAttribute('data-src'),
                    title: img.getAttribute('data-title'),
                    description: img.getAttribute('data-description'),
                    index: index
                });
            });
        }
        
        // Open preview modal
        previewTriggers.forEach((trigger, index) => {
            trigger.addEventListener('click', function() {
                collectPreviewImages();
                currentPreviewIndex = index;
                openPreview(currentPreviewIndex);
            });
        });
        
        function openPreview(index) {
            const image = previewImages[index];
            previewImage.src = image.src;
            previewImage.alt = image.title;
            previewModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Update navigation buttons
            updatePreviewNavigation();
        }
        
        function closePreviewModal() {
            previewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function prevPreviewImage() {
            if (currentPreviewIndex > 0) {
                currentPreviewIndex--;
                openPreview(currentPreviewIndex);
            }
        }
        
        function nextPreviewImage() {
            if (currentPreviewIndex < previewImages.length - 1) {
                currentPreviewIndex++;
                openPreview(currentPreviewIndex);
            }
        }
        
        function updatePreviewNavigation() {
            prevPreview.style.display = currentPreviewIndex > 0 ? 'flex' : 'none';
            nextPreview.style.display = currentPreviewIndex < previewImages.length - 1 ? 'flex' : 'none';
        }
        
        // Event listeners for preview modal
        closePreview.addEventListener('click', closePreviewModal);
        prevPreview.addEventListener('click', prevPreviewImage);
        nextPreview.addEventListener('click', nextPreviewImage);
        
        // Close preview modal when clicking outside
        previewModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });
        
        // Keyboard navigation for preview
        document.addEventListener('keydown', function(e) {
            if (previewModal.style.display === 'flex') {
                if (e.key === 'Escape') {
                    closePreviewModal();
                } else if (e.key === 'ArrowLeft') {
                    prevPreviewImage();
                } else if (e.key === 'ArrowRight') {
                    nextPreviewImage();
                }
            }
        });
        
        // Touch swipe for preview
        let touchStartX = 0;
        let touchEndX = 0;
        
        previewImage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        previewImage.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handlePreviewSwipe();
        });
        
        function handlePreviewSwipe() {
            const diff = touchStartX - touchEndX;
            const swipeThreshold = 50;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextPreviewImage();
                } else {
                    prevPreviewImage();
                }
            }
        }