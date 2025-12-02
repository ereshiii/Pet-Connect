# ============================================
# Firebase Credentials Base64 Encoder
# ============================================
# This script encodes your Firebase service account JSON
# for Railway deployment
# ============================================

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Firebase Credentials Base64 Encoder" -ForegroundColor Cyan
Write-Host "  For Railway Deployment" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

$firebaseFile = "storage\app\firebase-credentials.json"

# Check if file exists
if (-Not (Test-Path $firebaseFile)) {
    Write-Host "ERROR: Firebase credentials file not found!" -ForegroundColor Red
    Write-Host "Expected location: $firebaseFile" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please ensure your Firebase service account JSON is at:" -ForegroundColor Yellow
    Write-Host "  storage/app/firebase-credentials.json" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host "✓ Found Firebase credentials file" -ForegroundColor Green
Write-Host ""

# Read and encode
try {
    $bytes = [System.IO.File]::ReadAllBytes($firebaseFile)
    $base64 = [Convert]::ToBase64String($bytes)
    
    # Copy to clipboard
    Set-Clipboard -Value $base64
    
    Write-Host "✓ Successfully encoded to base64" -ForegroundColor Green
    Write-Host "✓ Copied to clipboard!" -ForegroundColor Green
    Write-Host ""
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host "  Next Steps:" -ForegroundColor Cyan
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "1. Go to Railway Dashboard → Your Project → Variables" -ForegroundColor White
    Write-Host "2. Add a new variable:" -ForegroundColor White
    Write-Host "   Name:  FIREBASE_CREDENTIALS_BASE64" -ForegroundColor Yellow
    Write-Host "   Value: <Paste from clipboard (Ctrl+V)>" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "3. Save and redeploy" -ForegroundColor White
    Write-Host ""
    Write-Host "The base64 string has been copied to your clipboard!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Base64 length: $($base64.Length) characters" -ForegroundColor Gray
    Write-Host "Preview (first 100 chars): $($base64.Substring(0, [Math]::Min(100, $base64.Length)))..." -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "ERROR: Failed to encode file" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

Write-Host "Done! ✨" -ForegroundColor Cyan
Write-Host ""
