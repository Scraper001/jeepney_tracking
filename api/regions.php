<?php
/**
 * Simple API endpoint to serve Philippine regions data
 * This replaces the external API call to make the form work locally
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Read and return the regions data
$regions = file_get_contents(__DIR__ . '/regions.json');
echo $regions;
?>