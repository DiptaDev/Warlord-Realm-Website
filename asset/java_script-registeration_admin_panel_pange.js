    // Responsive JavaScript enhancements
    function initResponsiveFeatures() {
        // Update table cells with data-label attributes for mobile view
        const tableHeaders = document.querySelectorAll('table thead th');
        const tableRows = document.querySelectorAll('table tbody tr');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (index < tableHeaders.length) {
                    const headerText = tableHeaders[index].textContent;
                    cell.setAttribute('data-label', headerText);
                }
            });
        });
        
        // Make status filter work
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                filterByStatus(this.value);
            });
        }
        
        // Make search input work with Enter key
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchApplications();
                }
            });
        }
        
        // Adjust modal for mobile
        window.addEventListener('resize', adjustModalLayout);
        adjustModalLayout();
    }
    
    function adjustModalLayout() {
        const modalContent = document.getElementById('modalContent');
        if (!modalContent) return;
        
        const screenWidth = window.innerWidth;
        const modalDivs = modalContent.querySelectorAll('div[style*="grid-template-columns"]');
        
        modalDivs.forEach(div => {
            if (screenWidth <= 768) {
                div.style.gridTemplateColumns = '1fr !important';
                div.style.gap = '15px !important';
            } else if (screenWidth <= 992) {
                div.style.gridTemplateColumns = 'repeat(2, 1fr) !important';
            }
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('viewModal');
    const closeBtn = document.querySelector('.close');
    const modalContent = document.getElementById('modalContent');
    
    function viewApplication(data) {
        modalContent.innerHTML = `
            <div style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-id-card"></i> Informasi Dasar
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                        <div><strong>Username Minecraft:</strong><br><strong style="color: #ff6666;">${data.mc_username}</strong></div>
                        <div><strong>Umur:</strong><br>${data.age} tahun</div>
                        <div><strong>Discord:</strong><br>${data.discord}</div>
                        <div><strong>Email:</strong><br>${data.email}</div>
                        <div><strong>Durasi Bermain:</strong><br>${data.play_duration}</div>
                        <div><strong>Waktu Bermain/Hari:</strong><br>${data.play_time}</div>
                        <div><strong>Jam Aktif:</strong><br>${data.active_hours}</div>
                        <div><strong>Level Skill:</strong><br>${data.skill_level}</div>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Konsistensi:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.consistency}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-graduation-cap"></i> Pengalaman & Keahlian
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Pengalaman Server:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.server_experience}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Keahlian:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.expertise || '<em style="color: #888;">Tidak diisi</em>'}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Target Utama:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.main_target}
                        </div>
                    </div>
                    <div>
                        <strong>Tipe Kepribadian:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.personality_type}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-brain"></i> Kepribadian & Sikap
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika base dicuri:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.base_stolen}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Sikap ke pemula:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.attitude_newbies}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Konflik sebelumnya:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.past_conflict}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Reaksi kehilangan:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.reaction_loss}
                        </div>
                    </div>
                    <div>
                        <strong>Definisi fair play:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.fair_play}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-scale-balanced"></i> Kejujuran & Moral
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Pelanggaran aturan:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.rule_violation}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika menemukan bug:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.bug_response}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika tak setuju admin:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.admin_disagreement}
                        </div>
                    </div>
                    <div>
                        <strong>Aturan terpenting:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.important_rule}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-star"></i> Kelebihan & Kekurangan
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Kelebihan:</strong><br>
                            <div style="background: rgba(0,255,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                                ${data.strength}
                            </div>
                        </div>
                        <div>
                            <strong>Kekurangan:</strong><br>
                            <div style="background: rgba(255,0,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                                ${data.weakness}
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-file-signature"></i> Komitmen & Kontribusi
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Alasan komitmen:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.commitment_reason}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Kenapa harus diterima:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.why_accept}
                        </div>
                    </div>
                    ${data.contribution_willingness ? `
                    <div style="margin-bottom: 15px;">
                        <strong>Kesediaan kontribusi:</strong><br>
                        <div style="background: rgba(255,200,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.contribution_willingness}
                        </div>
                    </div>
                    ` : ''}
                    ${data.contribution_type ? `
                    <div>
                        <strong>Jenis kontribusi:</strong><br>
                        <div style="background: rgba(255,200,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.contribution_type}
                        </div>
                    </div>
                    ` : ''}
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> Informasi Admin
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div><strong>ID Aplikasi:</strong><br><strong>#${data.id}</strong></div>
                        <div><strong>Status:</strong><br><span class="status-badge status-${data.status}">${data.status}</span></div>
                        <div><strong>Tanggal:</strong><br>${new Date(data.application_date).toLocaleString('id-ID')}</div>
                    </div>
                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                        ${data.status !== 'interview' ? `
                        <button type="button" class="btn btn-interview" onclick="updateStatus(${data.id}, 'interview')">
                            <i class="fas fa-comments"></i> Set Interview
                        </button>
                        ` : ''}
                        
                        ${data.status !== 'trial' ? `
                        <button type="button" class="btn btn-trial" onclick="updateStatus(${data.id}, 'trial')">
                            <i class="fas fa-user-clock"></i> Set Trial
                        </button>
                        ` : ''}
                        
                        ${data.status !== 'approved' ? `
                        <button type="button" class="btn btn-approve" onclick="updateStatus(${data.id}, 'approve')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        ` : ''}
                        
                        ${data.status !== 'rejected' ? `
                        <button type="button" class="btn btn-reject" onclick="updateStatus(${data.id}, 'reject')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        // Adjust modal layout for current screen size
        adjustModalLayout();
        modal.style.display = 'block';
    }

    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Update status function
    function updateStatus(applicationId, action) {
        const actionTexts = {
            'approve': 'approve',
            'reject': 'reject', 
            'interview': 'set interview',
            'trial': 'set trial',
            'pending': 'set pending'
        };

        if (!confirm(`Are you sure you want to ${actionTexts[action]} this application?`)) {
            return;
        }
        
        const buttons = document.querySelectorAll(`button[onclick*="updateStatus(${applicationId}, '${action}')"]`);
        
        buttons.forEach(button => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        });

        const formData = new FormData();
        formData.append('id', applicationId);
        formData.append('action', action);

        fetch('admin_panel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat update status.');
            
            buttons.forEach(button => {
                button.innerHTML = button.getAttribute('data-original') || originalText;
                button.disabled = false;
            });
        });
    }

    // Search functionality
    function searchApplications() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter by status
    function filterByStatus(status) {
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge && statusBadge.classList.contains(`status-${status}`)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Initialize responsive features when page loads
    document.addEventListener('DOMContentLoaded', initResponsiveFeatures);