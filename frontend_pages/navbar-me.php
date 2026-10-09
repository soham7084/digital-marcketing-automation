<nav class="navbar navbar-expand-lg navbar-dark py-3">
    <div class="container-fluid px-4">

        <button id="sidebarToggle" class="btn btn-link text-dark-emphasis p-0 me-3" type="button"
            style="font-size: 1.5rem; text-decoration: none;">
            <i class="bi bi-list"></i>
        </button>

        <h4 class="navbar-brand fw-bold logo m-0 me-auto" id="page_name">
            SEO & Ranking Suite
        </h4>

        <div class="d-flex align-items-center gap-3">

            <button id="theme-toggle" class="btn btn-link nav-link p-2" aria-label="Toggle theme" type="button"
                style="font-size: 1.2rem; cursor: pointer;">
                <i class="bi bi-moon-stars dark-icon"></i>
                <i class="bi bi-sun light-icon"></i>
            </button>

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