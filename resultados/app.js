// --- PASSO 1: CONFIGURAÇÃO DO FIREBASE ---
// Seus dados de configuração (fornecidos por você)
const firebaseConfig = {
  apiKey: "AIzaSyB7tyWt4UlivjnJVevkXDZ1wwgPrzV1hvc",
  authDomain: "meus-recibos-vip.firebaseapp.com",
  databaseURL: "https://meus-recibos-vip-default-rtdb.firebaseio.com",
  projectId: "meus-recibos-vip",
  storageBucket: "meus-recibos-vip.firebasestorage.app",
  messagingSenderId: "254959368683",
  appId: "1:254959368683:web:80676a26f614040df6c1ae"
};

// Inicializa o Firebase
firebase.initializeApp(firebaseConfig);

// Inicializa o Realtime Database
const database = firebase.database();

// --- PASSO 2: BUSCAR E EXIBIR OS DADOS ---

// Seleciona o container no HTML onde os resultados vão entrar
const resultadosContainer = document.getElementById('resultados-container');

/* // --- MUDE AQUI (1) --- 
   Substitua 'resultados' pelo nome real do "nó" (o caminho) 
   onde seus dados estão salvos no Realtime Database.
   Ex: Se seus dados estão em "extracao/dia_1", o caminho seria 'extracao/dia_1'
*/
const resultadosRef = database.ref('resultados');

// Ouve os dados uma única vez (usando 'once')
// Isso vai "ler" os dados do seu banco assim que a página carregar
resultadosRef.once('value', (snapshot) => {
    
    // Limpa o container (remove o "Carregando resultados...")
    resultadosContainer.innerHTML = "";

    const dados = snapshot.val();

    // Verifica se o 'nó' está vazio ou não existe
    if (!dados) {
        resultadosContainer.innerHTML = "<p>Nenhum resultado encontrado.</p>";
        console.warn("Nenhum dado encontrado no nó: 'resultados'"); // Aviso para o console
        return;
    }

    // O Realtime Database retorna um objeto (chave: valor)
    // Vamos percorrer cada item (cada "chave") dentro desse objeto.
    Object.keys(dados).forEach((key) => {
        
        // 'resultado' é o objeto individual (ex: um resultado específico)
        const resultado = dados[key]; 

        // --- Crie seu HTML dinâmico aqui ---
        const divResultado = document.createElement('div');
        divResultado.classList.add('resultado-item'); // Adiciona a classe CSS 'resultado-item'
        
        /* // --- MUDE AQUI (2) --- 
           Substitua 'resultado.titulo', 'resultado.dataExtracao' e 'resultado.numeros'
           pelos nomes REAIS dos campos que você tem no seu banco de dados.
        */
        
        // Exemplo de como montar o HTML (AJUSTE CONFORME SEUS DADOS)
        divResultado.innerHTML = `
            <h3>${resultado.titulo || 'Resultado Sem Título'}</h3>
            <p>Data: ${resultado.dataExtracao || 'Data não informada'}</p>
            <strong>Números: ${resultado.numeros ? resultado.numeros.join(', ') : 'N/A'}</strong>
        `;
        
        // Adiciona o novo elemento <div> criado ao container na página
        resultadosContainer.appendChild(divResultado);
    });

}, (errorObject) => {
    // Isso será executado se houver um erro de permissão ou de rede
    console.error("A leitura do Firebase falhou: " + errorObject.name);
    resultadosContainer.innerHTML = "<p>Erro ao carregar os resultados. Verifique o console.</p>";
});