// Proper Web Push VAPID key generator
const crypto = require('crypto');

function generateWebPushVAPIDKeys() {
    // Generate ECDSA key pair using P-256 curve (required for Web Push)
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

    // Extract the raw 65-byte public key from SPKI format
    // SPKI header is 26 bytes, followed by 65 bytes of actual public key
    const rawPublicKey = publicKey.slice(26);
    
    // Convert to base64url format (required for Web Push)
    const publicKeyBase64 = urlBase64(rawPublicKey);
    const privateKeyBase64 = urlBase64(privateKey);

    console.log('\n=== Web Push VAPID Keys Generated ===\n');
    console.log('✅ These keys are properly formatted for Web Push API\n');
    console.log('Add to your .env file:\n');
    console.log(`VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log(`VAPID_PRIVATE_KEY=${privateKeyBase64}`);
    console.log(`VITE_VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log('\n📝 Key Details:');
    console.log(`   Public Key Length: ${rawPublicKey.length} bytes (should be 65)`);
    console.log(`   Base64 Length: ${publicKeyBase64.length} characters (should be ~87)`);
    console.log('\n🔧 Next Steps:');
    console.log('   1. Copy these keys to your .env file');
    console.log('   2. Restart your dev server (npm run dev)');
    console.log('   3. Test at https://petconnect.test/test-push.html\n');
}

function urlBase64(buffer) {
    return buffer.toString('base64')
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

generateWebPushVAPIDKeys();
