<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["status" => 405, "message" => "Método não permitido"]);
    exit;
}

if (!isset($_GET["cpf"]) || empty($_GET["cpf"])) {
    echo json_encode(["status" => 400, "message" => "CPF é obrigatório"]);
    exit;
}

$cpf = preg_replace("/\D/", "", $_GET["cpf"]);

if (strlen($cpf) !== 11) {
    echo json_encode(["status" => 400, "message" => "CPF inválido"]);
    exit;
}

$api_url = "https://apela-api.tech?user=c2af4c30-ed08-4672-9b8a-f172ca2880cd&cpf=" . urlencode($cpf);
$response = file_get_contents($api_url);

if ($response === false) {
    echo json_encode(["status" => 500, "message" => "Erro ao se conectar à API"]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data["status"]) || $data["status"] !== 200) {
    echo json_encode(["status" => 400, "message" => "CPF não encontrado"]);
    exit;
}

$_SESSION['nome'] = $data['nome'];
$_SESSION['cpf'] = $data['cpf'];

echo json_encode($data);
