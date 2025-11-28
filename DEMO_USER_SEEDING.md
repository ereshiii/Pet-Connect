# Demo User Seeding Configuration

## Overview
The seeders have been modified to create a single comprehensive demo user account instead of multiple test users. This provides a clean, focused demonstration environment for production deployment.

## Demo User Credentials

```
Email: demo@petconnect.com
Password: password123
Account Type: Regular User
```

## What Gets Seeded

### 1. ProductionSeeder
The main production seeder that orchestrates all other seeders in the correct order:

```bash
php artisan db:seed --class=ProductionSeeder
```

This will seed:
- Pet types (9 types)
- Pet breeds (40+ breeds)
- Subscription plans (3 tiers)
- Admin account
- Demo clinics (multiple sample clinics)
- **Demo user (demo@petconnect.com)**
- **5 pets for demo user**
- **20-30 appointments for demo user's pets**
- **Medical records for completed appointments**
- **Reviews for ~70% of completed appointments**

### 2. Demo User's Pets (5 Total)

#### Dogs (3):
1. **Max** - Male Golden Retriever, 3.5 years old, 30.5 kg, neutered
   - Golden colored, friendly and loves to play fetch

2. **Charlie** - Male Labrador Retriever, 4 years old, 32 kg, neutered
   - Chocolate colored, very energetic, needs lots of exercise

3. **Rocky** - Male Beagle, 5 years old, 12.5 kg, neutered
   - Tricolor, loves food, needs weight management

#### Cats (2):
1. **Bella** - Female Persian, 2 years old, 4.5 kg, neutered
   - White colored, indoor cat, very calm

2. **Luna** - Female Siamese, 1 year old, 3.8 kg, not neutered
   - Seal Point colored, very vocal and active

### 3. Appointments
- **4-6 appointments per pet** = 20-30 total appointments
- Distributed across different clinics for variety
- Status distribution:
  - **60% Completed** (past appointments with medical records)
  - **20% Confirmed** (upcoming appointments within 14 days)
  - **20% Pending** (future appointments 15-30 days out)
- Various services: checkups, vaccinations, grooming, consultations, etc.

### 4. Medical Records
- Automatically created for all **completed appointments**
- Record types based on service:
  - Checkups (vital signs, physical exam)
  - Vaccinations (vaccine details, batch numbers, next due dates)
  - Surgery (procedure details, post-op instructions)
  - Emergency (triage, treatment, disposition)
  - Dental (cleaning, procedures performed)
  - Grooming (services provided)
  - Consultations (diagnosis, treatment plan)

### 5. Reviews
- Created for **~70% of completed appointments**
- Realistic rating distribution (skewed towards positive):
  - 5 stars: Most common
  - 4 stars: Common
  - 3 stars: Occasional
  - 2-1 stars: Rare
- Includes clinic responses for:
  - **80% of low-rated reviews** (1-3 stars)
  - **40% of high-rated reviews** (4-5 stars)
- **20% of 5-star reviews** marked as featured

## Modified Seeders

### UserSeeder.php
- Changed from creating 10 test users to **1 demo user**
- Creates user with profile, address, and emergency contact
- Simplified data structure for production

### PetSeeder.php
- Changed from creating 18 pets across 10 users to **5 pets for 1 demo user**
- Mix of dogs and cats with realistic breeds and characteristics
- Removed unnecessary fields (species string - uses type_id relationship)
- Added special needs/notes for each pet

### AppointmentSeeder.php
- Changed from creating appointments for all users to **demo user only**
- Creates **4-6 appointments per pet** across different clinics
- Maintains status distribution for realistic demo data
- Ensures referential integrity (appointments → pets → user)

### MedicalRecordSeeder.php
- No changes needed (already works with any completed appointments)
- Automatically creates records for demo user's completed appointments
- Generates type-specific data based on service/appointment type

### ReviewSeeder.php
- Removed unique constraint that prevented multiple reviews per user per clinic
- Now allows demo user to review multiple clinics
- Automatically creates reviews for ~70% of demo user's completed appointments

### ProductionSeeder.php
- Updated to include demo data seeders
- Provides comprehensive seeding order
- Includes helpful output messages with demo credentials

## Usage

### For Fresh Database:
```bash
# Reset database and seed with demo data
php artisan migrate:fresh --seed --seeder=ProductionSeeder
```

### For Existing Database:
```bash
# Just run the production seeder
php artisan db:seed --class=ProductionSeeder
```

### For Development (All Test Data):
```bash
# Use the organized seeder for full test data
php artisan migrate:fresh --seed --seeder=OrganizedDatabaseSeeder
```

## Benefits of Single Demo User

1. **Clean Demo Environment**: One comprehensive account shows all features
2. **Easy Testing**: Single login credentials for demonstration
3. **Realistic Data**: Full appointment history, medical records, and reviews
4. **Cross-Clinic Data**: Appointments across multiple clinics show platform breadth
5. **Production-Ready**: No clutter from multiple test accounts
6. **Referential Integrity**: All data properly linked and traceable

## Data Relationships

```
Demo User (demo@petconnect.com)
├── Profile (name, phone, DOB, etc.)
├── Address (123 Taft Avenue, Manila)
├── Emergency Contact
└── Pets (5)
    ├── Max (Golden Retriever)
    │   ├── Appointments (4-6)
    │   │   ├── Medical Records
    │   │   └── Reviews (~70%)
    │   └── ...
    ├── Bella (Persian Cat)
    ├── Charlie (Labrador)
    ├── Luna (Siamese Cat)
    └── Rocky (Beagle)
```

## Notes

- All appointments have proper timestamps based on status
- Completed appointments have check-in/check-out times
- Medical records created automatically after appointments complete
- Reviews posted 2-48 hours after appointment completion
- Clinic ratings updated automatically based on reviews
- All foreign key relationships maintained correctly

## Future Modifications

To change demo user data:
1. Edit `database/seeders/UserSeeder.php` - Update user details
2. Edit `database/seeders/PetSeeder.php` - Modify pet names/breeds/details
3. Run: `php artisan migrate:fresh --seed --seeder=ProductionSeeder`

To add more pets or appointments:
- Adjust the `$petData` array in `PetSeeder.php`
- Adjust `rand(4, 6)` in `AppointmentSeeder.php` to create more appointments per pet
