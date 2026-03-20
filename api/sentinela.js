export default function handler(req, res) {
  return res.status(200).json({ 
    status: "Vercel está viva!", 
    mensagem: "O problema era as importações do arquivo antigo." 
  });
}