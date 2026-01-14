<?php
/**
 * View Full Job Response from TrackerRMS
 * This shows ALL fields available for a job/opportunity
 */

$client_id = 'EvoApi_1.0';
$client_secret = '2c318fae-8b98-11e9-bc42-526af7764f64';
$refresh_token = 'ecffd89dcde0461cb022aa66b337511e';

$old_api_base = 'https://evoapi.tracker-rms.com/';
$new_api_base = 'https://evoglapi.tracker-rms.com/';

echo "=== TrackerRMS Job Fields Reference ===\n\n";

// Get tokens
echo "Authenticating...\n";
$token_response = get_access_token($old_api_base, $client_id, $client_secret, $refresh_token);
if (isset($token_response['error'])) die("ERROR: " . $token_response['error'] . "\n");

$jwt = exchange_for_jwt($new_api_base, $token_response['access_token']);
if (isset($jwt['error'])) die("ERROR: " . $jwt['error'] . "\n");
echo "✓ Authenticated\n\n";

// Fetch opportunities
echo "Fetching opportunities...\n";
$opportunities = search_opportunities($new_api_base, $jwt['token'], [
    'maxResults' => 10,
    'pageNumber' => 1,
    'searchTerm' => '',
    'onlyMyRecords' => false,
    'includeCustomFields' => true
]);

if (isset($opportunities['error'])) {
    die("ERROR: " . $opportunities['error'] . "\n");
}

echo "✓ Found " . count($opportunities) . " opportunities\n\n";

// Find JOB-1026 (the published one)
$target_job = null;
foreach ($opportunities as $opp) {
    if ($opp['opportunityId'] == 1026) {
        $target_job = $opp;
        break;
    }
}

if (!$target_job) {
    die("JOB-1026 not found!\n");
}

// Output in multiple formats
echo "===========================================\n";
echo "FULL JOB DATA - JOB-1026\n";
echo "===========================================\n\n";

// 1. Pretty JSON format
file_put_contents(
    __DIR__ . '/job-1026-full-response.json',
    json_encode($target_job, JSON_PRETTY_PRINT)
);
echo "✓ Saved full JSON to: job-1026-full-response.json\n\n";

// 2. Organized by category
echo "===========================================\n";
echo "BASIC INFORMATION\n";
echo "===========================================\n";
$basic_fields = [
    'opportunityId', 'opportunityName', 'opportunityStatusId', 'opportunityStatusDesc',
    'clientRef', 'location', 'locationCity', 'locationState', 'postcodeLocation',
    'duration', 'startDate', 'endDate', 'dateOpened', 'creationDate', 
    'active', 'workType', 'workingFolder', 'contractType'
];
foreach ($basic_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "CLIENT & CONTACT INFORMATION\n";
echo "===========================================\n";
$client_fields = [
    'clientId', 'clientName', 'contactId', 'secondaryContactId',
    'opportunityOwnerId', 'secondaryOwnerId', 'thirdOwnerId', 'fourthOwnerId', 'fifthOwnerId',
    'ownerPercent', 'secondaryOwnerPercent', 'thirdOwnerPercent', 'fourthOwnerPercent', 'fifthOwnerPercent',
    'ownerRoleId', 'secondaryOwnerRoleId', 'thirdOwnerRoleId', 'fourthOwnerRoleId', 'fifthOwnerRoleId'
];
foreach ($client_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "WEB PUBLISHING FIELDS (publishXXX)\n";
echo "===========================================\n";
$publish_fields = [];
foreach ($target_job as $key => $value) {
    if (stripos($key, 'publish') !== false) {
        $publish_fields[$key] = $value;
    }
}
foreach ($publish_fields as $field => $value) {
    echo sprintf("%-30s : %s\n", $field, format_value($value));
}

echo "\n===========================================\n";
echo "FINANCIAL INFORMATION\n";
echo "===========================================\n";
$financial_fields = [
    'estimatedValue', 'factoredValue', 'targetMargin', 'marginCalculation',
    'opportunityRate', 'opportunityChargeRate', 'payPer', 'unitsInADay',
    'bidCosts', 'currencyCode'
];
foreach ($financial_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "ADVERT INFORMATION\n";
echo "===========================================\n";
$advert_fields = [
    'advertId', 'advertUserId', 'advertStatus', 'publishDate',
    'publishViewCount', 'publishApplyCount', 'indeedPost'
];
foreach ($advert_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "DESCRIPTION & NOTES\n";
echo "===========================================\n";
$desc_fields = ['opportunityDescription', 'note', 'publishDescription', 'publishBenefits', 'publishSkills'];
foreach ($desc_fields as $field) {
    if (isset($target_job[$field]) && !empty($target_job[$field])) {
        echo "\n--- $field ---\n";
        echo wordwrap($target_job[$field], 70) . "\n";
    }
}

echo "\n===========================================\n";
echo "DATES & TIMESTAMPS\n";
echo "===========================================\n";
$date_fields = [
    'dateOpened', 'creationDate', 'lastUpdatedDateTime', 'invoiceStartDate',
    'startDate', 'endDate', 'publishStartDate', 'awardDate', 'approvalDate',
    'dateFilled', 'bidDueDate'
];
foreach ($date_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "CANDIDATE/SHORTLIST INFORMATION\n";
echo "===========================================\n";
$candidate_fields = ['countShortlisted'];
foreach ($candidate_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "MANDACT SCORING (Sales Qualification)\n";
echo "===========================================\n";
$mandact_fields = [
    'money', 'moneyScore', 'authority', 'authorityScore', 'need', 'needScore',
    'decision', 'decisionScore', 'ability', 'abilityScore', 'competition', 
    'competitionScore', 'timing', 'timingScore', 'winStrategy'
];
foreach ($mandact_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "BID INFORMATION\n";
echo "===========================================\n";
$bid_fields = [
    'bidType', 'bidDueDate', 'bidDriver', 'bidNoBid', 'bidNoBidReason',
    'bidManager', 'bidCosts', 'newRepeat', 'competitive'
];
foreach ($bid_fields as $field) {
    if (isset($target_job[$field])) {
        echo sprintf("%-25s : %s\n", $field, format_value($target_job[$field]));
    }
}

echo "\n===========================================\n";
echo "CUSTOM FIELDS\n";
echo "===========================================\n";
if (isset($target_job['customFields']) && is_array($target_job['customFields'])) {
    foreach ($target_job['customFields'] as $cf) {
        echo sprintf("%-25s : %s\n", $cf['name'] ?? 'Unknown', $cf['value'] ?? '');
    }
} else {
    echo "(No custom fields)\n";
}

echo "\n===========================================\n";
echo "OTHER FIELDS\n";
echo "===========================================\n";
$displayed_fields = array_merge(
    $basic_fields, $client_fields, array_keys($publish_fields),
    $financial_fields, $advert_fields, $desc_fields, $date_fields,
    $candidate_fields, $mandact_fields, $bid_fields
);
foreach ($target_job as $field => $value) {
    if (!in_array($field, $displayed_fields) && $field !== 'customFields') {
        echo sprintf("%-30s : %s\n", $field, format_value($value));
    }
}

echo "\n===========================================\n";
echo "ALL FIELD NAMES (for reference)\n";
echo "===========================================\n";
$all_fields = array_keys($target_job);
sort($all_fields);
foreach (array_chunk($all_fields, 3) as $chunk) {
    echo implode(', ', array_map(function($f) { return str_pad($f, 30); }, $chunk)) . "\n";
}

echo "\n===========================================\n";
echo "SUMMARY\n";
echo "===========================================\n";
echo "Total Fields Available: " . count($target_job) . "\n";
echo "Full JSON saved to: job-1026-full-response.json\n";
echo "\nUse these field names in your WordPress templates!\n";

// Helper function
function format_value($value) {
    if (is_array($value)) {
        return json_encode($value);
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_null($value)) {
        return '(null)';
    }
    if ($value === '') {
        return '(empty)';
    }
    return (string)$value;
}

// Helper functions (same as before)
function get_access_token($base_url, $client_id, $client_secret, $refresh_token) {
    $url = rtrim($base_url, '/') . '/oAuth2/Token';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
    ]));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) return ['error' => "HTTP $http_code"];
    return json_decode($response, true);
}

function exchange_for_jwt($base_url, $access_token) {
    $url = rtrim($base_url, '/') . '/api/Auth/ExchangeToken';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['bearerToken' => $access_token]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) return ['error' => "HTTP $http_code"];
    return json_decode($response, true);
}

function search_opportunities($base_url, $jwt_token, $search_params) {
    $url = rtrim($base_url, '/') . '/api/v1/Opportunity/Search';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($search_params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json',
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) return ['error' => "HTTP $http_code"];
    return json_decode($response, true);
}

