/**
 * adminteam.js
 * JavaScript for Team Management Administration with Pagination
 * Integrates with TeamController API
 */

// Global state
let allTeams = [];
let allTournois = [];
let currentSort = { column: null, direction: 'asc' };
let currentEditId = null;

// Pagination state
let currentPage = 1;
let itemsPerPage = 2;
let filteredTeams = [];

// Constants
const API_ENDPOINT = 'team_handler.php';
const MEMBER_LIMIT = 4;

// ============ VALIDATION RULES ============

const validationRules = {
    'edit_team_name': {
        required: true,
        minLength: 3,
        maxLength: 100,
        pattern: /^[a-zA-Z0-9\s\-_]+$/,
        messages: {
            required: 'Team name is required',
            minLength: 'Team name must be at least 3 characters',
            maxLength: 'Team name must not exceed 100 characters',
            pattern: 'Team name can only contain letters, numbers, spaces, hyphens and underscores'
        }
    },
    'edit_team_tag': {
        required: true,
        minLength: 2,
        maxLength: 10,
        pattern: /^[A-Z0-9]+$/,
        messages: {
            required: 'Team tag is required',
            minLength: 'Tag must be at least 2 characters',
            maxLength: 'Tag must not exceed 10 characters',
            pattern: 'Tag must be uppercase letters and numbers only'
        }
    },
    'edit_leader_name': {
        required: true,
        minLength: 3,
        maxLength: 100,
        pattern: /^[a-zA-ZÀ-ÿ\s\-']+$/,
        messages: {
            required: 'Leader name is required',
            minLength: 'Name must be at least 3 characters',
            maxLength: 'Name must not exceed 100 characters',
            pattern: 'Name can only contain letters, spaces, hyphens and apostrophes'
        }
    },
    'edit_leader_email': {
        required: true,
        email: true,
        messages: {
            required: 'Email is required',
            email: 'Please enter a valid email address'
        }
    },
    'edit_leader_phone': {
        required: true,
        minLength: 8,
        maxLength: 20,
        pattern: /^[\d\s\+\-\(\)\.]+$/,
        messages: {
            required: 'Phone number is required',
            minLength: 'Phone number must be at least 8 characters',
            maxLength: 'Phone number must not exceed 20 characters',
            pattern: 'Phone number can only contain numbers, spaces, +, -, (, ), and .'
        }
    },
    'edit_id_tournoi': {
        required: true,
        messages: {
            required: 'Please select a tournament'
        }
    },
    'edit_country': {
        required: true,
        messages: {
            required: 'Please select a country'
        }
    },
    'edit_category': {
        required: true,
        messages: {
            required: 'Please select a category'
        }
    }
};

// ============ VALIDATION FUNCTIONS ============

function createErrorElement(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-error-message';
    errorDiv.style.cssText = `
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
        animation: slideDown 0.3s ease-out;
    `;
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${message}`;
    return errorDiv;
}

function createSuccessElement(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'validation-success-message';
    successDiv.style.cssText = `
        color: #28a745;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
        animation: slideDown 0.3s ease-out;
    `;
    successDiv.innerHTML = `<i class="fas fa-check-circle me-1"></i>${message}`;
    return successDiv;
}

function removeValidationMessages(field) {
    const parent = field.parentElement;
    const existingError = parent.querySelector('.validation-error-message');
    const existingSuccess = parent.querySelector('.validation-success-message');
    const existingInfo = parent.querySelector('.validation-info-message');
    
    if (existingError) existingError.remove();
    if (existingSuccess) existingSuccess.remove();
    if (existingInfo) existingInfo.remove();
    
    field.classList.remove('is-invalid', 'is-valid');
    field.style.borderColor = '';
}

function showFieldError(field, message) {
    removeValidationMessages(field);
    field.classList.add('is-invalid');
    field.style.borderColor = '#dc3545';
    const errorElement = createErrorElement(message);
    field.parentElement.appendChild(errorElement);
}

function showFieldSuccess(field, message = 'Valid') {
    removeValidationMessages(field);
    field.classList.add('is-valid');
    field.style.borderColor = '#28a745';
    const successElement = createSuccessElement(message);
    field.parentElement.appendChild(successElement);
}

function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return true;
    
    const rules = validationRules[fieldId];
    if (!rules) return true;
    
    const value = field.value.trim();
    
    if (rules.required && !value) {
        showFieldError(field, rules.messages.required);
        return false;
    }
    
    if (!value && !rules.required) {
        removeValidationMessages(field);
        return true;
    }
    
    if (rules.minLength && value.length < rules.minLength) {
        showFieldError(field, rules.messages.minLength);
        return false;
    }
    
    if (rules.maxLength && value.length > rules.maxLength) {
        showFieldError(field, rules.messages.maxLength);
        return false;
    }
    
    if (rules.pattern && !rules.pattern.test(value)) {
        showFieldError(field, rules.messages.pattern);
        return false;
    }
    
    if (rules.email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            showFieldError(field, rules.messages.email);
            return false;
        }
    }
    
    showFieldSuccess(field);
    return true;
}

function validateFormComplete() {
    let isValid = true;
    const fields = Object.keys(validationRules);
    
    fields.forEach(fieldId => {
        const fieldValid = validateField(fieldId);
        if (!fieldValid) {
            isValid = false;
        }
    });
    
    return isValid;
}

function setupRealtimeValidation() {
    Object.keys(validationRules).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        field.removeAttribute('required');
        field.removeAttribute('pattern');
        field.removeAttribute('minlength');
        field.removeAttribute('maxlength');
        
        field.addEventListener('blur', function() {
            validateField(fieldId);
        });
        
        field.addEventListener('focus', function() {
            if (this.classList.contains('is-invalid')) {
                removeValidationMessages(this);
            }
        });
        
        let timeout;
        field.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (this.value.trim()) {
                    validateField(fieldId);
                }
            }, 500);
        });
        
        if (fieldId === 'edit_team_tag') {
            field.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    });
}

function clearAllValidation() {
    Object.keys(validationRules).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            removeValidationMessages(field);
        }
    });
}

function addValidationStyles() {
    if (document.getElementById('validation-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'validation-styles';
    style.textContent = `
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #28a745 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .form-control:focus.is-invalid,
        .form-select:focus.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .form-control:focus.is-valid,
        .form-select:focus.is-valid {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    `;
    document.head.appendChild(style);
}

// ============ PAGINATION FUNCTIONS ============

function getTotalPages() {
    return Math.ceil(filteredTeams.length / itemsPerPage);
}

function getCurrentPageTeams() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return filteredTeams.slice(startIndex, endIndex);
}

function renderPagination() {
    const totalPages = getTotalPages();
    
    if (totalPages <= 1) {
        return ''; // No pagination needed
    }
    
    let paginationHTML = '<tr class="pagination-row"><td colspan="10"><div style="display: flex; justify-content: center; align-items: center; padding: 20px; gap: 8px;">';
    
    // Previous button
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    paginationHTML += `<span class="pagination-prev ${prevDisabled}" onclick="goToPage(${currentPage - 1})">
        <i class="fas fa-chevron-left"></i> Previous
    </span>`;
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        paginationHTML += `<span class="pagination-number" onclick="goToPage(1)">1</span>`;
        if (startPage > 2) {
            paginationHTML += '<span style="padding: 0 8px; color: #6c757d;">...</span>';
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        paginationHTML += `<span class="pagination-number ${activeClass}" onclick="goToPage(${i})">${i}</span>`;
    }
    
    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += '<span style="padding: 0 8px; color: #6c757d;">...</span>';
        }
        paginationHTML += `<span class="pagination-number" onclick="goToPage(${totalPages})">${totalPages}</span>`;
    }
    
    // Next button
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    paginationHTML += `<span class="pagination-next ${nextDisabled}" onclick="goToPage(${currentPage + 1})">
        Next <i class="fas fa-chevron-right"></i>
    </span>`;
    
    paginationHTML += '</div></td></tr>';
    
    return paginationHTML;
}

function goToPage(page) {
    const totalPages = getTotalPages();
    
    if (page < 1 || page > totalPages) {
        return;
    }
    
    currentPage = page;
    renderTeams(filteredTeams);
    
    // Scroll to top of table
    const table = document.querySelector('.table-responsive');
    if (table) {
        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ============ INITIALIZATION ============

document.addEventListener('DOMContentLoaded', function() {
    addValidationStyles();
    loadTournois();
    loadTeams();
    loadStatistics();
    setupEventListeners();
    setupRealtimeValidation();
});

// ============ EVENT LISTENERS ============

function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterTeams, 300));
    }
    
    const filterTournoi = document.getElementById('filterTournoi');
    if (filterTournoi) {
        filterTournoi.addEventListener('change', filterTeams);
    }
    
    const filterCategory = document.getElementById('filterCategory');
    if (filterCategory) {
        filterCategory.addEventListener('change', filterTeams);
    }
    
    const btnClearFilters = document.getElementById('btnClearFilters');
    if (btnClearFilters) {
        btnClearFilters.addEventListener('click', clearFilters);
    }
    
    const btnRefresh = document.getElementById('btnRefresh');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            loadTeams();
            showNotification('Data refreshed', 'success');
        });
    }
    
    const btnExportCSV = document.getElementById('btnExportCSV');
    if (btnExportCSV) {
        btnExportCSV.addEventListener('click', exportToCSV);
    }
    
    const btnExportJSON = document.getElementById('btnExportJSON');
    if (btnExportJSON) {
        btnExportJSON.addEventListener('click', exportToJSON);
    }
    
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            sortTable(column);
        });
    });
    
    const editTeamForm = document.getElementById('editTeamForm');
    if (editTeamForm) {
        editTeamForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditForm();
            return false;
        });
    }
    
    document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.closest('.modal').id;
            closeModal(modalId);
        });
    });
    
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    const editTagInput = document.getElementById('edit_team_tag');
    if (editTagInput) {
        let tagTimeout;
        editTagInput.addEventListener('input', function() {
            clearTimeout(tagTimeout);
            const value = this.value.trim();
            if (value.length >= 2) {
                tagTimeout = setTimeout(() => {
                    checkTagAvailability(value, currentEditId);
                }, 800);
            }
        });
    }
}

// ============ API CALLS ============

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

function loadTeams() {
    const tbody = document.getElementById('teamTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    
    fetch(`${API_ENDPOINT}?action=list`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data && data.data.teams) {
                allTeams = data.data.teams;
                filteredTeams = allTeams;
                currentPage = 1;
                renderTeams(filteredTeams);
                updateTeamCount(filteredTeams.length);
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

function submitEditForm() {
    const form = document.getElementById('editTeamForm');
    if (!form) return;
    
    const isValid = validateFormComplete();
    
    if (!isValid) {
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    
    if (!currentEditId) {
        showNotification('Error: No team ID found. Please close and reopen the form.', 'error');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'update');
    formData.append('id_team', currentEditId);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    }
    
    fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(JSON.stringify(errorData));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Team updated successfully', 'success');
                closeModal('editModal');
                loadTeams();
                loadStatistics();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(fieldName => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            showFieldError(field, data.errors[fieldName]);
                        }
                    });
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    showNotification(data.message || 'Error updating team', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            try {
                const errorData = JSON.parse(error.message);
                if (errorData.errors) {
                    Object.keys(errorData.errors).forEach(fieldName => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            showFieldError(field, errorData.errors[fieldName]);
                        }
                    });
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    showNotification(errorData.message || 'Server error occurred', 'error');
                }
            } catch (e) {
                handleError(error);
            }
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save';
            }
        });
}

function checkTagAvailability(tag, excludeId = null) {
    if (!tag || tag.length < 2) return;
    
    const tagInput = document.getElementById('edit_team_tag');
    if (!tagInput) return;
    
    removeValidationMessages(tagInput);
    tagInput.style.borderColor = '#17a2b8';
    
    const checkingMsg = document.createElement('div');
    checkingMsg.className = 'validation-info-message';
    checkingMsg.style.cssText = `
        color: #17a2b8;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    `;
    checkingMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking availability...';
    tagInput.parentElement.appendChild(checkingMsg);
    
    const url = excludeId 
        ? `${API_ENDPOINT}?action=checkTag&tag=${encodeURIComponent(tag)}&exclude_id=${excludeId}`
        : `${API_ENDPOINT}?action=checkTag&tag=${encodeURIComponent(tag)}`;
    
    fetch(url)
        .then(handleResponse)
        .then(data => {
            checkingMsg.remove();
            if (data.success && data.data) {
                if (data.data.available) {
                    showFieldSuccess(tagInput, 'Tag is available');
                } else {
                    showFieldError(tagInput, 'This tag is already in use');
                }
            }
        })
        .catch(error => {
            console.error('Tag check error:', error);
            checkingMsg.remove();
            showFieldError(tagInput, 'Could not verify tag availability');
        });
}

// ============ UI RENDERING ============

function renderTeams(teams) {
    const tbody = document.getElementById('teamTableBody');
    if (!tbody) return;
    
    if (!teams || teams.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No teams found</td></tr>';
        return;
    }
    
    const currentPageTeams = getCurrentPageTeams();
    
    const teamsHTML = currentPageTeams.map(team => {
        const members = Array.isArray(team.members) ? team.members : [];
        const totalMembers = members.length + 1;
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
    
    tbody.innerHTML = teamsHTML + renderPagination();
}

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

function populateEditForm(team) {
    document.getElementById('edit_id_tournoi').value = team.id_tournoi || '';
    document.getElementById('edit_team_name').value = team.team_name || '';
    document.getElementById('edit_team_tag').value = team.team_tag || '';
    document.getElementById('edit_country').value = team.country || '';
    document.getElementById('edit_category').value = team.category || '';
    document.getElementById('edit_leader_name').value = team.leader_name || '';
    document.getElementById('edit_leader_email').value = team.leader_email || '';
    document.getElementById('edit_leader_phone').value = team.leader_phone || '';
    
    clearAllValidation();
    
    const modalTitle = document.querySelector('#editModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = `<i class="fas fa-edit me-2"></i>Edit: ${team.team_name}`;
    }
}

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

function updateTeamCount(count) {
    const countEl = document.getElementById('displayedTeamCount');
    if (countEl) {
        const totalPages = getTotalPages();
        const pageInfo = totalPages > 1 ? ` (Page ${currentPage}/${totalPages})` : '';
        countEl.textContent = `${count} team(s) displayed${pageInfo}`;
    }
}

// ============ FILTERING & SORTING ============

function filterTeams() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const tournoiFilter = document.getElementById('filterTournoi')?.value || '';
    const categoryFilter = document.getElementById('filterCategory')?.value || '';
    
    let filtered = allTeams;
    
    if (searchTerm) {
        filtered = filtered.filter(team => 
            team.team_name.toLowerCase().includes(searchTerm) ||
            team.team_tag.toLowerCase().includes(searchTerm) ||
            team.leader_name.toLowerCase().includes(searchTerm) ||
            team.country.toLowerCase().includes(searchTerm) ||
            (team.nom_tournoi && team.nom_tournoi.toLowerCase().includes(searchTerm))
        );
    }
    
    if (tournoiFilter) {
        filtered = filtered.filter(team => team.id_tournoi == tournoiFilter);
    }
    
    if (categoryFilter) {
        filtered = filtered.filter(team => team.category.toLowerCase() === categoryFilter.toLowerCase());
    }
    
    filteredTeams = filtered;
    currentPage = 1;
    renderTeams(filteredTeams);
    updateTeamCount(filteredTeams.length);
}

function clearFilters() {
    const searchInput = document.getElementById('searchInput');
    const filterTournoi = document.getElementById('filterTournoi');
    const filterCategory = document.getElementById('filterCategory');
    
    if (searchInput) searchInput.value = '';
    if (filterTournoi) filterTournoi.value = '';
    if (filterCategory) filterCategory.value = '';
    
    filteredTeams = allTeams;
    currentPage = 1;
    renderTeams(filteredTeams);
    updateTeamCount(filteredTeams.length);
    showNotification('Filters cleared', 'info');
}

function sortTable(column) {
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'asc';
    }
    
    const sorted = [...filteredTeams].sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        if (aVal == null) aVal = '';
        if (bVal == null) bVal = '';
        
        if (column === 'total_members') {
            const aMemberCount = Array.isArray(a.members) ? a.members.length + 1 : 1;
            const bMemberCount = Array.isArray(b.members) ? b.members.length + 1 : 1;
            return currentSort.direction === 'asc' 
                ? aMemberCount - bMemberCount 
                : bMemberCount - aMemberCount;
        }
        
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    filteredTeams = sorted;
    renderTeams(filteredTeams);
    updateSortIndicators(column);
}

function updateSortIndicators(column) {
    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        if (th.dataset.sort === column) {
            th.classList.add(currentSort.direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

// ============ EXPORT ============

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
    
    downloadFile(csv, `teams_export_${getDateString()}.csv`, 'text/csv;charset=utf-8;');
    showNotification(`CSV export successful (${allTeams.length} teams)`, 'success');
}

function exportToJSON() {
    if (allTeams.length === 0) {
        showNotification('No data to export', 'warning');
        return;
    }
    
    const json = JSON.stringify(allTeams, null, 2);
    downloadFile(json, `teams_export_${getDateString()}.json`, 'application/json;charset=utf-8;');
    showNotification(`JSON export successful (${allTeams.length} teams)`, 'success');
}

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

function getDateString() {
    return new Date().toISOString().split('T')[0];
}

// ============ UTILITY FUNCTIONS ============

function handleResponse(response) {
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
}

function handleError(error) {
    console.error('Error:', error);
    const message = error.message || 'An error occurred. Please try again.';
    showNotification(message, 'error');
}

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
                clearAllValidation();
            }
            currentEditId = null;
        }
    }
}

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

function getCountryFlag(countryName) {
    const flagMap = {
        'Tunisia': '🇹🇳', 'Tunisie': '🇹🇳',
        'France': '🇫🇷',
        'Algeria': '🇩🇿', 'Algérie': '🇩🇿',
        'Morocco': '🇲🇦', 'Maroc': '🇲🇦',
        'Egypt': '🇪🇬', 'Égypte': '🇪🇬',
        'USA': '🇺🇸', 'United States': '🇺🇸',
        'UK': '🇬🇧', 'United Kingdom': '🇬🇧',
        'Germany': '🇩🇪', 'Allemagne': '🇩🇪',
        'Spain': '🇪🇸', 'Espagne': '🇪🇸',
        'Italy': '🇮🇹', 'Italie': '🇮🇹',
        'Canada': '🇨🇦',
        'Belgium': '🇧🇪', 'Belgique': '🇧🇪',
        'Switzerland': '🇨🇭', 'Suisse': '🇨🇭',
        'Japan': '🇯🇵', 'Japon': '🇯🇵',
        'South Korea': '🇰🇷', 'Corée du Sud': '🇰🇷',
        'China': '🇨🇳', 'Chine': '🇨🇳',
        'Brazil': '🇧🇷', 'Brésil': '🇧🇷',
        'Argentina': '🇦🇷', 'Argentine': '🇦🇷',
        'Australia': '🇦🇺', 'Australie': '🇦🇺',
        'India': '🇮🇳', 'Inde': '🇮🇳',
        'Saudi Arabia': '🇸🇦', 'Arabie Saoudite': '🇸🇦',
        'UAE': '🇦🇪', 'EAU': '🇦🇪'
    };
    return flagMap[countryName] || '🌍';
}

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

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.focus();
    }
    
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
            closeModal(modal.id);
        });
    }
    
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadTeams();
        showNotification('Data refreshed', 'success');
    }
});

window.addEventListener('online', function() {
    showNotification('Connection restored', 'success');
    loadTeams();
});

window.addEventListener('offline', function() {
    showNotification('Connection lost', 'warning');
});