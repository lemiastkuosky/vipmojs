module.exports = async (req, res) => {
  return res.status(200).json({
    status: "Sobrevivemos!",
    mensagem: "O problema é que a Vercel não está conseguindo achar o Axios e o Cheerio."
  });
};
