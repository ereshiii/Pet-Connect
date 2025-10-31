# 🔧 User Registration Database Constraint Fix

## 🚨 **Issue Resolved**
**Error**: `SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: user_profiles.user_id`

**Root Cause**: The `setNameAttribute` method in the User model was trying to create a user profile before the user was saved to the database, causing the user_id to be null.

## ✅ **Solution Implemented**

### **1. Modified CreateNewUser Action**
**File**: `app/Actions/Fortify/CreateNewUser.php`

**Before** (Problematic):
```php
return User::create([
    'name' => $input['name'],        // This triggered setNameAttribute too early
    'email' => $input['email'],
    'password' => $input['password'],
    'account_type' => $input['account_type'],
]);
```

**After** (Fixed):
```php
// Create user without name first
$user = User::create([
    'email' => $input['email'],
    'password' => $input['password'],
    'account_type' => $input['account_type'],
]);

// Now set the name, which will create the profile
$user->name = $input['name'];

return $user;
```

### **2. Updated User Model**
**File**: `app/Models/User.php`

#### **Removed 'name' from fillable array**:
```php
protected $fillable = [
    'email',
    'password',
    'account_type',
    // ... other fields
    // 'name' is a virtual attribute handled by accessor/mutator
];
```

#### **Added safety check in setNameAttribute method**:
```php
public function setNameAttribute(string $value): void
{
    // Only proceed if the user has been saved and has an ID
    if (!$this->exists || !$this->id) {
        return;
    }

    $nameParts = explode(' ', trim($value), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';

    // Create or update the user profile
    $this->profile()->updateOrCreate(
        ['user_id' => $this->id],
        [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]
    );
}
```

## 🔍 **Technical Details**

### **Problem Flow**:
1. User submits registration form with name "Khen Macaltao"
2. `User::create()` called with `'name' => 'Khen Macaltao'`
3. Laravel calls `setNameAttribute()` during model creation
4. `setNameAttribute()` tries to create user profile with `$this->id` (which is null)
5. Database constraint violation because `user_id` cannot be null

### **Solution Flow**:
1. User submits registration form with name "Khen Macaltao"
2. `User::create()` called without the name field
3. User is saved to database and gets an ID
4. `$user->name = $input['name']` triggers `setNameAttribute()`
5. Profile is created successfully with valid user_id

## 🛡️ **Safety Measures Added**

1. **Existence Check**: `if (!$this->exists || !$this->id)` prevents profile creation for unsaved users
2. **Graceful Return**: Method returns early if user isn't ready for profile creation
3. **Proper Sequencing**: User creation happens before profile creation

## ✅ **Validation**

- ✅ Build passes successfully
- ✅ No database constraint violations
- ✅ User registration flow maintains functionality
- ✅ Profile creation works correctly after user is saved
- ✅ Name accessor/mutator pattern preserved

## 🔄 **User Experience**

**Registration process now works as intended**:
1. User fills out registration form
2. Account is created successfully
3. Profile is automatically created with parsed first/last name
4. User can immediately access the application

This fix ensures that user registration works reliably without database constraint violations while maintaining the clean separation between user accounts and user profiles.