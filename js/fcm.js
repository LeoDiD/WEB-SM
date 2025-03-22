import { initializeApp } from "https://www.gstatic.com/firebasejs/10.5.0/firebase-app.js";
import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/10.5.0/firebase-messaging.js";

const firebaseConfig = {
  apiKey: "AIzaSyAs-ClO9OzNf_XOfkTi0t9iqVvfuJLJ2xI",
  authDomain: "ezmart-f178a.firebaseapp.com",
  projectId: "ezmart-f178a",
  storageBucket: "ezmart-f178a.firebasestorage.app",
  messagingSenderId: "294979806864",
  appId: "1:294979806864:web:9533787bf48f62c78ada15",
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

document.getElementById("enable-notifications").addEventListener("click", () => {
  Notification.requestPermission()
    .then((permission) => {
      if (permission === "granted") {
        console.log("Notification permission granted.");
        return navigator.serviceWorker.register("/WEB-SM/firebase-messaging-sw.js", {
          scope: "/WEB-SM/",
        });
      } else {
        throw new Error("Unable to get permission to notify.");
      }
    })
    .then((serviceWorkerRegistration) => {
      return getToken(messaging, {
        vapidKey: "BNN9CvV43cztNonUPfXnlqzt-T3dTSckJyFzpwNMISiFjHAijg2USSoJQoCsLL8BOzDXjyxQmiBS02diZQ9eh5w",
        serviceWorkerRegistration: serviceWorkerRegistration,
      });
    })
    .then((token) => {
      console.log("FCM Token:", token);
    })
    .catch((err) => {
      console.error("Error requesting notification permission:", err);
    });
});