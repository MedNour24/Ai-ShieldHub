<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Kaiadmin - Cybersecurity Course Management</title>
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
      .price-badge {
        font-weight: bold;
        font-size: 14px;
      }
      .course-description {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
      }
      .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
      }
      .stats-label {
        font-size: 0.9rem;
        opacity: 0.9;
      }
      .payment-status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
      }
      .purchase-count {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
        border-radius: 20px;
        padding: 8px 12px;
        font-weight: bold;
        font-size: 0.9rem;
        display: inline-block;
      }
      .enrollment-count {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-radius: 20px;
        padding: 8px 12px;
        font-weight: bold;
        font-size: 0.9rem;
        display: inline-block;
      }
      .course-purchase-stats {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(99, 102, 241, 0.2);
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
            <a href="admin.php" class="logo">
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
                <a href="admin.php">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              
              <!-- ADDED: Public Site Navigation Link -->
              <li class="nav-item">
                <a href="index.php">
                  <i class="fas fa-globe"></i>
                  <p>View Public Site</p>
                </a>
              </li>

              <!-- NEW: Cours Main Menu with Sub-menu -->
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#cours">
                  <i class="fas fa-book"></i>
                  <p>Cours</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse show" id="cours">
                  <ul class="nav nav-collapse">
                    <li class="active">
                      <a href="#contenu" data-tab="contenu">
                        <span class="sub-item">Contenu</span>
                      </a>
                    </li>
                    <li>
                      <a href="#paiement" data-tab="paiement">
                        <span class="sub-item">Paiement</span>
                      </a>
                    </li>
                  </ul>
                </div>
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
              </li>
              <li class="nav-item">
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
              <a href="admin.php" class="logo">
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
                        <a class="dropdown-item" href="index.php">View Public Site</a>
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
                <h3 class="fw-bold mb-3">Cybersecurity Training Dashboard</h3>
                <h6 class="op-7 mb-2">Professional Security Course Management System</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal" id="addCourseBtn" style="display: none;">
                  <i class="fas fa-plus me-2"></i>Add New Course
                </button>
              </div>
            </div>

            <!-- Tab Navigation -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="card card-round">
                  <div class="card-body py-3">
                    <ul class="nav nav-pills nav-primary nav-pills-no-bd" id="pills-tab" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" id="pills-contenu-tab" data-toggle="pill" href="#pills-contenu" role="tab" aria-controls="pills-contenu" aria-selected="true">
                          <i class="fas fa-book me-2"></i>Contenu des Cours
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="pills-paiement-tab" data-toggle="pill" href="#pills-paiement" role="tab" aria-controls="pills-paiement" aria-selected="false">
                          <i class="fas fa-credit-card me-2"></i>Suivi des Paiements
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">
              
              <!-- Contenu Tab - RESTORED TO INITIAL STATE -->
              <div class="tab-pane fade show active" id="pills-contenu" role="tabpanel" aria-labelledby="pills-contenu-tab">
                <!-- Statistics Cards for Contenu -->
                <div class="row">
                  <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                      <div class="card-body">
                        <div class="row align-items-center">
                          <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                              <i class="fas fa-book"></i>
                            </div>
                          </div>
                          <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                              <p class="card-category">Total Courses</p>
                              <h4 class="card-title" id="total-courses">0</h4>
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
                              <i class="fas fa-tag"></i>
                            </div>
                          </div>
                          <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                              <p class="card-category">Free Courses</p>
                              <h4 class="card-title" id="free-count">0</h4>
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
                              <i class="fas fa-dollar-sign"></i>
                            </div>
                          </div>
                          <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                              <p class="card-category">Paid Courses</p>
                              <h4 class="card-title" id="paid-count">0</h4>
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
                              <p class="card-category">Active Courses</p>
                              <h4 class="card-title" id="active-count">0</h4>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Course Management Section -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="card card-round">
                      <div class="card-header">
                        <div class="card-head-row">
                          <div class="card-title">Cybersecurity Courses List</div>
                          <div class="card-tools">
                            <button class="btn btn-sm btn-light" id="refresh-courses">
                              <i class="fas fa-sync-alt"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="table-responsive">
                          <table class="table table-hover mb-0" id="courses-table">
                            <thead class="table-light">
                              <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>License Type</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                              </tr>
                            </thead>
                            <tbody id="courses-tbody">
                              <tr class="loading-spinner">
                                <td colspan="8" class="text-center">
                                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                  Loading courses...
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

              <!-- Paiement Tab - ENHANCED WITH PURCHASE STATISTICS -->
              <div class="tab-pane fade" id="pills-paiement" role="tabpanel" aria-labelledby="pills-paiement-tab">
                <!-- Statistics Cards for Paiement -->
                <div class="row">
                  <div class="col-sm-6 col-md-3">
                    <div class="stats-card">
                      <div class="stats-number" id="total-revenue">$0</div>
                      <div class="stats-label">Total Revenue</div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                      <div class="stats-number" id="total-purchases">0</div>
                      <div class="stats-label">Total Purchases</div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                      <div class="stats-number" id="unique-customers">0</div>
                      <div class="stats-label">Unique Customers</div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                      <div class="stats-number" id="avg-purchase">$0</div>
                      <div class="stats-label">Average Purchase</div>
                    </div>
                  </div>
                </div>

                <!-- Course Purchase Statistics -->
                <div class="row">
                  <div class="col-12">
                    <div class="course-purchase-stats">
                      <h5 class="text-white mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Course Purchase & Enrollment Statistics
                      </h5>
                      <div class="table-responsive">
                        <table class="table table-hover mb-0" id="purchase-stats-table">
                          <thead class="table-light">
                            <tr>
                              <th>Course ID</th>
                              <th>Course Title</th>
                              <th>License Type</th>
                              <th>Price</th>
                              <th>Status</th>
                              <th class="text-center">Purchases/Enrollments</th>
                              <th class="text-center">Total Revenue</th>
                            </tr>
                          </thead>
                          <tbody id="purchase-stats-tbody">
                            <tr class="loading-spinner">
                              <td colspan="7" class="text-center">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Loading purchase statistics...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Payment Tracking Section -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="card card-round">
                      <div class="card-header">
                        <div class="card-head-row">
                          <div class="card-title">Payment History & Tracking</div>
                          <div class="card-tools">
                            <button class="btn btn-sm btn-light" id="refresh-payments">
                              <i class="fas fa-sync-alt"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="table-responsive">
                          <table class="table table-hover mb-0" id="payments-table">
                            <thead class="table-light">
                              <tr>
                                <th>Transaction ID</th>
                                <th>Course</th>
                                <th>Student</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                              </tr>
                            </thead>
                            <tbody id="payments-tbody">
                              <tr class="loading-spinner">
                                <td colspan="8" class="text-center">
                                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                  Loading payment history...
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
      </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCourseModalLabel">Add New Cybersecurity Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="add-course-form">
              <div class="mb-3">
                <label for="title" class="form-label">Course Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
                <div class="invalid-feedback" id="title-error"></div>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                <div class="invalid-feedback" id="description-error"></div>
              </div>
              <div class="mb-3">
                <label for="license_type" class="form-label">License Type</label>
                <select class="form-control" id="license_type" name="license_type" required>
                  <option value="">Select License Type</option>
                  <option value="free">Free</option>
                  <option value="paid">Paid</option>
                </select>
                <div class="invalid-feedback" id="license-type-error"></div>
              </div>
              <div class="mb-3" id="price-field" style="display: none;">
                <label for="price" class="form-label">Price ($)</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="0">
                <div class="invalid-feedback" id="price-error"></div>
              </div>
              <div class="mb-3">
                <label for="duration" class="form-label">Duration (hours)</label>
                <input type="number" class="form-control" id="duration" name="duration" min="1" required>
                <div class="invalid-feedback" id="duration-error"></div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="submit-add-course">Add Course</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editCourseModalLabel">Edit Cybersecurity Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="edit-course-form">
              <input type="hidden" id="edit-id" name="id">
              <div class="mb-3">
                <label for="edit-title" class="form-label">Course Title</label>
                <input type="text" class="form-control" id="edit-title" name="title" required>
                <div class="invalid-feedback" id="edit-title-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-description" class="form-label">Description</label>
                <textarea class="form-control" id="edit-description" name="description" rows="3" required></textarea>
                <div class="invalid-feedback" id="edit-description-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-license_type" class="form-label">License Type</label>
                <select class="form-control" id="edit-license_type" name="license_type" required>
                  <option value="">Select License Type</option>
                  <option value="free">Free</option>
                  <option value="paid">Paid</option>
                </select>
                <div class="invalid-feedback" id="edit-license-type-error"></div>
              </div>
              <div class="mb-3" id="edit-price-field">
                <label for="edit-price" class="form-label">Price ($)</label>
                <input type="number" class="form-control" id="edit-price" name="price" min="0" step="0.01">
                <div class="invalid-feedback" id="edit-price-error"></div>
              </div>
              <div class="mb-3">
                <label for="edit-duration" class="form-label">Duration (hours)</label>
                <input type="number" class="form-control" id="edit-duration" name="duration" min="1" required>
                <div class="invalid-feedback" id="edit-duration-error"></div>
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
            <button type="button" class="btn btn-primary" id="submit-edit-course">Update Course</button>
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
        const controllerUrl = 'controller/CourseController.php';

        // Initialize tabs
        function initializeTabs() {
          // Show "Add Course" button only in Contenu tab
          $('#pills-contenu-tab').on('click', function() {
            $('#addCourseBtn').show();
          });
          
          $('#pills-paiement-tab').on('click', function() {
            $('#addCourseBtn').hide();
            loadPaymentData();
          });

          // Sidebar navigation
          $('a[data-tab="contenu"]').on('click', function(e) {
            e.preventDefault();
            $('#pills-contenu-tab').tab('show');
            $('#addCourseBtn').show();
          });

          $('a[data-tab="paiement"]').on('click', function(e) {
            e.preventDefault();
            $('#pills-paiement-tab').tab('show');
            $('#addCourseBtn').hide();
            loadPaymentData();
          });
        }

        // Load courses on page load
        loadCourses();

        // Initialize tabs
        initializeTabs();

        // Refresh courses button
        $('#refresh-courses').on('click', function() {
          loadCourses();
        });

        // Refresh payments button
        $('#refresh-payments').on('click', function() {
          loadPaymentData();
        });

        // Show/hide price field based on license type
        $('#license_type').on('change', function() {
          if ($(this).val() === 'paid') {
            $('#price-field').show();
            $('#price').prop('required', true);
          } else {
            $('#price-field').hide();
            $('#price').prop('required', false);
            $('#price').val('0');
          }
        });

        $('#edit-license_type').on('change', function() {
          if ($(this).val() === 'paid') {
            $('#edit-price-field').show();
            $('#edit-price').prop('required', true);
          } else {
            $('#edit-price-field').hide();
            $('#edit-price').prop('required', false);
            $('#edit-price').val('0');
          }
        });

        // Add course functionality
        $('#submit-add-course').on('click', function() {
          addCourse();
        });

        // Edit course functionality
        $('#submit-edit-course').on('click', function() {
          updateCourse();
        });

        // Enter key in modals
        $('#add-course-form, #edit-course-form').on('keypress', function(e) {
          if (e.which === 13) {
            e.preventDefault();
            if ($(this).attr('id') === 'add-course-form') {
              addCourse();
            } else {
              updateCourse();
            }
          }
        });

        function loadCourses() {
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'list' },
            dataType: 'json',
            beforeSend: function() {
              $('#courses-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="8" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading courses...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              if (response.success) {
                displayCourses(response.data);
                updateStatistics(response.data);
              } else {
                showError('Failed to load courses: ' + (response.message || 'Unknown error'));
              }
            },
            error: function(xhr, status, error) {
              showError('Error loading courses: ' + error);
            }
          });
        }

        function displayCourses(courses) {
          const tbody = $('#courses-tbody');
          tbody.empty();

          if (courses.length === 0) {
            tbody.html(`
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="empty-state">
                    <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                    <p>No cybersecurity courses found</p>
                  </div>
                </td>
              </tr>
            `);
            return;
          }

          courses.forEach(course => {
            const statusBadge = course.status === 'active' 
              ? '<span class="badge badge-success">Active</span>'
              : '<span class="badge badge-danger">Inactive</span>';

            const licenseBadge = course.license_type === 'paid'
              ? '<span class="badge badge-primary">Paid</span>'
              : '<span class="badge badge-info">Free</span>';

            const priceDisplay = course.license_type === 'paid' 
              ? `<span class="price-badge text-success">$${parseFloat(course.price).toFixed(2)}</span>`
              : '<span class="text-muted">Free</span>';

            const row = `
              <tr>
                <td>${course.id}</td>
                <td>${escapeHtml(course.title)}</td>
                <td class="course-description" title="${escapeHtml(course.description)}">${escapeHtml(course.description)}</td>
                <td>${licenseBadge}</td>
                <td>${priceDisplay}</td>
                <td>${course.duration}h</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-warning btn-action btn-edit" 
                            data-id="${course.id}"
                            data-title="${escapeHtml(course.title)}"
                            data-description="${escapeHtml(course.description)}"
                            data-license_type="${course.license_type}"
                            data-price="${course.price}"
                            data-duration="${course.duration}"
                            data-status="${course.status}"
                            title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-action btn-delete" 
                            data-id="${course.id}"
                            data-title="${escapeHtml(course.title)}"
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
            const title = $(this).data('title');
            const description = $(this).data('description');
            const license_type = $(this).data('license_type');
            const price = $(this).data('price');
            const duration = $(this).data('duration');
            const status = $(this).data('status');
            openEditModal(id, title, description, license_type, price, duration, status);
          });

          $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            deleteCourse(id, title);
          });
        }

        function updateStatistics(courses) {
          const totalCourses = courses.length;
          const freeCount = courses.filter(c => c.license_type === 'free').length;
          const paidCount = courses.filter(c => c.license_type === 'paid').length;
          const activeCount = courses.filter(c => c.status === 'active').length;

          $('#total-courses').text(totalCourses);
          $('#free-count').text(freeCount);
          $('#paid-count').text(paidCount);
          $('#active-count').text(activeCount);
        }

        function loadPaymentData() {
          // Load payment statistics
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'purchase_stats' },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                updatePaymentStatistics(response.data);
              }
            }
          });

          // Load course purchase statistics
          loadPurchaseStatistics();

          // Load payment history
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'user_purchases', user_id: 'all' },
            dataType: 'json',
            beforeSend: function() {
              $('#payments-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="8" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading payment history...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              if (response.success) {
                displayPayments(response.data);
              } else {
                showError('Failed to load payment history: ' + (response.message || 'Unknown error'));
              }
            },
            error: function(xhr, status, error) {
              showError('Error loading payment history: ' + error);
            }
          });
        }

        function loadPurchaseStatistics() {
          // Load user purchases to calculate course purchase counts
          $.ajax({
            url: controllerUrl,
            type: 'GET',
            data: { action: 'user_purchases', user_id: 'all' },
            dataType: 'json',
            beforeSend: function() {
              $('#purchase-stats-tbody').html(`
                <tr class="loading-spinner">
                  <td colspan="7" class="text-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Loading purchase statistics...
                  </td>
                </tr>
              `);
            },
            success: function(response) {
              if (response.success) {
                displayPurchaseStatistics(response.data);
              }
            }
          });
        }

        function displayPurchaseStatistics(purchases) {
          const tbody = $('#purchase-stats-tbody');
          tbody.empty();

          if (purchases.length === 0) {
            tbody.html(`
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="empty-state">
                    <i class="fas fa-chart-bar fa-3x mb-3 text-muted"></i>
                    <p>No purchase data available</p>
                  </div>
                </td>
              </tr>
            `);
            return;
          }

          // Group purchases by course
          const courseStats = {};
          let totalPurchases = 0;

          purchases.forEach(purchase => {
            const courseId = purchase.course_id;
            if (!courseStats[courseId]) {
              courseStats[courseId] = {
                course_title: purchase.course_title,
                license_type: purchase.license_type || 'paid',
                price: parseFloat(purchase.amount),
                status: 'active',
                purchase_count: 0,
                total_revenue: 0
              };
            }
            courseStats[courseId].purchase_count++;
            courseStats[courseId].total_revenue += parseFloat(purchase.amount);
            totalPurchases++;
          });

          // Display course statistics
          Object.keys(courseStats).forEach(courseId => {
            const stats = courseStats[courseId];
            const licenseBadge = stats.license_type === 'paid'
              ? '<span class="badge badge-primary">Paid</span>'
              : '<span class="badge badge-info">Free</span>';

            const statusBadge = '<span class="badge badge-success">Active</span>';

            const priceDisplay = stats.license_type === 'paid' 
              ? `$${parseFloat(stats.price).toFixed(2)}`
              : 'Free';

            const revenueDisplay = stats.license_type === 'paid' 
              ? `<span class="text-success fw-bold">$${parseFloat(stats.total_revenue).toFixed(2)}</span>`
              : '<span class="text-muted">-</span>';

            const countDisplay = stats.license_type === 'paid'
              ? `<span class="purchase-count">${stats.purchase_count} purchases</span>`
              : `<span class="enrollment-count">${stats.purchase_count} enrollments</span>`;

            const row = `
              <tr>
                <td>${courseId}</td>
                <td>${escapeHtml(stats.course_title)}</td>
                <td>${licenseBadge}</td>
                <td>${priceDisplay}</td>
                <td>${statusBadge}</td>
                <td class="text-center">${countDisplay}</td>
                <td class="text-center">${revenueDisplay}</td>
              </tr>
            `;
            tbody.append(row);
          });
        }

        function updatePaymentStatistics(stats) {
          $('#total-revenue').text('$' + parseFloat(stats.total_revenue).toFixed(2));
          $('#total-purchases').text(stats.total_purchases);
          $('#unique-customers').text(stats.unique_customers);
          $('#avg-purchase').text('$' + parseFloat(stats.average_purchase).toFixed(2));
        }

        function displayPayments(payments) {
          const tbody = $('#payments-tbody');
          tbody.empty();

          if (payments.length === 0) {
            tbody.html(`
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="empty-state">
                    <i class="fas fa-credit-card fa-3x mb-3 text-muted"></i>
                    <p>No payment records found</p>
                  </div>
                </td>
              </tr>
            `);
            return;
          }

          payments.forEach(payment => {
            const statusBadge = payment.status === 'completed' 
              ? '<span class="badge badge-success payment-status-badge">Completed</span>'
              : '<span class="badge badge-warning payment-status-badge">Pending</span>';

            const row = `
              <tr>
                <td><code>${payment.transaction_id}</code></td>
                <td>${escapeHtml(payment.course_title)}</td>
                <td>User #${payment.user_id}</td>
                <td class="text-success fw-bold">$${parseFloat(payment.amount).toFixed(2)}</td>
                <td>${payment.payment_method}</td>
                <td>${new Date(payment.purchase_date).toLocaleDateString()}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-info btn-action btn-view-payment" 
                            data-id="${payment.id}"
                            title="View Details">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning btn-action btn-refund" 
                            data-id="${payment.id}"
                            data-amount="${payment.amount}"
                            title="Process Refund">
                      <i class="fas fa-undo"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `;
            tbody.append(row);
          });

          // Attach event listeners to payment action buttons
          $('.btn-view-payment').on('click', function() {
            const paymentId = $(this).data('id');
            viewPaymentDetails(paymentId);
          });

          $('.btn-refund').on('click', function() {
            const paymentId = $(this).data('id');
            const amount = $(this).data('amount');
            processRefund(paymentId, amount);
          });
        }

        function viewPaymentDetails(paymentId) {
          // In a real application, this would fetch and display detailed payment information
          alert('Viewing payment details for ID: ' + paymentId + '\nThis would show detailed payment information in a real application.');
        }

        function processRefund(paymentId, amount) {
          if (!confirm(`Are you sure you want to process a refund of $${amount} for payment ID: ${paymentId}?`)) {
            return;
          }

          // In a real application, this would process the refund through your payment processor
          showSuccess('Refund processed successfully for payment ID: ' + paymentId);
        }

        function addCourse() {
          const formData = {
            action: 'add',
            title: $('#title').val().trim(),
            description: $('#description').val().trim(),
            license_type: $('#license_type').val(),
            price: $('#license_type').val() === 'paid' ? $('#price').val() : '0',
            duration: $('#duration').val()
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
            success: function(response) {
              if (response.success) {
                $('#addCourseModal').modal('hide');
                $('#add-course-form')[0].reset();
                $('#price-field').hide();
                showSuccess('Cybersecurity course added successfully!');
                loadCourses();
              } else {
                showError(response.message || 'Failed to add course');
              }
            },
            error: function(xhr, status, error) {
              showError('Error adding course: ' + error);
            }
          });
        }

        function openEditModal(id, title, description, license_type, price, duration, status) {
          $('#edit-id').val(id);
          $('#edit-title').val(title);
          $('#edit-description').val(description);
          $('#edit-license_type').val(license_type);
          $('#edit-price').val(price);
          $('#edit-duration').val(duration);
          $('#edit-status').val(status);

          // Show/hide price field based on license type
          if (license_type === 'paid') {
            $('#edit-price-field').show();
            $('#edit-price').prop('required', true);
          } else {
            $('#edit-price-field').hide();
            $('#edit-price').prop('required', false);
          }

          $('#editCourseModal').modal('show');
        }

        function updateCourse() {
          const formData = {
            action: 'update',
            id: $('#edit-id').val(),
            title: $('#edit-title').val().trim(),
            description: $('#edit-description').val().trim(),
            license_type: $('#edit-license_type').val(),
            price: $('#edit-license_type').val() === 'paid' ? $('#edit-price').val() : '0',
            duration: $('#edit-duration').val(),
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
            success: function(response) {
              if (response.success) {
                $('#editCourseModal').modal('hide');
                showSuccess('Cybersecurity course updated successfully!');
                loadCourses();
              } else {
                showError(response.message || 'Failed to update course');
              }
            },
            error: function(xhr, status, error) {
              showError('Error updating course: ' + error);
            }
          });
        }

        function deleteCourse(id, title) {
          if (!confirm(`Are you sure you want to delete the course "${title}"? This action cannot be undone.`)) {
            return;
          }

          $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showSuccess('Cybersecurity course deleted successfully!');
                loadCourses();
              } else {
                showError(response.message || 'Failed to delete course');
              }
            },
            error: function(xhr, status, error) {
              showError('Error deleting course: ' + error);
            }
          });
        }

        function validateAddForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();

          if (!data.title) {
            $('#title-error').text('Course title is required').show();
            isValid = false;
          }

          if (!data.description) {
            $('#description-error').text('Description is required').show();
            isValid = false;
          }

          if (!data.license_type) {
            $('#license-type-error').text('License type is required').show();
            isValid = false;
          }

          if (data.license_type === 'paid' && (!data.price || parseFloat(data.price) <= 0)) {
            $('#price-error').text('Please enter a valid price for paid courses').show();
            isValid = false;
          }

          if (!data.duration || parseInt(data.duration) < 1) {
            $('#duration-error').text('Please enter a valid duration').show();
            isValid = false;
          }

          return isValid;
        }

        function validateEditForm(data) {
          let isValid = true;
          $('.invalid-feedback').text('').hide();

          if (!data.title) {
            $('#edit-title-error').text('Course title is required').show();
            isValid = false;
          }

          if (!data.description) {
            $('#edit-description-error').text('Description is required').show();
            isValid = false;
          }

          if (!data.license_type) {
            $('#edit-license-type-error').text('License type is required').show();
            isValid = false;
          }

          if (data.license_type === 'paid' && (!data.price || parseFloat(data.price) <= 0)) {
            $('#edit-price-error').text('Please enter a valid price for paid courses').show();
            isValid = false;
          }

          if (!data.duration || parseInt(data.duration) < 1) {
            $('#edit-duration-error').text('Please enter a valid duration').show();
            isValid = false;
          }

          return isValid;
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
        $('#addCourseModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
          $('#add-course-form')[0].reset();
          $('#price-field').hide();
          $('#price').prop('required', false);
        });

        $('#editCourseModal').on('show.bs.modal', function() {
          $('.invalid-feedback').text('').hide();
        });
      });
    </script>
  </body>
</html>