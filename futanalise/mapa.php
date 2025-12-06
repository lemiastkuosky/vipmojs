<!DOCTYPE html>
<html lang="pt-br">
<body style="font-family: monospace; padding: 20px;">
    <h2>🗺️ Mapa das Colunas do CSV</h2>
    <?php
    $arquivos = glob("*.csv");
    if (count($arquivos) > 0) {
        $arquivo = $arquivos[0]; // Pega o primeiro arquivo
        echo "<p>Lendo arquivo: <strong>$arquivo</strong></p>";
        
        if (($handle = fopen($arquivo, "r")) !== FALSE) {
            $cabecalho = fgetcsv($handle, 1000, ","); // Linha 1 (Nomes)
            $dados = fgetcsv($handle, 1000, ",");     // Linha 2 (Exemplo de dados)
            
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background:#ccc'><th>NÚMERO (Use este)</th><th>Nome da Coluna</th><th>Exemplo de Dado</th></tr>";
            
            foreach ($cabecalho as $indice => $nome) {
                $exemplo = isset($dados[$indice]) ? $dados[$indice] : '-';
                // Destaca as colunas prováveis
                $bg = "";
                if(stripos($nome, 'Date') !== false) $bg = "#e3f2fd"; // Azul claro
                if(stripos($nome, 'Team') !== false) $bg = "#c8e6c9"; // Verde claro
                if(stripos($nome, 'Goal') !== false || stripos($nome, 'HG') !== false || stripos($nome, 'AG') !== false) $bg = "#ffecb3"; // Amarelo
                
                echo "<tr style='background: $bg'>";
                echo "<td style='font-size: 20px; font-weight: bold; text-align: center; color: red;'>[$indice]</td>";
                echo "<td>$nome</td>";
                echo "<td>$exemplo</td>";
                echo "</tr>";
            }
            echo "</table>";
            fclose($handle);
        }
    } else {
        echo "Nenhum arquivo .csv encontrado.";
    }
    ?>
</body>
</html>