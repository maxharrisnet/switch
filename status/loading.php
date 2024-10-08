  <div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var loadingOverlay = document.getElementById("loadingOverlay");

      // Show loading overlay
      loadingOverlay.style.display = "flex";

      // Simulate data fetching
      setTimeout(function() {
        // Hide loading overlay after data is loaded
        loadingOverlay.style.display = "none";
      }, 3000); // Adjust the timeout duration as needed
    });
  </script>