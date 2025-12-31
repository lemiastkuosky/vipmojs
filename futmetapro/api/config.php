<?php
// --- CONFIGURAÇÕES ---

// 1. Cole aqui a URL do seu Firebase (Mantenha o /teste.json no final para criar uma pasta de teste)
$firebase_url = 'https://futanalise-121f9-default-rtdb.firebaseio.com/teste.json';

// 2. Dados Fictícios para testar a gravação
$dados_teste = [
    'mensagem' => 'Olá, Firebase!',
    'horario' => date('Y-m-d H:i:s'),
    'status' => 'Conexão PHP -> Firebase funcionando perfeitamente.'
];

// --- ENVIO (CURL) ---

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebase_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // PUT cria ou substitui o dado
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para localhost, em produção ative o SSL

$resposta = curl_exec($ch);
$erro = curl_error($ch);
curl_close($ch);

// --- RESULTADO ---

if ($erro) {
    echo "❌ Erro ao conectar: " . $erro;
} else {
    echo "✅ Sucesso! O Firebase respondeu: <br>";
    echo "<pre>" . $resposta . "</pre>";
    echo "<br>Vá até o painel do Firebase e veja se o dado apareceu lá.";
}
?>