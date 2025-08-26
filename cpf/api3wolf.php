<?php
session_start();
header("Content-Type: application/json");

// Permitir apenas GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["status" => 405, "message" => "Método não permitido"]);
    exit;
}

// Verificar CPF
if (!isset($_GET["cpf"]) || empty($_GET["cpf"])) {
    echo json_encode(["status" => 400, "message" => "CPF é obrigatório"]);
    exit;
}

$cpf = preg_replace("/\D/", "", $_GET["cpf"]);

if (strlen($cpf) !== 11) {
    echo json_encode(["status" => 400, "message" => "CPF inválido"]);
    exit;
}

// URL da API
$api_url = "https://apela.tech?user=ff287045-51cb-4539-bc32-77ac4c3089f1&cpf=" . urlencode($cpf);

// Usar cURL (mais seguro e confiável que file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout de 10s

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["status" => 500, "message" => "Erro ao conectar com API"]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(["status" => 500, "message" => "API retornou erro"]);
    exit;
}

// Decodificar JSON
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => 500, "message" => "Erro ao processar resposta da API"]);
    exit;
}

// Validar se veio nome/cpf
if (!isset($data['cpf']) || !isset($data['nome'])) {
    echo json_encode(["status" => 400, "message" => "CPF não encontrado"]);
    exit;
}

// Salvar sessão
$_SESSION['nome'] = $data['nome'];
$_SESSION['cpf']  = $data['cpf'];

// Retornar resultado
echo json_encode([
    "status" => 200,
    "nome"   => $data['nome'],
    "cpf"    => $data['cpf']
]);
