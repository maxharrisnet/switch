  <?php
  require __DIR__ . '/../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  // TODO: Fix this
  // API documentation: https://api-compass.speedcast.com/api-docs/
  // API authentication
  $apiUrl = 'https://api-compass.speedcast.com/v1/services'; // Correct API URL from docs
  $accessToken = fetchAccessToken();

  // Helper function to get the access token
  function fetchAccessToken()
  {
    $url = "https://api-compass.speedcast.com/v2.0/auth";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
      'username' => 'alison.jane@switch.ca',
      'password' => 'Sw!tch36'
    ]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
      $data = json_decode($response, true);
      return isset($data['access_token']) ? $data['access_token'] : null;
    } else {
      return "Error: HTTP code $httpCode";
    }
  }

  function getServiceURL($provider, $sysId)
  {
    $baseUrl = "https://api-compass.speedcast.com/v2.0";

    switch ($provider) {
      case 'starlink':
        return "$baseUrl/starlink/{$sysId}";
      case 'idirect':
        return "$baseUrl/idirectmodem/{$sysId}";
      case 'newtec':
        return "$baseUrl/newtecmodem/{$sysId}";
      case 'oneweb':
        return "$baseUrl/oneweb/{$sysId}"; // TODO: Test, fix with terminalId (see docs)
      default:
        return null;
    }
  }

  function getGPSURL($provider)
  {
    $baseUrl = "https://api-compass.speedcast.com/v2.0";

    switch ($provider) {
      case 'starlink':
        return "$baseUrl/starlinkgps";
      case 'idirect':
        return "$baseUrl/idirectgps";
      case 'newtec':
        return "$baseUrl/newtecgps";
      case 'oneweb':
        return "$baseUrl/oneweb"; // TODO: Test, fix with terminalId (see docs)
      default:
        return null;
    }
  }

  // Function to fetch modem data for each company
  function fetchCompanyServices($provider, $params, $accessToken)
  {

    // Construct the URL based on the provider
    $url = getServiceURL($provider, $params);

    // Initialize the cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer $accessToken",
      'Content-Type: application/json'
    ]);

    // Execute and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle response based on HTTP status code
    if ($httpCode === 200) {
      return json_decode($response, true);  // Return the data
    } else {
      return "Error: HTTP code $httpCode <br> Response: $response";
    }
  }

  // Fetch and handle services for all providers
  function fetchAllServices($accessToken)
  {
    $baseUrl = "https://api-compass.speedcast.com/v2.0";
    $companyId = 'ab940aba9783e95064ba7f9e2153af0e'; // TODO: environment variable
    $url = "$baseUrl/company/$companyId";

    // Initialize the cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer $accessToken",
      'Content-Type: application/json'
    ]);

    // Execute and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle response based on HTTP status code
    if ($httpCode === 200) {
      // print_r($response);
      return json_decode($response, true);
    } else {
      return "Error: HTTP code $httpCode <br> Response: $response";
    }
  }

  // Fetch modem details from the API
  function fetchModemDetails($url, $accessToken)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer $accessToken",
      'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
      return json_decode($response, true);
    } elseif ($httpCode === 401) {
      return ['error' => 'Unauthorized: Invalid API Key'];
    } elseif ($httpCode === 404) {
      return ['error' => 'Modem not found'];
    } elseif ($error) {
      return ['error' => 'Network Error: ' . $error];
    } else {
      return ['error' => 'Unexpected Error! (HTTP Code: ' . $httpCode . ')'];
    }
  }

  function fetchGPS($provider, $ids, $accessToken)
  {
    $cacheFile = "status/cache/gps_cache.json";

    // Check if cache exists and is still valid (e.g., 5 minutes expiration)
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 300) {
      return json_decode(file_get_contents($cacheFile), true);
    }

    // Proceed with API call
    $url = getGPSURL($provider);
    $postData = json_encode(['ids' => $ids]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer $accessToken",
      'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
      file_put_contents($cacheFile, $response);  // Save to cache
      return json_decode($response, true);
    } elseif ($httpCode === 429) {
      echo "Rate limit exceeded. Using cached data...\n";
      return file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : null;
    } else {
      return "Error: HTTP code $httpCode.";
    }
  }

  function filterTimestamps(string $timestamp, int $hourIncrement): ?string
  {
    $hours = date('H', $timestamp);
    $minutes = date('i', $timestamp);

    if ($minutes == 0 && $hours % 2 == 0) {
      return date('H:i', $timestamp);
    }
    return null;
  }

  function getLatencyClass($latency)
  {
    // Returns the class based on latency value
    if ($latency < 50) return "latency-green";
    elseif ($latency < 150) return "latency-orange";
    else return "latency-red";
  }

  ?>