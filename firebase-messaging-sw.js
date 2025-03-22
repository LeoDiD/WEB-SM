console.log("Service Worker loaded");

importScripts("https://www.gstatic.com/firebasejs/10.5.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/10.5.0/firebase-messaging.js");

console.log("Firebase scripts imported");

const firebaseConfig = {
  apiKey: "AIzaSyAs-ClO9OzNf_XOfkTi0t9iqVvfuJLJ2xI",
  authDomain: "ezmart-f178a.firebaseapp.com",
  projectId: "ezmart-f178a",
  storageBucket: "ezmart-f178a.firebasestorage.app",
  messagingSenderId: "294979806864",
  appId: "1:294979806864:web:9533787bf48f62c78ada15",
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

console.log("Firebase initialized");

messaging.onBackgroundMessage((payload) => {
  console.log("Background message received:", payload);

  const notificationTitle = payload.notification?.title || "New Notification";
  const notificationOptions = {
    body: payload.notification?.body || "You have a new message.",
    icon: payload.notification?.icon || "/default-icon.png",
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});