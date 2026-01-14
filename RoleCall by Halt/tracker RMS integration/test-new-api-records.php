<?php
/**
 * Test script using NEW REST API to create records in TrackerRMS
 * 
 * This uses /api/v1/Resource, /api/v1/Contact, /api/v1/Lead
 * 
 * Run: php test-new-api-records.php
 */

// Configuration
$client_id = 'EvoApi_1.0';
$client_secret = '2c318fae-8b98-11e9-bc42-526af7764f64';
$refresh_token = 'ecffd89dcde0461cb022aa66b337511e';
$environment = 'row'; // row = UK/ROW

// Base URLs
$oauth_base = 'https://evoapi.tracker-rms.com/';
$new_api_base = 'https://evoglapi.tracker-rms.com/';

echo "=== TrackerRMS Record Creation Test (NEW API) ===\n\n";

// Get Access Token
echo "Step 1: Getting OAuth Access Token...\n";

$token_url = $oauth_base . 'oAuth2/Token';
$token_data = [
    'grant_type' => 'refresh_token',
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'refresh_token' => $refresh_token
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    die("ERROR: Failed to get access token. HTTP $http_code\n");
}

$token_response = json_decode($response, true);
$access_token = $token_response['access_token'] ?? null;

if (!$access_token) {
    die("ERROR: No access token in response.\n");
}

echo "✓ Access token obtained\n\n";

// Exchange for JWT
echo "Step 2: Exchanging for JWT token...\n";

$jwt_url = $new_api_base . 'api/auth/exchangetoken';
$jwt_body = json_encode(['bearerToken' => $access_token]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $jwt_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jwt_body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    die("ERROR: Failed to get JWT. HTTP $http_code\nResponse: $response\n");
}

$jwt_response = json_decode($response, true);
$jwt = $jwt_response['token'] ?? null;

if (!$jwt) {
    die("ERROR: No JWT token in response.\n");
}

echo "✓ JWT token obtained\n\n";

// ============================================
// Create TEST RESOURCE using NEW API
// ============================================
echo "Step 3: Creating TEST RESOURCE via NEW API...\n";

$resource_data = [
    'source' => 'Website - CV Upload (TEST)',
    'firstName' => 'Test',
    'surname' => 'Candidate',
    'contactDetails' => [
        'email' => 'test.candidate.newapi@example.com',
        'mobilePhone' => '+44 7700 900123',
        'telephone' => '+44 20 7946 0958'
    ],
    'jobTitle' => 'Software Developer',
    'availableForWork' => true,
    'currentSalaryNum' => 50000,
    'desiredSalaryNum' => 60000,
    'noticePeriod' => 30,
    'note' => 'This is a test candidate created via NEW REST API. Created: ' . date('Y-m-d H:i:s'),
    'tagText' => 'TEST, Website Candidate, CV Upload, NEW API'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_base . 'api/v1/Resource');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resource_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response HTTP Code: $http_code\n";
echo "Response Body:\n" . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "\n\n";

$resource_response = json_decode($response, true);
$resource_id = null;
if ($http_code === 201 || $http_code === 200) {
    $resource_id = $resource_response['resourceId'] ?? $resource_response['id'] ?? null;
    if ($resource_id) {
        echo "✓ RESOURCE CREATED! ID: $resource_id\n\n";
    } else {
        echo "? Resource may have been created but couldn't extract ID\n\n";
    }
} else {
    echo "✗ RESOURCE CREATION FAILED\n\n";
}

// ============================================
// Create TEST CONTACT using NEW API
// ============================================
echo "Step 4: Creating TEST CONTACT via NEW API...\n";

$contact_data = [
    'source' => 'Website - Event Registration (TEST)',
    'firstName' => 'Test',
    'surname' => 'Contact',
    'contactDetails' => [
        'email' => 'test.contact.newapi@example.com',
        'mobilePhone' => '+44 7700 900456',
        'telephone' => '+44 20 7946 0123'
    ],
    'company' => 'Test Company Ltd',
    'jobTitle' => 'Marketing Manager',
    'marketingPreference' => 'Opted In',
    'note' => 'This is a test contact created via NEW REST API. Created: ' . date('Y-m-d H:i:s'),
    'tagText' => 'TEST, Event Attendee, NEW API'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_base . 'api/v1/Contact');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response HTTP Code: $http_code\n";
echo "Response Body:\n" . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "\n\n";

$contact_response = json_decode($response, true);
$contact_id = null;
if ($http_code === 201 || $http_code === 200) {
    $contact_id = $contact_response['contactId'] ?? $contact_response['id'] ?? null;
    if ($contact_id) {
        echo "✓ CONTACT CREATED! ID: $contact_id\n\n";
    } else {
        echo "? Contact may have been created but couldn't extract ID\n\n";
    }
} else {
    echo "✗ CONTACT CREATION FAILED\n\n";
}

// ============================================
// Create TEST LEAD using NEW API
// ============================================
echo "Step 5: Creating TEST LEAD via NEW API...\n";

$lead_data = [
    'source' => 'Website - Content Download (TEST)',
    'name' => 'Test Lead - Salary Survey Download',
    'firstName' => 'Test',
    'surname' => 'Lead',
    'contactDetails' => [
        'email' => 'test.lead.newapi@example.com',
        'mobilePhone' => '+44 7700 900789',
        'telephone' => '+44 20 7946 0789'
    ],
    'currencyCode' => 'GBP',
    'description' => 'Downloaded Salary Survey 2025. Potential client for recruitment services.',
    'potentialValue' => 5000,
    'tagText' => 'TEST, Hot Lead, Content Download, NEW API'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_base . 'api/v1/Lead');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($lead_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response HTTP Code: $http_code\n";
echo "Response Body:\n" . json_encode(json_decode($response, true), JSON_PRETTY_PRINT) . "\n\n";

$lead_response = json_decode($response, true);
$lead_id = null;
if ($http_code === 201 || $http_code === 200) {
    $lead_id = $lead_response['leadId'] ?? $lead_response['id'] ?? null;
    if ($lead_id) {
        echo "✓ LEAD CREATED! ID: $lead_id\n\n";
    } else {
        echo "? Lead may have been created but couldn't extract ID\n\n";
    }
} else {
    echo "✗ LEAD CREATION FAILED\n\n";
}

// ============================================
// Summary
// ============================================
echo "=== SUMMARY ===\n\n";
echo "Created Test Records:\n";
echo "---------------------\n";
if ($resource_id) {
    echo "✓ RESOURCE (Candidate): ID $resource_id\n";
    echo "  Email: test.candidate.newapi@example.com\n\n";
}

if ($contact_id) {
    echo "✓ CONTACT: ID $contact_id\n";
    echo "  Email: test.contact.newapi@example.com\n\n";
}

if ($lead_id) {
    echo "✓ LEAD: ID $lead_id\n";
    echo "  Email: test.lead.newapi@example.com\n\n";
}

echo "---------------------\n";
echo "Check TrackerRMS UI for records tagged with 'TEST' and 'NEW API'\n";

