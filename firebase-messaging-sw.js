importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyDudo9LHec77kb6BJuhx8YiFU2FCHH1mIw",
    authDomain: "fleetmove-7a7db.firebaseapp.com",
    projectId: "fleetmove-7a7db",
    storageBucket: "fleetmove-7a7db.firebasestorage.app",
    messagingSenderId: "976133876016",
    appId: "1:976133876016:web:bbae2caf1a60c6c20e40f5",
    measurementId: "G-6D94HZP69X"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});