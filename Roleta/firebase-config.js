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

// Inicializa o Firebase com a configuração acima.
firebase.initializeApp(firebaseConfig);

// Cria a variável 'auth' que será usada em outros scripts.
// Esta linha é muito importante!
const auth = firebase.auth();