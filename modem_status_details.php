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

    // $gpsData = fetchGPS($provider, [$modemId], $accessToken);
    $latencyData = $modem['data']['latency']['data'] ?? [];
    $throughputData = $modem['data']['throughput']['data'] ?? [];
    $signalQualityData = $modem['data']['signal']['data'] ?? [];
    $obstructionData = $modem['data']['obstruction']['data'] ?? [];  // In case this data is available
    $usageData = $modem['usage'] ?? [];
    $uptimeData = $modem['data']['uptime']['data'] ?? [];


    // @ Usage Data
    $currentDate = new DateTime();
    $usageDayOffset = clone $currentDate;
    $usageDayOffset->modify('-14 days');

    $weeklyUsageData = array_filter($usageData, function ($entry) use ($usageDayOffset, $currentDate) {
      $entryDate = new DateTime($entry['date']);
      return $entryDate >= $usageDayOffset && $entryDate <= $currentDate;
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
    }


    // @ Signal Quality Data
    $signalTimestamps = [];
    $signalValues = [];

    if (is_array($signalQualityData) && !empty($signalQualityData)) {
      $signalTimestamps = array_map(function ($entry) {
        return date('H:i', $entry[0]); // Timestamp (Unix)
      }, $signalQualityData);
      $signalTimestamps = array_values($signalTimestamps);
      $signalValues = array_map(
        function ($entry) {
          return $entry[1];
        },
        $signalQualityData
      );
    }


    // @ Throughtput Data
    if (is_array($throughputData) && !empty($throughputData)) {
      $throughputTimestamps = array_map(function ($entry) {
        return date('H:i', $entry[0]); // Timestamp (Unix)
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


    // @ Latency Data
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


    // @ Obstruction Data
    $obstructionData = array_map(function ($entry) {
      return [$entry[0], $entry[1] * 100];  // Convert to percentage
    }, $obstructionData);



    // @ Uptime Data
    $uptimeLabels = [];
    $uptimeValues = [];
    foreach ($uptimeData as $dataPoint) {
      $uptimeLabels[] = date('H:i', $dataPoint[0]);  // Format the UNIX timestamp to time (hours:minutes)
      $uptimeValues[] = ceil(($dataPoint[1] / 86400) * 10) / 10;
    }
  }
}

// Helper functions
function filterTimestampHours($dataPoints)
{
  $filteredLabels = [];
  $previousHour = null;

  foreach ($dataPoints as $dataPoint) {
    $currentHour = date('H', $dataPoint[0]);
    if ($previousHour === null || ($currentHour % 2 == 0 && $currentHour != $previousHour)) {
      $filteredLabels[] = date('H:i', $dataPoint[0]);  // Format the UNIX timestamp to time (hours:minutes)
      $previousHour = $currentHour;
    }
  }

  return $filteredLabels;
}

// Helper function to filter Unix timestamps and return only those that fall on even hours
function filterEvenHourTimestamps(array $dataPoints)
{
  $filteredData = [];

  foreach ($dataPoints as $dataPoint) {
    $timestamp = $dataPoint[0];
    $hour = date('H', $timestamp);
    if ($hour % 2 === 0 && date('i', $timestamp) == '00') {
      $filteredData[] = $dataPoint;
    }
  }

  return $filteredData;
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
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

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
          <?php if ($modem['id']) : ?>
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
    // Global Chart Configuration
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.elements.point.radius = 0;
    Chart.defaults.elements.point.hoverRadius = 5;
    Chart.defaults.elements.point.hoverBorderWidth = 1;
    Chart.defaults.elements.point.backgroundColor = '#3986a8';
    Chart.defaults.elements.point.borderColor = '#3986a8';

    // Bar Charts
    Chart.defaults.elements.bar.backgroundColor = '#3986a8';
    Chart.defaults.elements.bar.borderWidth = 1;

    // Line Charts
    Chart.defaults.elements.line.hitRadius = 15;
    Chart.defaults.elements.line.pointRadius = 0;
    Chart.defaults.elements.line.borderCapStyle = 'round';
    Chart.defaults.elements.line.borderColor = '#3986a8';
    Chart.defaults.elements.line.borderWidth = 1;
    Chart.defaults.elements.line.fill = true;
    Chart.defaults.elements.line.fill.target = 'origin';


    // ~ Usage Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    const usageChart = new Chart(usageCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($usageLabels); ?>,
        datasets: [{
            label: 'Priority Usage',
            data: <?php echo json_encode($usagePriority); ?>
          },
          {
            label: 'Unlimited Usage',
            backgroundColor: '#5baed9',
            data: <?php echo json_encode($usageUnlimited); ?>
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value + 'GB';
              },
              stepSize: 5,
            },
          }
        },
        plugins: {
          subtitle: {
            display: true,
            position: 'bottom',
            text: 'Data usage tracking is not immediate and may be delayed by 24 hours or more. Counting shown is for informational purposes only and final overages reflected in monthly invoice are accurate.'
          },
          legend: {
            display: true,
            position: 'bottom',
          }
        }
      }
    });


    // ~ Signal Quality Chart
    const signalCtx = document.getElementById('signalQualityChart').getContext('2d');
    new Chart(signalCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($signalTimestamps); ?>,
        datasets: [{
          label: 'Signal Quality (%)',
          data: <?php echo json_encode($signalValues); ?>,
        }]
      },
      options: {
        scales: {
          y: {
            ticks: {
              callback: function(value) {
                return value + '%';
              },
              stepSize: 50,
            },
            beginAtZero: true,
            min: 0,
            max: 100
          }
        }
      }
    });


    // ~ Throughput Chart
    const throughputCtx = document.getElementById('throughputChart').getContext('2d');
    new Chart(throughputCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($throughputTimestamps); ?>,
        datasets: [{
            label: 'Download Throughput (Mbps)',
            data: <?php echo json_encode($throughputDownload); ?>,
          },
          {
            label: 'Upload Throughput (Mbps)',
            data: <?php echo json_encode($throughputUpload); ?>,
          }
        ]
      },
      options: {
        scales: {
          y: {
            ticks: {
              callback: function(value) {
                return value + 'Mbs';
              },
              stepSize: 5,
            },
            beginAtZero: true,
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
          }
        }
      }
    });


    // ~ Latency Chart
    const latencyCtx = document.getElementById('latencyChart').getContext('2d');
    new Chart(latencyCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($latencyTimestamps); ?>,
        datasets: [{
          label: 'Latency (ms)',
          data: <?php echo json_encode($latencyValues); ?>,
        }]
      },
      options: {
        scales: {
          y: {
            ticks: {
              callback: function(value) {
                return value + 'ms';
              },
              stepSize: 20,
            },
            beginAtZero: true,
          }
        }
      }
    });


    // ~ Obstruction Chart
    const obstructionData = <?php echo json_encode($obstructionData); ?>;
    const labels = obstructionData.map(entry => {
      const date = new Date(entry[0] * 1000);
      return date.toISOString().slice(0, 16).replace('T', ' ');
    });

    // Extract obstruction percentages for y-axis data
    const dataPoints = obstructionData.map(entry => (100 * entry[1]));
    const obstructionCtx = document.getElementById('obstructionChart').getContext('2d');
    const obstructionChart = new Chart(obstructionCtx, {
      type: 'line',
      data: {
        labels: labels, // Dates for x-axis
        datasets: [{
          label: 'Obstruction (%)',
          data: dataPoints,
        }]
      },
      options: {
        scales: {
          y: {
            ticks: {
              callback: function(value) {
                return value + '%';
              },
              stepSize: 50,
            },
            max: 100,
            beginAtZero: true,
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


    // ~ Uptime Chart
    document.addEventListener("DOMContentLoaded", function() {
      const uptimeLabels = <?php echo json_encode($uptimeLabels); ?>;
      const uptimeCtx = document.getElementById('uptimeChart').getContext('2d');
      const uptimeChart = new Chart(uptimeCtx, {
        type: 'line',
        data: {
          labels: uptimeLabels,
          datasets: [{
            label: 'Uptime',
            data: <?php echo json_encode($uptimeValues); ?>,
            stepped: true,
          }]
        },
        options: {
          scales: {
            y: {
              min: 0,
              beginAtZero: true,
              ticks: {
                stepSize: 0.5
              }
            }
          }
        }
      });
    });
  </script>
</body>

</html>