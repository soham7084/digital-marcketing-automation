<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("header_link.php"); ?>
    <style>
    #filterDate::-webkit-calendar-picker-indicator {
        position: absolute;
        right: 10px;
        cursor: pointer;
        opacity: 0;
        width: 100%;
    }
    #filterDate {
        flex: 1;
        min-width: 120px;
    }
    .score-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    .score-green { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1.5px solid #22c55e; }
    .score-yellow { background: rgba(234, 179, 8, 0.15); color: #eab308; border: 1.5px solid #eab308; }
    .score-red { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1.5px solid #ef4444; }
    .impact-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
    }
    .impact-high { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .impact-medium { background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
    .impact-low { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    </style>
</head>

<body data-section="seo">
    <div class="dashboard-wrapper">
        <?php include("sidebar-me.php"); ?>
        
        <main class="main-content">
            <?php include("navbar-me.php"); ?>

            <div class="container-fluid px-4 py-4">
                <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="report-container p-4 rounded-4 border border-secondary border-opacity-10 shadow-lg">
                    
                    <!-- HEADER CONTROL ROW -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="report-main-title fw-bold m-0" style="font-size: 22px;">SEO Technical Audit Results</h2>

                            <div class="btn-group rounded-3 overflow-hidden border border-secondary border-opacity-20"
                                style="padding: 3px; background: var(--bg-main);">
                                <button id="btnViewAll" class="btn btn-sm text-white px-3 py-1 fw-bold active"
                                    style="background: var(--bg-badge); font-size: 13px; border: none; border-radius: 6px;">All</button>
                                <button id="btnViewFilters" class="btn btn-sm text-muted px-3 py-1 fw-medium"
                                    style="font-size: 13px; border: none; background: transparent;">Filters</button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div id="dateFilterWrapper" class="d-flex align-items-center px-3"
                                style="border-radius: 8px; border: 2px solid #8e959b; background-color: var(--bg-input); height: 42px; gap: 8px;">
                                <i class="bi bi-calendar-event-fill" style="color: var(--text-placeholder); font-size: 16px;"></i>
                                <input type="date" id="filterDate" class="form-control border-0 fw-bold shadow-none bg-transparent"
                                    style="font-size: 14px; cursor: pointer; color: var(--text-primary) !important; background: transparent !important; height: 100%; border: none !important; width: 140px;">
                            </div>

                            <button id="btnExport" class="btn btn-rerun d-flex align-items-center gap-2 rounded-3 py-2 px-3 fw-bold m-0 shadow-sm"
                                type="button" style="font-size: 13px; height: 42px;">
                                <i class="bi bi-download text-primary"></i> Export CSV
                            </button>
                        </div>
                    </div>

                    <!-- METRICS CARDS ROW -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm text-center">
                                <span class="text-muted small d-block mb-2 text-uppercase font-monospace">SEO Score</span>
                                <h3 id="avgSeoScore" class="fw-bold text-success mb-0">-- / 100</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm text-center">
                                <span class="text-muted small d-block mb-2 text-uppercase font-monospace">Performance</span>
                                <h3 id="avgPerfScore" class="fw-bold text-info mb-0">-- / 100</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm text-center">
                                <span class="text-muted small d-block mb-2 text-uppercase font-monospace">Accessibility</span>
                                <h3 id="avgAccessScore" class="fw-bold text-warning mb-0">-- / 100</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm text-center">
                                <span class="text-muted small d-block mb-2 text-uppercase font-monospace">Best Practices</span>
                                <h3 id="avgBestScore" class="fw-bold text-primary mb-0">-- / 100</h3>
                            </div>
                        </div>
                    </div>

                    <!-- AUDIT RESULTS TABLE -->
                    <div class="table-responsive border border-secondary border-opacity-10 rounded-3 overflow-hidden">
                        <table class="table table-hover align-middle custom-report-table m-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3 ps-4">Timestamp</th>
                                    <th scope="col" class="py-3">Target URL</th>
                                    <th scope="col" class="py-3 text-center">SEO Score</th>
                                    <th scope="col" class="py-3 text-center">Performance</th>
                                    <th scope="col" class="py-3 text-center">Accessibility</th>
                                    <th scope="col" class="py-3 text-center">Best Practices</th>
                                    <th scope="col" class="py-3 text-center">Issues</th>
                                    <th scope="col" class="py-3 pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="auditTableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Loading backend database rows... <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION FOOTER -->
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-4 gap-3">
                        <span id="pageInfo" class="text-muted small" style="font-size: 13px;">Showing 0-0 of 0 audits</span>
                        <nav aria-label="SEO Audit pagination navigator">
                            <ul id="paginationTarget" class="pagination pagination-custom m-0 gap-1 align-items-center"></ul>
                        </nav>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- AUDIT DETAIL MODAL -->
    <div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="auditDetailModalLabel"><i class="bi bi-clipboard-data text-warning me-2"></i> Audit Breakdown & Recommendations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetailContent">
                    <!-- Dynamic modal content inserted via JS -->
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer_link.php"); ?>
    <script src="../js/seo_output.js?v=<?php echo time(); ?>"></script>
</body>

</html>
