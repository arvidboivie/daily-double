<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// TODO: Get authenticated
$authUrl = 'https://accounts.spotify.com/api/token';

$clientId = '***REMOVED***';
$clientSecret = '***REMOVED***';

$ch = curl_init();

if (FALSE === $ch)
    throw new Exception('failed to initialize');

$curlConfig = [
    CURLOP_URL => $curlUrl,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [ 'Authorization: Basic ' . base64_encode($clientId.':'.$clientSecret) ],
    CURLOPT_POSTFIELDS => [ 'grant_type' => 'client_credentials' ],
];

curl_setopt_array($ch, $curlConfig);

$result = curl_exec($ch);

curl_close($ch);

var_dump(curl_errno());
var_dump(curl_error());
var_dump($result);
