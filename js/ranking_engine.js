document.addEventListener("DOMContentLoaded", async () => {
    const userIdInput = document.getElementById("user_id");
    if (!userIdInput) return;
    const userId = userIdInput.value.trim();

    const form = document.getElementById("rankingEngineForm");
    const businessNameInput = document.getElementById("business_name");
    const locationSelect = document.getElementById("user_location");
    const prioritySlider = document.getElementById("priority_min");
    const levelDisplay = document.getElementById("levelDisplay");
    const runTimeInput = document.getElementById("run_time");
    const executionSwitch = document.getElementById("ExecutionSwitch");
    const submitBtn = document.getElementById("submitBtn");

    const apiPath = "../api/business_ranking/business_input_config_get.php";

    // ==========================================
    // 1. FETCH AND PRE-FILL PREVIOUS DATA
    // ==========================================
    if (userId !== "0") {
        try {
            const response = await fetch(`${apiPath}?user_id=${userId}`);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                const saved = result.data[0];
                if (businessNameInput) businessNameInput.value = saved.business_name || "";
                if (locationSelect) locationSelect.value = saved.user_location || "";
                if (prioritySlider) {
                    prioritySlider.value = saved.priority_min || 3;
                    levelDisplay.textContent = "Level " + prioritySlider.value;
                }
                if (executionSwitch && runTimeInput) {
                    const isEnabled = (saved.run_time !== null && saved.run_time !== "");
                    executionSwitch.checked = isEnabled;
                    runTimeInput.value = saved.run_time || "";
                    runTimeInput.disabled = !isEnabled;
                }
            }
        } catch (error) {
            console.warn("Could not fetch previous config:", error);
        }
    }

    // Slider UI sync
    if (prioritySlider && levelDisplay) {
        prioritySlider.addEventListener("input", (e) => {
            levelDisplay.textContent = "Level " + e.target.value;
        });
    }

    // Toggle logic
    if (executionSwitch && runTimeInput) {
        executionSwitch.addEventListener("change", (e) => {
            runTimeInput.disabled = !e.target.checked;
            runTimeInput.required = e.target.checked;
        });
    }

    // ==========================================
    // 2. SUBMIT FORM LOGIC
    // ==========================================
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const payload = {
            user_id: parseInt(userId),
            business_name: businessNameInput.value.trim(),
            user_location: locationSelect.value,
            priority_min: parseInt(prioritySlider.value),
            run_time: executionSwitch.checked ? runTimeInput.value : null
        };

        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;

        // Show loading indicator
        Swal.fire({
            title: 'Executing Engine...',
            html: 'Initializing your business ranking audit.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(apiPath, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const res = await response.json();

            if (!response.ok) throw new Error(res.error || "Failed to save configuration.");

            // Success Notification (Matches keyword_research.js style)
            Swal.fire({
                icon: 'success',
                title: 'Successfully Executed!',
                text: 'Your Ranking configuration is saved.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });

        } catch (error) {
            console.error("API Error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Engine Error',
                text: error.message
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});