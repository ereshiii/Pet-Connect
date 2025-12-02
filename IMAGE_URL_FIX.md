# Image Display Fix for Railway Deployment

## Problem
When deployed to Railway, images (profile pictures, pet photos, clinic photos) were not displaying because the image paths needed to be converted to publicly accessible URLs.

## Root Cause
- Images are stored in `storage/app/public/` 
- Laravel creates a symlink from `public/storage` → `storage/app/public`
- Controllers were returning raw storage paths (e.g., `profile_pictures/xyz.jpg`) instead of full URLs (e.g., `https://domain.com/storage/profile_pictures/xyz.jpg`)
- Frontend was manually constructing URLs with `/storage/` prefix, which works locally but may fail in production environments

## Solution

### 1. Added Model Accessors

**Pet Model** (`app/Models/Pet.php`):
```php
public function getProfileImageUrlAttribute(): ?string
{
    return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
}

public function getImagesUrlAttribute(): array
{
    if (!$this->images || !is_array($this->images)) {
        return [];
    }
    return array_map(fn($image) => asset('storage/' . $image), $this->images);
}
```

**UserProfile Model** (`app/Models/UserProfile.php`):
```php
public function getProfileImageUrlAttribute(): ?string
{
    return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
}
```

**ClinicRegistration Model** (`app/Models/ClinicRegistration.php`):
```php
public function getClinicPhotoUrlAttribute(): ?string
{
    return $this->clinic_photo ? asset('storage/' . $this->clinic_photo) : null;
}

public function getGalleryUrlAttribute(): array
{
    if (!$this->gallery || !is_array($this->gallery)) {
        return [];
    }
    return array_map(fn($image) => asset('storage/' . $image), $this->gallery);
}
```

### 2. Updated Controllers

**PetController** (`app/Http/Controllers/PetController.php`):
- Changed `'profile_image' => $pet->profile_image` to `'profile_image' => $pet->profile_image_url`
- Changed `'images' => $pet->images` to `'images' => $pet->images_url`

**ProfileController** (`app/Http/Controllers/Settings/ProfileController.php`):
- Added `profile_image_url` to user profile data

**ClinicPatientsController** (`app/Http/Controllers/ClinicPatientsController.php`):
- Changed `'profile_image' => $pet->profile_image` to `'profile_image' => $pet->profile_image_url`

### 3. Updated Vue Components

**Profile Pages**:
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/2clinicPages/settings/Profile.vue`

Changed from:
```vue
const photoPreview = ref(user.profile?.profile_image ? `/storage/${user.profile.profile_image}?v=${Date.now()}` : null);
```

To:
```vue
const photoPreview = ref(user.profile?.profile_image_url ? `${user.profile.profile_image_url}?v=${Date.now()}` : null);
```

**Pet Pages**:
- `resources/js/pages/pets/Pet.vue`: Changed `:style="petItem.profile_image ? \`background-image: url('/storage/${petItem.profile_image}')\` : ''"` to `:style="petItem.profile_image ? \`background-image: url('${petItem.profile_image}')\` : ''"`
- `resources/js/pages/pets/petDetailed.vue`: Changed `:src="\`/storage/${pet.profile_image}\`"` to `:src="pet.profile_image"`

## Deployment Checklist

### Local Testing
- [x] Build completed successfully
- [ ] Test user profile photo upload and display
- [ ] Test pet profile photo upload and display
- [ ] Test clinic profile photo upload and display
- [ ] Test clinic gallery photos

### Railway Deployment Steps

1. **Storage Symlink (Automatic)**
   - The `docker-entrypoint.sh` script automatically runs `php artisan storage:link` on deployment
   - No manual intervention needed

2. **Environment Variables**
   - Set `APP_URL` to your Railway domain (e.g., `https://yourapp.up.railway.app`)
   - This ensures `asset()` helper generates correct URLs
   - See `RAILWAY_DEPLOYMENT.md` for complete environment variable list

3. **File Permissions**
   - Ensure `storage/app/public` is writable
   - Railway/Docker handles this automatically via entrypoint script

4. **Database**
   - Migrate to PostgreSQL (recommended for production)
   - Update `DATABASE_URL` in Railway environment variables

5. **Test After Deployment**
   - Upload a profile picture
   - Upload a pet photo
   - Upload a clinic photo
   - Verify all images display correctly
   - Check browser console for 404 errors

## Benefits

1. **Environment Agnostic**: Works in local, staging, and production
2. **Full URLs**: `asset()` generates full URLs based on `APP_URL`
3. **CDN Ready**: Easy to switch to CDN by changing `ASSET_URL` env variable
4. **Consistent**: Single source of truth for image URLs
5. **No Manual URL Construction**: Backend handles all URL generation

## Files Modified

### Models
- `app/Models/Pet.php`
- `app/Models/UserProfile.php`
- `app/Models/ClinicRegistration.php`

### Controllers
- `app/Http/Controllers/PetController.php`
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Controllers/ClinicPatientsController.php`

### Vue Components
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/2clinicPages/settings/Profile.vue`
- `resources/js/pages/pets/Pet.vue`
- `resources/js/pages/pets/petDetailed.vue`

## Notes

- Controllers that were already using `asset('storage/' . $path)` (like `ClinicProfileController`, `ClinicController`) continue to work as before
- The new accessors provide a consistent way to get image URLs across the application
- Clinic photos and galleries were already handled correctly in most controllers
