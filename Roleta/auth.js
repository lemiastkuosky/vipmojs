// auth.js

document.addEventListener('DOMContentLoaded', () => {
    const btnCadastrar = document.getElementById('btn-cadastrar');
    const btnLogin = document.getElementById('btn-login');

    // Lógica para a página de CADASTRO
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            if (!email || !senha) {
                alert("Por favor, preencha todos os campos.");
                return;
            }

            // Agora a variável 'auth' vai existir e esta função será chamada corretamente
            auth.createUserWithEmailAndPassword(email, senha)
                .then((userCredential) => {
                    alert("Conta criada com sucesso! Você será redirecionado para o login.");
                    window.location.href = 'login.html';
                })
                .catch((error) => {
                    alert(`Erro ao criar conta: ${error.message}`);
                });
        });
    }

    // Lógica para a página de LOGIN
    if (btnLogin) {
        btnLogin.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            if (!email || !senha) {
                alert("Por favor, preencha todos os campos.");
                return;
            }
            
            // Agora a variável 'auth' vai existir e esta função será chamada corretamente
            auth.signInWithEmailAndPassword(email, senha)
                .then((userCredential) => {
                    alert("Login efetuado com sucesso!");
                    window.location.href = 'index.html';
                })
                .catch((error) => {
                    alert(`Erro ao fazer login: ${error.message}`);
                });
        });
    }
});