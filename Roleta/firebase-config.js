// firebase-config.js

// ATENÇÃO: Cole aqui o objeto de configuração que você pegou do site do Firebase.
// As chaves abaixo são apenas um exemplo. Use as SUAS chaves.
const firebaseConfig = {
  apiKey: "AIzaSy...xxxxxxxxxxxxxxxxxxx",
  authDomain: "seu-projeto.firebaseapp.com",
  projectId: "seu-projeto",
  storageBucket: "seu-projeto.appspot.com",
  messagingSenderId: "1234567890",
  appId: "1:1234567890:web:xxxxxxxxxxxx"
};

// Inicializa o Firebase com a configuração acima.
firebase.initializeApp(firebaseConfig);

// Cria a variável 'auth' que será usada em outros scripts.
// Esta linha é muito importante!
const auth = firebase.auth();