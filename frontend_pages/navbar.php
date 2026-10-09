<nav class="navbar navbar-expand-lg border-bottom px-4 py-2 sticky-top">
    <div class="container-fluid p-0">

        <!-- LEFT -->
        <div class="d-flex align-items-center">
            <button class="btn btn-light border-secondary-subtle d-flex align-items-center justify-content-center me-3" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <span class="navbar-text fw-semibold d-block">
                    SEO & Ranking Suite
                </span>
                <small class="text-secondary">
                    SEO Audit, Keyword Research & Business Ranking
                </small>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="d-flex align-items-center gap-3">
            <!-- THEME -->
            <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Theme">
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
            </button>

            <!-- PROFILE -->
            <div class="dropdown">
                <button class="btn profile-dropdown-btn" data-bs-toggle="dropdown">
                    <div class="avatar-circle">
                        <?= $initials ?>
                    </div>
                    <span class="d-none d-md-inline fw-semibold">
                        <?= htmlspecialchars($name) ?>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu">
                    <li class="px-3 py-2 border-bottom">
                        <strong><?= htmlspecialchars($name) ?></strong>
                        <br>
                        <small class="text-secondary"><?= htmlspecialchars($email) ?></small>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</nav>