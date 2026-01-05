        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('image-preview');
            const uploadArea = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfo');
            const fileDetails = document.getElementById('fileDetails');
            const uploadButton = document.getElementById('uploadButton');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    uploadArea.style.display = 'none';
                    
                    // Show file info
                    const fileSize = file.size;
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    
                    fileDetails.textContent = `Selected: ${file.name} (${formatBytes(fileSize)})`;
                    fileInfo.className = 'file-info';
                    fileInfo.style.display = 'block';
                    
                    // Check if image needs compression
                    if (fileSize > 1024 * 1024) { // 1MB
                        fileDetails.innerHTML += `<br><i class="fas fa-compress-alt" style="color: #2196F3;"></i> Will be optimized for web`;
                    }
                    
                    if (fileSize > maxSize) {
                        fileInfo.innerHTML = `<i class="fas fa-exclamation-triangle"></i> 
                            File size (${formatBytes(fileSize)}) exceeds 5MB limit!`;
                        fileInfo.className = 'file-info warning';
                        uploadButton.disabled = true;
                        uploadButton.style.opacity = '0.7';
                    } else {
                        uploadButton.disabled = false;
                        uploadButton.style.opacity = '1';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        }

        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image');

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--accent-red)';
            this.style.backgroundColor = 'rgba(211, 47, 47, 0.2)';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
            this.style.backgroundColor = 'rgba(33, 33, 33, 0.5)';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
            this.style.backgroundColor = 'rgba(33, 33, 33, 0.5)';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                previewImage(fileInput);
            }
        });

        // Show progress bar on form submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('image');
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('File size exceeds 5MB limit. Please choose a smaller image.');
                    return false;
                }
                
                // Show progress bar
                const uploadProgress = document.getElementById('uploadProgress');
                const progressFill = document.getElementById('progressFill');
                const progressText = document.getElementById('progressText');
                const uploadButton = document.getElementById('uploadButton');
                
                uploadProgress.style.display = 'block';
                uploadButton.disabled = true;
                uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // Simulate progress animation
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 5;
                    progressFill.style.width = progress + '%';
                    
                    if (progress < 30) {
                        progressText.textContent = 'Validating image...';
                    } else if (progress < 60) {
                        progressText.textContent = 'Optimizing image...';
                    } else if (progress < 90) {
                        progressText.textContent = 'Uploading to server...';
                    } else {
                        progressText.textContent = 'Finalizing upload...';
                    }
                    
                    if (progress >= 95) {
                        clearInterval(interval);
                    }
                }, 100);
            }
        });

        // File size validation
        fileInput.addEventListener('change', function() {
            const uploadButton = document.getElementById('uploadButton');
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (this.files.length > 0) {
                const fileSize = this.files[0].size;
                
                if (fileSize > maxSize) {
                    uploadButton.disabled = true;
                    uploadButton.style.opacity = '0.7';
                } else {
                    uploadButton.disabled = false;
                    uploadButton.style.opacity = '1';
                }
            }
        });
        
        // Clear any existing preview on page load
        window.addEventListener('load', function() {
            const previewContainer = document.getElementById('image-preview');
            const uploadArea = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfo');
            const fileInput = document.getElementById('image');
            
            // Reset preview
            previewContainer.style.display = 'none';
            uploadArea.style.display = 'flex';
            fileInfo.style.display = 'none';
            fileInput.value = '';
        });