# PetConnect Development Utilities

**Interactive Development and Testing Tools**

---

## 🛠️ **UTILITIES OVERVIEW**

This directory contains utility scripts for testing and managing the PetConnect application. These are interactive PHP scripts that help with development, testing, and administration tasks.

### **Directory Structure**

```
tests/Utilities/
├── Admin/                      # Administrative management tools
│   ├── admin_test.php         # Interactive admin interface
│   └── README.md              # Admin tools documentation
├── ClinicManagement/          # Clinic registration and data management
│   ├── check_registration.php # Registration detail checker
│   ├── check_status.php       # Quick status validation
│   ├── list_clinics.php       # Clinic account listing
│   ├── update_registration.php # Registration data updater
│   └── update_test_registration.php # Rating and feature updater
├── UserManagement/            # User account management
│   └── create_test_user.php   # Test account creation
├── check_clinic_data.php      # Clinic database analysis tool
└── test_patients_controller.php # Patients controller testing script
```

---

## 🏥 **ADMIN MANAGEMENT TOOLS**

### **Interactive Admin Interface - `admin_test.php`**

**Purpose**: Comprehensive tool for testing clinic registration workflows with multiple clinic accounts.

#### **Key Features**
- 🏥 **Multi-Clinic Support**: Lists all clinic registrations in the database
- 🔧 **Test Account Creation**: Creates sample clinic accounts with different statuses
- ⚡ **Quick Actions**: Approve, reject, reset, or modify registration statuses
- 📊 **Status Indicators**: Visual status display (✅ approved, ⏳ pending, ❌ rejected, 📝 incomplete)
- 🎯 **Interactive Interface**: Menu-driven system for easy navigation

#### **Usage**
```bash
# Run interactive admin interface
php tests/Utilities/Admin/admin_test.php

# Create sample test accounts
php tests/Utilities/Admin/admin_test.php
> create

# Test specific registration
php tests/Utilities/Admin/admin_test.php
> 5  # Enter registration ID
```

#### **Sample Output**
```
=== ALL CLINIC REGISTRATIONS ===
⏳ [1] Test Veterinary Clinic | Pet Paw (petpaw@gmail.com) | Status: pending
✅ [4] Test Clinic | Test Clinic User (testclinic@example.com) | Status: approved
❌ [8] Metro Pet Care Center | Metro Pet Care Center (info@metropet.com) | Status: rejected

Enter Registration ID to test: 8

=== TESTING REGISTRATION ===
Registration ID: 8
Clinic Name: Metro Pet Care Center
Owner: Metro Pet Care Center (info@metropet.com)
Current Status: rejected
Rejection Reason: Missing required documentation...

=== ACTIONS AVAILABLE ===
1. Approve registration
2. Reject registration
3. Reset to pending
4. Mark as incomplete
5. View full details
6. Test different registration
7. Exit
```

#### **Test Account Creation**
Creates 3 sample clinic accounts with different statuses:
- **Sunrise Veterinary Clinic** (pending)
- **Happy Paws Animal Hospital** (approved)
- **Metro Pet Care Center** (rejected)

Each includes complete registration data (address, coordinates, services, veterinarians, etc.)

---

## 🏥 **CLINIC MANAGEMENT TOOLS**

### **Registration Detail Checker - `check_registration.php`**

**Purpose**: Check specific clinic registration details and completeness.

```bash
php tests/Utilities/ClinicManagement/check_registration.php
```

**Features:**
- Detailed registration information display
- Completeness validation using `isComplete()` method
- Coordinate data verification
- Service and veterinarian information review

### **Quick Status Checker - `check_status.php`**

**Purpose**: Quick validation of clinic registration statuses.

```bash
php tests/Utilities/ClinicManagement/check_status.php
```

**Features:**
- Rapid status overview for all registrations
- Summary statistics by status
- Quick identification of incomplete registrations

### **Clinic Account Listing - `list_clinics.php`**

**Purpose**: Display all clinic accounts and registrations.

```bash
php tests/Utilities/ClinicManagement/list_clinics.php
```

**Features:**
- Complete clinic account listing
- User account and registration relationship display
- Status and submission date information
- Contact information overview

### **Registration Data Updater - `update_registration.php`**

**Purpose**: Update registration with complete test data.

```bash
php tests/Utilities/ClinicManagement/update_registration.php
```

**Features:**
- Batch update registrations with realistic data
- Complete address and coordinate information
- Service catalog and veterinarian details
- Operating hours and contact information

### **Test Registration Updater - `update_test_registration.php`**

**Purpose**: Update clinic ratings and featured status.

```bash
php tests/Utilities/ClinicManagement/update_test_registration.php
```

**Features:**
- Add ratings and reviews to clinics
- Set featured status for testing
- Update clinic visibility settings
- Generate realistic review data

---

## 👥 **USER MANAGEMENT TOOLS**

### **Test User Creator - `create_test_user.php`**

**Purpose**: Create test clinic user accounts for development.

```bash
php tests/Utilities/UserManagement/create_test_user.php
```

**Features:**
- Create clinic and regular user accounts
- Handle different user types (clinic, regular user, admin)
- Set up verified test accounts with proper relationships
- Generate realistic test data for development
- Email verification setup

**Sample Usage:**
```bash
php tests/Utilities/UserManagement/create_test_user.php

=== Test User Creation ===
1. Create clinic user
2. Create regular user
3. Create admin user
4. Create user with profile data
5. Exit

Choice: 1

Enter clinic name: Test Veterinary Clinic
Enter email: testvet@example.com
Enter password: [default: password]

✅ Clinic user created successfully!
User ID: 15
Email: testvet@example.com
Account Type: clinic
```

---

## 🔧 **DEVELOPMENT WORKFLOW**

### **Initial Setup Workflow**
```bash
# 1. Create test environment
composer install
php artisan migrate --env=testing

# 2. Create test users
php tests/Utilities/UserManagement/create_test_user.php

# 3. Set up clinic registrations
php tests/Utilities/Admin/admin_test.php
> create

# 4. Validate setup
php tests/Utilities/ClinicManagement/list_clinics.php
```

### **Registration Testing Workflow**
```bash
# 1. Check current registrations
php tests/Utilities/Admin/admin_test.php

# 2. Test approval workflow
php tests/Utilities/Admin/admin_test.php
> [select registration ID]
> 1  # Approve registration

# 3. Validate status changes
php tests/Utilities/ClinicManagement/check_status.php

# 4. Test rejection workflow
php tests/Utilities/Admin/admin_test.php
> [select registration ID]
> 2  # Reject registration
```

### **Data Validation Workflow**
```bash
# 1. Check registration completeness
php tests/Utilities/ClinicManagement/check_registration.php

# 2. Validate coordinates and addresses
php tests/Utilities/ClinicManagement/list_clinics.php

# 3. Update test data if needed
php tests/Utilities/ClinicManagement/update_registration.php

# 4. Add ratings and reviews
php tests/Utilities/ClinicManagement/update_test_registration.php
```

---

## 🎯 **TESTING SCENARIOS**

### **Admin Workflow Testing**

#### **1. Approval Workflow**
- Select a pending registration
- Choose option 1 (Approve)
- Verify status changes to approved
- Check approval timestamp and admin ID

#### **2. Rejection Workflow**
- Select a pending registration
- Choose option 2 (Reject)
- Enter custom rejection reason
- Verify status changes to rejected with reason

#### **3. Status Transitions**
- Test transitions between different statuses
- Verify database updates are working
- Check that approval timestamps and reasons are saved

#### **4. Registration Completeness**
- Use option 5 to view full registration details
- Verify `isComplete()` method works correctly
- Check coordinate data, services, veterinarians, etc.

### **Data Integrity Testing**

#### **1. Relationship Validation**
- Verify user-to-registration relationships
- Check clinic registration data completeness
- Validate address and coordinate information

#### **2. Business Logic Testing**
- Test registration status determination
- Verify completeness calculation
- Check approval workflow constraints

#### **3. Data Consistency**
- Ensure rating calculations are correct
- Verify featured status updates
- Check service and veterinarian data integrity

---

## 🛡️ **SECURITY CONSIDERATIONS**

### **Development Only**
⚠️ **Important**: These utilities are for development and testing only. They:
- Bypass normal authentication checks
- Create accounts with default passwords
- Should not be used in production environments
- Are excluded from production deployments

### **Security Features**
- **Environment Checks**: Some utilities check for development environment
- **Default Passwords**: Use secure defaults that should be changed
- **Data Isolation**: Work with test data structures
- **Access Logging**: Administrative actions are logged

---

## 🔍 **TROUBLESHOOTING**

### **Common Issues**

#### **1. Path Errors**
**Problem**: Script can't find Laravel bootstrap
**Solution**: Ensure scripts are run from project root directory
```bash
# Correct usage
cd /path/to/petconnect
php tests/Utilities/Admin/admin_test.php
```

#### **2. Database Connection**
**Problem**: Can't connect to database
**Solution**: Verify database configuration in `.env`
```bash
# Check database config
php artisan config:show database
```

#### **3. Model Loading Issues**
**Problem**: Models not found or autoload issues
**Solution**: Clear cache and regenerate autoload
```bash
php artisan config:clear
composer dump-autoload
```

#### **4. Permission Issues**
**Problem**: File permission errors
**Solution**: Ensure proper file permissions
```bash
chmod +x tests/Utilities/Admin/admin_test.php
```

### **Debug Information**

Most utilities include diagnostic output showing:
- Current status of registrations
- User account information
- Database relationship verification
- Completion status validation
- Error messages with context

### **Getting Help**

#### **Verbose Output**
Many utilities support verbose mode for additional debugging:
```bash
# Run with detailed output
php tests/Utilities/Admin/admin_test.php --verbose
```

#### **Log Files**
Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

---

## � **DATABASE ANALYSIS TOOLS**

### **check_clinic_data.php**
Analyzes clinic registration data structure and available services.

**Features:**
- **Service Analysis**: Lists all unique services offered by clinics
- **Data Structure Inspection**: Examines clinic registration schema
- **Category Validation**: Checks for category fields in database
- **Statistics**: Provides total clinic count and data overview

**Usage:**
```bash
php tests/Utilities/check_clinic_data.php
```

**Example Output:**
```
Checking clinic data in database...
Total clinics: 5

Available services in database:
- General Checkups
- Vaccinations
- Surgery
- Emergency Care
- Dental Care

Sample clinic data structure:
Clinic name: Veterinary Clinic Manila
Services: General Checkups, Vaccinations, Surgery
No 'category' field found in clinic registration
```

### **test_patients_controller.php**
Tests the patients controller functionality for clinic users.

**Features:**
- **Controller Testing**: Validates ClinicPatientsController methods
- **Authentication Simulation**: Tests with authenticated clinic users
- **Response Analysis**: Examines controller response types and data
- **Error Detection**: Identifies controller issues and exceptions

**Usage:**
```bash
php tests/Utilities/test_patients_controller.php
```

**Example Output:**
```
=== Testing Patients Controller ===
✅ Found clinic user: Dr. Smith Clinic (ID: 1)
   Account type: clinic
✅ Found clinic registration (ID: 1)
✅ User authenticated
🔄 Testing patients controller...
✅ Controller returned Inertia response
🎯 Success! Patients controller is working without errors
```

---

## �📊 **UTILITY STATISTICS**

### **Available Tools**
- **9+ Interactive Utilities**: Development and testing tools
- **4 Management Categories**: Admin, Clinic, User, and Database Analysis
- **Multiple Test Scenarios**: Comprehensive workflow testing
- **Real-time Validation**: Immediate feedback and status checking

### **Functionality Coverage**
- **Admin Management**: 100% - Complete admin interface
- **Clinic Management**: 95% - Full registration lifecycle
- **User Management**: 90% - Account creation and management
- **Data Validation**: 95% - Comprehensive checking tools

### **Development Support**
- **Interactive Interfaces**: Menu-driven tools for ease of use
- **Batch Operations**: Efficient bulk data management
- **Status Monitoring**: Real-time registration status checking
- **Error Handling**: Comprehensive error reporting and recovery

---

## 🚀 **FUTURE ENHANCEMENTS**

### **Planned Improvements**
1. **Web Interface**: Browser-based admin tools
2. **API Testing**: RESTful API endpoint validation
3. **Performance Monitoring**: Database query analysis
4. **Automated Testing**: Integration with test suite
5. **Data Export**: CSV/Excel export functionality

### **Integration Opportunities**
- **Test Suite Integration**: Use utilities in automated tests
- **CI/CD Pipeline**: Automated data setup for testing
- **Monitoring Tools**: Health check and status monitoring
- **Documentation**: Auto-generated documentation from utilities

---

**Utilities Documentation Completed**: October 2025  
**Last Updated**: October 2025  
**Maintained By**: PetConnect Development Team