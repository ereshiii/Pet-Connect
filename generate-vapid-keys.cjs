// Simple VAPID key generator
const crypto = require('crypto');

function generateVAPIDKeys() {
    const { publicKey, privateKey } = crypto.generateKeyPairSync('ec', {
        namedCurve: 'prime256v1',
        publicKeyEncoding: {
            type: 'spki',
            format: 'der'
        },
        privateKeyEncoding: {
            type: 'pkcs8',
            format: 'der'
        }
    });

    // Convert to base64url format
    const publicKeyBase64 = urlBase64(publicKey);
    const privateKeyBase64 = urlBase64(privateKey);

    console.log('\n=== VAPID Keys Generated Successfully ===\n');
    console.log('Add these to your .env file:\n');
    console.log(`VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log(`VAPID_PRIVATE_KEY=${privateKeyBase64}`);
    console.log(`VITE_VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log('\nCopy the VITE_VAPID_PUBLIC_KEY to your frontend .env for push notifications.\n');
}

function urlBase64(buffer) {
    return buffer.toString('base64')
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

generateVAPIDKeys();
