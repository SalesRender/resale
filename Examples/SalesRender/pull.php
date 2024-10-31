<?php
header('Content-Type: application/json');

$server = $_GET['cl'] ?? 'de';
$companyId = $_GET['cid'] ?? null;
$token = $_GET['token'] ?? null;

$data = [
    'id' => $_POST['externalId'],
    'statusGroup' => $_POST['statusGroup'],
    'status' => $_POST['status'],
    'reward' => [
        'value' => $_POST['reward']['value'],
        'currency' => $_POST['reward']['currency'],
    ],
];

$url = 'https://' . $_GET['cl'] . '.backend.salesrender.com/companies/' . $companyId . '/resale/update?token=' . $_GET['token'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'message' => 'SalesRender server error',
            'code' => 500,
        ],
    ]);
    return;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode != 201) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'message' => 'SalesRender respond with wrong status code. Expected: 201. Actual: ' . $httpCode,
            'code' => 400,
        ],
    ]);
    return;
}

http_response_code(201);
echo json_encode(['error' => null]);
