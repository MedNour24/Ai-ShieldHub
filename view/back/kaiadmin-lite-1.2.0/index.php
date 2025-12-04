<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - User Management Dashboard</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    
    <style>
      .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: center;
      }
      .btn-action {
        padding: 4px 8px;
        font-size: 12px;
      }
      .loading-spinner {
        text-align: center;
        padding: 20px;
      }
      .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
      }
      .badge {
        font-size: 12px;
        padding: 5px 10px;
      }
      .search-filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
      }
      .search-input-group {
        position: relative;
      }
      .search-input-group .form-control {
        padding-left: 40px;
      }
      .search-input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
      }
      .filter-select {
        cursor: pointer;
      }
      .clear-filters-btn {
        display: none;
      }
      .clear-filters-btn.active {
        display: inline-block;
      }
      .highlight {
        background-color: #fff3cd;
        font-weight: 500;
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="index.php" class="logo">
              <img
                src="assets/img/kaiadmin/logo_light.svg"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item active">
                <a href="index.php">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Components</h4>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#base">
                  <i class="fas fa-layer-group"></i>
                  <p>Base</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="base">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Avatars</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Buttons</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#forms">
                  <i class="fas fa-pen-square"></i>
                  <p>Forms</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="forms">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Basic Form</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#tables">
                  <i class="fas fa-table"></i>
                  <p>Tables</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="tables">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Basic Table</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li><li class="nav-item">
                <a data-bs-toggle="collapse" href="#maps">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Maps</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="maps">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Google Maps</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="index.php" class="logo">
                <img
                  src="assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      <img
                        src="assets/img/profile.jpg"
                        alt="..."
                        class="avatar-img rounded-circle"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold">Admin</span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4>Admin</h4>
                            <p class="text-muted">admin@example.com</p>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">My Profile</a>
                        <a class="dropdown-item" href="#">Account Setting</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Logout</a>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
            >
              <div>
                <h3 class="fw-bold mb-3">Dashboard</h3>
                <h6 class="op-7 mb-2">User Management System</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                  <i class="fas fa-user-plus me-2"></i>Add New User
                </button>
              </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                          <i class="fas fa-users"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Total Users</p>
                          <h4 class="card-title" id="total-users">0</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                          <i class="fas fa-user-graduate"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Students</p>
                          <h4 class="card-title" id="student-count">0</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                          <i class="fas fa-user-shield"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Admins</p>
                          <h4 class="card-title" id="admin-count">0</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                          <i class="far fa-check-circle"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Active</p>
                          <h4 class="card-title" id="active-count">0</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Management Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Users List</div>
                      <div class="card-tools">
                        <button class="btn btn-sm btn-light" id="refresh-users">
                          <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <!-- Search and Filter Section -->
                    <div class="search-filter-section">
                      <div class="row g-3">
                        <div class="col-md-5">
                          <label class="form-label fw-bold">
                            <i class="fas fa-search me-2"></i>Search Users
                          </label>
                          <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input 
                              type="text" 
                              class="form-control" 
                              id="search-input" 
                              placeholder="Search by name or email..."
                            >
                          </div>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-bold">
                            <i class="fas fa-filter me-2"></i>Filter by Role
                          </label>
                          <select class="form-control filter-select" id="filter-role">
                            <option value="">All Roles</option>
                            <option value="student">Student</option>
                            <option value="admin">Admin</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label fw-bold">
                            <i class="fas fa-toggle-on me-2"></i>Filter by Status
                          </label>
                          <select class="form-control filter-select" id="filter-status">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                          </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                          <button class="btn btn-secondary w-100 clear-filters-btn" id="clear-filters">
                            <i class="fas fa-times"></i>
                          </button>
                        </div>
                      </div>
                      <div class="mt-3">
                        <small class="text-muted">
                          <i class="fas fa-info-circle me-1"></i>
                          <span id="filter-results-text">Showing all users</span>
                        </small>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-hover mb-0" id="users-table">
                        <thead class="table-light">
                          <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                          </tr>
                        </thead>
                        <tbody id="users-tbody">
                          <tr class="loading-spinner">
                            <td colspan="7" class="text-center">
                              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                              Loading users...
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="add-user-form">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback" id="name-error"></div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback" id="email-error"></div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback" id="password-error"></div>
              </div>
              <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="invalid-feedback" id="confirm-password-error"></div>
              </div>
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role" required>
                  <option value="">Select Role</option>
                  <option value="student">Student</option>
                  <option value="admin">Admin</option>
                </select>
                <div class="invalid-feedback" id="role-error"></div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="submit-add-user">Add User</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="edit-user-form">
              <input type="hidden" id="edit-id" name="id">
              <div class="mb-3">
                <label for="edit-name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="edit-name" name="name" required>
                <div class="invalid-feedback" id="edit-name-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="edit-email" name="email" required>
                <div class="invalid-feedback" id="edit-email-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-role" class="form-label">Role</label>
                <select class="form-control" id="edit-role" name="role" required>
                  <option value="">Select Role</option>
                  <option value="student">Student</option>
                  <option value="admin">Admin</option>
                </select>
                <div class="invalid-feedback" id="edit-role-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-status" class="form-label">Status</label>
                <select class="form-control" id="edit-status" name="status" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="submit-edit-user">Update User</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="assets/js/kaiadmin.min.js"></script>

    <script>
      $(document).ready(function() {
        // CHEMIN CORRECT vers le contrôleur (depuis view/back/kaidmin-lite/)
        const controllerUrl = '../../../controller/UserController.php';

        // Variables globales pour le filtrage
        let allUsers = [];
        let filteredUsers = [];

        // Test de la connexion au contrôleur
        console.log('Controller URL:', controllerUrl);

        // Load users on page load
        loadUsers();

        // Refresh users button
        $('#refresh-users').on('click', function() {
          loadUsers();
        });

        // Add user functionality
        $('#submit-add-user').on('click', function() {
          addUser();
        });

        // Edit user functionality
        $('#submit-edit-user').on('click', function() {
          updateUser();
        });

        // Search functionality
        $('#search-input').on('keyup', function() {
          applyFilters();
        });

        // Filter by role
        $('#filter-role').on('change', function() {
          applyFilters();
        });

        // Filter by status
        $('#filter-status').on('change', function() {
          applyFilters();
        });

        // Clear filters
        $('#clear-filters').on('click', function() {
          $('#search-input').val('');
          $('#filter-role').val('');
          $('#filter-status').val('');
          applyFilters();
        });

        function loadUsers() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            beforeSend: function() {
              $('#users-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="7" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading users...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              console.log('Response:', response);
              if (response.success) {
                allUsers = response.data;
                applyFilters();
                updateStatistics(response.data);
              } else {
                showError('Failed to load users: ' + (response.message || 'Unknown error'));
                $('#users-tbody').html(`
                  <tr>
                    <td colspan="7" class="text-center text-danger">
                      <i class="fas fa-exclamation-triangle me-2"></i>
                      Error: ${response.message || 'Failed to load users'}
                    </td>
                  </tr>
                `);
              }
            },
            error: function(xhr, status, error) {
              console.error('AJAX Error:', xhr);
              console.error('Status:', status);
              console.error('Error:', error);
              console.error('Response Text:', xhr.responseText);
              showError('Error loading users: ' + error);
              $('#users-tbody').html(`
                <tr>
                  <td colspan="7" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Network error: Cannot load users. Check console for details.
                  </td>
                </tr>
              `);
            }
          });
        }

        function applyFilters() {
          const searchTerm = $('#search-input').val().toLowerCase().trim();
          const roleFilter = $('#filter-role').val();
          const statusFilter = $('#filter-status').val();

          // Check if any filter is active
          const hasActiveFilters = searchTerm !== '' || roleFilter !== '' || statusFilter !== '';
          
          // Toggle clear button visibility
          if (hasActiveFilters) {
            $('#clear-filters').addClass('active');
          } else {
            $('#clear-filters').removeClass('active');
          }

          // Apply filters
          filteredUsers = allUsers.filter(user => {
            // Search filter (name or email)
            const matchesSearch = searchTerm === '' || 
              user.name.toLowerCase().includes(searchTerm) || 
              user.email.toLowerCase().includes(searchTerm);

            // Role filter
            const matchesRole = roleFilter === '' || user.role === roleFilter;

            // Status filter
            const matchesStatus = statusFilter === '' || user.status === statusFilter;

            return matchesSearch && matchesRole && matchesStatus;
          });

          // Update results text
          updateFilterResultsText(filteredUsers.length, allUsers.length, searchTerm, roleFilter, statusFilter);

          // Display filtered users
          displayUsers(filteredUsers, searchTerm);
        }

        function updateFilterResultsText(filteredCount, totalCount, searchTerm, roleFilter, statusFilter) {
          let text = '';
          
          if (filteredCount === totalCount) {
            text = `Showing all ${totalCount} users`;
          } else {
            text = `Showing ${filteredCount} of ${totalCount} users`;
            
            const filters = [];
            if (searchTerm) filters.push(`search: "${searchTerm}"`);
            if (roleFilter) filters.push(`role: ${roleFilter}`);
            if (statusFilter) filters.push(`status: ${statusFilter}`);
            
            if (filters.length > 0) {
              text += ` (filtered by ${filters.join(', ')})`;
            }
          }
          
          $('#filter-results-text').text(text);
        }

        function displayUsers(users, highlightTerm = '') {
          const tbody = $('#users-tbody');
          tbody.empty();

          if (users.length === 0) {
            const isFiltered = $('#search-input').val() !== '' || 
                              $('#filter-role').val() !== '' || 
                              $('#filter-status').val() !== '';
            
            if (isFiltered) {
              tbody.html(`
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <div class="empty-state">
                      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                      <p>No users found matching your search criteria</p>
                      <button class="btn btn-secondary mt-2" id="clear-search-inline">
                        <i class="fas fa-times me-2"></i>Clear Filters
                      </button>
                    </div>
                  </td>
                </tr>
              `);
              
              $('#clear-search-inline').on('click', function() {
                $('#search-input').val('');
                $('#filter-role').val('');
                $('#filter-status').val('');
                applyFilters();
              });
            } else {
              tbody.html(`
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <div class="empty-state">
                      <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                      <p>No users found</p>
                      <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-2"></i>Add First User
                      </button>
                    </div>
                  </td>
                </tr>
              `);
            }
            return;
          }

          users.forEach(user => {
            const statusBadge = user.status === 'active' 
              ? '<span class="badge badge-success">Active</span>'
              : '<span class="badge badge-danger">Inactive</span>';

            const roleBadge = user.role === 'admin'
              ? '<span class="badge badge-primary">Admin</span>'
              : '<span class="badge badge-info">Student</span>';

            const createdDate = new Date(user.created_at).toLocaleDateString();

            // Highlight search terms
            let displayName = escapeHtml(user.name);
            let displayEmail = escapeHtml(user.email);
            
            if (highlightTerm) {
              const regex = new RegExp(`(${escapeRegex(highlightTerm)})`, 'gi');
              displayName = displayName.replace(regex, '<span class="highlight">$1</span>');
              displayEmail = displayEmail.replace(regex, '<span class="highlight">$1</span>');
            }

            const row = `
              <tr>
                <td>${user.id}</td>
                <td>${displayName}</td>
                <td>${displayEmail}</td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td>${createdDate}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-warning btn-action btn-edit" 
                            data-id="${user.id}"
                            data-name="${escapeHtml(user.name)}"
                            data-email="${escapeHtml(user.email)}"
                            data-role="${user.role}"
                            data-status="${user.status}"
                            title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-action btn-delete" 
                            data-id="${user.id}"
                            data-name="${escapeHtml(user.name)}"
                            title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `;
            tbody.append(row);
          });

          // Attach event listeners to action buttons
          $('.btn-edit').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const role = $(this).data('role');
            const status = $(this).data('status');
            openEditModal(id, name, email, role, status);
          });

          $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            deleteUser(id, name);
          });
        }

        function updateStatistics(users) {
          const totalUsers = users.length;
          const studentCount = users.filter(u => u.role === 'student').length;
          const adminCount = users.filter(u => u.role === 'admin').length;
          const activeCount = users.filter(u => u.status === 'active').length;

          $('#total-users').text(totalUsers);
          $('#student-count').text(studentCount);
          $('#admin-count').text(adminCount);
          $('#active-count').text(activeCount);
        }

        function addUser() {
          const formData = {
            action: 'add',
            name: $('#name').val().trim(),
            email: $('#email').val().trim(),
            password: $('#password').val(),
            confirm_password: $('#confirm_password').val(),
            role: $('#role').val()
          };

          // Basic validation
          if (!validateAddForm(formData)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
              $('#submit-add-user').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');
            },
            success: function(response) {
              if (response.success) {
                $('#addUserModal').modal('hide');
                $('#add-user-form')[0].reset();
                showSuccess('User added successfully!');
                loadUsers();
              } else {
                showError(response.message || 'Failed to add user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error adding user: ' + error);
              console.error('Error details:', xhr);
              console.error('Response Text:', xhr.responseText);
            },
            complete: function() {
              $('#submit-add-user').prop('disabled', false).html('Add User');
            }
          });
        }

        function openEditModal(id, name, email, role, status) {
          $('#edit-id').val(id);
          $('#edit-name').val(name);
          $('#edit-email').val(email);
          $('#edit-role').val(role);
          $('#edit-status').val(status);
          $('#editUserModal').modal('show');
        }

        function updateUser() {
          const formData = {
            action: 'update',
            id: $('#edit-id').val(),
            name: $('#edit-name').val().trim(),
            email: $('#edit-email').val().trim(),
            role: $('#edit-role').val(),
            status: $('#edit-status').val()
          };

          if (!validateEditForm(formData)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
              $('#submit-edit-user').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
            },
            success: function(response) {
              if (response.success) {
                $('#editUserModal').modal('hide');
                showSuccess('User updated successfully!');
                loadUsers();
              } else {
                showError(response.message || 'Failed to update user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error updating user: ' + error);
              console.error('Error details:', xhr);
            },
            complete: function() {
              $('#submit-edit-user').prop('disabled', false).html('Update User');
            }
          });
        }

        function deleteUser(id, name) {
          if (!confirm(`Are you sure you want to delete user "${name}"? This action cannot be undone.`)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('User deleted successfully!');
                loadUsers();
              } else {
                showError(response.message || 'Failed to delete user');
              }
            },
            error: function(xhr, status, error) {
              showError('Error deleting user: ' + error);
              console.error('Error details:', xhr);
            }
          });
        }

        function validateAddForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');

          if (!data.name) {
            $('#name').addClass('is-invalid');
            $('#name-error').text('Name is required').show();
            isValid = false;
          }

          if (!data.email) {
            $('#email').addClass('is-invalid');
            $('#email-error').text('Email is required').show();
            isValid = false;
          } else if (!isValidEmail(data.email)) {
            $('#email').addClass('is-invalid');
            $('#email-error').text('Please enter a valid email address').show();
            isValid = false;
          }

          if (!data.password) {
            $('#password').addClass('is-invalid');
            $('#password-error').text('Password is required').show();
            isValid = false;
          } else if (data.password.length < 6) {
            $('#password').addClass('is-invalid');
            $('#password-error').text('Password must be at least 6 characters').show();
            isValid = false;
          }

          if (data.password !== data.confirm_password) {
            $('#confirm_password').addClass('is-invalid');
            $('#confirm-password-error').text('Passwords do not match').show();
            isValid = false;
          }

          if (!data.role) {
            $('#role').addClass('is-invalid');
            $('#role-error').text('Role is required').show();
            isValid = false;
          }

          return isValid;
        }

        function validateEditForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');

          if (!data.name) {
            $('#edit-name').addClass('is-invalid');
            $('#edit-name-error').text('Name is required').show();
            isValid = false;
          }

          if (!data.email) {
            $('#edit-email').addClass('is-invalid');
            $('#edit-email-error').text('Email is required').show();
            isValid = false;
          } else if (!isValidEmail(data.email)) {
            $('#edit-email').addClass('is-invalid');
            $('#edit-email-error').text('Please enter a valid email address').show();
            isValid = false;
          }

          if (!data.role) {
            $('#edit-role').addClass('is-invalid');
            $('#edit-role-error').text('Role is required').show();
            isValid = false;
          }

          return isValid;
        }

        function isValidEmail(email) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return emailRegex.test(email);
        }

        function escapeHtml(text) {
          const s = String(text || '');
          const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
          };
          return s.replace(/[&<>\"']/g, m => map[m]);
        }

        function escapeRegex(text) {
          return text.replace(/[.*+?^${}()|[\]\\]/g, '\\          users.forEach(user =>');
        }

        function showSuccess(message) {
          $.notify({
            message: message
          }, {
            type: 'success',
            placement: {
              from: 'top',
              align: 'right'
            },
            delay: 3000
          });
        }

        function showError(message) {
          $.notify({
            message: message
          }, {
            type: 'danger',
            placement: {
              from: 'top',
              align: 'right'
            },
            delay: 5000
          });
        }

        // Clear validation errors when modal is shown
        $('#addUserModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');
          $('#add-user-form')[0].reset();
        });

        $('#editUserModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
          $('.form-control').removeClass('is-invalid');
        });
      });
    </script>
  </body>
</html>