const TOURNAMENT_API_URL = '../../tournoi_handler.php';

let allTournaments = [];
let currentSort = { column: null, direction: null };
let selectedImageFile = null;
let editSelectedImageFile = null;
let imageRemovalState = {
  markedForRemoval: false,
  originalImagePath: null
};

// Pagination state
let currentPage = 1;
let itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
  loadTournois();
  
  document.getElementById('btnSaveAjout').addEventListener('click', handleAjoutTournoi);
  document.getElementById('btnSaveModifier').addEventListener('click', handleModifierTournoi);
  
  // Search and filter event listeners
  document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    applyFiltersAndSort();
  });
  document.getElementById('filterNiveau').addEventListener('change', () => {
    currentPage = 1;
    applyFiltersAndSort();
  });
  document.getElementById('filterDateRange').addEventListener('change', () => {
    currentPage = 1;
    applyFiltersAndSort();
  });
  document.getElementById('btnClearFilters').addEventListener('click', clearFilters);
  
  // Sorting event listeners
  document.querySelectorAll('.sortable').forEach(th => {
    th.addEventListener('click', function() {
      const column = this.getAttribute('data-sort');
      sortTable(column);
    });
  });

  // Image upload handlers for Add modal
  setupImageUpload('uploadArea', 'image', 'imagePreview', 'previewImg', 'removeImage', false);
  
  // Image upload handlers for Edit modal with removal logic
  setupEditImageHandlers();
});

function setupImageUpload(areaId, inputId, previewId, imgId, removeId, isEdit) {
  const uploadArea = document.getElementById(areaId);
  const fileInput = document.getElementById(inputId);
  const imagePreview = document.getElementById(previewId);
  const previewImg = document.getElementById(imgId);
  const removeBtn = document.getElementById(removeId);

  // Click to browse
  uploadArea.addEventListener('click', () => fileInput.click());

  // File input change
  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      handleImageFile(file, uploadArea, imagePreview, previewImg, isEdit);
    }
  });

  // Drag and drop
  uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
  });

  uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
  });

  uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
      handleImageFile(file, uploadArea, imagePreview, previewImg, isEdit);
    }
  });

  // Remove image (for Add modal only)
  if (!isEdit) {
    removeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      fileInput.value = '';
      uploadArea.style.display = 'block';
      imagePreview.style.display = 'none';
      previewImg.src = '';
      selectedImageFile = null;
    });
  }
}

function setupEditImageHandlers() {
  const editUploadArea = document.getElementById('editUploadArea');
  const editFileInput = document.getElementById('edit_image');
  const editImagePreview = document.getElementById('editImagePreview');
  const editPreviewImg = document.getElementById('editPreviewImg');
  const editRemoveBtn = document.getElementById('editRemoveImage');

  // Click to browse
  editUploadArea.addEventListener('click', () => editFileInput.click());

  // File input change
  editFileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      handleImageFile(file, editUploadArea, editImagePreview, editPreviewImg, true);
      // Reset removal state when new image is selected
      imageRemovalState.markedForRemoval = false;
      updateRemoveButton(false);
      hideRemovalWarning();
    }
  });

  // Drag and drop handlers
  editUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    editUploadArea.classList.add('dragover');
  });

  editUploadArea.addEventListener('dragleave', () => {
    editUploadArea.classList.remove('dragover');
  });

  editUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    editUploadArea.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
      handleImageFile(file, editUploadArea, editImagePreview, editPreviewImg, true);
      imageRemovalState.markedForRemoval = false;
      updateRemoveButton(false);
      hideRemovalWarning();
    }
  });

  // Remove/Undo button handler
  editRemoveBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    
    if (!imageRemovalState.markedForRemoval) {
      // Mark for removal
      imageRemovalState.markedForRemoval = true;
      
      // Visual feedback
      editImagePreview.style.opacity = '0.5';
      editImagePreview.style.filter = 'grayscale(100%)';
      
      // Update button to show "Undo"
      updateRemoveButton(true);
      
      // Show warning
      showRemovalWarning();
      
      // Clear file input
      editFileInput.value = '';
      editSelectedImageFile = null;
    } else {
      // Undo removal
      imageRemovalState.markedForRemoval = false;
      
      // Restore visual
      editImagePreview.style.opacity = '1';
      editImagePreview.style.filter = 'none';
      
      // Update button back to "Remove"
      updateRemoveButton(false);
      
      // Hide warning
      hideRemovalWarning();
    }
  });
}

function updateRemoveButton(isUndo) {
  const editRemoveBtn = document.getElementById('editRemoveImage');
  if (isUndo) {
    editRemoveBtn.innerHTML = '<i class="fas fa-undo me-1"></i>Undo Remove';
    editRemoveBtn.classList.remove('btn-danger');
    editRemoveBtn.classList.add('btn-secondary');
  } else {
    editRemoveBtn.innerHTML = '<i class="fas fa-times me-1"></i>Remove Image';
    editRemoveBtn.classList.remove('btn-secondary');
    editRemoveBtn.classList.add('btn-danger');
  }
}

function showRemovalWarning() {
  const editImagePreview = document.getElementById('editImagePreview');
  let warning = document.getElementById('imageRemovalWarning');
  
  if (!warning) {
    warning = document.createElement('div');
    warning.id = 'imageRemovalWarning';
    warning.className = 'alert alert-warning mt-2';
    warning.style.marginTop = '10px';
    warning.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Image will be removed when you update the tournament';
    editImagePreview.parentElement.insertBefore(warning, editImagePreview.nextSibling);
  }
}

function hideRemovalWarning() {
  const warning = document.getElementById('imageRemovalWarning');
  if (warning) {
    warning.remove();
  }
}

function handleImageFile(file, uploadArea, imagePreview, previewImg, isEdit) {
  // Validate file type
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!validTypes.includes(file.type)) {
    alert('Please upload a JPEG or PNG image');
    return;
  }

  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Image size must be less than 5 MB');
    return;
  }

  // Store file and preview
  if (isEdit) {
    editSelectedImageFile = file;
  } else {
    selectedImageFile = file;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    previewImg.src = e.target.result;
    uploadArea.style.display = 'none';
    imagePreview.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('filterNiveau').value = '';
  document.getElementById('filterDateRange').value = '';
  currentPage = 1;
  applyFiltersAndSort();
}

function sortTable(column) {
  if (currentSort.column === column) {
    if (currentSort.direction === 'asc') {
      currentSort.direction = 'desc';
    } else if (currentSort.direction === 'desc') {
      currentSort.column = null;
      currentSort.direction = null;
    } else {
      currentSort.direction = 'asc';
    }
  } else {
    currentSort.column = column;
    currentSort.direction = 'asc';
  }
  
  // Update UI
  document.querySelectorAll('.sortable').forEach(th => {
    th.classList.remove('asc', 'desc');
  });
  
  if (currentSort.column) {
    const th = document.querySelector(`[data-sort="${currentSort.column}"]`);
    th.classList.add(currentSort.direction);
  }
  
  applyFiltersAndSort();
}

function applyFiltersAndSort() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const niveauFilter = document.getElementById('filterNiveau').value;
  const dateRangeFilter = document.getElementById('filterDateRange').value;
  
  let filtered = allTournaments.filter(t => {
    // Search filter
    const matchesSearch = !searchTerm || 
      t.nom.toLowerCase().includes(searchTerm) || 
      t.theme.toLowerCase().includes(searchTerm) ||
      (t.description && t.description.toLowerCase().includes(searchTerm));
    
    // Level filter
    const matchesNiveau = !niveauFilter || t.niveau === niveauFilter;
    
    // Date range filter
    let matchesDateRange = true;
    if (dateRangeFilter) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const startDate = new Date(t.date_debut);
      const endDate = new Date(t.date_fin);
      
      if (dateRangeFilter === 'upcoming') {
        matchesDateRange = startDate > today;
      } else if (dateRangeFilter === 'ongoing') {
        matchesDateRange = startDate <= today && endDate >= today;
      } else if (dateRangeFilter === 'past') {
        matchesDateRange = endDate < today;
      }
    }
    
    return matchesSearch && matchesNiveau && matchesDateRange;
  });
  
  // Apply sorting
  if (currentSort.column) {
    filtered.sort((a, b) => {
      let aVal = a[currentSort.column];
      let bVal = b[currentSort.column];
      
      // Handle date sorting
      if (currentSort.column === 'date_debut' || currentSort.column === 'date_fin') {
        aVal = new Date(aVal);
        bVal = new Date(bVal);
      }
      
      // Handle numeric sorting
      if (currentSort.column === 'id') {
        aVal = parseInt(aVal);
        bVal = parseInt(bVal);
      }
      
      // Handle string sorting
      if (typeof aVal === 'string') {
        aVal = aVal.toLowerCase();
        bVal = bVal.toLowerCase();
      }
      
      if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
      if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
      return 0;
    });
  }
  
  displayTournaments(filtered);
}

function displayTournaments(tournaments) {
  const tbody = document.getElementById('tournoiTableBody');
  document.getElementById('tournamentCount').textContent = tournaments.length;
  
  if (tournaments.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="empty-state"><i class="fas fa-trophy fa-3x mb-3 text-muted"></i><p>No tournaments found</p></div></td></tr>';
    return;
  }
  
  // Calculate pagination
  const totalPages = Math.ceil(tournaments.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, tournaments.length);
  const currentPageTournaments = tournaments.slice(startIndex, endIndex);
  
  // Build tournament rows
  const rows = currentPageTournaments.map(t => `
    <tr>
      <td><strong>${t.id}</strong></td>
      <td>
        ${t.image ? `<img src="${escapeHtml(t.image)}" alt="${escapeHtml(t.nom)}" class="tournament-image">` : '<i class="fas fa-image text-muted"></i>'}
      </td>
      <td><strong>${escapeHtml(t.nom)}</strong></td>
      <td>${escapeHtml(t.theme)}</td>
      <td><div class="description-preview" title="${escapeHtml(t.description || 'No description')}">${escapeHtml(t.description || 'No description')}</div></td>
      <td><span class="badge-niveau badge-${getNiveauClass(t.niveau)}">${escapeHtml(t.niveau)}</span></td>
      <td>${formatDate(t.date_debut)}</td>
      <td>${formatDate(t.date_fin)}</td>
      <td class="text-center">
        <div class="dropdown">
          <button class="action-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); viewTournoi(${t.id})"><i class="far fa-eye"></i> View</a></li>
            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); editTournoi(${t.id})"><i class="far fa-edit"></i> Edit</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deleteTournoi(${t.id})"><i class="far fa-trash-alt"></i> Delete</a></li>
          </ul>
        </div>
      </td>
    </tr>
  `).join('');
  
  // Build pagination row
  const paginationRow = buildPaginationRow(currentPage, totalPages, startIndex + 1, endIndex, tournaments.length);
  
  tbody.innerHTML = rows + paginationRow;
}

function buildPaginationRow(currentPage, totalPages, startItem, endItem, totalItems) {
  if (totalPages <= 1) return '';
  
  let pageNumbers = '';
  const maxVisiblePages = 5;
  
  let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
  
  if (endPage - startPage < maxVisiblePages - 1) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  // First page
  if (startPage > 1) {
    pageNumbers += `<span class="pagination-number" onclick="goToPage(1)">1</span>`;
    if (startPage > 2) {
      pageNumbers += `<span class="pagination-number" style="cursor: default; border: none;">...</span>`;
    }
  }
  
  // Page numbers
  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === currentPage ? 'active' : '';
    pageNumbers += `<span class="pagination-number ${activeClass}" onclick="goToPage(${i})">${i}</span>`;
  }
  
  // Last page
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      pageNumbers += `<span class="pagination-number" style="cursor: default; border: none;">...</span>`;
    }
    pageNumbers += `<span class="pagination-number" onclick="goToPage(${totalPages})">${totalPages}</span>`;
  }
  
  const prevDisabled = currentPage === 1 ? 'disabled' : '';
  const nextDisabled = currentPage === totalPages ? 'disabled' : '';
  
  return `
    <tr class="pagination-row">
      <td colspan="9" style="border: none; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
          <div class="items-per-page">
            <span style="color: #6c757d; font-size: 0.875rem; font-weight: 600;">Show:</span>
            <select onchange="changeItemsPerPage(this.value)" style="padding: 6px 12px; border: 2px solid #dee2e6; border-radius: 6px; background: white; cursor: pointer; font-weight: 600; color: #495057;">
              <option value="5" ${itemsPerPage === 5 ? 'selected' : ''}>5</option>
              <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
              <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25</option>
              <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
              <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
            </select>
          </div>
          
          <div class="pagination-info">
            Showing <strong>${startItem}</strong> to <strong>${endItem}</strong> of <strong>${totalItems}</strong> tournaments
          </div>
          
          <div style="display: flex; align-items: center; gap: 8px;">
            <button class="pagination-prev ${prevDisabled}" onclick="goToPage(${currentPage - 1})" ${prevDisabled ? 'disabled' : ''}>
              <i class="fas fa-chevron-left"></i> Previous
            </button>
            
            ${pageNumbers}
            
            <button class="pagination-next ${nextDisabled}" onclick="goToPage(${currentPage + 1})" ${nextDisabled ? 'disabled' : ''}>
              Next <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </td>
    </tr>
  `;
}

function goToPage(page) {
  currentPage = page;
  applyFiltersAndSort();
  // Smooth scroll to top of table
  document.querySelector('.card-round').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function changeItemsPerPage(value) {
  itemsPerPage = parseInt(value);
  currentPage = 1;
  applyFiltersAndSort();
}

async function loadTournois() {
  try {
    const response = await fetch(TOURNAMENT_API_URL);
    const text = await response.text();
    const result = JSON.parse(text);
    
    if (result.success && result.data && result.data.length > 0) {
      allTournaments = result.data;
      applyFiltersAndSort();
    } else {
      allTournaments = [];
      displayTournaments([]);
    }
  } catch (error) {
    console.error('Loading error:', error);
    showTournoiMessage('Loading error: ' + error.message, 'danger');
    document.getElementById('tournoiTableBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading tournaments</td></tr>';
  }
}

function showFieldError(fieldId, message) {
  const errorDiv = document.getElementById(`${fieldId}-error`);
  const field = document.getElementById(fieldId);
  
  if (errorDiv && field) {
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    field.classList.add('is-invalid');
  }
}

function clearFieldError(fieldId) {
  const errorDiv = document.getElementById(`${fieldId}-error`);
  const field = document.getElementById(fieldId);
  
  if (errorDiv && field) {
    errorDiv.textContent = '';
    errorDiv.classList.remove('show');
    field.classList.remove('is-invalid');
  }
}

function clearAllErrors(prefix = '') {
  const fields = prefix ? ['edit_nom', 'edit_theme', 'edit_description', 'edit_niveau', 'edit_date_debut', 'edit_date_fin'] : ['nom', 'theme', 'description', 'niveau', 'date_debut', 'date_fin'];
  fields.forEach(fieldId => clearFieldError(fieldId));
}

function isValidDateFormat(dateStr) {
  const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  const match = dateStr.match(regex);
  
  if (!match) return false;
  
  const jour = parseInt(match[1], 10);
  const mois = parseInt(match[2], 10);
  const annee = parseInt(match[3], 10);
  
  if (mois < 1 || mois > 12) return false;
  if (jour < 1 || jour > 31) return false;
  if (annee < 1900 || annee > 2100) return false;
  
  const joursParMois = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  
  if ((annee % 4 === 0 && annee % 100 !== 0) || (annee % 400 === 0)) {
    joursParMois[1] = 29;
  }
  
  if (jour > joursParMois[mois - 1]) return false;
  
  return true;
}

function parseDate(dateStr) {
  const parts = dateStr.split('/');
  return new Date(parts[2], parts[1] - 1, parts[0]);
}

function convertToMysqlDate(dateStr) {
  const parts = dateStr.split('/');
  return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
}

function convertMysqlToDisplay(mysqlDate) {
  if (!mysqlDate) return '';
  const parts = mysqlDate.split('-');
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

async function handleAjoutTournoi() {
  clearAllErrors();
  
  let hasError = false;
  
  const nom = document.getElementById('nom').value.trim();
  const theme = document.getElementById('theme').value.trim();
  const description = document.getElementById('description').value.trim();
  const niveau = document.getElementById('niveau').value;
  const dateDebut = document.getElementById('date_debut').value.trim();
  const dateFin = document.getElementById('date_fin').value.trim();
  
  if (nom === '') {
    showFieldError('nom', 'Tournament name is required');
    hasError = true;
  } else if (nom.length < 3) {
    showFieldError('nom', 'Name must be at least 3 characters');
    hasError = true;
  } else if (nom.length > 100) {
    showFieldError('nom', 'Name must not exceed 100 characters');
    hasError = true;
  }
  
  if (theme === '') {
    showFieldError('theme', 'Theme is required');
    hasError = true;
  } else if (theme.length < 3) {
    showFieldError('theme', 'Theme must be at least 3 characters');
    hasError = true;
  }
  
  if (description === '') {
    showFieldError('description', 'Description is required');
    hasError = true;
  } else if (description.length < 10) {
    showFieldError('description', 'Description must be at least 10 characters');
    hasError = true;
  }
  
  if (niveau === '') {
    showFieldError('niveau', 'Please select a level');
    hasError = true;
  }
  
  if (dateDebut === '') {
    showFieldError('date_debut', 'Start date is required');
    hasError = true;
  } else if (!isValidDateFormat(dateDebut)) {
    showFieldError('date_debut', 'Invalid format. Use dd/mm/yyyy (e.g., 25/12/2024)');
    hasError = true;
  }
  
  if (dateFin === '') {
    showFieldError('date_fin', 'End date is required');
    hasError = true;
  } else if (!isValidDateFormat(dateFin)) {
    showFieldError('date_fin', 'Invalid format. Use dd/mm/yyyy (e.g., 31/12/2024)');
    hasError = true;
  }
  
  if (dateDebut && dateFin && isValidDateFormat(dateDebut) && isValidDateFormat(dateFin)) {
    const debut = parseDate(dateDebut);
    const fin = parseDate(dateFin);
    
    if (fin <= debut) {
      showFieldError('date_fin', 'End date must be after start date');
      hasError = true;
    }
  }
  
  if (hasError) return;
  
  const formData = new FormData();
  formData.append('action', 'ajouter');
  formData.append('nom', nom);
  formData.append('theme', theme);
  formData.append('description', description);
  formData.append('niveau', niveau);
  formData.append('date_debut', convertToMysqlDate(dateDebut));
  formData.append('date_fin', convertToMysqlDate(dateFin));
  
  // Add image if selected
  if (selectedImageFile) {
    formData.append('image', selectedImageFile);
  }
  
  try {
    const response = await fetch(TOURNAMENT_API_URL, { method: 'POST', body: formData });
    const text = await response.text();
    const result = JSON.parse(text);
    
    showTournoiMessage(result.message, result.success ? 'success' : 'danger');
    
    if (result.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('ajoutTournoiModal'));
      if (modal) modal.hide();
      
      // Reset form
      document.getElementById('nom').value = '';
      document.getElementById('theme').value = '';
      document.getElementById('description').value = '';
      document.getElementById('niveau').value = '';
      document.getElementById('date_debut').value = '';
      document.getElementById('date_fin').value = '';
      document.getElementById('image').value = '';
      document.getElementById('uploadArea').style.display = 'block';
      document.getElementById('imagePreview').style.display = 'none';
      selectedImageFile = null;
      
      clearAllErrors();
      loadTournois();
    }
  } catch (error) {
    console.error('Error:', error);
    showTournoiMessage('Connection error: ' + error.message, 'danger');
  }
}

async function handleModifierTournoi() {
  clearAllErrors('edit_');
  
  let hasError = false;
  
  const id = document.getElementById('edit_id').value;
  const nom = document.getElementById('edit_nom').value.trim();
  const theme = document.getElementById('edit_theme').value.trim();
  const description = document.getElementById('edit_description').value.trim();
  const niveau = document.getElementById('edit_niveau').value;
  const dateDebut = document.getElementById('edit_date_debut').value.trim();
  const dateFin = document.getElementById('edit_date_fin').value.trim();
  
  if (nom === '') {
    showFieldError('edit_nom', 'Tournament name is required');
    hasError = true;
  } else if (nom.length < 3) {
    showFieldError('edit_nom', 'Name must be at least 3 characters');
    hasError = true;
  } else if (nom.length > 100) {
    showFieldError('edit_nom', 'Name must not exceed 100 characters');
    hasError = true;
  }
  
  if (theme === '') {
    showFieldError('edit_theme', 'Theme is required');
    hasError = true;
  } else if (theme.length < 3) {
    showFieldError('edit_theme', 'Theme must be at least 3 characters');
    hasError = true;
  }
  
  if (description === '') {
    showFieldError('edit_description', 'Description is required');
    hasError = true;
  } else if (description.length < 10) {
    showFieldError('edit_description', 'Description must be at least 10 characters');
    hasError = true;
  }
  
  if (niveau === '') {
    showFieldError('edit_niveau', 'Please select a level');
    hasError = true;
  }
  
  if (dateDebut === '') {
    showFieldError('edit_date_debut', 'Start date is required');
    hasError = true;
  } else if (!isValidDateFormat(dateDebut)) {
    showFieldError('edit_date_debut', 'Invalid format. Use dd/mm/yyyy');
    hasError = true;
  }
  
  if (dateFin === '') {
    showFieldError('edit_date_fin', 'End date is required');
    hasError = true;
  } else if (!isValidDateFormat(dateFin)) {
    showFieldError('edit_date_fin', 'Invalid format. Use dd/mm/yyyy');
    hasError = true;
  }
  
  if (dateDebut && dateFin && isValidDateFormat(dateDebut) && isValidDateFormat(dateFin)) {
    const debut = parseDate(dateDebut);
    const fin = parseDate(dateFin);
    
    if (fin <= debut) {
      showFieldError('edit_date_fin', 'End date must be after start date');
      hasError = true;
    }
  }
  
  if (hasError) return;
  
  const formData = new FormData();
  formData.append('action', 'modifier');
  formData.append('id', id);
  formData.append('nom', nom);
  formData.append('theme', theme);
  formData.append('description', description);
  formData.append('niveau', niveau);
  formData.append('date_debut', convertToMysqlDate(dateDebut));
  formData.append('date_fin', convertToMysqlDate(dateFin));
  
  // Handle image removal or upload
  if (imageRemovalState.markedForRemoval) {
    formData.append('remove_image', '1');
  } else if (editSelectedImageFile) {
    formData.append('image', editSelectedImageFile);
  }
  
  try {
    const response = await fetch(TOURNAMENT_API_URL, { method: 'POST', body: formData });
    const text = await response.text();
    const result = JSON.parse(text);
    
    showTournoiMessage(result.message, result.success ? 'success' : 'danger');
    
    if (result.success) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('modifierTournoiModal'));
      if (modal) modal.hide();
      
      // Reset state
      editSelectedImageFile = null;
      imageRemovalState.markedForRemoval = false;
      imageRemovalState.originalImagePath = null;
      hideRemovalWarning();
      clearAllErrors('edit_');
      loadTournois();
    }
  } catch (error) {
    console.error('Error:', error);
    showTournoiMessage('Connection error: ' + error.message, 'danger');
  }
}

async function editTournoi(id) {
  try {
    const response = await fetch(`${TOURNAMENT_API_URL}?id=${id}`);
    const text = await response.text();
    const result = JSON.parse(text);
    
    if (result.success && result.data) {
      const t = result.data;
      document.getElementById('edit_id').value = t.id;
      document.getElementById('edit_nom').value = t.nom;
      document.getElementById('edit_theme').value = t.theme;
      document.getElementById('edit_description').value = t.description || '';
      document.getElementById('edit_niveau').value = t.niveau;
      document.getElementById('edit_date_debut').value = convertMysqlToDisplay(t.date_debut);
      document.getElementById('edit_date_fin').value = convertMysqlToDisplay(t.date_fin);
      document.getElementById('edit_current_image').value = t.image || '';
      
      // Reset removal state
      imageRemovalState.markedForRemoval = false;
      imageRemovalState.originalImagePath = t.image;
      hideRemovalWarning();
      
      const editImagePreview = document.getElementById('editImagePreview');
      const editUploadArea = document.getElementById('editUploadArea');
      const editPreviewImg = document.getElementById('editPreviewImg');
      
      // Show existing image if available
      if (t.image) {
        editPreviewImg.src = t.image;
        editUploadArea.style.display = 'none';
        editImagePreview.style.display = 'block';
        editImagePreview.style.opacity = '1';
        editImagePreview.style.filter = 'none';
        updateRemoveButton(false);
      } else {
        editUploadArea.style.display = 'block';
        editImagePreview.style.display = 'none';
      }
      
      editSelectedImageFile = null;
      document.getElementById('edit_image').value = '';
      clearAllErrors('edit_');
      new bootstrap.Modal(document.getElementById('modifierTournoiModal')).show();
    } else {
      showTournoiMessage('Tournament not found', 'danger');
    }
  } catch (error) {
    console.error('Loading error:', error);
    showTournoiMessage('Loading error: ' + error.message, 'danger');
  }
}

function viewTournoi(id) {
  const tournament = allTournaments.find(t => t.id === id);
  if (tournament) {
    const content = `
      <div class="row">
        ${tournament.image ? `
        <div class="col-md-4 mb-3">
          <img src="${escapeHtml(tournament.image)}" alt="${escapeHtml(tournament.nom)}" class="img-fluid rounded">
        </div>
        ` : ''}
        <div class="${tournament.image ? 'col-md-8' : 'col-12'}">
          <h5 class="mb-3"><i class="fas fa-trophy text-primary me-2"></i>${escapeHtml(tournament.nom)}</h5>
          <p><strong><i class="fas fa-tag me-2"></i>Theme:</strong> ${escapeHtml(tournament.theme)}</p>
          <p><strong><i class="fas fa-align-left me-2"></i>Description:</strong><br>${escapeHtml(tournament.description || 'No description')}</p>
          <p><strong><i class="fas fa-layer-group me-2"></i>Level:</strong> <span class="badge-niveau badge-${getNiveauClass(tournament.niveau)}">${escapeHtml(tournament.niveau)}</span></p>
          <p><strong><i class="fas fa-calendar-alt me-2"></i>Start Date:</strong> ${formatDate(tournament.date_debut)}</p>
          <p><strong><i class="fas fa-calendar-check me-2"></i>End Date:</strong> ${formatDate(tournament.date_fin)}</p>
        </div>
      </div>
    `;
    document.getElementById('viewTournoiContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewTournoiModal')).show();
  }
}

async function deleteTournoi(id) {
  const userConfirmed = window.confirm('Are you sure you want to delete this tournament?');
  if (!userConfirmed) return;
  
  const formData = new FormData();
  formData.append('action', 'supprimer');
  formData.append('id', id);
  
  try {
    const response = await fetch(TOURNAMENT_API_URL, { method: 'POST', body: formData });
    const text = await response.text();
    const result = JSON.parse(text);
    
    showTournoiMessage(result.message, result.success ? 'success' : 'danger');
    
    if (result.success) {
      loadTournois();
    }
  } catch (error) {
    console.error('Connection error:', error);
    showTournoiMessage('Connection error: ' + error.message, 'danger');
  }
}

function showTournoiMessage(message, type) {
  const container = document.getElementById('messageContainer');
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  container.innerHTML = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
      <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;
  setTimeout(() => container.innerHTML = '', 5000);
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US');
}

function getNiveauClass(niveau) {
  const map = {
    'Débutant': 'debutant',
    'Intermédiaire': 'intermediaire',
    'Expert': 'expert'
  };
  return map[niveau] || 'debutant';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}