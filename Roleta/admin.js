document.addEventListener('DOMContentLoaded', () => {
    const userEmailSpan = document.getElementById('user-email');
    let foundUserId = null;

    // --- ELEMENTOS DO DOM ---
    const searchUserInput = document.getElementById('search-user-input');
    const searchUserBtn = document.getElementById('search-user-btn');
    const userDetailsDiv = document.getElementById('user-details');
    const foundUserEmailSpan = document.getElementById('found-user-email');
    const userSpinsInput = document.getElementById('user-spins-input');
    const updateSpinsBtn = document.getElementById('update-spins-btn');
    const adminPrizeList = document.getElementById('admin-prize-list');
    const updateWeightsBtn = document.getElementById('update-weights-btn');
    const globalHistoryTableBody = document.querySelector('#global-history-table tbody');

    // --- GUARDA DE AUTENTICAÇÃO E AUTORIZAÇÃO ---
    auth.onAuthStateChanged(async user => {
        if (user) {
            const userDoc = await db.collection('users').doc(user.uid).get();
            if (!userDoc.exists || userDoc.data().role !== 'admin') {
                Swal.fire('Acesso Negado', 'Você não tem permissão para acessar esta página.', 'error')
                   .then(() => window.location.href = 'index.html');
            } else {
                // Se for admin, carrega os dados da página
                userEmailSpan.textContent = `Admin: ${user.email}`;
                loadPrizes();
                loadGlobalHistory();
            }
        } else {
            window.location.href = 'login.html';
        }
    });

    // --- GERENCIAMENTO DE USUÁRIOS ---
    searchUserBtn.addEventListener('click', async () => {
        const email = searchUserInput.value.trim();
        if (!email) return;

        const userQuery = await db.collection('users').where('email', '==', email).limit(1).get();
        if (userQuery.empty) {
            Swal.fire('Erro', 'Usuário não encontrado.', 'error');
            userDetailsDiv.style.display = 'none';
        } else {
            const userDoc = userQuery.docs[0];
            foundUserId = userDoc.id;
            const userData = userDoc.data();
            
            foundUserEmailSpan.textContent = userData.email;
            userSpinsInput.value = userData.limiteGiros || 5; // Usa 5 como padrão se não existir
            userDetailsDiv.style.display = 'block';
        }
    });

    updateSpinsBtn.addEventListener('click', async () => {
        if (!foundUserId) return;
        const newLimit = parseInt(userSpinsInput.value);
        if (isNaN(newLimit) || newLimit < 0) {
            Swal.fire('Erro', 'Por favor, insira um número válido.', 'error');
            return;
        }

        try {
            await db.collection('users').doc(foundUserId).update({ limiteGiros: newLimit });
            Swal.fire('Sucesso!', 'Limite de giros atualizado.', 'success');
        } catch (error) {
            Swal.fire('Erro', 'Não foi possível atualizar o limite de giros.', 'error');
            console.error(error);
        }
    });

    // --- GERENCIAMENTO DE PRÊMIOS E PESOS ---
    async function loadPrizes() {
        const snapshot = await db.collection('premios').get();
        adminPrizeList.innerHTML = '';
        snapshot.forEach(doc => {
            const prize = doc.data();
            const li = document.createElement('li');
            li.dataset.id = doc.id;
            li.innerHTML = `
                <span>${prize.name}</span>
                <input type="number" class="weight-input" value="${prize.peso || 10}" min="1">
            `;
            li.style.borderLeft = `5px solid ${prize.color}`;
            adminPrizeList.appendChild(li);
        });
    }

    updateWeightsBtn.addEventListener('click', async () => {
        const updates = [];
        const prizeItems = adminPrizeList.querySelectorAll('li');

        prizeItems.forEach(item => {
            const id = item.dataset.id;
            const newWeight = parseInt(item.querySelector('.weight-input').value);
            if (id && !isNaN(newWeight) && newWeight > 0) {
                const promise = db.collection('premios').doc(id).update({ peso: newWeight });
                updates.push(promise);
            }
        });

        try {
            await Promise.all(updates);
            Swal.fire('Sucesso!', 'Os pesos de todos os prêmios foram atualizados.', 'success');
        } catch (error) {
            Swal.fire('Erro', 'Ocorreu um problema ao atualizar os pesos.', 'error');
            console.error(error);
        }
    });

    // --- HISTÓRICO GLOBAL ---
    async function loadGlobalHistory() {
        globalHistoryTableBody.innerHTML = '';
        const snapshot = await db.collection('sorteios').orderBy('data', 'desc').limit(50).get();
        
        // Para evitar múltiplas leituras, primeiro coletamos todos os userIds únicos
        const userIds = [...new Set(snapshot.docs.map(doc => doc.data().userId))];
        const userEmails = {};
        for (const userId of userIds) {
            const userDoc = await db.collection('users').doc(userId).get();
            userEmails[userId] = userDoc.exists ? userDoc.data().email : 'Usuário Deletado';
        }

        snapshot.forEach(doc => {
            const sorteio = doc.data();
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${userEmails[sorteio.userId]}</td>
                <td>${sorteio.premio}</td>
                <td>${sorteio.data.toDate().toLocaleString('pt-BR')}</td>
            `;
            globalHistoryTableBody.appendChild(tr);
        });
    }
});