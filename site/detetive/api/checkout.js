// api/checkout.js

export default async function handler(req, res) {
    // 1. Configuração de CORS (Para aceitar requisição do seu HTML)
    res.setHeader('Access-Control-Allow-Credentials', true);
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
    res.setHeader(
        'Access-Control-Allow-Headers',
        'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version'
    );

    // Responde ao "Preflight" do navegador
    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    if (req.method !== 'POST') {
        return res.status(405).json({ sucesso: false, mensagem: 'Método não permitido.' });
    }

    try {
        const dados = req.body;

        // --- DADOS DA YAMPI ---
        const ALIAS = "store39";
        const TOKEN = "YuxLCumxFftaA1LlGzdInxFPqpLYnrODvL0BMHnG";
        const SECRET_KEY = "sk_jGBmqP1AWTKsxHlNtrVOmjVCGrkske08M5X6F";
        const API_URL = "https://api.yampi.com.br/v1/orders";

        // Formatação simples
        const cpfLimpo = dados.cpf.replace(/\D/g, '');
        const telLimpo = dados.telefone.replace(/\D/g, '');

        // 2. Monta o Payload
        const payload = {
            customer: {
                name: dados.nome,
                email: dados.email,
                cpf: cpfLimpo,
                phone: {
                    number: telLimpo.substring(2),
                    area_code: telLimpo.substring(0, 2)
                }
            },
            shipping: {
                price: 0,
                delivery_days: 1,
                address: {
                    zipcode: "01001000",
                    street: "Entrega Digital",
                    number: "1",
                    neighborhood: "Digital",
                    city: "São Paulo",
                    state: "SP",
                    country: "BR"
                }
            },
            items: [
                {
                    sku: "PROD-FORENSE-001",
                    title: "Pacote Mente Forense Premium",
                    quantity: 1,
                    price: 19.99
                }
            ],
            payments: []
        };

        // Adiciona Pagamento
        if (dados.metodo === 'pix') {
            payload.payments.push({ payment_method: "pix" });
        } else {
            payload.payments.push({
                payment_method: "credit_card",
                credit_card: {
                    number: dados.cartao.numero.replace(/\s/g, ''),
                    holder_name: dados.cartao.nome,
                    expiration_month: dados.cartao.validade.split('/')[0],
                    expiration_year: "20" + dados.cartao.validade.split('/')[1],
                    cvv: dados.cartao.cvv,
                    installments: parseInt(dados.cartao.parcelas)
                }
            });
        }

        // 3. Envia para Yampi (Fetch Nativo do Node.js)
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Alias': ALIAS,
                'Token': TOKEN,
                'Key': SECRET_KEY
            },
            body: JSON.stringify(payload)
        });

        const resYampi = await response.json();

        // 4. Retorna para o HTML
        if (response.ok) { // Status 200-299
            const retorno = { sucesso: true, metodo: dados.metodo };
            
            if (dados.metodo === 'pix') {
                const transaction = resYampi.data?.resource?.transactions?.[0];
                retorno.qrcode_imagem = transaction?.pix_qrcode_url || '';
                retorno.qrcode_copicola = transaction?.pix_qrcode_text || '';
            } else {
                retorno.redirect_url = resYampi.data?.resource?.checkout_url || "obrigado.html";
            }
            
            return res.status(200).json(retorno);
        } else {
            // Erro da Yampi
            const msgErro = resYampi.errors?.[0]?.message || resYampi.message || 'Erro no processamento';
            return res.status(400).json({ sucesso: false, mensagem: msgErro, debug: resYampi });
        }

    } catch (error) {
        console.error(error);
        return res.status(500).json({ sucesso: false, mensagem: 'Erro interno no servidor Vercel.' });
    }
}