document.addEventListener("DOMContentLoaded", async () => {
    const runAuditBtn = document.getElementById("runAuditBtn");
    const websiteInput = document.getElementById("websiteUrl");
    const emailInput = document.getElementById("emailAddress");
    const executionTimeInput = document.getElementById("executionTime");
    const executionSwitch = document.getElementById("executionSwitch");
    const auditForm = document.getElementById("auditForm");

    const getUserId = () => {
        return (document.getElementById("userId")?.value || document.getElementById("user_id")?.value || "0").trim();
    };

    const userIdValue = getUserId();

    // Toggle execution time input based on switch
    if (executionSwitch && executionTimeInput) {
        executionSwitch.addEventListener("change", () => {
            executionTimeInput.disabled = !executionSwitch.checked;
        });
    }

    // ==================================================
    // 1. FETCH AND PRE-FILL PREVIOUS DATA ON PAGE LOAD
    // ==================================================
    if (userIdValue !== "0" && userIdValue !== "") {
        try {
            const fetchUrl = `../api/seo_audit/create_audit.php?user_id=${userIdValue}`;
            const response = await fetch(fetchUrl);
            const result = await response.json();

            if (response.ok && result.success && result.data) {
                if (websiteInput && result.data.page_url) {
                    websiteInput.value = result.data.page_url;
                }
                if (emailInput && result.data.email) {
                    emailInput.value = result.data.email;
                }
                if (executionTimeInput) {
                    if (result.data.run_time) {
                        executionTimeInput.value = result.data.run_time;
                        if (executionSwitch) executionSwitch.checked = true;
                        executionTimeInput.disabled = false;
                    } else if (result.data.run_time === null) {
                        if (executionSwitch) executionSwitch.checked = false;
                        executionTimeInput.disabled = true;
                    }
                }
            }
        } catch (error) {
            console.warn("Could not fetch previous SEO audit config:", error);
        }
    }

    if (!runAuditBtn) return;

    // ==================================================
    // 2. PROGRESS MODAL ANIMATION & STATUS POLLING & REDIRECT
    // ==================================================
    let progressInterval = null;
    let pollInterval = null;

    const startProgressAnimation = (triggerId) => {
        const modalEl = document.getElementById("progressModal");
        if (!modalEl) return;

        const progressModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        progressModal.show();

        const progressBar = document.getElementById("auditProgressBar");
        const statusText = document.getElementById("progressStatusText");
        const btnGoToResult = document.getElementById("btnGoToResult");

        const step1 = document.getElementById("step1");
        const step2 = document.getElementById("step2");
        const step3 = document.getElementById("step3");
        const step4 = document.getElementById("step4");

        let currentProgress = 15;
        if (progressBar) progressBar.style.width = `${currentProgress}%`;
        if (btnGoToResult) btnGoToResult.classList.add("d-none");

        // Helper to mark a step as active/done
        const setStepState = (stepEl, state) => {
            if (!stepEl) return;
            if (state === 'active') {
                stepEl.className = "d-flex align-items-center mb-3 text-warning fw-bold";
                stepEl.querySelector("i").className = "bi bi-arrow-repeat spin-icon me-2 fs-5";
            } else if (state === 'done') {
                stepEl.className = "d-flex align-items-center mb-3 text-success";
                stepEl.querySelector("i").className = "bi bi-check-circle-fill me-2 fs-5";
            }
        };

        // Smooth visual progress bar advance
        progressInterval = setInterval(() => {
            if (currentProgress < 90) {
                currentProgress += 5;
                if (progressBar) progressBar.style.width = `${currentProgress}%`;

                if (currentProgress >= 25 && currentProgress < 55) {
                    setStepState(step1, 'done');
                    setStepState(step2, 'active');
                } else if (currentProgress >= 55 && currentProgress < 85) {
                    setStepState(step2, 'done');
                    setStepState(step3, 'active');
                } else if (currentProgress >= 85) {
                    setStepState(step3, 'done');
                    setStepState(step4, 'active');
                }
            }
        }, 600);

        let isCompleted = false;

        // Completion function with automatic redirection
        const completeProgress = () => {
            if (isCompleted) return;
            isCompleted = true;
            clearInterval(progressInterval);
            clearInterval(pollInterval);

            currentProgress = 100;
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                progressBar.classList.add('bg-success');
            }

            setStepState(step1, 'done');
            setStepState(step2, 'done');
            setStepState(step3, 'done');
            setStepState(step4, 'done');

            if (statusText) {
                statusText.className = "text-success fw-bold small";
                statusText.innerHTML = `<i class="bi bi-check-all me-1"></i> SEO Audit Completed Successfully! Redirecting...`;
            }

            if (btnGoToResult) {
                btnGoToResult.classList.remove("d-none");
            }

            // Redirect automatically after completion
            setTimeout(() => {
                window.location.href = "seo_output.php";
            }, 1500);
        };

        // Poll API status every 2 seconds
        pollInterval = setInterval(async () => {
            if (!triggerId) return;
            try {
                const res = await fetch(`../api/seo_audit/check_status.php?trigger_id=${triggerId}`);
                const data = await res.json();
                if (data.success && data.is_done) {
                    completeProgress();
                }
            } catch (e) {
                console.warn("Status poll check error:", e);
            }
        }, 2000);

        // Safety fallback timer: auto-complete after 12 seconds if webhook delayed
        setTimeout(() => {
            completeProgress();
        }, 12000);
    };

    // ==================================================
    // 3. SUBMIT FORM & TRIGGER N8N FLOW: seo audit_1
    // ==================================================
    const handleFormSubmit = async (e) => {
        if (e) e.preventDefault();

        const websiteValue = websiteInput?.value.trim();
        const emailValue = emailInput?.value.trim();
        const currentUserId = getUserId();
        const runTimeValue = (executionSwitch && !executionSwitch.checked) ? null : (executionTimeInput?.value.trim() || "09:00");

        if (!websiteValue || !emailValue) {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in both Website URL and Email Address.'
                });
            } else {
                alert("Please fill in both Website URL and Email Address.");
            }
            return;
        }

        if (!currentUserId || currentUserId === "0") {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: 'error',
                    title: 'Session Error',
                    text: 'User ID not found. Please log in again.'
                });
            } else {
                alert("Session expired. Please log in again.");
            }
            return;
        }

        const originalBtnText = runAuditBtn.innerHTML;
        runAuditBtn.disabled = true;
        runAuditBtn.innerHTML = `Running Audit... <span class="spinner-border spinner-border-sm ms-2" role="status"></span>`;

        try {
            const response = await fetch("../api/seo_audit/create_audit.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ 
                    user_id: parseInt(currentUserId, 10),
                    page_url: websiteValue,
                    email: emailValue,
                    run_time: runTimeValue
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.error || result.message || "Failed to initialize SEO audit.");
            }

            sessionStorage.setItem("seo_result", JSON.stringify(result));
            sessionStorage.setItem("seo_websiteUrl", websiteValue);
            sessionStorage.setItem("seo_email", emailValue);

            runAuditBtn.disabled = false;
            runAuditBtn.innerHTML = originalBtnText;

            // Trigger the Progress Animation Modal
            startProgressAnimation(result.trigger_id);

        } catch (error) {
            console.error("SEO Audit Error:", error);
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: 'error',
                    title: 'Audit Failed',
                    text: error.message
                });
            } else {
                alert("SEO Audit failed: " + error.message);
            }
            runAuditBtn.disabled = false;
            runAuditBtn.innerHTML = originalBtnText;
        }
    };

    if (auditForm) {
        auditForm.addEventListener("submit", handleFormSubmit);
    }
    
    runAuditBtn.addEventListener("click", (e) => {
        if (auditForm) return;
        handleFormSubmit(e);
    });
});