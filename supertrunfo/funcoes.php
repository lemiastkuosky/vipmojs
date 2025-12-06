<?php
// funcoes.php

// Inicia um novo jogo
function iniciarJogo() {
    $todos_carros = getBaralho();
    shuffle($todos_carros);
    $metade = count($todos_carros) / 2;
    
    $_SESSION['baralho_jogador'] = array_slice($todos_carros, 0, $metade);
    $_SESSION['baralho_cpu'] = array_slice($todos_carros, $metade);
    $_SESSION['fase'] = 'escolha';
    $_SESSION['msg'] = "Sua vez! Escolha um atributo.";
}

// Compara os atributos e retorna quem venceu (true = jogador, false = cpu)
function processarRodada($atributo, $cartaJ, $cartaC) {
    $valJ = $cartaJ[$atributo];
    $valC = $cartaC[$atributo];

    // Regra especial: Aceleração (acc) vence o MENOR valor
    if ($atributo == 'acc') {
        return $valJ < $valC;
    }
    // Regra padrão: Vence o MAIOR valor
    return $valJ > $valC;
}

// Move as cartas para o final do baralho do vencedor
function moverCartas($venceuJogador, $cartaJ, $cartaC) {
    // Remove do topo
    array_shift($_SESSION['baralho_jogador']);
    array_shift($_SESSION['baralho_cpu']);

    if ($venceuJogador) {
        array_push($_SESSION['baralho_jogador'], $cartaJ, $cartaC);
        return "Você GANHOU esta rodada!";
    } else {
        array_push($_SESSION['baralho_cpu'], $cartaC, $cartaJ);
        return "A CPU ganhou esta rodada...";
    }
}