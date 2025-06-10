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

            auth.createUserWithEmailAndPassword(email, senha)
                .then((userCredential) => {
                    // Cadastro bem-sucedido
                    alert("Conta criada com sucesso! Você será redirecionado para o login.");
                    window.location.href = 'login.html';
                })
                .catch((error) => {
                    // Trata erros (ex: senha fraca, e-mail já em uso)
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

            auth.signInWithEmailAndPassword(email, senha)
                .then((userCredential) => {
                    // Login bem-sucedido
                    alert("Login efetuado com sucesso!");
                    window.location.href = 'index.html'; // Redireciona para a roleta
                })
                .catch((error) => {
                    // Trata erros (ex: senha incorreta, usuário não encontrado)
                    alert(`Erro ao fazer login: ${error.message}`);
                });
        });
    }
});