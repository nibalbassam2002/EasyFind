importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

const firebaseConfig = {

  apiKey: "AIzaSyABOQOeenk68g5swkqpiDmAHnso87Twvo0",

  authDomain: "easyfind-realestate.firebaseapp.com",

  projectId: "easyfind-realestate",

  storageBucket: "easyfind-realestate.firebasestorage.app",

  messagingSenderId: "1027579634327",

  appId: "1:1027579634327:web:9717b0d643a803a63bbfd2",

  measurementId: "G-CLBV7E2018"

};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: 'assets/img/logo for tab.png' 
  };
  self.registration.showNotification(notificationTitle, notificationOptions);
});