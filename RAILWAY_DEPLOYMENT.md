# Railway Deployment Guide for PetConnect

## ⚠️ SQLite Limitation Warning

**This deployment uses SQLite which has a critical limitation on Railway:**

- 🔴 **Database will be RESET on every deployment** (when you push code changes)
- 🔴 **Data is NOT persistent** - Railway containers are ephemeral
- 🟡 **Good for:** Testing, demos, proof-of-concept
- 🔴 **NOT good for:** Production, storing real user data

**For production, you'll need to migrate to PostgreSQL** (covered in separate guide).

---

## 🚀 Step 1: Prepare Your Repository

1. **Commit all files:**
   ```bash
   git add .
   git commit -m "Prepare for Railway deployment"
   git push origin master
   ```

2. **Make sure these files are in your repo:**
   - ✅ `Dockerfile`
   - ✅ `docker-entrypoint.sh`
   - ✅ `railway.json`

---

## 🌐 Step 2: Deploy to Railway

### A. Create Railway Account
1. Go to https://railway.app/
2. Click "Login" → "Login with GitHub"
3. Authorize Railway to access your repositories

### B. Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your repository: **ereshiii/Pet-Connect**
4. Railway will automatically detect the Dockerfile

### C. Configure Environment Variables
In Railway dashboard, go to your project → Variables tab and add:

```bash
APP_NAME=PetConnect
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

# Generate this key locally first
APP_KEY=base64:YOUR_GENERATED_KEY

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=database

MAIL_MAILER=log

PAYMONGO_MOCK_MODE=true
PAYMONGO_PUBLIC_KEY=pk_test_your_public_key_here
PAYMONGO_SECRET_KEY=sk_test_your_secret_key_here

MONITOR_QUERIES=false

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### D. Generate APP_KEY
Run locally:
```bash
php artisan key:generate --show
```
Copy the output (starts with `base64:`) and paste it as `APP_KEY` in Railway.

---

## 📝 Step 3: Deploy

1. Railway will **automatically deploy** after you add variables
2. Watch the build logs in Railway dashboard
3. Build takes ~3-5 minutes
4. Once deployed, Railway gives you a public URL: `https://your-app.up.railway.app`

---

## 🔄 Step 4: Auto-Deploy Updates

Every time you push to GitHub:
```bash
git add .
git commit -m "Update feature"
git push
```

Railway automatically:
1. Detects the push
2. Rebuilds the Docker image
3. Runs migrations
4. Deploys new version
5. ⚠️ **RESETS SQLite database**

---

## ✅ Step 5: Verify Deployment

Visit your Railway URL:
- Homepage: `https://your-app.up.railway.app/`
- Login: `https://your-app.up.railway.app/login`
- Register: `https://your-app.up.railway.app/register`

---

## 🐛 Troubleshooting

### Build Fails
- Check Railway logs for errors
- Verify all environment variables are set
- Ensure `APP_KEY` is properly formatted

### 500 Error
- Check Railway logs: Click "View Logs"
- Verify `APP_DEBUG=false` and `APP_ENV=production`
- Check storage permissions

### Assets Not Loading
- Verify `APP_URL` matches your Railway domain
- Check if `npm run build` succeeded in build logs
- Ensure Vite built assets correctly

### Database Empty After Deploy
- **This is expected with SQLite** - data is wiped on each deploy
- Solution: Migrate to PostgreSQL (see next guide)

---

## 📊 Railway Free Tier Limits

- ✅ **$5 free credit per month**
- ✅ **500 execution hours**
- ✅ **1GB RAM**
- ✅ **1GB disk** (ephemeral - resets on deploy)
- ✅ **100GB outbound network**

Your app should stay within free tier for testing.

---

## 🔧 Common Commands

### View Logs
In Railway dashboard → Your service → "View Logs"

### Restart Service
Railway dashboard → Your service → Three dots → "Restart"

### Rollback Deployment
Railway dashboard → Deployments → Select old deployment → "Redeploy"

### Custom Domain (Optional)
Railway dashboard → Settings → "Generate Domain" or add custom domain

---

## ⏭️ Next Steps

Once you've tested and confirmed the app works:

1. **Migrate to PostgreSQL** for persistent data
2. **Set up proper environment variables** for production
3. **Configure real PayMongo keys** (if needed)
4. **Set APP_DEBUG=false** for security
5. **Add custom domain** (optional)

---

## 🆘 Need Help?

- Railway Docs: https://docs.railway.app/
- Railway Discord: https://discord.gg/railway
- Check build logs in Railway dashboard
- Review error logs for debugging

---

**Ready to deploy?** Follow the steps above! 🚀
