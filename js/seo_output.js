document.addEventListener("DOMContentLoaded", function () {
    const userId = document.getElementById("user_id")?.value;
    if (!userId || userId === "0") return;

    const apiPath = "../api/seo_audit/seo_result_fetch.php";

    let allData = [];
    let currentPage = 1;
    let isFilterMode = false;
    const rowsPerPage = 15;

    // UI Elements
    const tableBody = document.getElementById("auditTableBody");
    const dateFilter = document.getElementById("filterDate");
    const btnAll = document.getElementById("btnViewAll");
    const btnFilters = document.getElementById("btnViewFilters");
    const btnExport = document.getElementById("btnExport");
    const paginationTarget = document.getElementById("paginationTarget");
    const pageInfo = document.getElementById("pageInfo");

    const avgSeoScoreEl = document.getElementById("avgSeoScore");
    const avgPerfScoreEl = document.getElementById("avgPerfScore");
    const avgAccessScoreEl = document.getElementById("avgAccessScore");
    const avgBestScoreEl = document.getElementById("avgBestScore");

    const today = new Date();
    const dateString = today.toISOString().split('T')[0];
    if (dateFilter) dateFilter.value = dateString;

    function setActive(activeBtn, inactiveBtn) {
        activeBtn.classList.add('fw-bold', 'text-white');
        activeBtn.style.background = "var(--bg-badge)";
        inactiveBtn.classList.remove('fw-bold', 'text-white');
        inactiveBtn.classList.add('text-muted');
        inactiveBtn.style.background = "transparent";
    }

    if (btnFilters && btnAll) {
        btnFilters.addEventListener("click", function () {
            isFilterMode = true;
            setActive(btnFilters, btnAll);
            if (dateFilter?.value) fetchResults(dateFilter.value);
        });

        btnAll.addEventListener("click", function () {
            isFilterMode = false;
            setActive(btnAll, btnFilters);
            fetchResults();
        });
    }

    if (dateFilter) {
        dateFilter.addEventListener("change", function () {
            if (isFilterMode) fetchResults(this.value);
        });
    }

    function fetchResults(filterDate = null) {
        let url = `${apiPath}?user_id=${userId}`;
        if (isFilterMode && filterDate) url += `&date=${filterDate}`;

        if (tableBody) {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">Loading backend database rows... <span class="spinner-border spinner-border-sm" role="status"></span></td></tr>`;
        }

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    allData = res.data;
                } else {
                    allData = [];
                }
                currentPage = 1;
                updateOverviewCards(allData);
                displayPage(1);
            })
            .catch(err => {
                console.error("Error fetching SEO audit data:", err);
                allData = [];
                updateOverviewCards([]);
                displayPage(1);
            });
    }

    function updateOverviewCards(data) {
        if (!data || data.length === 0) {
            if (avgSeoScoreEl) avgSeoScoreEl.innerText = "-- / 100";
            if (avgPerfScoreEl) avgPerfScoreEl.innerText = "-- / 100";
            if (avgAccessScoreEl) avgAccessScoreEl.innerText = "-- / 100";
            if (avgBestScoreEl) avgBestScoreEl.innerText = "-- / 100";
            return;
        }

        const total = data.length;
        const sumSeo = data.reduce((acc, r) => acc + (parseInt(r.seo_score) || 0), 0);
        const sumPerf = data.reduce((acc, r) => acc + (parseInt(r.performance_score) || 0), 0);
        const sumAccess = data.reduce((acc, r) => acc + (parseInt(r.accessibility_score) || 0), 0);
        const sumBest = data.reduce((acc, r) => acc + (parseInt(r.best_practices_score) || 0), 0);

        if (avgSeoScoreEl) avgSeoScoreEl.innerText = `${Math.round(sumSeo / total)} / 100`;
        if (avgPerfScoreEl) avgPerfScoreEl.innerText = `${Math.round(sumPerf / total)} / 100`;
        if (avgAccessScoreEl) avgAccessScoreEl.innerText = `${Math.round(sumAccess / total)} / 100`;
        if (avgBestScoreEl) avgBestScoreEl.innerText = `${Math.round(sumBest / total)} / 100`;
    }

    function getScoreClass(score) {
        const val = parseInt(score) || 0;
        if (val >= 90) return "score-green";
        if (val >= 70) return "score-yellow";
        return "score-red";
    }

    function displayPage(page) {
        currentPage = page;
        if (!tableBody) return;

        if (allData.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No SEO technical audits found for this selection.</td></tr>`;
            if (pageInfo) pageInfo.innerText = "Showing 0-0 of 0 audits";
            if (paginationTarget) paginationTarget.innerHTML = "";
            return;
        }

        const start = (page - 1) * rowsPerPage;
        const pageData = allData.slice(start, start + rowsPerPage);

        if (pageInfo) {
            pageInfo.innerText = `Showing ${start + 1}-${Math.min(start + rowsPerPage, allData.length)} of ${allData.length} audits`;
        }

        tableBody.innerHTML = pageData.map((row, idx) => {
            const rowIdx = start + idx;
            const seoClass = getScoreClass(row.seo_score);
            const perfClass = getScoreClass(row.performance_score);
            const accessClass = getScoreClass(row.accessibility_score);
            const bestClass = getScoreClass(row.best_practices_score);

            return `<tr>
                <td class="ps-4 text-muted small">${escapeHtml(row.get_time || '-')}</td>
                <td class="fw-bold"><a href="${escapeHtml(row.page_url || '#')}" target="_blank" class="text-decoration-none text-info">${escapeHtml(row.page_url || '-')}</a></td>
                <td class="text-center"><span class="score-circle ${seoClass}">${row.seo_score || 0}</span></td>
                <td class="text-center"><span class="score-circle ${perfClass}">${row.performance_score || 0}</span></td>
                <td class="text-center"><span class="score-circle ${accessClass}">${row.accessibility_score || 0}</span></td>
                <td class="text-center"><span class="score-circle ${bestClass}">${row.best_practices_score || 0}</span></td>
                <td class="text-center"><span class="badge bg-warning bg-opacity-20 text-warning px-2 py-1">${row.total_issues || 0} Issues (${row.critical_issues || 0} Critical)</span></td>
                <td class="pe-4 text-end">
                    <button class="btn btn-sm btn-outline-warning view-detail-btn" data-index="${rowIdx}">
                        <i class="bi bi-eye-fill me-1"></i> View Report
                    </button>
                </td>
            </tr>`;
        }).join('');

        renderPagination();

        // Attach click listeners to View Report buttons
        document.querySelectorAll(".view-detail-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                const index = parseInt(this.getAttribute("data-index"));
                showDetailModal(allData[index]);
            });
        });
    }

    function showDetailModal(row) {
        if (!row) return;

        const modalContent = document.getElementById("modalDetailContent");
        if (!modalContent) return;

        let issuesHtml = "";
        let details = row.detailed_results;
        if (typeof details === "string") {
            try { details = JSON.parse(details); } catch (e) { details = []; }
        }

        if (Array.isArray(details) && details.length > 0) {
            issuesHtml = details.map(item => {
                const impact = (item.impact || 'Medium').toLowerCase();
                let impactClass = "impact-medium";
                if (impact === "high" || impact === "critical") impactClass = "impact-high";
                if (impact === "low") impactClass = "impact-low";

                return `<div class="p-3 mb-3 rounded-3 border border-secondary border-opacity-20 bg-dark bg-opacity-50">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${escapeHtml(item.issue || 'Audit Issue')}</span>
                        <span class="impact-pill ${impactClass}">${escapeHtml(item.impact || 'Medium')} Impact</span>
                    </div>
                    <p class="small text-muted mb-2">Category: <span class="text-white font-monospace">${escapeHtml(item.category || 'General')}</span></p>
                    <div class="p-2 rounded bg-secondary bg-opacity-10 small text-light">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i> <strong>Recommendation:</strong> ${escapeHtml(item.recommendation || 'Follow SEO best practices.')}
                    </div>
                </div>`;
            }).join('');
        } else {
            issuesHtml = `<div class="p-3 rounded-3 bg-secondary bg-opacity-10 text-muted text-center">No individual breakdown issues listed for this audit.</div>`;
        }

        modalContent.innerHTML = `
            <div class="mb-4 pb-3 border-bottom border-secondary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning text-dark text-uppercase font-monospace">SEO Technical Report</span>
                    <span class="text-muted small">${escapeHtml(row.get_time || '')}</span>
                </div>
                <h5 class="fw-bold text-white mb-2"><a href="${escapeHtml(row.page_url || '#')}" target="_blank" class="text-info text-decoration-none">${escapeHtml(row.page_url || '')}</a></h5>
                <p class="small text-secondary mb-0">${escapeHtml(row.audit_summary || 'Comprehensive technical audit complete.')}</p>
            </div>

            <div class="row g-3 mb-4 text-center">
                <div class="col-3">
                    <div class="p-2 rounded border border-secondary bg-dark">
                        <small class="text-muted d-block font-monospace">SEO</small>
                        <span class="fw-bold text-success fs-5">${row.seo_score || 0}/100</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded border border-secondary bg-dark">
                        <small class="text-muted d-block font-monospace">Performance</small>
                        <span class="fw-bold text-info fs-5">${row.performance_score || 0}/100</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded border border-secondary bg-dark">
                        <small class="text-muted d-block font-monospace">Accessibility</small>
                        <span class="fw-bold text-warning fs-5">${row.accessibility_score || 0}/100</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded border border-secondary bg-dark">
                        <small class="text-muted d-block font-monospace">Best Practices</small>
                        <span class="fw-bold text-primary fs-5">${row.best_practices_score || 0}/100</span>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-white mb-3"><i class="bi bi-list-check text-warning me-2"></i> Detected Issues & Action Items (${row.total_issues || 0})</h6>
            ${issuesHtml}
        `;

        const modal = new bootstrap.Modal(document.getElementById("auditDetailModal"));
        modal.show();
    }

    function renderPagination() {
        if (!paginationTarget) return;
        const totalPages = Math.ceil(allData.length / rowsPerPage);
        if (allData.length === 0 || totalPages <= 1) { paginationTarget.innerHTML = ""; return; }

        let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}"><i class="bi bi-chevron-left"></i></a></li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted px-1">•••</span></li>`;
            }
        }
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}"><i class="bi bi-chevron-right"></i></a></li>`;
        paginationTarget.innerHTML = html;

        paginationTarget.querySelectorAll(".page-link").forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                const p = parseInt(this.getAttribute("data-page"));
                if (p && p >= 1 && p <= totalPages) displayPage(p);
            });
        });
    }

    if (btnExport) {
        btnExport.addEventListener("click", function () {
            if (allData.length === 0) return alert("No SEO audit data available to export.");

            const dateStr = new Date().toISOString().split('T')[0];
            const headers = ["Timestamp", "Target URL", "SEO Score", "Performance", "Accessibility", "Best Practices", "Total Issues", "Critical Issues"];

            const csvRows = allData.map(r => [
                r.get_time, r.page_url, r.seo_score, r.performance_score,
                r.accessibility_score, r.best_practices_score, r.total_issues, r.critical_issues
            ].map(val => `"${String(val || '').replace(/"/g, '""')}"`).join(","));

            const customMessage = `\n"Thank You To Be A Beliver Of Action Ai 😊"`;
            const csvString = [headers.join(","), ...csvRows].join("\n") + customMessage;
            const blob = new Blob(['\uFEFF', csvString], { type: 'text/csv;charset=utf-8;' });

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `seo_audit_report_${dateStr}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        const txt = document.createElement("textarea");
        txt.innerHTML = String(str);
        const decoded = txt.value;
        return decoded
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    fetchResults();
});