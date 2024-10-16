<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Switch Service Status Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="status/status.css">
</head>

<body>
  <div class="bg-light">
    <div class="container-sm">
      <div class="row sticky-top  bg-light p-4 mb-3 border-medium border-bottom">
        <div>
          <img src="status/switch-logo.png" alt="Switch Logo" class="img-fluid media">
        </div>
      </div>
      <!-- Modem items start -->
      <?php

      require "status/status.php";

      $allServices = fetchAllServices($accessToken);
      if (is_array($allServices) && !empty($allServices)) {
        foreach ($allServices as $service) {
          if (!empty($service['modems'])) {
            foreach ($service['modems'] as $modem) {
              $sysId = $modem['id'];
              $detailsURL = getServiceURL(strtolower($modem['type']), $sysId);
              $modemDetails = fetchModemDetails($detailsURL, $accessToken);
              echo "<div class='row p-2'>";
              echo "<a href='http://localhost/switch/modem_status_details.php?provider=" . strtolower($modem['type']) . "&modemid={$modem['id']}' class='text-black text-decoration-none fw-bold'>";
              echo "<div class='card modem-card shadow-sm mb-0' onmouseover=\"this.style.backgroundColor='#f8f9fa';\" onmouseout=\"this.style.backgroundColor='';\">";
              echo "<div class='card-body'>";
              echo "<div class='d-flex justify-content-between align-items-center'>";
              echo "<div class='w-25'>";
              echo "<h3 class='card-title fs-6'>" . $modem['name'] . "</h3>";
              echo "<h4 class='card-subtitle h6 font-weight-bolder text-secondary'>" . $service['name'] . "</h4>";
              echo "</div>";

              // Check for latency data
              if (isset($modemDetails['data']['latency']['data']) && !empty($modemDetails['data']['latency']['data'])) {
                $latencyData = $modemDetails['data']['latency']['data'];

                // Container for the full 24-hour bar
                echo "<div class='latency-bar-24h d-flex rounded' style='width: 70%; height: 50px;'>";

                foreach ($latencyData as $latencyPoint) {
                  $latencyValue = $latencyPoint[1];
                  $latencyClass = getLatencyClass($latencyValue);

                  // Assuming latencyPoint[0] is the timestamp in seconds
                  // Calculate the percentage width of this segment (based on time)
                  // For example, if you're getting data points every 10 minutes, adjust the width accordingly
                  $segmentWidth = (10 / 1440) * 100; // 10 minutes out of 1440 minutes in 24 hours

                  // Now, create a small bar segment for each latency point
                  echo "<div class='latency-segment $latencyClass' style='width: $segmentWidth%;'></div>";
                }

                echo "</div>"; // Close 24h container
              } else {
                echo "<p class='mb-0'>No data available</p>";
              }
              echo "</div>"; // Close d-flex container
              echo "</div>";
              echo "</div>";
              echo "</a>";
              echo "</div>";
            }
          } else {
            echo "<p>No modems available for service: " . $service['name'] . "</p>";
          }
        }
      } else {
        echo "<div class='bg-light'>";
        echo "<div class='container-sm'>";
        echo "<div class='row text-center'>";
        echo "<p>No services available.</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
      }
      ?>
    </div>
  </div>
  <?php include('status/loading.php'); ?>
</body>

</html>