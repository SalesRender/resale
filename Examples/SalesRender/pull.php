<?php
header('Content-Type: application/json');

$server = $_GET['cl'] ?? 'de';
$companyId = $_GET['cid'] ?? null;
$token = $_GET['token'] ?? null;

$env = [];
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
}

$data = [
    'token' => $token,
    'id' => $_POST['externalId'],
];

if (isset($_POST['statusGroup'])) {
    $data['statusGroup'] = $_POST['statusGroup'];
}

if (isset($_POST['status'])) {
    $data['status'] = $_POST['status'];
}

if (isset($_POST['reward']['value']) && isset($_POST['reward']['currency'])) {
    $data['reward'] = [
        'value' => $_POST['reward']['value'],
        'currency' => $_POST['reward']['currency'],
    ];
}

$host = $env['SR_HOST'] ?? "{$_GET['cl']}.backend.salesrender.com";
$url = 'https://' . $host . '/companies/' . $companyId . '/CRM/plugin/resale/' . $_GET['lpid'] . '/update';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

try {
    $response = curl_exec($ch);

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
        http_response_code($httpCode);

        $response = @json_decode($response, true);
        $message = "SalesRender respond with wrong status code. Expected: 201. Actual: {$httpCode}";
        if (is_array($response) && isset($response['error']) && is_string($response['error'])) {
            $message.= '. ' . $response['error'];
        }

        echo json_encode([
            'error' => [
                'message' => $message,
                'code' => $httpCode,
                'response' => $response,
            ],
        ]);
        return;
    }
} catch (Throwable $throwable) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'message' => 'Connection error',
            'code' => 500,
        ],
    ]);
    return;
} finally {
    curl_close($ch);
}

http_response_code(201);
echo json_encode(['error' => null]);