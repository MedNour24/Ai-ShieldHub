/**
 * team.js - Frontend Team Management
 * Handles team creation and joining WITH STRICT VALIDATION
 * Updated to support auto-tournament selection from URL
 */

// Constants
const API_ENDPOINT = 'Back/team_handler.php';
const MAX_MEMBERS = 4;

// State
let memberCount = 0;
let allTeams = [];
let selectedTournoiId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get tournament ID from URL or global variable
    const urlParams = new URLSearchParams(window.location.search);
    selectedTournoiId = urlParams.get('tournoi') || window.selectedTournoiId;
    
    if (selectedTournoiId) {
        // Set the hidden field
        document.getElementById('id_tournoi').value = selectedTournoiId;
        
        // Load teams for this specific tournament only
        loadTeamsByTournoi(selectedTournoiId);
    } else {
        // No tournament selected - load all teams
        loadTeams();
    }
    
    setupFormSubmitHandlers();
});

// ============ TAB SWITCHING ============

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    if (tabName === 'create') {
        document.getElementById('createTab').classList.add('active');
    } else if (tabName === 'join') {
        document.getElementById('joinTab').classList.add('active');
        
        // Reload teams when switching to join tab
        if (selectedTournoiId) {
            loadTeamsByTournoi(selectedTournoiId);
        } else {
            loadTeams();
        }
    }
}

// ============ LOAD TEAMS ============

function loadTeams() {
    const teamsList = document.getElementById('teamsList');
    teamsList.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--light);"><div style="font-size: 48px;">⏳</div><p>Chargement...</p></div>';
    
    fetch(`${API_ENDPOINT}?action=list`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.teams) {
                allTeams = data.data.teams;
                displayTeams(allTeams);
            } else {
                displayNoTeams();
            }
        })
        .catch(error => {
            console.error('Error loading teams:', error);
            showAlert('joinAlert', 'Erreur lors du chargement des équipes', 'error');
        });
}

function loadTeamsByTournoi(tournoiId) {
    if (!tournoiId) {
        loadTeams();
        return;
    }
    
    const teamsList = document.getElementById('teamsList');
    teamsList.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--light);"><div style="font-size: 48px;">⏳</div><p>Chargement des équipes...</p></div>';
    
    fetch(`${API_ENDPOINT}?action=listByTournoi&id_tournoi=${tournoiId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.teams) {
                allTeams = data.data.teams;
                displayTeams(allTeams);
            } else {
                displayNoTeams();
            }
        })
        .catch(error => {
            console.error('Error loading teams:', error);
            showAlert('joinAlert', 'Erreur lors du chargement des équipes', 'error');
        });
}

function displayTeams(teams) {
    const teamsList = document.getElementById('teamsList');
    
    if (!teams || teams.length === 0) {
        displayNoTeams();
        return;
    }
    
    teamsList.innerHTML = teams.map(team => createTeamCard(team)).join('');
}

function displayNoTeams() {
    const teamsList = document.getElementById('teamsList');
    teamsList.innerHTML = `
        <div style="text-align: center; padding: 40px; color: var(--light); opacity: 0.6;">
            <div style="font-size: 48px; margin-bottom: 20px;">🔍</div>
            <p style="font-size: 18px; margin-bottom: 10px;">Aucune équipe disponible pour le moment</p>
            <p style="font-size: 14px; opacity: 0.7;">Soyez le premier à créer une équipe !</p>
        </div>
    `;
}

function createTeamCard(team) {
    const members = Array.isArray(team.members) ? team.members : JSON.parse(team.members || '[]');
    const totalMembers = members.length + 1; // +1 for leader
    const isFull = totalMembers >= 5;
    const statusBadge = isFull 
        ? '<span class="badge-full">✓ Complet</span>' 
        : '<span class="badge-open">Places disponibles</span>';
    
    return `
        <div class="team-card">
            <div class="team-header">
                <div class="team-name">${escapeHtml(team.team_name)}</div>
                <div>
                    <span class="team-tag">${escapeHtml(team.team_tag)}</span>
                </div>
            </div>
            <div class="team-info">
                <div class="team-info-item">
                    <strong>Tournoi:</strong> ${escapeHtml(team.nom_tournoi || 'N/A')}
                </div>
                <div class="team-info-item">
                    <strong>Catégorie:</strong> ${escapeHtml(team.category)}
                </div>
                <div class="team-info-item">
                    <strong>Pays:</strong> ${escapeHtml(team.country)}
                </div>
                <div class="team-info-item">
                    <strong>Membres:</strong> ${totalMembers}/5
                </div>
                <div class="team-info-item">
                    <strong>Leader:</strong> ${escapeHtml(team.leader_name)}
                </div>
                <div class="team-info-item">
                    ${statusBadge}
                </div>
            </div>
            <button class="join-btn" onclick="openJoinModal(${team.id_team}, '${escapeHtml(team.team_name)}')" 
                    ${isFull ? 'disabled' : ''}>
                ${isFull ? '✓ Équipe complète' : '👥 Rejoindre cette équipe'}
            </button>
        </div>
    `;
}

// ============ DYNAMIC MEMBERS ============

function addMember() {
    if (memberCount >= MAX_MEMBERS) {
        showAlert('createAlert', `Maximum ${MAX_MEMBERS} membres additionnels autorisés`, 'error');
        return;
    }
    
    memberCount++;
    const memberHtml = `
        <div class="member-item" id="member-${memberCount}">
            <button type="button" class="remove-member" onclick="removeMember(${memberCount})">×</button>
            <div class="member-number">Membre #${memberCount}</div>
            
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="member_names[]" placeholder="Nom du membre" autocomplete="off">
                <div class="error-message"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="member_emails[]" placeholder="email@exemple.com" autocomplete="off">
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="member_phones[]" placeholder="+216 XX XXX XXX" autocomplete="off">
                    <div class="error-message"></div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('membersContainer').insertAdjacentHTML('beforeend', memberHtml);
    updateAddMemberButton();
    
    // Attach validation to new member inputs
    setTimeout(() => {
        attachValidationToMemberInputs(memberCount);
    }, 100);
}

function attachValidationToMemberInputs(memberIndex) {
    const memberItem = document.getElementById(`member-${memberIndex}`);
    if (!memberItem) return;
    
    const inputs = memberItem.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            const fieldType = this.name.includes('names') ? 'leader_name' 
                            : this.name.includes('emails') ? 'leader_email' 
                            : 'leader_phone';
            
            const result = window.validateField(fieldType, this.value, false);
            if (this.value && !result.valid) {
                const errorDiv = this.parentElement.querySelector('.error-message');
                if (errorDiv) {
                    errorDiv.textContent = result.message;
                    errorDiv.classList.add('show');
                }
                this.classList.add('error');
            }
        });
        
        input.addEventListener('input', function() {
            const errorDiv = this.parentElement.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.classList.remove('show');
                errorDiv.textContent = '';
            }
            this.classList.remove('error');
        });
    });
}

function removeMember(id) {
    const memberElement = document.getElementById(`member-${id}`);
    if (memberElement) {
        memberElement.remove();
        memberCount--;
        updateAddMemberButton();
        renumberMembers();
    }
}

function updateAddMemberButton() {
    const btn = document.getElementById('addMemberBtn');
    if (memberCount >= MAX_MEMBERS) {
        btn.disabled = true;
        btn.textContent = `✓ Maximum de ${MAX_MEMBERS} membres atteint`;
    } else {
        btn.disabled = false;
        btn.textContent = '➕ Ajouter un membre';
    }
}

function renumberMembers() {
    const members = document.querySelectorAll('.member-item');
    members.forEach((member, index) => {
        const numberDiv = member.querySelector('.member-number');
        if (numberDiv) {
            numberDiv.textContent = `Membre #${index + 1}`;
        }
    });
}

// ============ FORM SUBMISSION ============

function setupFormSubmitHandlers() {
    // Create team form
    document.getElementById('createTeamForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if tournament ID is set
        const tournoiId = document.getElementById('id_tournoi').value;
        if (!tournoiId) {
            showAlert('createAlert', 'Erreur: Aucun tournoi sélectionné', 'error');
            return false;
        }
        
        // STRICT JAVASCRIPT VALIDATION
        if (!window.validateCreateTeamForm || !window.validateCreateTeamForm()) {
            console.log('Validation failed - form not submitted');
            return false;
        }
        
        submitCreateForm();
    });
    
    // Join team form
    document.getElementById('joinTeamForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // STRICT JAVASCRIPT VALIDATION
        if (!window.validateJoinTeamForm || !window.validateJoinTeamForm()) {
            console.log('Validation failed - join form not submitted');
            return false;
        }
        
        submitJoinForm();
    });
}

function submitCreateForm() {
    const form = document.getElementById('createTeamForm');
    const formData = new FormData(form);
    formData.append('action', 'create');
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Création en cours...';
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('createAlert', 'Équipe créée avec succès! 🎉', 'success');
            form.reset();
            
            // Reset member count
            memberCount = 0;
            document.getElementById('membersContainer').innerHTML = '';
            updateAddMemberButton();
            
            // Restore tournament ID in hidden field
            if (selectedTournoiId) {
                document.getElementById('id_tournoi').value = selectedTournoiId;
            }
            
            // Switch to join tab after 2 seconds
            setTimeout(() => {
                const joinTab = document.querySelector('.tab-btn:not(.active)');
                if (joinTab) joinTab.click();
            }, 2000);
        } else {
            showAlert('createAlert', data.message || 'Erreur lors de la création', 'error');
            if (data.errors) {
                displayFormErrors(data.errors, 'error_');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('createAlert', 'Une erreur est survenue. Veuillez réessayer.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Créer l\'équipe';
    });
}

function submitJoinForm() {
    const form = document.getElementById('joinTeamForm');
    const formData = new FormData(form);
    formData.append('action', 'join');
    
    const submitBtn = document.getElementById('joinSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Rejoindre...';
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('joinAlert', 'Vous avez rejoint l\'équipe avec succès! 🎉', 'success');
            closeJoinModal();
            form.reset();
            
            // Reload teams for current tournament
            if (selectedTournoiId) {
                loadTeamsByTournoi(selectedTournoiId);
            } else {
                loadTeams();
            }
        } else {
            const modalContent = document.querySelector('.modal-content');
            const existingAlert = modalContent.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            
            const alert = document.createElement('div');
            alert.className = 'alert error show';
            alert.textContent = data.message || 'Erreur lors de l\'inscription';
            modalContent.insertBefore(alert, modalContent.firstChild);
            
            if (data.errors) {
                displayFormErrors(data.errors, 'error_');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('joinAlert', 'Une erreur est survenue. Veuillez réessayer.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Rejoindre';
    });
}

// ============ MODAL MANAGEMENT ============

function openJoinModal(teamId, teamName) {
    document.getElementById('join_id_team').value = teamId;
    document.getElementById('joinModal').classList.add('show');
    
    // Clear previous errors
    const modalAlerts = document.querySelector('.modal-content .alert');
    if (modalAlerts) modalAlerts.remove();
    
    // Clear all error messages
    document.querySelectorAll('#joinTeamForm .error-message').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
    });
    document.querySelectorAll('#joinTeamForm .error').forEach(el => {
        el.classList.remove('error');
    });
}

function closeJoinModal() {
    document.getElementById('joinModal').classList.remove('show');
    document.getElementById('joinTeamForm').reset();
    
    // Clear errors
    document.querySelectorAll('#joinTeamForm .error-message').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
    });
    document.querySelectorAll('#joinTeamForm .error').forEach(el => {
        el.classList.remove('error');
    });
}

// Close modal on background click
document.getElementById('joinModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeJoinModal();
    }
});

// ============ ERROR HANDLING ============

function displayFormErrors(errors, prefix = 'error_') {
    Object.keys(errors).forEach(fieldName => {
        const errorId = prefix + fieldName.replace(/\[|\]/g, '_');
        const errorElement = document.getElementById(errorId);
        const inputElement = document.querySelector(`[name="${fieldName}"]`);
        
        if (errorElement) {
            errorElement.textContent = errors[fieldName];
            errorElement.classList.add('show');
        }
        
        if (inputElement) {
            inputElement.classList.add('error');
        }
    });
}

function showAlert(alertId, message, type = 'success') {
    const alert = document.getElementById(alertId);
    alert.className = `alert ${type} show`;
    alert.textContent = message;
    
    setTimeout(() => {
        alert.classList.remove('show');
    }, 5000);
}

// ============ UTILITY FUNCTIONS ============

function escapeHtml(text) {
    if (text == null) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        return dateString;
    }
}