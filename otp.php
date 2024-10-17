<?php
function makeApiRequest($url) {
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'method' => 'GET',
        ]
    ]);

    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['success' => false, 'error' => 'Failed to make API request'];
    }

    $statusLine = $http_response_header[0];
    preg_match('{HTTP/\S*\s(\d{3})}', $statusLine, $match);
    $statusCode = $match[1];

    return [
        'success' => $statusCode == 200,
        'statusCode' => $statusCode,
        'response' => $response
    ];
}

// API endpoint and parameters
$apiEndpoint = 'https://api.fekrawhats.com/send';
$token = 'oKOM0z8Qjh6lqDJNmFwg';
$receiver = '34644975414';
$msgtext = 'Testing send message through API';

// First API call
$url1 = "{$apiEndpoint}?receiver={$receiver}&msgtext=" . urlencode($msgtext) . "&token={$token}";
$result1 = makeApiRequest($url1);

// Second API call with media URL
$mediaUrl = 'https://fekrawhats.com/public/users/1/avatar.png';
$url2 = "{$url1}&mediaurl=" . urlencode($mediaUrl);
$result2 = makeApiRequest($url2);

// Output results
echo "First API call result: ";
var_dump($result1);

echo "\nSecond API call result: ";
var_dump($result2);
?>