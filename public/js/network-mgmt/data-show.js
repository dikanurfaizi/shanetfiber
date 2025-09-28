// Simple JavaScript for interactivity
document.addEventListener("DOMContentLoaded", function () {
    // Refresh button functionality
    document
        .getElementById("refresh-data-btn")
        .addEventListener("click", function () {
            this.innerHTML =
                '<i class="fas fa-circle-notch fa-spin"></i> Refreshing...';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                alert("Data refreshed successfully!");
            }, 1500);
        });

    // Summary cards click functionality
    document.querySelectorAll(".summary-card").forEach((card) => {
        card.addEventListener("click", function () {
            const status = this.getAttribute("data-status");
            alert(`Showing ONUs with status: ${status}`);
        });
    });

    // Simulate scan progress
    document
        .querySelector(".scan-button")
        .addEventListener("click", function (e) {
            e.preventDefault();
            const progressContainer = document.getElementById(
                "scan-progress-container"
            );
            const progressBar = document.getElementById("scan-progress-bar");
            const progressText = document.getElementById("scan-progress-text");
            const progressPercentage = document.getElementById(
                "scan-progress-percentage"
            );

            progressContainer.style.display = "block";

            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 5;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    progressText.textContent = "Scan completed";
                    setTimeout(() => {
                        progressContainer.style.display = "none";
                    }, 2000);
                }

                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute("aria-valuenow", progress);
                progressPercentage.textContent = `${Math.round(progress)}%`;

                if (progress < 30) {
                    progressText.textContent = "Discovering ONUs...";
                } else if (progress < 70) {
                    progressText.textContent = "Reading signal levels...";
                } else {
                    progressText.textContent = "Finalizing scan...";
                }
            }, 200);
        });
});
