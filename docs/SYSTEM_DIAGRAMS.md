# PetConnect System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Hardware and Software Requirements](#hardware-and-software-requirements)
3. [Features and Modules](#features-and-modules)
4. [System Architecture](#system-architecture)
5. [System Diagrams](#system-diagrams)

---

## System Overview

### Project Title
**PetConnect - Veterinary Appointment Management System**

### Description
PetConnect is a comprehensive web-based platform that connects pet owners with veterinary clinics in Lingayen, Pangasinan and surrounding areas. The system streamlines the appointment booking process, manages pet medical records, and facilitates communication between pet owners and veterinary professionals.

### Primary Objectives
- **Simplify Appointment Booking**: Enable pet owners to easily find and book appointments with local veterinary clinics
- **Centralized Medical Records**: Maintain comprehensive medical histories accessible across multiple clinics
- **Improve Clinic Operations**: Provide tools for clinics to manage appointments, patients, and staff efficiently
- **Enhance Communication**: Real-time notifications and updates for appointments and medical records
- **Quality Assurance**: Review and rating system to help pet owners make informed decisions

### Target Users
1. **Pet Owners** - Book appointments, manage pet profiles, view medical records
2. **Clinic Staff** - Manage appointments, create medical records, handle patient care
3. **System Administrators** - Oversee system operations, manage clinics and subscriptions

### Key Benefits
- **For Pet Owners**: 24/7 booking access, medical record portability, clinic comparison
- **For Clinics**: Reduced administrative burden, improved scheduling, digital record keeping
- **For Pets**: Better continuity of care through comprehensive medical history

---

## Hardware and Software Requirements

### Client-Side Requirements

#### Minimum Hardware Requirements
- **Processor**: Intel Core i3 or equivalent (2.0 GHz)
- **RAM**: 4 GB
- **Storage**: 500 MB free space (for browser cache and offline data)
- **Display**: 1366x768 resolution
- **Network**: Broadband internet connection (minimum 5 Mbps)
- **Input Devices**: Keyboard and mouse/touchpad

#### Recommended Hardware Requirements
- **Processor**: Intel Core i5 or equivalent (2.5 GHz or higher)
- **RAM**: 8 GB or more
- **Storage**: 1 GB free space
- **Display**: 1920x1080 resolution or higher
- **Network**: High-speed internet connection (10 Mbps or higher)
- **Input Devices**: Keyboard, mouse, and optional touchscreen

#### Software Requirements
- **Operating System**: 
  - Windows 10/11
  - macOS 10.15 (Catalina) or later
  - Linux (Ubuntu 20.04 LTS or equivalent)
- **Web Browser** (latest versions):
  - Google Chrome 90+
  - Mozilla Firefox 88+
  - Microsoft Edge 90+
  - Safari 14+ (for macOS)
- **Additional Software**:
  - PDF Reader (for viewing medical documents)
  - Image viewer (for pet photos and clinic images)

#### Mobile Device Requirements
- **Android**: Version 8.0 (Oreo) or higher
- **iOS**: Version 13.0 or higher
- **RAM**: Minimum 3 GB
- **Storage**: 200 MB free space
- **Mobile Browser**: Chrome Mobile, Safari Mobile, Firefox Mobile

---

### Server-Side Requirements

#### Hardware Requirements (Production Server)
- **Processor**: 4-core CPU (2.5 GHz minimum)
- **RAM**: 8 GB minimum (16 GB recommended)
- **Storage**: 
  - System: 50 GB SSD
  - Database: 100 GB SSD (scalable)
  - Backups: 200 GB HDD
- **Network**: 100 Mbps dedicated connection
- **Redundancy**: RAID configuration for data protection

#### Software Requirements (Production Server)
- **Operating System**: Ubuntu Server 22.04 LTS or similar
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Runtime Environment**: PHP 8.2+
- **Database**: SQLite 3.40+ (current) / MySQL 8.0+ or PostgreSQL 14+ (scalable)
- **Additional Services**:
  - Node.js 18+ (for build tools)
  - Redis 7.0+ (caching - optional)
  - Supervisor (process management)
  - Certbot (SSL certificates)

#### Development Environment
- **Code Editor/IDE**:
  - Visual Studio Code (recommended)
  - PHPStorm
  - Sublime Text
- **Version Control**: Git 2.30+
- **Package Managers**:
  - Composer 2.0+ (PHP dependencies)
  - NPM 8.0+ or Yarn 1.22+ (JavaScript dependencies)
- **Local Development**: 
  - Laravel Herd (macOS/Windows)
  - Laravel Valet (macOS)
  - Docker + Laravel Sail (cross-platform)

---

## Features and Modules

### Module 1: User Management

#### Unit 1.1: Authentication
**Features:**
- User registration with email verification
- Secure login with password hashing (bcrypt)
- Password reset via email
- Two-factor authentication (2FA) support
- Session management
- Social media login integration (future enhancement)

**Actors:** All users (Pet Owners, Clinic Staff, Admin)

**Key Functions:**
- `register()` - Create new user account
- `login()` - Authenticate user credentials
- `logout()` - Terminate user session
- `resetPassword()` - Send password reset link
- `verifyEmail()` - Confirm email address

#### Unit 1.2: User Profiles
**Features:**
- Personal information management
- Contact details and emergency contacts
- Profile photo upload
- Account settings and preferences
- Privacy controls

**Actors:** All users

**Key Functions:**
- `updateProfile()` - Modify user information
- `uploadPhoto()` - Update profile picture
- `manageSettings()` - Configure account preferences

---

### Module 2: Pet Management

#### Unit 2.1: Pet Registration
**Features:**
- Add multiple pets per owner
- Pet type and breed selection (9 types, 40+ breeds)
- Basic information (name, gender, birth date, weight)
- Microchip number tracking
- Pet photo upload
- Special needs and behavioral notes

**Actors:** Pet Owners

**Key Functions:**
- `createPet()` - Register new pet
- `updatePet()` - Modify pet information
- `deletePet()` - Remove pet record
- `uploadPetPhoto()` - Add/update pet image

#### Unit 2.2: Health Profile Management
**Features:**
- Medical conditions tracking
- Allergy management
- Current medications list
- Vaccination status monitoring
- Health history timeline

**Actors:** Pet Owners, Clinic Staff

**Key Functions:**
- `addHealthCondition()` - Record new condition
- `updateHealthStatus()` - Modify health information
- `viewHealthHistory()` - Display complete health timeline

---

### Module 3: Clinic Management

#### Unit 3.1: Clinic Registration
**Features:**
- Multi-step registration process
- Business information submission
- Service offerings configuration
- Staff and veterinarian details
- Operating hours setup
- Document upload (certifications, licenses)
- Location mapping with coordinates

**Actors:** Clinic Owners

**Key Functions:**
- `registerClinic()` - Submit clinic application
- `uploadDocuments()` - Submit verification documents
- `configureServices()` - Set up service catalog
- `manageStaff()` - Add veterinarians and staff

#### Unit 3.2: Clinic Profile Management
**Features:**
- Business information updates
- Photo gallery management
- Service pricing and descriptions
- Operating hours modification
- Holiday/closure scheduling
- Contact information management

**Actors:** Clinic Staff

**Key Functions:**
- `updateClinicInfo()` - Modify clinic details
- `manageGallery()` - Upload clinic photos
- `updateServices()` - Modify service offerings
- `setHolidays()` - Configure closures

#### Unit 3.3: Staff Management
**Features:**
- Veterinarian profiles
- Staff role assignment
- Schedule management
- Specialization tracking
- License verification

**Actors:** Clinic Administrators

**Key Functions:**
- `addStaff()` - Register new staff member
- `updateStaffInfo()` - Modify staff details
- `assignRoles()` - Set staff permissions
- `manageSchedule()` - Configure availability

---

### Module 4: Appointment System

#### Unit 4.1: Appointment Booking
**Features:**
- Clinic search and filtering (location, services, ratings)
- Interactive map view
- Available time slot display
- Service selection
- Pet selection
- Appointment notes
- Booking confirmation
- Calendar integration

**Actors:** Pet Owners

**Key Functions:**
- `searchClinics()` - Find nearby clinics
- `checkAvailability()` - View available slots
- `bookAppointment()` - Create appointment
- `confirmBooking()` - Finalize appointment

#### Unit 4.2: Appointment Management
**Features:**
- View upcoming appointments
- Appointment history
- Reschedule requests
- Cancellation with policies
- Check-in/check-out tracking
- Status updates (pending, confirmed, completed)
- Follow-up appointment scheduling

**Actors:** Pet Owners, Clinic Staff

**Key Functions:**
- `viewAppointments()` - Display appointment list
- `rescheduleAppointment()` - Change appointment time
- `cancelAppointment()` - Cancel booking
- `checkIn()` - Mark patient arrival
- `checkOut()` - Complete appointment
- `scheduleFollowUp()` - Book follow-up visit

#### Unit 4.3: Schedule Management
**Features:**
- Calendar view (day, week, month)
- Appointment slot configuration
- Veterinarian assignment
- Schedule conflicts detection
- Capacity management
- Emergency appointment handling

**Actors:** Clinic Staff

**Key Functions:**
- `viewSchedule()` - Display clinic calendar
- `configureSlots()` - Set appointment availability
- `assignVeterinarian()` - Allocate staff to appointments
- `blockTimeSlots()` - Reserve slots for emergencies

---

### Module 5: Medical Records

#### Unit 5.1: Record Creation
**Features:**
- Simplified medical record format
- Diagnosis documentation
- Clinical findings
- Treatment given
- Prescription management
- Record templates for common visits
- Automatic record creation on appointment completion

**Actors:** Clinic Staff (Veterinarians)

**Key Functions:**
- `createMedicalRecord()` - Generate new record
- `addDiagnosis()` - Document findings
- `prescribeMedication()` - Add prescriptions
- `recordTreatment()` - Document procedures

#### Unit 5.2: Record Viewing
**Features:**
- Comprehensive medical history
- Cross-clinic record access
- Timeline view
- Record filtering and search
- PDF export
- Record sharing between clinics

**Actors:** Pet Owners, Clinic Staff

**Key Functions:**
- `viewMedicalHistory()` - Display all records
- `filterRecords()` - Search by date/type/clinic
- `exportRecord()` - Generate PDF
- `shareMedicalRecord()` - Grant clinic access

---

### Module 6: Review and Rating System

#### Unit 6.1: Review Submission
**Features:**
- 5-star rating system
- Written review comments
- Photo/image upload
- Review editing within 24 hours
- Review flagging for inappropriate content

**Actors:** Pet Owners

**Key Functions:**
- `submitReview()` - Create clinic review
- `editReview()` - Modify existing review
- `deleteReview()` - Remove review
- `uploadReviewPhoto()` - Add images

#### Unit 6.2: Review Management
**Features:**
- View all clinic reviews
- Reply to reviews
- Review moderation
- Rating aggregation
- Review analytics

**Actors:** Clinic Staff, Administrators

**Key Functions:**
- `viewReviews()` - Display clinic reviews
- `replyToReview()` - Respond to feedback
- `reportReview()` - Flag inappropriate content
- `calculateRating()` - Update average rating

---

### Module 7: Notification System

#### Unit 7.1: In-App Notifications
**Features:**
- Real-time notification feed
- Notification categorization
- Mark as read/unread
- Notification history
- Customizable notification preferences

**Actors:** All users

**Key Functions:**
- `sendNotification()` - Create notification
- `viewNotifications()` - Display notification list
- `markAsRead()` - Update notification status
- `configurePreferences()` - Set notification settings

#### Unit 7.2: Push Notifications
**Features:**
- Browser push notifications
- Appointment reminders (24-hour advance)
- Medical record updates
- Review notifications
- System announcements
- VAPID-based web push

**Actors:** All users

**Key Functions:**
- `subscribeToPush()` - Enable push notifications
- `sendPushNotification()` - Deliver push message
- `unsubscribeFromPush()` - Disable push notifications

---

### Module 8: Subscription Management

#### Unit 8.1: Subscription Plans
**Features:**
- Three-tier plans (Basic Free, Professional, Pro Plus)
- Feature-based access control
- Monthly/annual billing
- Plan comparison
- Automatic renewal
- Payment processing (PayMongo integration)

**Actors:** Clinic Staff, Administrators

**Key Functions:**
- `viewPlans()` - Display available plans
- `subscribe()` - Purchase subscription
- `upgradePlan()` - Change to higher tier
- `cancelSubscription()` - End subscription

#### Unit 8.2: Payment Processing
**Features:**
- Secure payment gateway
- Payment history
- Invoice generation
- Refund processing
- Payment method management

**Actors:** Clinic Staff

**Key Functions:**
- `processPayment()` - Handle payment transaction
- `generateInvoice()` - Create billing document
- `viewPaymentHistory()` - Display transactions

---

### Module 9: Analytics and Reporting

#### Unit 9.1: Clinic Analytics
**Features:**
- Appointment statistics
- Patient demographics
- Revenue tracking
- Service popularity
- Staff performance metrics
- Trend analysis

**Actors:** Clinic Staff

**Key Functions:**
- `viewDashboard()` - Display analytics overview
- `generateReport()` - Create custom reports
- `exportAnalytics()` - Download data

#### Unit 9.2: System Analytics
**Features:**
- User activity monitoring
- System performance metrics
- Clinic registration trends
- Appointment volume analysis
- Geographic distribution

**Actors:** Administrators

**Key Functions:**
- `monitorSystem()` - Track system health
- `analyzeUsage()` - Review system statistics
- `generateSystemReport()` - Create admin reports

---

### Module 10: Administration

#### Unit 10.1: User Administration
**Features:**
- User account management
- Role assignment
- Account suspension/activation
- User activity logs
- Bulk user operations

**Actors:** Administrators

**Key Functions:**
- `manageUsers()` - Administer user accounts
- `assignRoles()` - Set user permissions
- `suspendAccount()` - Deactivate user
- `viewActivityLogs()` - Monitor user actions

#### Unit 10.2: Clinic Administration
**Features:**
- Clinic approval workflow
- Clinic verification
- Subscription management
- Clinic suspension
- Document review

**Actors:** Administrators

**Key Functions:**
- `reviewClinicApplication()` - Verify clinic registration
- `approveClinic()` - Activate clinic account
- `rejectClinic()` - Deny registration
- `manageClinicSubscription()` - Handle billing

---

## System Architecture

### Technology Stack

#### Frontend
- **Framework**: Vue.js 3.3 with Composition API
- **UI Library**: Shadcn-vue (Radix UI components)
- **Styling**: Tailwind CSS 3.0
- **Build Tool**: Vite 5.0
- **State Management**: Vue Reactivity API
- **Routing**: Inertia.js (server-driven SPA)
- **Icons**: Lucide Icons
- **Maps**: Leaflet.js with OpenStreetMap

#### Backend
- **Framework**: Laravel 11.x
- **Language**: PHP 8.2+
- **Architecture**: MVC (Model-View-Controller)
- **ORM**: Eloquent
- **API**: RESTful endpoints
- **Authentication**: Laravel Fortify
- **Authorization**: Laravel Policies

#### Database
- **Development/Production**: SQLite 3.40+
- **Schema Management**: Laravel Migrations
- **Seeding**: Laravel Seeders
- **Query Builder**: Eloquent ORM

#### Additional Technologies
- **Push Notifications**: Web Push API with VAPID
- **Service Workers**: Workbox (PWA support)
- **Image Processing**: Intervention Image
- **PDF Generation**: DomPDF
- **Queue Management**: Laravel Queue (Database driver)
- **Caching**: File-based cache

### Deployment Architecture

#### Development
- **Environment**: Laravel Herd (local HTTPS)
- **Database**: SQLite (file-based)
- **Asset Compilation**: Vite dev server
- **Hot Module Replacement**: Enabled

#### Production
- **Hosting**: Railway.app (PaaS)
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **SSL**: Let's Encrypt (automatic)
- **CDN**: Railway Edge Network
- **Database**: SQLite (with regular backups)
- **Monitoring**: Laravel Telescope (disabled in production)

### Security Features
- **Password Hashing**: Bcrypt (cost factor 12)
- **CSRF Protection**: Laravel CSRF tokens
- **XSS Prevention**: Automatic escaping
- **SQL Injection**: Parameterized queries (Eloquent)
- **Rate Limiting**: Throttling middleware
- **Input Validation**: Laravel Form Requests
- **File Upload**: Type and size validation
- **Session Security**: Secure cookies, HTTP-only flags

---

# System Diagrams

## 1. Use Case Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          PetConnect System                                  │
│                                                                             │
│  ┌────────────────────────────────────────────────────────────────────┐   │
│  │                                                                     │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ Manage Pets  │              │ Book         │                   │   │
│  │  │              │              │ Appointment  │                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │         │                              │                          │   │
│  │         │                              │                          │   │
Pet Owner ───┤                              │                          │   │
│  │         │                              │                          │   │
│  │         │                              │                          │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ View Medical │              │ Write        │                   │   │
│  │  │ Records      │              │ Reviews      │                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │                                                                     │   │
│  │─────────────────────────────────────────────────────────────────  │   │
│  │                                                                     │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ Manage       │              │ Manage       │                   │   │
│  │  │ Appointments │              │ Medical      │                   │   │
│  │  │              │              │ Records      │                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │         │                              │                          │   │
│  │         │                              │                          │   │
Clinic ──────┤                              │                          │   │
Staff        │                              │                          │   │
│  │         │                              │                          │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ Manage       │              │ View         │                   │   │
│  │  │ Schedule     │              │ Patients     │                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │                                                                     │   │
│  │─────────────────────────────────────────────────────────────────  │   │
│  │                                                                     │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ Manage       │              │ Manage       │                   │   │
│  │  │ Clinics      │              │ Users        │                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │         │                              │                          │   │
│  │         │                              │                          │   │
Admin ───────┤                              │                          │   │
│  │         │                              │                          │   │
│  │  ┌──────────────┐              ┌──────────────┐                   │   │
│  │  │ View         │              │ Manage       │                   │   │
│  │  │ Analytics    │              │ Subscriptions│                   │   │
│  │  └──────────────┘              └──────────────┘                   │   │
│  │                                                                     │   │
│  └────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

Actors:
- Pet Owner: Books appointments, manages pets, views records, writes reviews
- Clinic Staff: Manages appointments, creates medical records, manages schedule
- Admin: Manages system users, clinics, and subscriptions
```

## 2. Data Flow Diagrams (DFD)

### Level 0 - Context Diagram

```
                    ┌─────────────┐
                    │  Pet Owner  │
                    └──────┬──────┘
                           │
            Pet Info       │      Appointment Confirmation
            Appointment    │      Medical Records
            Request        │      Notifications
                           │
                           ▼
    ┌──────────────────────────────────────────────┐
    │                                              │
    │         PetConnect System                    │
    │    (Veterinary Appointment Management)       │
    │                                              │
    └──────────┬───────────────────────┬───────────┘
               │                       │
               │                       │
               │                       │
Appointment    │                       │    Clinic Info
Details        │                       │    Statistics
Patient Data   │                       │    Reports
               │                       │
               ▼                       ▼
         ┌────────────┐          ┌──────────┐
         │   Clinic   │          │  Admin   │
         │   Staff    │          └──────────┘
         └────────────┘
```

### Level 1 - Main System Processes

```
┌──────────┐
│Pet Owner │
└────┬─────┘
     │
     │ Pet Data
     ▼
┌─────────────────┐         ┌──────────────┐
│   1.0           │────────▶│  Pets DB     │
│ Manage Pets     │◀────────│              │
└────────┬────────┘         └──────────────┘
         │
         │ Pet Selection
         │
         ▼
┌─────────────────┐         ┌──────────────┐
│   2.0           │────────▶│ Appointments │
│ Book            │◀────────│     DB       │
│ Appointment     │         └──────────────┘
└────────┬────────┘                │
         │                         │
         │ Appointment ID          │
         │                         │
         ▼                         ▼
┌─────────────────┐         ┌──────────────┐
│   3.0           │────────▶│  Clinics DB  │
│ Process         │◀────────│              │
│ Appointment     │         └──────────────┘
└────────┬────────┘                │
         │                         │
         │ Completed               │
         │ Appointment             │
         │                         │
         ▼                         ▼
┌─────────────────┐         ┌──────────────┐
│   4.0           │────────▶│ Medical      │
│ Manage Medical  │◀────────│ Records DB   │
│ Records         │         └──────────────┘
└────────┬────────┘
         │
         │ Medical Data
         │
         ▼
┌─────────────────┐         ┌──────────────┐
│   5.0           │────────▶│ Reviews DB   │
│ Review          │◀────────│              │
│ System          │         └──────────────┘
└─────────────────┘
```

## 3. System Flowcharts

### 3.1 Appointment Booking Flow

```
        ┌─────────┐
        │  START  │
        └────┬────┘
             │
             ▼
        ┌─────────────┐
        │ Select Clinic│
        └────┬────────┘
             │
             ▼
        ┌─────────────┐
        │ Select Pet  │
        └────┬────────┘
             │
             ▼
        ┌─────────────┐
        │Select Service│
        └────┬────────┘
             │
             ▼
        ┌─────────────┐
        │View Available│
        │  Time Slots │
        └────┬────────┘
             │
             ▼
        ┌─────────────┐
        │Select Date & │
        │    Time     │
        └────┬────────┘
             │
             ▼
        ┌─────────────┐
        │   Confirm   │
        │ Appointment │
        └────┬────────┘
             │
             ▼
        ◇───────────◇
       /  Available? \
      /               \
     /                 \
    │       No         │
    ▼                  │ Yes
┌──────────┐           │
│  Show    │           │
│  Error   │           │
└────┬─────┘           │
     │                 │
     └────────┬────────┘
              │
              ▼
         ┌──────────┐
         │  Create  │
         │Appointment│
         └────┬─────┘
              │
              ▼
         ┌──────────┐
         │   Send   │
         │Notification│
         └────┬─────┘
              │
              ▼
         ┌─────────┐
         │   END   │
         └─────────┘
```

### 3.2 Medical Record Creation Flow

```
        ┌─────────┐
        │  START  │
        └────┬────┘
             │
             ▼
        ◇─────────────◇
       / Appointment   \
      /   Completed?    \
     /                   \
    │       No           │ Yes
    ▼                    │
┌──────────┐             │
│  Cannot  │             │
│  Create  │             │
└────┬─────┘             │
     │                   │
     └──────────┬────────┘
                │
                ▼
           ┌──────────┐
           │  Enter   │
           │Diagnosis │
           └────┬─────┘
                │
                ▼
           ┌──────────┐
           │  Enter   │
           │ Findings │
           └────┬─────┘
                │
                ▼
           ┌──────────┐
           │  Enter   │
           │Treatment │
           └────┬─────┘
                │
                ▼
           ┌──────────┐
           │   Enter  │
           │Prescriptions│
           └────┬─────┘
                │
                ▼
           ┌──────────┐
           │   Save   │
           │  Record  │
           └────┬─────┘
                │
                ▼
           ┌──────────┐
           │  Notify  │
           │Pet Owner │
           └────┬─────┘
                │
                ▼
           ┌─────────┐
           │   END   │
           └─────────┘
```

### 3.3 Clinic Registration Flow

```
        ┌─────────┐
        │  START  │
        └────┬────┘
             │
             ▼
        ┌──────────┐
        │  Create  │
        │  Account │
        └────┬─────┘
             │
             ▼
        ┌──────────┐
        │  Enter   │
        │  Clinic  │
        │   Info   │
        └────┬─────┘
             │
             ▼
        ┌──────────┐
        │  Upload  │
        │Documents │
        └────┬─────┘
             │
             ▼
        ┌──────────┐
        │  Submit  │
        │   For    │
        │ Approval │
        └────┬─────┘
             │
             ▼
        ◇──────────◇
       /   Admin    \
      /   Review     \
     /                \
    │   Rejected      │ Approved
    ▼                 │
┌──────────┐          │
│  Notify  │          │
│Rejection │          │
└────┬─────┘          │
     │                │
     └────────┬───────┘
              │
              ▼
         ┌──────────┐
         │  Activate│
         │  Clinic  │
         └────┬─────┘
              │
              ▼
         ┌──────────┐
         │   Send   │
         │Notification│
         └────┬─────┘
              │
              ▼
         ┌─────────┐
         │   END   │
         └─────────┘
```

## 4. Entity Relationship Diagram (ERD)

```
┌─────────────────────┐
│       users         │
├─────────────────────┤
│ PK: id              │
│     name            │
│     email           │
│     password        │
│     role            │
│     is_admin        │
│     created_at      │
│     updated_at      │
└──────────┬──────────┘
           │
           │ 1:1
           │
           ▼
┌─────────────────────┐
│   user_profiles     │
├─────────────────────┤
│ PK: id              │
│ FK: user_id         │
│     phone           │
│     address         │
│     emergency_contact│
│     created_at      │
│     updated_at      │
└─────────────────────┘


┌─────────────────────┐           1:N            ┌─────────────────────┐
│       users         │────────────────────────▶│        pets         │
│     (Pet Owner)     │                         ├─────────────────────┤
└─────────────────────┘                         │ PK: id              │
                                                 │ FK: owner_id        │
                                                 │ FK: type_id         │
                                                 │ FK: breed_id        │
                                                 │     name            │
                                                 │     gender          │
                                                 │     birth_date      │
                                                 │     weight          │
                                                 │     microchip_number│
                                                 │     created_at      │
                                                 │     updated_at      │
                                                 └──────────┬──────────┘
                                                            │
                                                            │ 1:N
                                                            │
                                                            ▼
┌─────────────────────┐           1:N            ┌─────────────────────┐
│clinic_registrations │────────────────────────▶│    appointments     │
├─────────────────────┤                         ├─────────────────────┤
│ PK: id              │                         │ PK: id              │
│ FK: user_id         │                         │ FK: pet_id          │
│     clinic_name     │                         │ FK: owner_id        │
│     email           │                         │ FK: clinic_id       │
│     phone           │                         │ FK: service_id      │
│     address         │                         │ FK: clinic_staff_id │
│     latitude        │                         │     appointment_number│
│     longitude       │                         │     scheduled_at    │
│     status          │                         │     status          │
│     created_at      │                         │     type            │
│     updated_at      │                         │     priority        │
└──────────┬──────────┘                         │     actual_cost     │
           │                                    │     created_at      │
           │ 1:N                                │     updated_at      │
           │                                    └──────────┬──────────┘
           ▼                                               │
┌─────────────────────┐                                   │ 1:1
│   clinic_services   │                                   │
├─────────────────────┤                                   ▼
│ PK: id              │                       ┌─────────────────────┐
│ FK: clinic_id       │                       │pet_medical_records  │
│     name            │                       ├─────────────────────┤
│     description     │                       │ PK: id              │
│     price           │                       │ FK: pet_id          │
│     duration_minutes│                       │ FK: veterinarian_id │
│     created_at      │                       │ FK: clinic_id       │
│     updated_at      │                       │ FK: appointment_id  │
└─────────────────────┘                       │     title           │
                                               │     description     │
                                               │     diagnosis       │
┌─────────────────────┐                       │     findings        │
│   clinic_staff      │                       │     treatment_given │
├─────────────────────┤                       │     prescriptions   │
│ PK: id              │                       │     date            │
│ FK: clinic_id       │                       │     created_at      │
│ FK: user_id         │                       │     updated_at      │
│     name            │                       └─────────────────────┘
│     role            │
│     specializations │
│     license_number  │
│     created_at      │
│     updated_at      │
└─────────────────────┘


┌─────────────────────┐           1:N            ┌─────────────────────┐
│clinic_registrations │────────────────────────▶│       reviews       │
├─────────────────────┤                         ├─────────────────────┤
│ PK: id              │                         │ PK: id              │
└─────────────────────┘                         │ FK: user_id         │
                                                 │ FK: clinic_id       │
                                                 │ FK: appointment_id  │
                                                 │     rating          │
                                                 │     comment         │
                                                 │     created_at      │
                                                 │     updated_at      │
                                                 └─────────────────────┘


┌─────────────────────┐           N:M            ┌─────────────────────┐
│        pets         │────────────────────────▶│  pet_health_conditions│
└─────────────────────┘                         ├─────────────────────┤
                                                 │ PK: id              │
                                                 │ FK: pet_id          │
                                                 │     type            │
                                                 │     name            │
                                                 │     severity        │
                                                 │     status          │
                                                 │     diagnosed_date  │
                                                 │     created_at      │
                                                 │     updated_at      │
                                                 └─────────────────────┘


┌─────────────────────┐           1:N            ┌─────────────────────┐
│subscription_plans   │────────────────────────▶│clinic_subscriptions │
├─────────────────────┤                         ├─────────────────────┤
│ PK: id              │                         │ PK: id              │
│     name            │                         │ FK: clinic_id       │
│     description     │                         │ FK: plan_id         │
│     price           │                         │     status          │
│     billing_cycle   │                         │     starts_at       │
│     features        │                         │     expires_at      │
│     created_at      │                         │     created_at      │
│     updated_at      │                         │     updated_at      │
└─────────────────────┘                         └─────────────────────┘


┌─────────────────────┐
│    pet_types        │
├─────────────────────┤
│ PK: id              │
│     name            │
│     created_at      │
│     updated_at      │
└──────────┬──────────┘
           │
           │ 1:N
           │
           ▼
┌─────────────────────┐
│    pet_breeds       │
├─────────────────────┤
│ PK: id              │
│ FK: pet_type_id     │
│     name            │
│     created_at      │
│     updated_at      │
└─────────────────────┘


┌─────────────────────┐           1:N            ┌─────────────────────┐
│       users         │────────────────────────▶│push_subscriptions   │
└─────────────────────┘                         ├─────────────────────┤
                                                 │ PK: id              │
                                                 │ FK: user_id         │
                                                 │     endpoint        │
                                                 │     public_key      │
                                                 │     auth_token      │
                                                 │     created_at      │
                                                 │     updated_at      │
                                                 └─────────────────────┘
```

## Relationship Summary

### One-to-One (1:1)
- User → User Profile
- Appointment → Pet Medical Record

### One-to-Many (1:N)
- User (Pet Owner) → Pets
- Pet Type → Pet Breeds
- Pet → Pet Health Conditions
- Clinic Registration → Clinic Services
- Clinic Registration → Clinic Staff
- Clinic Registration → Appointments
- Clinic Registration → Reviews
- Pet → Appointments
- User → Appointments
- Subscription Plan → Clinic Subscriptions
- User → Push Subscriptions

### Many-to-Many (N:M)
- Pets ↔ Health Conditions (through pet_health_conditions)

## Key Notes

1. **Users** can have multiple roles (pet owner, clinic staff, admin)
2. **Appointments** link pets, owners, clinics, and services
3. **Medical Records** are created after completed appointments
4. **Clinics** must be approved before they can accept appointments
5. **Reviews** can only be created for completed appointments
6. **Subscriptions** control clinic feature access
7. **Push Notifications** enable real-time alerts for users
