<?php
// Define o cabeçalho como JSON para a resposta
header('Content-Type: application/json');

// Pasta onde as imagens serão salvas
$pasta_imagens = 'imagens/';

// Recebe os dados da imagem (enviados via POST pelo JavaScript)
$dados_imagem = $_POST['imagem'];

// Validação básica
if (!isset($dados_imagem)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado de imagem recebido.']);
    exit;
}

// Remove o cabeçalho 'data:image/png;base64,' para obter apenas os dados puros
list($type, $dados_imagem) = explode(';', $dados_imagem);
list(, $dados_imagem)      = explode(',', $dados_imagem);
$dados_imagem = base64_decode($dados_imagem);

// Cria um nome de arquivo único para evitar sobreposições
$nome_arquivo = uniqid() . '.png';
$caminho_completo = $pasta_imagens . $nome_arquivo;

// Salva o arquivo no servidor
if (file_put_contents($caminho_completo, $dados_imagem)) {
    // Determina o protocolo (http ou https)
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    // Monta a URL completa da imagem salva
    $url_imagem = $protocolo . $_SERVER['HTTP_HOST'] . '/' . $caminho_completo;
    
    // Envia a resposta de sucesso com a URL da imagem
    echo json_encode(['sucesso' => true, 'url' => $url_imagem]);
} else {
    // Envia uma resposta de erro se não conseguiu salvar
    echo json_encode(['sucesso' => false, 'erro' => 'Falha ao salvar a imagem no servidor. Verifique as permissões da pasta.']);
}
?>