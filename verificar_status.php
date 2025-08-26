<?php
require 'config.php'; // Arquivo com suas constantes e TOKEN_GATEWAY

if (!isset($_GET['transactionId'])) {
    echo json_encode(['status' => 'erro']);
    exit;
}

$transactionId = $_GET['transactionId'];
$status = verificar_status_pix($transactionId);
echo json_encode(['status' => $status]);
?>
