<?php
require "status/status.php";

$provider = $_GET['provider'] ?? null;
$modemId = $_GET['modemid'] ?? null;
$modem = null;
$gps = null;

if (empty($modemId) || empty($provider)) {
  die("Both modemId and provider parameters are required.");
}

if (is_string($accessToken) && strpos($accessToken, 'Error') === 0) {
  echo $accessToken;
} else {

  $modemDetailsURL =  getServiceURL($provider, $modemId);
  $modem = fetchModemDetails($modemDetailsURL, $accessToken);

  if (is_string($modem) && strpos($modem, 'Error') === 0) {
    echo $modem;
    die();
  } elseif (empty($modem)) {
    die("No data available for modem $modemId");
  } else {

    // $gps = fetchGPS($provider, [$modemId], $accessToken);

    $latencyData = $modem['data']['latency']['data'] ?? [];
    $throughputData = $modem['data']['throughput']['data'] ?? [];
    $signalQualityData = $modem['data']['signal']['data'] ?? [];
    $obstructionData = $modem['data']['obstruction']['data'] ?? [];  // In case this data is available
    $usageData = $modem['usage'] ?? [];
    $uptimeData = $modem['data']['uptime']['data'] ?? [];

    // Extract timestamps and values for latency and throughput
    $latencyTimestamps = [];
    $latencyValues = [];

    if (is_array($latencyData) && !empty($latencyData)) {
      $latencyTimestamps = array_map(function ($entry) {
        return date('H:i', $entry[0]); // Time formatting for Chart.js labels
      }, $latencyData);
      $latencyValues = array_map(function ($entry) {
        return $entry[1];  // Latency values
      }, $latencyData);
    }

    // Signal Quality
    $signalTimestamps = [];
    $signalValues = [];

    if (is_array($signalQualityData) && !empty($signalQualityData)) {
      $signalTimestamps = array_filter(array_map(
        function ($entry) {
          // $hours = date('H', $entry[0]);
          // $minutes = date('i', $entry[0]);

          // if ($minutes == 0 && $hours % 2 == 0) {
          //   return date('H:i', $entry[0]);
          // }
          // return null;

          return filterTimestamps($entry[0], 2);
        },

        $signalQualityData
      ));

      $signalTimestamps = array_values($signalTimestamps);
      $signalValues = array_map(
        function ($entry) {
          return $entry[1];  // Signal quality values
        },
        $signalQualityData
      );
    }


    // Throughtput Data

    // Get the current time in Unix timestamp
    $currentTimestamp = time();

    // Calculate the timestamp for 24 hours ago
    $twentyFourHoursAgo = $currentTimestamp - 86400;

    // Arrays to hold filtered data for the last 24 hours
    $filteredDates = [];
    $filteredDownload = [];
    $filteredUpload = [];
    $labels = [];


    // Loop through the throughput data and filter for the last 24 hours
    // foreach ($throughputData as $entry) {
    //   $timestamp = $entry[0];

    //   // Only include data within the last 24 hours
    //   if (
    //     $timestamp >= $twentyFourHoursAgo && $timestamp <= $currentTimestamp
    //   ) {
    //     // Add all data points to the arrays
    //     $filteredDates[] = $timestamp;
    //     $filteredDownload[] = $entry[1];
    //     $filteredUpload[] = $entry[2];

    //     // Check if it's a multiple of 2 hours (7200 seconds)
    //     if ($timestamp % 7200 === 0) {
    //       $labels[] = date('H:i', $timestamp); // Label every 2 hours
    //     } else {
    //       $labels[] = ''; // Empty label for non-2-hour intervals
    //     }
    //   }
    // }

    // Prepare the data for Chart.js
    // $throughputTimestamps = json_encode($filteredDates);
    // $throughputDownload = json_encode($filteredDownload);
    // $throughputUpload = json_encode($filteredUpload);
    // $throughputLabels = json_encode($labels);

    if (is_array($throughputData) && !empty($throughputData)) {
      $throughputTimestamps = array_map(function ($entry) {
        return date('H:i', $entry[0]); // Timestamp (Unix)
        // return filterTimestamps($entry[0], 2);
      }, $throughputData);
      $throughputDownload = array_map(function ($entry) {
        return $entry[1];  // Download throughput
      }, $throughputData);
      $throughputUpload = array_map(function ($entry) {
        return $entry[2];  // Upload throughput
      }, $throughputData);
    } else {
      $throughputTimestamps = [];
      $throughputDownload = [];
      $throughputUpload = [];
    }






    // Usage Data
    // Get the current date and time
    $currentDate = new DateTime();

    // Subtract 10 days to get the start of the range
    $daysAgo = clone $currentDate;
    $daysAgo->modify('-10 days');

    // Filter the data for entries within the last 7 days
    $weeklyUsageData = array_filter($usageData, function ($entry) use ($daysAgo, $currentDate) {
      $entryDate = new DateTime($entry['date']);
      return $entryDate >= $daysAgo && $entryDate <= $currentDate;
    });

    $usageLabels = [];
    $usagePriority = [];
    $usageUnlimited = [];
    if (is_array($weeklyUsageData) && !empty($weeklyUsageData)) {
      foreach ($weeklyUsageData as $day) {
        $usageLabels[] = date('M j', strtotime($day['date']));
        $usagePriority[] = $day['priority'] ?? 0;
        $usageUnlimited[] = $day['unlimited'] ?? 0;
      }
      // Encode data as JSON for JavaScript usage
      $usageLabelsJson = json_encode($usageLabels);
      $usagePriorityJson = json_encode($usagePriority);
      $usageUnlimitedJson = json_encode($usageUnlimited);
    } else {
      print_r('EMPTY V!'); //TODO: Improve this output message
    }



    // Obstruction Data
    $obstructionDataJson = json_encode($obstructionData);



    // Uptime Data
    $uptimeLabels = [];
    $uptimeValues = [];

    foreach ($uptimeData as $dataPoint) {
      $uptimeLabels[] = date('H:i', $dataPoint[0]);  // Format the UNIX timestamp to time (hours:minutes)
      $uptimeValues[] = $dataPoint[1];         // Uptime value
    }

    $uptimeLabelsJson = json_encode($uptimeLabels);
    $uptimeValuesJson = json_encode($uptimeValues);

    // print_r($uptimeData);
    // // Uptime Data
    // if (is_array($uptimeData) && !empty($uptimeData)) {
    //   $uptime = end($uptimeData);
    //   $uptimeTimestamp = date('M j, Y H:i', $uptime[0]);
    //   $uptimeValue = $uptime[1];
    // } else {
    //   $uptimeTimestamp = null;
    //   $uptimeValue = null;
    // }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modem Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="status/status.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- TODO: API Key variable -->
  <?php if ($gps): ?>
    <style>
      #map {
        height: 400px;
        width: 100%;
      }
    </style>
    <script loading=async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB_d4pgPWUXxNg8y4BEPoEWmjHrYbIOuY8&callback=initMap" defer></script>
  <?php endif; ?>
</head>

<body>
  <div class="d-flex justify-content-between align-items-start">
    <div class="w-25 sticky-top bg-white">
      <div class="p-4 border-medium border-bottom">
        <img src="status/switch-logo.png" alt="Switch Logo" class="img-fluid">
      </div>
      <div class="px-2 py-4 border-medium border-bottom ">
        <div class="d-flex align-items-center">
          <a href="/service-status" class="px-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="black" class="bi bi-arrow-left" viewBox="0 0 16 16" style="font-weight: bold;">
              <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z" />
            </svg>
          </a>
          <?php if ($modem) : ?>
            <h1 class="h5 mb-0 font-weight-bolder"><?= $modem['id']; ?> Modem Status</h1>
          <?php else : ?>
            <h1 class="h5 mb-0 font-weight-bolder">Modem Status</h1>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="bg-light w-75">
      <?php if ($modem && is_array($modem['data']) && !empty($modem['data'])) : ?>
        <?php if (is_array($gps) && $gps['lat'] && $gps['lon']): ?>
          <!-- Map container -->
          <div id="map"></div>
          <script>
            // PHP outputs latitude and longitude from the API response

            let map;

            async function initMap() {
              const lat = <?= json_encode($gps['lat']); ?>;
              const lon = <?= json_encode($gps['lon']); ?>;

              const {
                Map
              } = await google.maps.importLibrary("maps");

              map = new Map(document.getElementById("map"), {
                center: {
                  lat: lat,
                  lng: lon
                },
                zoom: 8,
              });

              // Add a marker at the modem location
              const marker = new google.maps.Marker({
                position: {
                  lat: lat,
                  lng: lon
                },
                map: map
              });
            }
          </script>

        <?php else: ?>
          <div class='row text-center p-4 mt-2'>
            <p>No GPS data available for modem <?= $modemId; ?></p>
          </div>
        <?php endif; ?>

        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Service Line Usage (<?php echo $modem['id']; ?>)</h2>
              <canvas id="usageChart" width="400" height="100"></canvas>
              <span class="f6">Data usage tracking is not immediate and may be delayed by 24 hours or more. Counting shown is for informational purposes only and final overages reflected in monthly invoice are accurate.</span>
            </div>
          </div>
        </div>

        <!-- Signal Quality Chart -->
        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Signal Quality</h2>
              <canvas id="signalQualityChart" width="400" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Throughput Chart -->
        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Throughput</h2>
              <canvas id="throughputChart" width="400" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Latency Chart -->
        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Latency/Ping Drop Rate</h2>
              <canvas id="latencyChart" width="400" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Obstruction Chart -->
        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Obstruction</h2>
              <canvas id="obstructionChart" width="400" height="100"></canvas>
            </div>
          </div>
        </div>

        <!-- Uptime Chart -->
        <div class="row py-4">
          <div class="card rounded shadow">
            <div class="card-body">
              <h2 class="card-title h5">Modem Uptime</h2>
              <canvas id="uptimeChart" width="400" height="100"></canvas>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class='row text-center p-4 mt-2'>
          <p>No data available for modem <?= $modemId; ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php include('status/loading.php'); ?>
  <script>
    // Throughput Chart
    const throughputCtx = document.getElementById('throughputChart').getContext('2d');
    new Chart(throughputCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($throughputTimestamps); ?>,
        datasets: [{
          label: 'Download Throughput (Mbps)',
          data: <?php echo json_encode($throughputDownload); ?>,
          borderCapStyle: 'round',
          borderColor: '#3986a8', // Darker border color
          borderWidth: 1,
          pointRadius: 0, // Remove dots at each point
          fill: {
            target: 'origin',
            above: '#8bcff0', // Lighter fill color
            below: '#8bcff0' // And blue below the origin
          }
        }, {
          label: 'Upload Throughput (Mbps)',
          data: <?php echo json_encode($throughputUpload); ?>,
          borderColor: '#c5522b',
          borderCapStyle: 'round',
          borderWidth: 1,
          pointRadius: 0, // Remove dots at each point
          fill: {
            target: 'origin',
            above: '#f69263', // Lighter fill color
            below: '#f69263' // And blue below the origin
          }
        }]
      }
    });

    // Signal Quality Chart
    const signalCtx = document.getElementById('signalQualityChart').getContext('2d');
    new Chart(signalCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($signalTimestamps); ?>,
        datasets: [{
          label: 'Signal Quality (%)',
          data: <?php echo json_encode($signalValues); ?>,
          borderCapStyle: 'round',
          borderColor: '#3986a8', // Darker border color
          borderWidth: 1,
          pointRadius: 0, // Remove dots at each point
          fill: {
            target: 'origin',
            above: '#8bcff0', // Lighter fill color
            below: '#8bcff0' // And blue below the origin
          }
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value + '%'; // Add percentage sign to y-axis labels
              },
              stepSize: 50, // Set step size to 50
              max: 100 // Set maximum value to 100
            }
          }
        }
      }
    });

    // Usage Chart
    const usageLabels = <?php echo $usageLabelsJson; ?>;
    const usagePriority = <?php echo $usagePriorityJson; ?>;
    const usageUnlimited = <?php echo $usageUnlimitedJson; ?>;
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    const usageChart = new Chart(usageCtx, {
      type: 'bar',
      data: {
        labels: usageLabels,
        datasets: [{
            label: 'Priority Usage',
            backgroundColor: '#8bcff0',
            data: usagePriority
          },
          {
            label: 'Unlimited Usage',
            backgroundColor: '#5baed9',
            data: usageUnlimited
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 0.05, // Set step size to 20 MB (0.02 GB)
              max: 1 // Set maximum value to 1 GB
            },
            title: {
              // move the usage label one decimal over
              display: true,
              text: 'Usage (GB)'
            }
          }
        }
      }
    });

    // Latency Chart
    const latencyCtx = document.getElementById('latencyChart').getContext('2d');
    new Chart(latencyCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($latencyTimestamps); ?>,
        datasets: [{
          label: 'Latency (ms)',
          data: <?php echo json_encode($latencyValues); ?>,
          borderCapStyle: 'round',
          borderColor: '#3986a8', // Darker border color
          borderWidth: 1,
          pointRadius: 0, // Remove dots at each point
          fill: {
            target: 'origin',
            above: '#8bcff0', // Lighter fill color
            below: '#5baed9' // And blue below the origin
          }
        }]
      }
    });

    // Obstruction Chart
    // PHP passes the obstruction data to JavaScript
    const obstructionData = <?php echo $obstructionDataJson; ?>;

    // Convert timestamps to readable dates for x-axis labels
    const labels = obstructionData.map(entry => {
      const date = new Date(entry[0] * 1000);
      return date.toISOString().slice(0, 16).replace('T', ' ');
    });

    // Extract obstruction percentages for y-axis data
    const dataPoints = obstructionData.map(entry => entry[1]);

    const obstructionCtx = document.getElementById('obstructionChart').getContext('2d');
    const obstructionChart = new Chart(obstructionCtx, {
      type: 'line',
      data: {
        labels: labels, // Dates for x-axis
        datasets: [{
          label: 'Obstruction (%)',
          data: dataPoints, // Obstruction percentages for y-axis
          backgroundColor: '#8bcff0',
          borderColor: '#3986a8',
          borderCapStyle: 'round',
          borderWidth: 1,
          pointRadius: 0, // Remove dots at each point
          fill: true
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Obstruction (%)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Date/Time'
            }
          }
        }
      }
    });

    // Uptime Chart
    document.addEventListener("DOMContentLoaded", function() {

      const uptimeLabels = <?php echo $uptimeLabelsJson; ?>;
      const uptimeValues = <?php echo $uptimeValuesJson; ?>;
      const uptimeCtx = document.getElementById('uptimeChart').getContext('2d');
      const uptimeChart = new Chart(uptimeCtx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Uptime',
            data: uptimeValues,
            borderColor: '#3986a8',
            borderWidth: 1,
            fill: false
          }]
        },
        options: {
          scales: {
            x: {
              title: {
                display: true,
                text: 'Time'
              }
            },
            y: {
              title: {
                display: true,
                text: 'Uptime (0 to 1)'
              },
              min: 0,
              max: 1,
              ticks: {
                stepSize: 0.1
              }
            }
          }
        }
      });
    });
  </script>
</body>

</html>