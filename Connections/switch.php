<?php
// Check if the 'trackerID' exists in the GET request before accessing it to prevent the warning
$trackerID = isset($_GET['trackerID']) ? $_GET['trackerID'] : null; // Defaulting to null if not set

// Database connection credentials (for local XAMPP environment)
$hostname = "localhost";      // Use 'localhost' for local development with XAMPP
$username = "root";           // Default username for XAMPP
$password = "";               // Leave blank (no password by default for XAMPP's root user)
$database = "switchwebsite";  // Replace this with the name of your local database

// Attempt to establish a connection to the MySQL database
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check if the connection was successful
if (!$connection) {
  die("Connection failed: " . mysqli_connect_error());
}

// You can proceed with your queries below, using the $connection variable
