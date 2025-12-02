# PetConnect Deployment Script
# Builds assets locally and deploys to Railway

Write-Host "`n🚀 PetConnect Deployment Script" -ForegroundColor Cyan
Write-Host "================================`n" -ForegroundColor Cyan

# Step 1: Build assets
Write-Host "📦 Building production assets..." -ForegroundColor Green
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Build failed! Please fix errors and try again." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Build completed successfully!`n" -ForegroundColor Green

# Step 2: Git add all changes
Write-Host "📝 Staging changes..." -ForegroundColor Green
git add .

# Step 3: Commit
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"
Write-Host "💾 Committing changes..." -ForegroundColor Green
git commit -m "Deploy: $timestamp"

if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Nothing to commit or commit failed" -ForegroundColor Yellow
    Write-Host "Proceeding with push anyway...`n" -ForegroundColor Yellow
}

# Step 4: Push to GitHub
Write-Host "⬆️  Pushing to GitHub..." -ForegroundColor Green
git push

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Push failed! Please check your git configuration." -ForegroundColor Red
    exit 1
}

Write-Host "`n[SUCCESS] Deployment initiated!" -ForegroundColor Green
Write-Host "[INFO] Check Railway dashboard: https://railway.app" -ForegroundColor Cyan
Write-Host "`nDone!`n" -ForegroundColor Green
