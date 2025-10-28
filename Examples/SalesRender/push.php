<?php
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'message' => 'Only post request is allowed',
            'code' => 400,
        ],
    ]);
    return;
}

$companyId = $_POST['params']['param_1'];
$offerId = $_POST['params']['param_2'];
$host = $_POST['params']['param_3'];

$url = "https://{$host}/companies/{$companyId}/CPA/lead/add";

$data = $_POST['data'];

$body = [
    'offerId' => $offerId,
    'data' => $data['data'],
    'source' => $data['source'],
    'timezoneOffset' => $data['timezone']['offset'],
    'bid' => [
        'type' => $data['resale']['bid']['type'],
        'percent' => $data['resale']['bid']['percent'] ?? null,
        'fixed' => $data['resale']['bid']['fixed']['value'] ?? null,
        'currency' => $data['resale']['bid']['fixed']['currency'] ?? null,
    ],
    'externalId' => $data['id'],
    'externalTag' => $data['lead']['webmaster']['id'],
    'price' => $data['cart']['totalPrice'] ?? 0,
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL,$url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: {$_POST['token']}",
    "Content-Type: application/json",
]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 15);

try {
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
} catch (Throwable $throwable) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'message' => 'SalesRender server error',
            'code' => 500,
        ],
    ]);
} finally {
    curl_close($curl);
}

$response = @json_decode($response, true);

if ($httpCode !== 201) {
    http_response_code(400);
    $message = "SalesRender respond with wrong status code. Expected: 201. Actual: {$httpCode}";
    if (is_array($response) && isset($response['errors']) && is_array($response['errors'])) {
        foreach ($response['errors'] as $error) {
            $message.= '. ' . $error['message'];
            break;
        }
    }

    echo json_encode([
        'error' => [
            'message' => $message,
            'code' => 400,
        ],
    ]);
    return;
}

http_response_code(201);
echo json_encode([
    'error' => null,
    'externalId' => $response['id'],
    'externalTag' => $response['id'],
    'status' => $response['status'],
    'method' => $response['method'],
    'reward' => $response['reward'],
]);