document.addEventListener('DOMContentLoaded', () => {
    const handleAuth = (authFunction, email, senha) => {
        if (!email || !senha) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Por favor, preencha todos os campos.'
            });
            return;
        }

        authFunction(email, senha)
            .then((userCredential) => {
                if (authFunction === auth.createUserWithEmailAndPassword) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Conta criada com sucesso! Você será redirecionado para o login.'
                    }).then(() => {
                        window.location.href = 'login.html';
                    });
                } else {
                    // O redirecionamento no login é tratado pelo onAuthStateChanged no index.html
                    window.location.href = 'index.html';
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message
                });
            });
    };

    const btnCadastrar = document.getElementById('btn-cadastrar');
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            handleAuth(auth.createUserWithEmailAndPassword, email, senha);
        });
    }

    const btnLogin = document.getElementById('btn-login');
    if (btnLogin) {
        btnLogin.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            handleAuth(auth.signInWithEmailAndPassword, email, senha);
        });
    }
});