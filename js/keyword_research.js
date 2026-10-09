document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("keywordEngineForm");
    const submitBtn = document.getElementById("submitBtn");
    
    // Grab the user ID right away so we can fetch their previous data
    const userId = document.getElementById("user_id")?.value || 0;

    if (!form) return;

    // ==================================================
    // NEW: FETCH AND PRE-FILL PREVIOUS DATA ON PAGE LOAD
    // ==================================================
    if (userId != 0) {
        try {
            const fetchUrl = `../api/keyword/keyword_input_get_config.php?user_id=${userId}`;
            
            const response = await fetch(fetchUrl);
            const result = await response.json();

            if (response.ok && result.success && result.data) {
                // Populate the text fields
                if (document.getElementById("project_topic")) document.getElementById("project_topic").value = result.data.topic || "";
                if (document.getElementById("seed_keywords")) document.getElementById("seed_keywords").value = result.data.seeds || "";
                
                // Populate the dropdowns
                if (document.getElementById("language")) document.getElementById("language").value = result.data.language_code || "EN";
                if (document.getElementById("location")) document.getElementById("location").value = result.data.location || "Asia/Kolkata";
                
                // Populate Automation Settings (Frequency removed)
                if (result.data.run_time) {
                    if (document.getElementById("executionSwitch")) document.getElementById("executionSwitch").checked = true;
                    if (document.getElementById("executionTime")) document.getElementById("executionTime").value = result.data.run_time;
                } else {
                    if (document.getElementById("executionSwitch")) document.getElementById("executionSwitch").checked = false;
                }
            }
        } 
        catch (error) {
            console.warn("Could not fetch previous config.");
        }
    }

    // ==================================================
    // EXISTING SUBMIT FORM LOGIC
    // ==================================================
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const projectTopic = document.getElementById("project_topic")?.value.trim() || "";
        const seedKeywords = document.getElementById("seed_keywords")?.value.trim() || "";
        const languageCode = document.getElementById("language")?.value || "";
        const location = document.getElementById("location")?.value || ""; 
        const executionSwitch = document.getElementById("executionSwitch")?.checked || false;
        const executionTime = document.getElementById("executionTime")?.value || "10:00";
        // Frequency completely removed

        if (!projectTopic || !seedKeywords) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill out Project Topic and Seed Keywords.',
                confirmButtonColor: '#ff007f' 
            });
            return;
        }

        if (userId == 0) {
            Swal.fire({
                icon: 'error',
                title: 'Session Error',
                text: 'User ID not found. Please log in again.',
                confirmButtonColor: '#ff007f'
            });
            return;
        }

        // Save original button state and lock it
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;

        Swal.fire({
            title: 'Saving Configuration...',
            html: 'Saving your keyword research settings.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // This ternary operator exactly handles the null requirement:
        // If executionSwitch is true -> send executionTime. If false -> send null.
        const apiPayload = {
            user_id: parseInt(userId),
            topic: projectTopic,
            seeds: seedKeywords,
            location: location,
            language_code: languageCode,
            run_time: executionSwitch ? executionTime : null 
        };

        try {
            const saveResponse = await fetch('../api/keyword/keyword_input_add_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(apiPayload)
            });

            const saveResult = await saveResponse.json();

            if (!saveResponse.ok) {
                throw new Error(saveResult.error || "Failed to save configuration.");
            }

            // Save to Session Storage (Frequency removed)
            sessionStorage.setItem("keyword_inputId", saveResult.input_id); 
            sessionStorage.setItem("keyword_projectTopic", projectTopic);
            sessionStorage.setItem("keyword_seedKeywords", seedKeywords);
            sessionStorage.setItem("keyword_language", languageCode);
            sessionStorage.setItem("keyword_location", location); 
            sessionStorage.setItem("keyword_executionTime", executionTime);
            sessionStorage.setItem("keyword_automationEnabled", executionSwitch);

            // Show Success Alert with link to view output
            Swal.fire({
                icon: 'success',
                title: 'Saved & Scheduled!',
                text: 'Your keyword research configuration has been saved. The cron API will execute the workflow at your scheduled time.',
                showCancelButton: true,
                confirmButtonText: 'View Keyword Output',
                cancelButtonText: 'Stay Here',
                confirmButtonColor: '#3b82f6'
            }).then((result) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (result.isConfirmed) {
                    window.location.href = 'keyword_output.php';
                }
            });

        } catch (error) {
            console.error("API Error:", error);
            
            Swal.fire({
                icon: 'error',
                title: 'Engine Error',
                text: error.message,
                confirmButtonColor: '#ff007f'
            });
            
            // Unlock the button on error as well
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});