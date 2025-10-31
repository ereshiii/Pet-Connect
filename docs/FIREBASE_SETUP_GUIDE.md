# Firebase Cloud Messaging Setup Guide

## Overview
This guide explains how to set up Firebase Cloud Messaging (FCM) for push notifications in PetConnect. The infrastructure is ready but requires Firebase project configuration.

## Prerequisites
- Firebase console access
- Google account for Firebase
- PetConnect application domain

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Add project"
3. Enter project name: `petconnect-app`
4. Enable Google Analytics (optional)
5. Choose or create Analytics account
6. Click "Create project"

## Step 2: Add Web App to Firebase Project

1. In Firebase console, click "Add app" → Web
2. Register app:
   - App nickname: `PetConnect Web`
   - Firebase Hosting: Check if you plan to use Firebase hosting
3. Copy Firebase SDK configuration (firebaseConfig object)

## Step 3: Generate Service Account Key

1. Go to Project Settings → Service accounts
2. Click "Generate new private key"
3. Download the JSON file
4. Rename to `firebase-credentials.json`
5. Store securely (DO NOT commit to repository)

## Step 4: Configure Environment Variables

Add these variables to your `.env` file:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYOUR_PRIVATE_KEY\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxx@your-project.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_CLIENT_CERT_URL=https://www.googleapis.com/v1/metadata/x509/firebase-adminsdk-xxx%40your-project.iam.gserviceaccount.com

# Firebase Database (optional)
FIREBASE_DATABASE_URL=https://your-project-default-rtdb.firebaseio.com

# Firebase Storage (optional)
FIREBASE_STORAGE_BUCKET=your-project.appspot.com

# FCM Configuration
FCM_SERVER_KEY=your-server-key
FCM_SENDER_ID=your-sender-id
FCM_VAPID_KEY=your-vapid-key

# Web Push Configuration
FIREBASE_WEB_API_KEY=your-web-api-key
FIREBASE_WEB_AUTH_DOMAIN=your-project.firebaseapp.com
FIREBASE_WEB_PROJECT_ID=your-project-id
FIREBASE_WEB_STORAGE_BUCKET=your-project.appspot.com
FIREBASE_WEB_MESSAGING_SENDER_ID=your-sender-id
FIREBASE_WEB_APP_ID=your-app-id
```

## Step 5: Enable Cloud Messaging

1. In Firebase console, go to Cloud Messaging
2. Enable the Cloud Messaging API
3. Generate Web Push certificates:
   - Go to Project Settings → Cloud Messaging
   - In "Web configuration" section
   - Generate key pair for VAPID

## Step 6: Configure Web App for Push Notifications

1. Create `public/firebase-messaging-sw.js`:

```javascript
// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize Firebase
firebase.initializeApp({
  apiKey: "your-api-key",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "your-sender-id",
  appId: "your-app-id"
});

// Initialize Firebase Messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('Received background message: ', payload);
  
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/icon-192x192.png',
    badge: '/icon-72x72.png',
    data: payload.data
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
```

## Step 7: Security Considerations

1. **Store credentials securely:**
   - Add `firebase-credentials.json` to `.gitignore`
   - Use environment variables for sensitive data
   - Consider using Laravel Vault for production

2. **Configure Firebase Security Rules:**
   ```javascript
   // Firestore Security Rules
   rules_version = '2';
   service cloud.firestore {
     match /databases/{database}/documents {
       match /{document=**} {
         allow read, write: if request.auth != null;
       }
     }
   }
   ```

## Step 8: Testing Push Notifications

1. Run Laravel application
2. Register device token via frontend
3. Send test notification using Firebase console
4. Verify notification delivery

## Infrastructure Components

### Models Created
- `DeviceToken` - Manages user device tokens
- `PushNotification` - Logs sent notifications

### Services
- `FirebaseCloudMessagingService` - Handles FCM operations

### Database Tables
- `device_tokens` - Stores user device registration tokens
- `push_notifications` - Logs notification history

## Frontend Integration (Ready for Implementation)

```javascript
// Request notification permission
async function requestNotificationPermission() {
  const permission = await Notification.requestPermission();
  if (permission === 'granted') {
    // Get FCM token
    const token = await getToken(messaging, { vapidKey: 'your-vapid-key' });
    // Send token to backend
    await registerDeviceToken(token);
  }
}
```

## API Endpoints (Available)

- `POST /api/device-tokens` - Register device token
- `POST /api/notifications/send` - Send notification
- `GET /api/notifications/history` - Get notification history

## Production Deployment

1. Use production Firebase project
2. Configure environment variables on server
3. Set up proper SSL certificates
4. Configure domain authorization in Firebase
5. Monitor FCM quota and usage

## Troubleshooting

### Common Issues
1. **Invalid credentials**: Check service account JSON format
2. **Permission denied**: Verify Firebase project permissions
3. **Token registration failed**: Check VAPID key configuration
4. **Notifications not received**: Verify user permissions and token validity

### Debug Mode
Set `FIREBASE_DEBUG=true` in environment to enable detailed logging.

## Next Steps (After Firebase Setup)

1. Implement frontend notification request flow
2. Add notification templates for different events
3. Create admin panel for managing notifications
4. Set up notification scheduling
5. Implement notification preferences per user

## Resources

- [Firebase Console](https://console.firebase.google.com/)
- [FCM Documentation](https://firebase.google.com/docs/cloud-messaging)
- [Firebase Admin SDK PHP](https://firebase-php.readthedocs.io/)
- [Web Push Protocol](https://tools.ietf.org/html/rfc8030)