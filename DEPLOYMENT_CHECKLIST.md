# Railway Deployment Checklist

## Pre-Deployment

- [ ] All code committed to GitHub
- [ ] `Dockerfile` exists in root
- [ ] `docker-entrypoint.sh` exists and is executable
- [ ] `railway.json` exists
- [ ] Firebase credentials file exists at `storage/app/firebase-credentials.json`
- [ ] Run `php artisan key:generate --show` and save the key
- [ ] Run `.\encode-firebase-credentials.ps1` to get base64 credentials

## Railway Setup

- [ ] Created Railway account and logged in with GitHub
- [ ] Created new project from GitHub repo
- [ ] Railway detected Dockerfile automatically

## Environment Variables (Railway Dashboard → Variables)

### Required Core Variables
- [ ] `APP_NAME=PetConnect`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://your-app.up.railway.app` (update after deployment)
- [ ] `APP_KEY=base64:...` (from `php artisan key:generate --show`)

### Database
- [ ] `DB_CONNECTION=sqlite` (or `pgsql` for PostgreSQL)

### Session & Cache
- [ ] `SESSION_DRIVER=database`
- [ ] `SESSION_LIFETIME=120`
- [ ] `CACHE_STORE=file`
- [ ] `QUEUE_CONNECTION=database`

### Logging
- [ ] `LOG_CHANNEL=stack`
- [ ] `LOG_LEVEL=error`

### Firebase Push Notifications (REQUIRED)
- [ ] `FIREBASE_CREDENTIALS=/var/www/html/storage/app/firebase-credentials.json`
- [ ] `FIREBASE_CREDENTIALS_BASE64=...` (from encode script)
- [ ] `VITE_FIREBASE_API_KEY=AIzaSyAtAbLHeEzih0Jd7zOTAlWNIbtQbOfJV0o`
- [ ] `VITE_FIREBASE_AUTH_DOMAIN=petconnect-d88c7.firebaseapp.com`
- [ ] `VITE_FIREBASE_PROJECT_ID=petconnect-d88c7`
- [ ] `VITE_FIREBASE_STORAGE_BUCKET=petconnect-d88c7.firebasestorage.app`
- [ ] `VITE_FIREBASE_MESSAGING_SENDER_ID=137614395456`
- [ ] `VITE_FIREBASE_APP_ID=1:137614395456:web:f94d99eb0d2e9da65cf7a5`
- [ ] `VITE_FIREBASE_MEASUREMENT_ID=G-B2ZEGE7JGF`
- [ ] `VITE_FIREBASE_VAPID_KEY=BCM3LConxlI3YvpgZtg6yrOSafqoXnrVmN6_cSQ_8GOCpBzCOpNJsqNXZqjQWoPmX1CYiO1fBLJdXmAhwULrmbY`

### Optional (Development)
- [ ] `MAIL_MAILER=log`
- [ ] `PAYMONGO_MOCK_MODE=true`
- [ ] `MONITOR_QUERIES=false`

## Post-Deployment

- [ ] Deployment completed successfully (check Railway logs)
- [ ] Update `APP_URL` with actual Railway domain
- [ ] Visit homepage - loads correctly
- [ ] Test user registration
- [ ] Test user login
- [ ] Test profile photo upload (verify storage:link worked)
- [ ] Test pet photo upload
- [ ] Test clinic listings
- [ ] Enable Firebase push notifications in browser
- [ ] Test sending a notification
- [ ] Check browser console for errors
- [ ] Verify images display correctly

## Automatic on Every Deploy

The following are handled automatically by `docker-entrypoint.sh`:

✓ Firebase credentials decoded from base64  
✓ Database migrations run  
✓ Storage symlink created (`php artisan storage:link`)  
✓ Production seeder run  
✓ Config cached  
✓ Routes cached  
✓ Views cached  
✓ File permissions set  

## Common Issues

### Images not displaying
- [ ] Check if `APP_URL` is set correctly
- [ ] Verify `php artisan storage:link` ran (check logs)
- [ ] Check browser console for 404 errors on image URLs

### Firebase notifications not working
- [ ] Verify `FIREBASE_CREDENTIALS_BASE64` is set
- [ ] Check logs for "Firebase credentials created" message
- [ ] Ensure all `VITE_FIREBASE_*` variables are set
- [ ] Test on /test-firebase page first

### 500 Server Error
- [ ] Check Railway logs for PHP errors
- [ ] Verify `APP_KEY` is set correctly
- [ ] Ensure `APP_DEBUG=false` in production
- [ ] Check storage permissions in logs

### Database reset on deploy
- [ ] This is expected with SQLite (ephemeral storage)
- [ ] Migrate to PostgreSQL for persistent data
- [ ] Add Railway PostgreSQL plugin

## Production Readiness

For production deployment:

- [ ] Migrate to PostgreSQL (add Railway PostgreSQL plugin)
- [ ] Set up proper backup strategy
- [ ] Configure real email service (replace `MAIL_MAILER=log`)
- [ ] Add real PayMongo keys (remove `PAYMONGO_MOCK_MODE`)
- [ ] Set up custom domain
- [ ] Enable HTTPS (Railway does this automatically)
- [ ] Configure monitoring/alerts
- [ ] Set up error tracking (Sentry, Bugsnag, etc.)

## Resources

- Railway Dashboard: https://railway.app/dashboard
- Railway Docs: https://docs.railway.app/
- Deployment Guide: `RAILWAY_DEPLOYMENT.md`
- Environment Variables Template: `.env.railway.example`
- Firebase Setup: `PUSH_NOTIFICATIONS_SETUP.md`
- Image Upload Fix: `IMAGE_URL_FIX.md`

---

**Last Updated:** December 2, 2025  
**Status:** Ready for deployment 🚀
