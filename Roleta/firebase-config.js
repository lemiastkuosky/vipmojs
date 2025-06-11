// firebase-config.js

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyAuqQMm02zxOlSZXUi5gB0IslDFB2m09Mk",
  authDomain: "roleta-a71d3.firebaseapp.com",
  projectId: "roleta-a71d3",
  storageBucket: "roleta-a71d3.firebasestorage.app",
  messagingSenderId: "235181323757",
  appId: "1:235181323757:web:e1ea6de39fff7f4ac4a3e4",
  measurementId: "G-ELHXPLD7W6"
};

// --- Bloco de Inicialização Robusto ---
// Usamos 'var' para garantir que as variáveis sejam globais entre os scripts.
var auth;
var db;

try {
  // Inicializa o app do Firebase
  firebase.initializeApp(firebaseConfig);
  
  // Atribui os serviços às nossas variáveis
  auth = firebase.auth();
  db = firebase.firestore();

  // Mensagem de sucesso no console para sabermos que tudo correu bem
  console.log("Firebase foi configurado e inicializado com sucesso!");

} catch (e) {
  // Se qualquer coisa der errado aqui (ex: chaves incorretas), veremos um erro claro.
  console.error("ERRO CRÍTICO NA CONFIGURAÇÃO DO FIREBASE:", e);
  alert("Erro fatal na configuração do Firebase. Verifique o console (F12) para mais detalhes. O app não irá funcionar.");
}