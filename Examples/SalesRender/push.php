<?php
header('Content-Type: application/json');

$server = $_POST['params']['param_10'] ?? 'de';
$companyId = $_POST['params']['param_1'];
$url = "https://{$server}.backend.salesrender.com/companies/{$companyId}/CPA/lead/add";
$offerId = $_POST['params']['param_2'];

$body = [
    'offerId' => $offerId,
    'data' => $_POST['lead']['data'],
    'source' => $_POST['lead']['source'],
    'timezoneOffset' => $_POST['lead']['timezone']['offset'],
    'bid' => [
        'type' => $_POST['resale']['bid']['type'],
        'percent' => $_POST['resale']['bid']['percent'] ?? null,
        'fixed' => $_POST['resale']['bid']['fixed']['value'] ?? null,
        'currency' => $_POST['resale']['bid']['fixed']['currency'] ?? null,
    ],
    'externalId' => $_POST['lead']['id'],
    'externalTag' => $_POST['lead']['lead']['webmaster']['id'],
    'price' => $_POST['lead']['cart']['totalPrice'],
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($body));
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: {$_POST['token']}"]);
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

if ($httpCode !== 201) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'message' => 'SalesRender respond with wrong status code. Expected: 201. Actual: ' . $httpCode,
            'code' => 400,
        ],
    ]);
}

http_response_code(201);
$response = json_decode($response, true);
echo json_encode([
    'error' => null,
    'externalId' => $response['id'],
    'externalTag' => $response['id'],
    'status' => $response['status'],
    'method' => $response['method'],
    'reward' => $response['reward'],
]);