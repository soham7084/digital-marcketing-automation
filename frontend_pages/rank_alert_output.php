<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("header_link.php"); ?>
</head>

<body data-section="ranking">

    <div class="dashboard-wrapper">

        <?php include("sidebar-me.php"); ?>

        <main class="main-content">

            <?php include("navbar-me.php"); ?>

            <div class="container-fluid px-4 py-4">
                <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="report-container p-4 rounded-4 border border-secondary border-opacity-10 shadow-lg">

                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="report-main-title fw-bold m-0" style="font-size: 22px;">Keyword Research</h2>

                            <div class="btn-group rounded-3 overflow-hidden border border-secondary border-opacity-20"
                                style="padding: 3px; background: var(--bg-main);">
                                <button id="btnToggleAll" class="btn btn-sm text-white px-3 py-1 fw-bold"
                                    style="background: var(--bg-badge); font-size: 13px; border: none; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.3s ease;">All</button>
                                <button id="btnToggleFilters" class="btn btn-sm text-muted px-3 py-1 fw-medium"
                                    style="font-size: 13px; border: none; background: transparent; transition: all 0.3s ease;">Filters</button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">

                            <div class="input-group shadow-sm" id="dateFilterWrapper"
                                style="border-radius: 8px; border: 2px solid #8e959b; overflow: hidden; height: 42px; display: flex; align-items: center; background-color: var(--bg-input);">

                                <span class="input-group-text border-0 bg-transparent"
                                    style="padding: 0 12px; display: flex; align-items: center;">
                                    <i class="bi bi-calendar-event-fill" style="color: var(--text-placeholder);"></i>
                                </span>

                                <input type="date" id="filterDate"
                                    class="form-control border-0 fw-bold shadow-none bg-transparent"
                                    style="font-size: 14px; cursor: pointer; color: var(--text-primary) !important; background: transparent !important; flex-grow: 1; height: 100%; border: none !important; padding: 0 10px 0 0;">
                            </div>

                            <button id="exportCsvBtn"
                                class="btn btn-rerun d-flex align-items-center gap-2 rounded-3 py-2 px-3 fw-bold m-0 shadow-sm"
                                type="button" style="font-size: 13px; height: 42px;">
                                <i class="bi bi-download text-primary"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div
                                class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm">
                                <h5 class="text-muted small mb-3">Search Intent Distribution</h5>
                                <canvas id="intentChart" style="max-height: 200px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border border-secondary border-opacity-10 rounded-3 overflow-hidden">
                        <table class="table table-hover align-middle custom-report-table m-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-uppercase tracking-wider font-monospace py-3 ps-4">
                                        Keyword</th>
                                    <th scope="col" class="text-uppercase tracking-wider font-monospace py-3">Cluster
                                    </th>
                                    <th scope="col" class="text-uppercase tracking-wider font-monospace py-3">Search
                                        Intent</th>
                                    <th scope="col"
                                        class="text-uppercase tracking-wider font-monospace py-3 text-center">Priority
                                    </th>
                                    <th scope="col" class="text-uppercase tracking-wider font-monospace py-3 pe-4">Date
                                        Created</th>
                                </tr>
                            </thead>
                            <tbody id="keywordRowsTarget">
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Loading backend database rows...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-4 gap-3">
                        <span id="showingCountText" class="text-muted small" style="font-size: 13px;">Showing 0-0 of 0
                            keywords</span>

                        <nav aria-label="Keyword pagination list navigator">
                            <ul id="paginationTarget" class="pagination pagination-custom m-0 gap-1 align-items-center">
                                <li class="page-item"><a class="page-link" href="#"><i
                                            class="bi bi-chevron-left"></i></a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item disabled"><span
                                        class="page-link border-0 bg-transparent text-muted px-1">•••</span></li>
                                <li class="page-item"><a class="page-link" href="#">7</a></li>
                                <li class="page-item"><a class="page-link" href="#"><i
                                            class="bi bi-chevron-right"></i></a></li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <?php include("footer_link.php"); ?>
    <script src="../js/rank_alert_engine.js?v=<?php echo time(); ?>"></script>

</body>

</html>