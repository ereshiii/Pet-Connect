# Future Fixes & Improvements

## Distance Calculation Issues

### Issue: Distance Mismatch Between Pages
- **Problem**: Distances shown on clinics listing page don't match clinic details page
- **Example**: Listing shows "6.6 km" while details shows "185.5 km" for same clinic
- **Status**: Partially fixed, but still inconsistent 
- **Priority**: Medium

### Root Causes:
1. Different calculation methods between backend LocationService and frontend Haversine formula
2. User location data might not be passing correctly between pages
3. Potential coordinate precision differences

### Attempted Fixes:
- ✅ Added user location URL parameters when navigating to details
- ✅ Backend now calculates distance in clinic details controller
- ✅ Frontend prefers backend-calculated distance over client-side calculation
- ❌ Still showing different values

### Next Steps:
1. Debug LocationService calculation method vs frontend Haversine formula
2. Verify coordinate precision and data types
3. Add logging to track location data flow between pages
4. Consider using single calculation method across all pages

### Files Involved:
- `app/Services/LocationService.php`
- `app/Http/Controllers/ClinicController.php` 
- `resources/js/pages/Clinics.vue`
- `resources/js/pages/clinics/clinicViewDetails.vue`

---

## Other Issues

### Booking Form Improvements ✅
- **Status**: Completed
- **Changes**: 
  - Removed clinic dropdown selector
  - Pre-filled clinic from navigation 
  - Auto-filled user contact info from profile
  - Enhanced UX with clinic information display

### Filter System ✅  
- **Status**: Completed
- **Changes**:
  - Removed apply button (automatic filtering)
  - Fixed region filtering for all Philippine regions
  - Improved distance filtering with real calculations
  - Created reusable ClinicFilters component