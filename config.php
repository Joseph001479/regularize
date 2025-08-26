<?php
// config_zeroonepay.php
// CONFIGURAÇÕES ESPECÍFICAS PARA ZEROONEPAY

// URL Base da API ZeroOnePay (CONFORME SUA DOCUMENTAÇÃO)
define('URL_API_BASE', 'https://pay.zeroonepay.com.br/api/v1/');

// SUA SECRET KEY DA ZEROONEPAY (do projeto "greee")
// NUNCA a compartilhe ou exponha publicamente.
define('ZEROONEPAY_SECRET_KEY', '169eb841-ddc5-4497-8182-d0e98a80c813');

// Informações do Cliente (Exemplo, você pode mudar para dinâmico)
define('CLIENTE_NOME', 'Nome do Cliente');
define('CLIENTE_EMAIL', 'email@cliente.com');
define('CLIENTE_PHONE', '11999999999');
define('CLIENTE_DOCUMENTO', '12345678900');

// Valor da transação (em centavos)
define('GTW_VALOR_PIX', 10000); // R$ 100.00 (100 * 100 centavos)

// Função para verificar status do Pix na ZEROONEPAY
function verificar_status_pix($transactionId)
{
    // Monta a URL de consulta específica da ZeroOnePay
    $url_consulta = URL_API_BASE . "transaction.getPayment?id=" . urlencode($transactionId);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url_consulta,
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            'Authorization: ' . ZEROONEPAY_SECRETKEY // Autenticação por Secret Key
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response && $http_code == 200) {
        $data = json_decode($response, true);
        // Retorna o status conforme a resposta da ZeroOnePay (ex: "PENDING", "APPROVED")
        return $data['status'] ?? 'desconhecido';
    }

    // Em caso de erro na consulta
    return 'erro';
}

// Função para CRIAR um pagamento PIX na ZeroOnePay
// (Você precisará implementar a chamada para essa função no seu fluxo)
function criar_pagamento_pix_zeroonepay($dadosCliente) {
    $url = URL_API_BASE . 'transaction.purchase';

    // Monta o payload EXATAMENTE como a documentação da ZeroOnePay exige
    $data = [
        "name" => $dadosCliente['nome'],
        "email" => $dadosCliente['email'],
        "cpf" => $dadosCliente['documento'],
        "phone" => $dadosCliente['telefone'],
        "paymentMethod" => "PIX",
        "amount" => $dadosCliente['valor'], // Valor em centavos
        "traceable" => true,
        "items" => [[
            "unitPrice" => $dadosCliente['valor'],
            "title" => "Pagamento de Serviço",
            "quantity" => 1,
            "tangible" => false
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: " . ZEROONEPAY_SECRET_KEY
        ]
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $http_code,
        'response' => json_decode($response, true)
    ];
}
?>