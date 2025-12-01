# Mobile Keyboard Composable

## Overview
The `useMobileKeyboard` composable provides automatic input field scrolling on mobile devices to prevent the virtual keyboard from covering form fields.

## Location
`resources/js/composables/useMobileKeyboard.ts`

## Features
- ✅ Automatically detects mobile devices (viewport width ≤ 768px)
- ✅ Scrolls focused input fields into view when keyboard appears
- ✅ Handles INPUT, TEXTAREA, and SELECT elements
- ✅ Smooth scrolling animation
- ✅ Center alignment for better visibility
- ✅ Works with all form types

## Usage

### 1. Import the Composable
```typescript
import { useMobileKeyboard } from '@/composables/useMobileKeyboard';
```

### 2. Initialize in Component
```typescript
const { handleInputFocus } = useMobileKeyboard();
```

### 3. Add to Form Element
```vue
<form @focusin="handleInputFocus">
  <!-- your form fields -->
</form>
```

### 4. Add Extra Padding (Optional but Recommended)
Add bottom padding to your form container to prevent fields from being cut off:
```vue
<div class="p-6 pb-20 sm:pb-6">
  <!-- form content -->
</div>
```

### 5. Update Viewport Meta Tag (Recommended)
```vue
<Head title="Your Page Title">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</Head>
```

## Complete Example

```vue
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useMobileKeyboard } from '@/composables/useMobileKeyboard';

const { handleInputFocus } = useMobileKeyboard();

// ... rest of your component logic
</script>

<template>
  <Head title="My Form Page">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  </Head>

  <div class="p-6 pb-20 sm:pb-6">
    <form @submit.prevent="submitForm" @focusin="handleInputFocus">
      <input type="text" v-model="form.name" />
      <textarea v-model="form.description"></textarea>
      <select v-model="form.category">
        <option>Option 1</option>
      </select>
      
      <button type="submit">Submit</button>
    </form>
  </div>
</template>
```

## Already Implemented On
- ✅ `ProfileSetup.vue` - User profile setup form
- ✅ `registerClinic.vue` - Clinic registration form

## Recommended For
The composable should be added to any page with form inputs, especially:
- `AddPatient.vue` - Patient registration forms
- `EditPatient.vue` - Patient editing forms
- `AppointmentForm.vue` - Appointment booking forms
- `StaffManagement.vue` - Staff management forms
- `ServicesList.vue` - Services management forms
- Any custom forms in your application

## How It Works
1. Listens for `@focusin` events on the form
2. Checks if the device is mobile (width ≤ 768px)
3. Verifies the focused element is an input field
4. Waits 300ms for keyboard animation
5. Scrolls the element to center of viewport
6. Applies additional offset to ensure visibility above keyboard

## Browser Compatibility
- ✅ Chrome/Edge (Android/iOS)
- ✅ Safari (iOS)
- ✅ Firefox (Android/iOS)
- ✅ All modern mobile browsers

## Notes
- The composable only activates on mobile devices to avoid unnecessary behavior on desktop
- The 300ms delay allows the keyboard animation to complete before scrolling
- The `user-scalable=no` viewport setting prevents zoom issues on double-tap
