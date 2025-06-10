// auth.js (Versão Corrigida e Refatorada)

document.addEventListener('DOMContentLoaded', () => {

    // A função agora recebe um 'tipo' ('login' ou 'cadastro')
    const handleAuth = (type, email, senha) => {
        if (!email || !senha) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Por favor, preencha todos os campos.'
            });
            return;
        }

        let authPromise;

        // Decidimos qual função chamar com base no tipo
        if (type === 'login') {
            authPromise = auth.signInWithEmailAndPassword(email, senha);
        } else { // type === 'cadastro'
            authPromise = auth.createUserWithEmailAndPassword(email, senha);
        }

        // O resto da lógica continua a mesma
        authPromise
            .then((userCredential) => {
                if (type === 'cadastro') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Conta criada com sucesso! Você será redirecionado para o login.'
                    }).then(() => {
                        window.location.href = 'login.html';
                    });
                } else { // type === 'login'
                    // No login, o redirecionamento já acontece automaticamente na página principal
                    window.location.href = 'index.html';
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: `Ocorreu um erro: ${error.message}` // Mensagem de erro mais clara
                });
            });
    };

    // --- Event Listeners Atualizados ---

    const btnCadastrar = document.getElementById('btn-cadastrar');
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            // Chamamos handleAuth com o tipo 'cadastro'
            handleAuth('cadastro', email, senha);
        });
    }

    const btnLogin = document.getElementById('btn-login');
    if (btnLogin) {
        btnLogin.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            // Chamamos handleAuth com o tipo 'login'
            handleAuth('login', email, senha);
        });
    }
});