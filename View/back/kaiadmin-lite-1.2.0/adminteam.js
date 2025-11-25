/**
 * adminteam.js
 * JavaScript for Team Management Administration
 * Integrates with TeamController API
 * UPDATED VERSION - Professional Dropdown Actions
 */

// Global state
let allTeams = [];
let allTournois = [];
let currentSort = { column: null, direction: 'asc' };
let currentEditId = null;

// Constants
const API_ENDPOINT = 'team_handler.php';
const MEMBER_LIMIT = 4; // Max additional members (excluding leader)

// ============ INITIALIZATION ============

document.addEventListener('DOMContentLoaded', function() {
    loadTournois();
    loadTeams();
    loadStatistics();
    setupEventListeners();
});

// ============ EVENT LISTENERS ============

function setupEventListeners() {
    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterTeams, 300));
    }
    
    // Filters
    const filterTournoi = document.getElementById('filterTournoi');
    if (filterTournoi) {
        filterTournoi.addEventListener('change', filterTeams);
    }
    
    const filterCategory = document.getElementById('filterCategory');
    if (filterCategory) {
        filterCategory.addEventListener('change', filterTeams);
    }
    
    // Clear filters button
    const btnClearFilters = document.getElementById('btnClearFilters');
    if (btnClearFilters) {
        btnClearFilters.addEventListener('click', clearFilters);
    }
    
    // Refresh button
    const btnRefresh = document.getElementById('btnRefresh');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            loadTeams();
            showNotification('Data refreshed', 'success');
        });
    }
    
    // Export CSV button
    const btnExportCSV = document.getElementById('btnExportCSV');
    if (btnExportCSV) {
        btnExportCSV.addEventListener('click', exportToCSV);
    }
    
    // Export JSON button
    const btnExportJSON = document.getElementById('btnExportJSON');
    if (btnExportJSON) {
        btnExportJSON.addEventListener('click', exportToJSON);
    }
    
    // Sorting
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            sortTable(column);
        });
    });
    
    // Edit form submission
    const editTeamForm = document.getElementById('editTeamForm');
    if (editTeamForm) {
        editTeamForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditForm();
        });
    }
    
    // Modal close buttons
    document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.closest('.modal').id;
            closeModal(modalId);
        });
    });
    
    // Close modals on background click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Tag availability checker in edit form
    const editTagInput = document.getElementById('edit_team_tag');
    if (editTagInput) {
        editTagInput.addEventListener('blur', function() {
            checkTagAvailability(this.value, currentEditId);
        });
    }
}

// ============ API CALLS ============

/**
 * Load tournaments
 */
function loadTournois() {
    fetch(`${API_ENDPOINT}?action=getTournois`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data && data.data.tournois) {
                allTournois = data.data.tournois;
                populateTournoiFilters();
            } else {
                console.warn('No tournaments found');
            }
        })
        .catch(handleError);
}

/**
 * Load teams
 */
function loadTeams() {
    const tbody = document.getElementById('teamTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    
    fetch(`${API_ENDPOINT}?action=list`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data && data.data.teams) {
                allTeams = data.data.teams;
                renderTeams(allTeams);
                updateTeamCount(allTeams.length);
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No teams found</td></tr>';
                updateTeamCount(0);
            }
        })
        .catch(error => {
            handleError(error);
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Loading error</td></tr>';
            updateTeamCount(0);
        });
}

/**
 * Load statistics
 */
function loadStatistics() {
    fetch(`${API_ENDPOINT}?action=getStatistics`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                updateStatistics(data.data);
            }
        })
        .catch(error => {
            console.warn('Failed to load statistics:', error);
        });
}

/**
 * Delete team
 */
function deleteTeam(id, teamName) {
    const confirmMessage = `Are you sure you want to delete the team "${teamName}"?\n\nThis action is irreversible!`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_team', id);
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData
    })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                showNotification('Team deleted successfully', 'success');
                loadTeams();
                loadStatistics();
            } else {
                showNotification(data.message || 'Error deleting team', 'error');
            }
        })
        .catch(handleError);
}

/**
 * View team details
 */
function viewTeam(id) {
    fetch(`${API_ENDPOINT}?action=read&id_team=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                displayTeamDetails(data.data);
                openModal('detailModal');
            } else {
                showNotification('Team not found', 'error');
            }
        })
        .catch(handleError);
}

/**
 * Edit team
 */
function editTeam(id) {
    fetch(`${API_ENDPOINT}?action=read&id_team=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                currentEditId = id;
                populateEditForm(data.data);
                openModal('editModal');
            } else {
                showNotification('Team not found', 'error');
            }
        })
        .catch(handleError);
}

/**
 * Submit edit form
 */
function submitEditForm() {
    const form = document.getElementById('editTeamForm');
    if (!form) return;
    
    // Clear previous errors
    clearFormErrors();
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'update');
    formData.append('id_team', currentEditId);
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    }
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData
    })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                showNotification('Team updated successfully', 'success');
                closeModal('editModal');
                loadTeams();
                loadStatistics();
            } else {
                showNotification(data.message || 'Error updating team', 'error');
                // Display field-specific errors
                if (data.errors) {
                    displayFormErrors(data.errors);
                }
            }
        })
        .catch(handleError)
        .finally(() => {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save';
            }
        });
}

/**
 * Check tag availability
 */
function checkTagAvailability(tag, excludeId = null) {
    if (!tag || tag.length < 2) return;
    
    const url = excludeId 
        ? `${API_ENDPOINT}?action=checkTag&tag=${encodeURIComponent(tag)}&exclude_id=${excludeId}`
        : `${API_ENDPOINT}?action=checkTag&tag=${encodeURIComponent(tag)}`;
    
    fetch(url)
        .then(handleResponse)
        .then(data => {
            const tagInput = document.getElementById('edit_team_tag');
            if (!tagInput) return;
            
            if (data.success && data.data) {
                if (data.data.available) {
                    tagInput.classList.remove('is-invalid');
                    tagInput.classList.add('is-valid');
                    showTagFeedback(tagInput, 'Tag available', 'valid');
                } else {
                    tagInput.classList.remove('is-valid');
                    tagInput.classList.add('is-invalid');
                    showTagFeedback(tagInput, 'Tag already in use', 'invalid');
                }
            }
        })
        .catch(console.error);
}

/**
 * Show tag feedback
 */
function showTagFeedback(input, message, type) {
    // Remove existing feedback
    const existingFeedback = input.parentNode.querySelector('.feedback-message');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Add new feedback
    const feedback = document.createElement('div');
    feedback.className = `feedback-message ${type === 'valid' ? 'valid-feedback' : 'invalid-feedback'}`;
    feedback.style.display = 'block';
    feedback.textContent = message;
    input.parentNode.appendChild(feedback);
}

// ============ UI RENDERING ============

/**
 * Render teams table with dropdown actions
 */
function renderTeams(teams) {
    const tbody = document.getElementById('teamTableBody');
    if (!tbody) return;
    
    if (!teams || teams.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No teams found</td></tr>';
        return;
    }
    
    tbody.innerHTML = teams.map(team => {
        const members = Array.isArray(team.members) ? team.members : [];
        const totalMembers = members.length + 1; // +1 for leader
        const isFull = members.length >= 4;
        const createdDate = formatDate(team.created_at);
        
        return `
            <tr data-team-id="${team.id_team}">
                <td>${escapeHtml(team.id_team)}</td>
                <td>
                    <strong>${escapeHtml(team.team_name)}</strong>
                    ${isFull ? '<span class="badge badge-status badge-full ms-1">Full</span>' : ''}
                </td>
                <td><span class="team-tag">${escapeHtml(team.team_tag)}</span></td>
                <td>${escapeHtml(team.nom_tournoi || '-')}</td>
                <td><span class="badge-category badge-${team.category.toLowerCase()}">${escapeHtml(team.category)}</span></td>
                <td>
                    <span class="country-flag" title="${escapeHtml(team.country)}">
                        ${getCountryFlag(team.country)} ${escapeHtml(team.country)}
                    </span>
                </td>
                <td>${escapeHtml(team.leader_name)}</td>
                <td>
                    <span class="${isFull ? 'text-success fw-bold' : ''}">
                        ${totalMembers}/5
                    </span>
                </td>
                <td><small class="text-muted">${createdDate}</small></td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="action-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); viewTeam(${team.id_team})">
                                    <i class="fas fa-eye text-info"></i> View
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); editTeam(${team.id_team})">
                                    <i class="fas fa-edit" style="color: #ffc107;"></i> Edit
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deleteTeam(${team.id_team}, '${escapeHtml(team.team_name).replace(/'/g, "\\'")}')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Display team details in modal
 */
function displayTeamDetails(team) {
    const detailContent = document.getElementById('detailContent');
    if (!detailContent) return;
    
    const members = Array.isArray(team.members) ? team.members : [];
    const membersHtml = members.length > 0
        ? members.map((member, index) => `
            <div class="member-item">
                <strong>Member ${index + 1}</strong>
                <small>
                    <i class="fas fa-user"></i> ${escapeHtml(member.name)}<br>
                    <i class="fas fa-envelope"></i> ${escapeHtml(member.email)}<br>
                    <i class="fas fa-phone"></i> ${escapeHtml(member.phone)}
                </small>
            </div>
        `).join('')
        : '<p class="text-muted"><i class="fas fa-info-circle"></i> No additional members</p>';
    
    detailContent.innerHTML = `
        <div class="team-detail">
            <div class="text-center mb-3">
                <h4>${escapeHtml(team.team_name)}</h4>
                <span class="team-tag">${escapeHtml(team.team_tag)}</span>
                ${members.length >= 4 ? '<span class="badge badge-status badge-full ms-2">Full Team</span>' : ''}
            </div>
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-trophy"></i> Tournament:</strong><br>
                    ${team.nom_tournoi ? escapeHtml(team.nom_tournoi) : '<span class="text-muted">Not specified</span>'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-tag"></i> Category:</strong><br>
                    <span class="badge-category badge-${team.category.toLowerCase()}">${escapeHtml(team.category)}</span></p>
                </div>
            </div>
            
            <p><strong><i class="fas fa-globe"></i> Country:</strong><br>
            <span class="country-flag">${getCountryFlag(team.country)} ${escapeHtml(team.country)}</span></p>
            
            <hr>
            
            <h6><i class="fas fa-star text-warning"></i> Team Leader</h6>
            <div class="ps-3 mb-3">
                <p class="mb-1"><i class="fas fa-user"></i> <strong>${escapeHtml(team.leader_name)}</strong></p>
                <p class="mb-1"><i class="fas fa-envelope"></i> ${escapeHtml(team.leader_email)}</p>
                <p class="mb-1"><i class="fas fa-phone"></i> ${escapeHtml(team.leader_phone)}</p>
            </div>
            
            <hr>
            
            <h6><i class="fas fa-users"></i> Members (${members.length}/4)</h6>
            ${membersHtml}
            
            <hr>
            
            <p class="text-muted text-center mb-0">
                <small><i class="fas fa-clock"></i> Created: ${formatDate(team.created_at)}</small>
            </p>
        </div>
    `;
}

/**
 * Populate edit form
 */
function populateEditForm(team) {
    // Basic fields
    document.getElementById('edit_id_tournoi').value = team.id_tournoi || '';
    document.getElementById('edit_team_name').value = team.team_name || '';
    document.getElementById('edit_team_tag').value = team.team_tag || '';
    document.getElementById('edit_country').value = team.country || '';
    document.getElementById('edit_category').value = team.category || '';
    document.getElementById('edit_leader_name').value = team.leader_name || '';
    document.getElementById('edit_leader_email').value = team.leader_email || '';
    document.getElementById('edit_leader_phone').value = team.leader_phone || '';
    
    // Clear previous errors and validation states
    clearFormErrors();
    document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
    
    // Set modal title
    const modalTitle = document.querySelector('#editModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = `<i class="fas fa-edit me-2"></i>Edit: ${team.team_name}`;
    }
}

/**
 * Populate tournament filters
 */
function populateTournoiFilters() {
    const filter = document.getElementById('filterTournoi');
    const editSelect = document.getElementById('edit_id_tournoi');
    
    if (!allTournois || allTournois.length === 0) return;
    
    allTournois.forEach(tournoi => {
        if (filter) {
            const option1 = document.createElement('option');
            option1.value = tournoi.id_tournoi;
            option1.textContent = tournoi.nom_tournoi;
            filter.appendChild(option1);
        }
        
        if (editSelect) {
            const option2 = document.createElement('option');
            option2.value = tournoi.id_tournoi;
            option2.textContent = tournoi.nom_tournoi;
            editSelect.appendChild(option2);
        }
    });
}

/**
 * Update statistics display
 */
function updateStatistics(stats) {
    const totalTeamsEl = document.getElementById('totalTeams');
    const totalMembersEl = document.getElementById('totalMembers');
    const fullTeamsEl = document.getElementById('fullTeams');
    const avgMembersEl = document.getElementById('avgMembers');
    
    if (totalTeamsEl) totalTeamsEl.textContent = stats.total_teams || 0;
    if (totalMembersEl) totalMembersEl.textContent = stats.total_members || 0;
    if (fullTeamsEl) fullTeamsEl.textContent = stats.full_teams || 0;
    if (avgMembersEl && stats.average_members) {
        avgMembersEl.textContent = stats.average_members;
    }
}

/**
 * Update team count display
 */
function updateTeamCount(count) {
    const countEl = document.getElementById('displayedTeamCount');
    if (countEl) {
        countEl.textContent = `${count} team(s) displayed`;
    }
}

// ============ FILTERING & SORTING ============

/**
 * Filter teams
 */
function filterTeams() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const tournoiFilter = document.getElementById('filterTournoi')?.value || '';
    const categoryFilter = document.getElementById('filterCategory')?.value || '';
    
    let filtered = allTeams;
    
    // Search filter
    if (searchTerm) {
        filtered = filtered.filter(team => 
            team.team_name.toLowerCase().includes(searchTerm) ||
            team.team_tag.toLowerCase().includes(searchTerm) ||
            team.leader_name.toLowerCase().includes(searchTerm) ||
            team.country.toLowerCase().includes(searchTerm) ||
            (team.nom_tournoi && team.nom_tournoi.toLowerCase().includes(searchTerm))
        );
    }
    
    // Tournament filter
    if (tournoiFilter) {
        filtered = filtered.filter(team => team.id_tournoi == tournoiFilter);
    }
    
    // Category filter
    if (categoryFilter) {
        filtered = filtered.filter(team => team.category.toLowerCase() === categoryFilter.toLowerCase());
    }
    
    renderTeams(filtered);
    updateTeamCount(filtered.length);
}

/**
 * Clear filters
 */
function clearFilters() {
    const searchInput = document.getElementById('searchInput');
    const filterTournoi = document.getElementById('filterTournoi');
    const filterCategory = document.getElementById('filterCategory');
    
    if (searchInput) searchInput.value = '';
    if (filterTournoi) filterTournoi.value = '';
    if (filterCategory) filterCategory.value = '';
    
    renderTeams(allTeams);
    updateTeamCount(allTeams.length);
    showNotification('Filters cleared', 'info');
}

/**
 * Sort table
 */
function sortTable(column) {
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'asc';
    }
    
    const sorted = [...allTeams].sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        // Handle null/undefined
        if (aVal == null) aVal = '';
        if (bVal == null) bVal = '';
        
        // Special handling for total_members
        if (column === 'total_members') {
            const aMemberCount = Array.isArray(a.members) ? a.members.length + 1 : 1;
            const bMemberCount = Array.isArray(b.members) ? b.members.length + 1 : 1;
            return currentSort.direction === 'asc' 
                ? aMemberCount - bMemberCount 
                : bMemberCount - aMemberCount;
        }
        
        // String comparison
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    renderTeams(sorted);
    updateSortIndicators(column);
}

/**
 * Update sort indicators
 */
function updateSortIndicators(column) {
    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        
        if (th.dataset.sort === column) {
            th.classList.add(currentSort.direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

// ============ EXPORT ============

/**
 * Export to CSV
 */
function exportToCSV() {
    if (allTeams.length === 0) {
        showNotification('No data to export', 'warning');
        return;
    }
    
    const headers = ['ID', 'Team Name', 'Tag', 'Tournament', 'Category', 'Country', 'Leader', 'Leader Email', 'Leader Phone', 'Members', 'Created Date'];
    
    const rows = allTeams.map(team => {
        const members = Array.isArray(team.members) ? team.members : [];
        const totalMembers = members.length + 1;
        
        return [
            team.id_team,
            team.team_name,
            team.team_tag,
            team.nom_tournoi || '-',
            team.category,
            team.country,
            team.leader_name,
            team.leader_email,
            team.leader_phone,
            totalMembers,
            formatDate(team.created_at)
        ];
    });
    
    let csv = headers.join(',') + '\n';
    rows.forEach(row => {
        csv += row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',') + '\n';
    });
    
    // Download
    downloadFile(csv, `teams_export_${getDateString()}.csv`, 'text/csv;charset=utf-8;');
    showNotification(`CSV export successful (${allTeams.length} teams)`, 'success');
}

/**
 * Export to JSON
 */
function exportToJSON() {
    if (allTeams.length === 0) {
        showNotification('No data to export', 'warning');
        return;
    }
    
    const json = JSON.stringify(allTeams, null, 2);
    downloadFile(json, `teams_export_${getDateString()}.json`, 'application/json;charset=utf-8;');
    showNotification(`JSON export successful (${allTeams.length} teams)`, 'success');
}

/**
 * Download file helper
 */
function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

/**
 * Get date string for filenames
 */
function getDateString() {
    return new Date().toISOString().split('T')[0];
}

// ============ UTILITY FUNCTIONS ============

/**
 * Handle fetch response
 */
function handleResponse(response) {
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
}

/**
 * Handle errors
 */
function handleError(error) {
    console.error('Error:', error);
    const message = error.message || 'An error occurred. Please try again.';
    showNotification(message, 'error');
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const alertClass = type === 'error' ? 'danger' : type;
    notification.className = `alert alert-${alertClass} alert-dismissible fade show notification-toast`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    notification.innerHTML = `
        <strong>${type === 'success' ? '<i class="fas fa-check-circle"></i>' : type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-info-circle"></i>'}</strong>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 150);
    }, 5000);
}

/**
 * Display form errors
 */
function displayFormErrors(errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('is-invalid');
            
            let errorDiv = field.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
            errorDiv.textContent = errors[fieldName];
        }
    });
}

/**
 * Clear form errors
 */
function clearFormErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    document.querySelectorAll('.valid-feedback').forEach(el => el.remove());
}

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = `${modalId}_backdrop`;
        document.body.appendChild(backdrop);
    }
}

/**
 * Close modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        const backdrop = document.getElementById(`${modalId}_backdrop`);
        if (backdrop) {
            backdrop.remove();
        }
        
        if (modalId === 'editModal') {
            const form = document.getElementById('editTeamForm');
            if (form) {
                form.reset();
                clearFormErrors();
            }
            currentEditId = null;
        }
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

/**
 * Escape HTML
 */
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

/**
 * Get country flag emoji
 */
function getCountryFlag(countryName) {
    const flagMap = {
        'Tunisia': '🇹🇳',
        'Tunisie': '🇹🇳',
        'France': '🇫🇷',
        'Algeria': '🇩🇿',
        'Algérie': '🇩🇿',
        'Morocco': '🇲🇦',
        'Maroc': '🇲🇦',
        'Egypt': '🇪🇬',
        'Égypte': '🇪🇬',
        'USA': '🇺🇸',
        'United States': '🇺🇸',
        'UK': '🇬🇧',
        'United Kingdom': '🇬🇧',
        'Germany': '🇩🇪',
        'Allemagne': '🇩🇪',
        'Spain': '🇪🇸',
        'Espagne': '🇪🇸',
        'Italy': '🇮🇹',
        'Italie': '🇮🇹',
        'Canada': '🇨🇦',
        'Belgium': '🇧🇪',
        'Belgique': '🇧🇪',
        'Switzerland': '🇨🇭',
        'Suisse': '🇨🇭',
        'Japan': '🇯🇵',
        'Japon': '🇯🇵',
        'South Korea': '🇰🇷',
        'Corée du Sud': '🇰🇷',
        'China': '🇨🇳',
        'Chine': '🇨🇳',
        'Brazil': '🇧🇷',
        'Brésil': '🇧🇷',
        'Argentina': '🇦🇷',
        'Argentine': '🇦🇷',
        'Australia': '🇦🇺',
        'Australie': '🇦🇺',
        'India': '🇮🇳',
        'Inde': '🇮🇳',
        'Saudi Arabia': '🇸🇦',
        'Arabie Saoudite': '🇸🇦',
        'UAE': '🇦🇪',
        'EAU': '🇦🇪'
    };
    
    return flagMap[countryName] || '🌍';
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Validate email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Validate phone
 */
function validatePhone(phone) {
    const re = /^[\d\s\+\-\(\)\.]+$/;
    return re.test(phone);
}

/**
 * Format number with separators
 */
function formatNumber(num) {
    return new Intl.NumberFormat('en-US').format(num);
}

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K: Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.focus();
    }
    
    // Escape: Close modals
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            closeModal(modal.id);
        });
    }
    
    // Ctrl/Cmd + R: Refresh data
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadTeams();
        showNotification('Data refreshed', 'success');
    }
});

/**
 * Handle online/offline status
 */
window.addEventListener('online', function() {
    showNotification('Connection restored', 'success');
    loadTeams();
});

window.addEventListener('offline', function() {
    showNotification('Connection lost', 'warning');
});