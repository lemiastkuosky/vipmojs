<?php
// --- INÍCIO DA LÓGICA PHP ---

// Definir variáveis iniciais
$resultado_texto = "";
$link_whatsapp = "";

// number_format para formatar como moeda BRL
function formatar_brl($valor) {
    return "R$ " . number_format($valor, 2, ',', '.');
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Coletar dados do formulário
    $valor_total = (float)$_POST['valor_total'];
    $modo_calculo = $_POST['modo_calculo'];
    
    // O 'valor_regra' pode ser R$ ou %
    $valor_regra = (float)$_POST['valor_regra']; 
    
    // Quantas pessoas vão dividir o "resto"
    $numero_demais = (int)$_POST['numero_demais']; 

    $premio_1_lugar = 0;
    $premio_demais = 0;
    $valor_restante = 0;
    
    $texto_calculo = ""; // Texto para o resumo

    // 2. Fazer o cálculo baseado no MODO
    switch ($modo_calculo) {
        
        // --- MODO FIXO ---
        case 'fixo':
            $premio_1_lugar = $valor_regra;
            $valor_restante = $valor_total - $premio_1_lugar;
            
            if ($numero_demais > 0) {
                $premio_demais = $valor_restante / $numero_demais;
            }
            
            $texto_calculo = "Modo: Valor Fixo\n" .
                             "Prêmio Total: " . formatar_brl($valor_total) . "\n" .
                             "1º Lugar (Fixo): " . formatar_brl($premio_1_lugar) . "\n" .
                             "Restante: " . formatar_brl($valor_restante) . " (dividido por $numero_demais pessoas)\n\n" .
                             "--------------------\n" .
                             "Resultado:\n" .
                             "🏆 1º Lugar: " . formatar_brl($premio_1_lugar) . "\n" .
                             "👥 Demais: " . formatar_brl($premio_demais) . " (para cada)";
            break;

        // --- MODO PORCENTAGEM ---
        case 'porcentagem':
            $premio_1_lugar = $valor_total * ($valor_regra / 100);
            $valor_restante = $valor_total - $premio_1_lugar;

            if ($numero_demais > 0) {
                $premio_demais = $valor_restante / $numero_demais;
            }
            
            $texto_calculo = "Modo: Porcentagem\n" .
                             "Prêmio Total: " . formatar_brl($valor_total) . "\n" .
                             "1º Lugar ($valor_regra%): " . formatar_brl($premio_1_lugar) . "\n" .
                             "Restante: " . formatar_brl($valor_restante) . " (dividido por $numero_demais pessoas)\n\n" .
                             "--------------------\n" .
                             "Resultado:\n" .
                             "🏆 1º Lugar: " . formatar_brl($premio_1_lugar) . "\n" .
                             "👥 Demais: " . formatar_brl($premio_demais) . " (para cada)";
            break;

        // --- MODO DIVIDIDO (Simples) ---
        case 'dividido':
            // Ignora o "valor_regra" e "numero_demais"
            // Pega o número total de pessoas para dividir
            $total_pessoas = (int)$_POST['total_pessoas_dividido'];
            
            if ($total_pessoas > 0) {
                $premio_demais = $valor_total / $total_pessoas;
            }
            
            $texto_calculo = "Modo: Divisão Simples\n" .
                             "Prêmio Total: " . formatar_brl($valor_total) . "\n" .
                             "Dividido por: $total_pessoas pessoas\n\n" .
                             "--------------------\n" .
                             "Resultado:\n" .
                             "💰 Valor p/ cada: " . formatar_brl($premio_demais);
            break;
    }

    // 3. Preparar o texto e o link do WhatsApp
    // A var $texto_calculo já está formatada
    
    // O PHP_EOL (End of Line) garante a quebra de linha no WhatsApp
    $texto_whatsapp_formatado = "🎉 *PREMIAÇÃO CALCULADA* 🎉" . PHP_EOL . PHP_EOL;
    $texto_whatsapp_formatado .= str_replace("\n", PHP_EOL, $texto_calculo);

    $resultado_texto = nl2br($texto_whatsapp_formatado); // Converte quebras de linha para <br> para exibir no HTML
    
    // Codifica o texto para uma URL
    $link_whatsapp = "https://api.whatsapp.com/send?text=" . urlencode($texto_whatsapp_formatado);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Premiação</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="number"], .form-group input[type="text"] { width: 95%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group input[type="radio"] { margin-right: 5px; }
        .form-group .radio-label { font-weight: normal; }
        .botao { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        .botao-wpp { display: block; text-align: center; background: #25D366; color: white; padding: 12px 20px; border-radius: 4px; text-decoration: none; font-size: 16px; margin-top: 20px; }
        .resultado { margin-top: 20px; padding: 15px; background: #e9f7ef; border: 1px solid #b6dccb; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Calculadora de Premiação</h2>
        
        <form action="" method="POST">
            
            <div class="form-group">
                <label for="valor_total">Valor Total do Prêmio (R$)</label>
                <input type="number" step="0.01" id="valor_total" name="valor_total" required>
            </div>

            <hr>
            
            <div class="form-group">
                <label>Modo de Cálculo:</label>
                <input type="radio" id="modo_fixo" name="modo_calculo" value="fixo" checked>
                <label for="modo_fixo" class="radio-label">1º Lugar (Valor Fixo)</label><br>
                
                <input type="radio" id="modo_porcentagem" name="modo_calculo" value="porcentagem">
                <label for="modo_porcentagem" class="radio-label">1º Lugar (Porcentagem %)</label><br>
                
                <input type="radio" id="modo_dividido" name="modo_calculo" value="dividido">
                <label for="modo_dividido" class="radio-label">Divisão Simples (Valor igual p/ todos)</label>
            </div>

            <hr>

            <div id="campos_1_lugar">
                <div class="form-group">
                    <label for="valor_regra">Valor da Regra (R$ ou %)</label>
                    <input type="number" step="0.01" id="valor_regra" name="valor_regra" placeholder="Ex: 100 (para R$) ou 40 (para %)">
                </div>

                <div class="form-group">
                    <label for="numero_demais">Nº de Pessoas (Restante)</label>
                    <input type="number" id="numero_demais" name="numero_demais" placeholder="Ex: 10">
                </div>
            </div>

            <div id="campos_dividido" style="display:none;">
                <div class="form-group">
                    <label for="total_pessoas_dividido">Dividir por quantas pessoas?</label>
                    <input type="number" id="total_pessoas_dividido" name="total_pessoas_dividido" placeholder="Ex: 11">
                </div>
            </div>

            <button type="submit" class="botao">Calcular</button>
        </form>

        <?php if (!empty($resultado_texto)): ?>
            <div class="resultado">
                <h3>Resumo do Cálculo:</h3>
                <p><?php echo $resultado_texto; ?></p>
                
                <a href="<?php echo $link_whatsapp; ?>" target="_blank" class="botao-wpp">
                    🚀 Enviar para o Grupo do WhatsApp
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Seleciona os inputs de rádio
        const radios = document.querySelectorAll('input[name="modo_calculo"]');
        
        // Seleciona os 'blocos' de inputs
        const campos1Lugar = document.getElementById('campos_1_lugar');
        const camposDividido = document.getElementById('campos_dividido');

        // Função para checar qual rádio está marcado
        function checarModo() {
            const modo = document.querySelector('input[name="modo_calculo"]:checked').value;
            
            if (modo === 'dividido') {
                campos1Lugar.style.display = 'none';
                camposDividido.style.display = 'block';
            } else {
                campos1Lugar.style.display = 'block';
                camposDividido.style.display = 'none';
            }
        }

        // Adiciona o 'ouvinte' para cada botão de rádio
        radios.forEach(radio => {
            radio.addEventListener('change', checarModo);
        });

        // Roda a função uma vez no início para acertar os campos
        checarModo();
    </script>

</body>
</html>