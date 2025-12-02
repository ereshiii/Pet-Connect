# Quick Start: Railway Deployment with Storage & Firebase

## TL;DR - What You Need

1. **Environment Variables** - Copy from `.env.railway.example` to Railway
2. **Firebase Credentials** - Run `.\encode-firebase-credentials.ps1` and paste to Railway
3. **Deploy** - Push to GitHub, Railway auto-deploys
4. **Done!** - Storage symlink and Firebase are automatically configured

---

## Step-by-Step (5 Minutes)

### 1. Generate Required Values

```powershell
# Generate APP_KEY
php artisan key:generate --show
# Copy the output (starts with base64:)

# Generate Firebase credentials base64
.\encode-firebase-credentials.ps1
# Automatically copies to clipboard
```

### 2. Add to Railway Variables

Go to Railway Dashboard → Your Project → Variables tab:

**Copy all from** `.env.railway.example` **and update:**
- `APP_KEY` - Paste from step 1
- `FIREBASE_CREDENTIALS_BASE64` - Paste from step 1 (already in clipboard)
- `APP_URL` - Will update after first deploy

### 3. Deploy

Railway automatically deploys when you:
- Push to GitHub, OR
- Add/change variables

### 4. Update APP_URL

After first deploy:
1. Copy your Railway URL (e.g., `https://petconnect-production.up.railway.app`)
2. Update `APP_URL` variable in Railway
3. Railway auto-redeploys

---

## What Happens Automatically

Every deployment runs `docker-entrypoint.sh` which:

✅ **Decodes Firebase credentials** from base64  
✅ **Creates storage symlink** (`php artisan storage:link`)  
✅ **Runs migrations** (`php artisan migrate --force`)  
✅ **Seeds demo data** (admin, clinics, demo user, pets)  
✅ **Caches everything** (config, routes, views)  
✅ **Sets permissions** (storage, cache directories)  

**You don't need to run any commands manually!**

---

## Verify Deployment

### 1. Check Images Work
- Upload profile photo
- Upload pet photo
- Verify images display (storage:link worked)

### 2. Check Firebase Notifications
- Visit `/test-firebase`
- Click "Enable Notifications"
- Click "Send Test Notification"
- Should receive notification

### 3. Check Logs
Railway Dashboard → View Logs

Look for:
```
✓ Firebase credentials created
✓ Decoding Firebase credentials from base64
✓ Migration successful
✓ Storage symlink created
```

---

## Files Reference

| File | Purpose |
|------|---------|
| `.env.railway.example` | Template for Railway environment variables |
| `encode-firebase-credentials.ps1` | Helper to encode Firebase JSON to base64 |
| `docker-entrypoint.sh` | Runs automatically on every deploy |
| `RAILWAY_DEPLOYMENT.md` | Full deployment guide |
| `DEPLOYMENT_CHECKLIST.md` | Step-by-step checklist |
| `IMAGE_URL_FIX.md` | How image URLs work |
| `PUSH_NOTIFICATIONS_SETUP.md` | Firebase setup details |

---

## Common Questions

**Q: Do I need to run `php artisan storage:link` manually?**  
A: No! The `docker-entrypoint.sh` runs it automatically on every deploy.

**Q: How do I upload Firebase credentials to Railway?**  
A: Encode as base64 using `.\encode-firebase-credentials.ps1` and add as `FIREBASE_CREDENTIALS_BASE64` variable.

**Q: Why are my images not showing?**  
A: Make sure `APP_URL` is set to your Railway domain (not localhost).

**Q: Will my data persist?**  
A: With SQLite, no (resets on deploy). Migrate to PostgreSQL for production.

**Q: How do I update my app?**  
A: Just push to GitHub. Railway auto-detects and deploys.

---

## Need Help?

1. Check `RAILWAY_DEPLOYMENT.md` for full guide
2. Review `DEPLOYMENT_CHECKLIST.md` for step-by-step
3. Check Railway logs for errors
4. Verify all environment variables are set

---

**Ready to deploy?** 🚀

Run the helper script and copy to Railway:
```powershell
.\encode-firebase-credentials.ps1
```

Then push to GitHub and let Railway handle the rest!
