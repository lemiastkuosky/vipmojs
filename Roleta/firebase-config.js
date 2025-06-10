// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

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

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);