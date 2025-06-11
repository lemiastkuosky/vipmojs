// auth.js (Versão Corrigida para Salvar Dados do Usuário)

document.addEventListener('DOMContentLoaded', () => {

    const handleAuth = (type, email, senha) => {
        if (!email || !senha) {
            Swal.fire({ icon: 'warning', title: 'Atenção!', text: 'Por favor, preencha todos os campos.' });
            return;
        }

        if (type === 'cadastro') {
            auth.createUserWithEmailAndPassword(email, senha)
                .then((userCredential) => {
                    const user = userCredential.user;
                    // ETAPA CRÍTICA: Cria o documento do usuário no Firestore
                    db.collection('users').doc(user.uid).set({
                        email: user.email,
                        role: 'user',        // Define um cargo padrão
                        limiteGiros: 5,      // Define um limite de giros padrão
                        girosHoje: 0
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Conta criada com sucesso! Você será redirecionado para o login.'
                        }).then(() => {
                            window.location.href = 'login.html';
                        });
                    }).catch(dbError => {
                         Swal.fire({ icon: 'error', title: 'Erro de Banco de Dados', text: `Sua conta foi criada, mas houve um erro ao salvar seu perfil: ${dbError.message}` });
                    });
                })
                .catch((error) => {
                    Swal.fire({ icon: 'error', title: 'Erro no Cadastro!', text: `Ocorreu um erro: ${error.message}` });
                });
        } else { // type === 'login'
            auth.signInWithEmailAndPassword(email, senha)
                .then(() => {
                    window.location.href = 'index.html';
                })
                .catch((error) => {
                    Swal.fire({ icon: 'error', title: 'Erro no Login!', text: `Ocorreu um erro: ${error.message}` });
                });
        }
    };

    // --- Event Listeners (não mudam) ---
    const btnCadastrar = document.getElementById('btn-cadastrar');
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            handleAuth('cadastro', email, senha);
        });
    }

    const btnLogin = document.getElementById('btn-login');
    if (btnLogin) {
        btnLogin.addEventListener('click', () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            handleAuth('login', email, senha);
        });
    }
});