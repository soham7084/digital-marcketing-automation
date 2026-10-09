document.addEventListener("DOMContentLoaded", function () {
    const userIdInput = document.getElementById("user_id");
    if (!userIdInput) return;
    const userId = userIdInput.value.trim();

    // Ensure this path matches the exact directory on your server
    const apiPath = "../api/business_ranking_alert/rank_alert_input_config.php";

    const alertForm = document.getElementById("alertEngineForm");
    const executionSwitch = document.getElementById("alertExecutionSwitch");
    const runTimeInput = document.getElementById("runTimeInput");
    const submitBtn = document.getElementById("alertSubmitBtn");

    // ==========================================
    // 1. Toggle Logic
    // ==========================================
    if (executionSwitch && runTimeInput) {
        executionSwitch.addEventListener("change", function() {
            runTimeInput.disabled = !this.checked;
        });
    }

    // ==========================================
    // 2. Load Initial Data (GET)
    // ==========================================
    if (userId !== "0") {
        fetch(`${apiPath}?user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data && runTimeInput && executionSwitch) {
                    if (data.run_time) {
                        runTimeInput.value = data.run_time;
                        executionSwitch.checked = true;
                        runTimeInput.disabled = false;
                    } else {
                        executionSwitch.checked = false;
                        runTimeInput.disabled = true;
                    }
                }
            })
            .catch(err => console.error("Fetch error:", err));
    }

    // ==========================================
    // 3. Submit Form Logic (POST)
    // ==========================================
    if (alertForm) {
        alertForm.addEventListener("submit", function(e) {
            e.preventDefault(); // Prevents page reload
			
            Swal.fire({
                title: 'Saving Settings...',
                text: 'Please wait while we update your configuration.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const payload = {
                user_id: parseInt(userId),
                run_time: executionSwitch.checked ? runTimeInput.value : null
            };

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving...';

            fetch(apiPath, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Successfully Executed!', 
                        text: 'Your Alert settings are saved.', 
                        showConfirmButton: false, 
                        timer: 3000 
                    });
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Failed', 
                        text: res.error || "An unknown error occurred."
                    });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Connection Error', text: err.message });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-lightning-charge-fill me-2"></i> Save Alert Settings';
            });
        });
    }
});