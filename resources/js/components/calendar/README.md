# PetConnect Calendar Component

A comprehensive, reusable calendar component built with Vue 3 and TypeScript, specifically designed for veterinary practice management and appointment scheduling.

## Features

### Core Functionality
- 📅 **Multiple View Modes**: Month, week, and day views
- 🎯 **Flexible Selection**: Single date, date range, or multiple date selection
- 📝 **Event Management**: Display and interact with appointments, reminders, and events
- 🎨 **Customizable Themes**: Default, minimal, and professional themes
- 📱 **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- ♿ **Accessibility**: Full keyboard navigation and screen reader support

### Veterinary-Specific Features
- 🐕 **Appointment Types**: Support for checkups, vaccinations, surgeries, emergencies
- 📊 **Status Tracking**: Confirmed, pending, cancelled, completed appointments
- 👨‍⚕️ **Doctor Scheduling**: Multi-doctor appointment management
- 🏥 **Clinic Integration**: Multiple clinic location support
- 📋 **Client/Pet Info**: Integrated pet and client information display

### Customization Options
- 🎨 **Themes**: Multiple built-in themes with custom theme support
- 📏 **Sizes**: Small, medium, and large size variants
- 🚫 **Date Restrictions**: Min/max dates, disabled dates, disabled days of week
- 🎯 **Event Limits**: Configurable maximum events per day
- 🌙 **Dark Mode**: Full dark mode support

## Basic Usage

```vue
<script setup lang="ts">
import Calendar from '@/components/calendar/calendar.vue';
import type { CalendarEvent } from '@/components/calendar/types';

const events: CalendarEvent[] = [
  {
    id: 1,
    title: 'Bella - Annual Checkup',
    date: '2025-10-27',
    time: '2:30 PM',
    type: 'appointment',
    status: 'confirmed'
  }
];

const handleDateSelect = (date: string) => {
  console.log('Selected date:', date);
};

const handleEventClick = (event: CalendarEvent) => {
  console.log('Event clicked:', event);
};
</script>

<template>
  <Calendar
    :events="events"
    @date-select="handleDateSelect"
    @event-click="handleEventClick"
  />
</template>
```

## Advanced Configuration

```vue
<template>
  <Calendar
    :show-week-numbers="true"
    :show-weekends="true"
    :highlight-today="true"
    :highlight-weekends="true"
    :selectable="true"
    select-mode="range"
    :show-events="true"
    :max-events-per-day="3"
    :events="appointments"
    size="large"
    theme="professional"
    :min-date="'2025-01-01'"
    :max-date="'2025-12-31'"
    :disabled-days-of-week="[0]"
    @date-select="handleDateSelect"
    @date-range-select="handleDateRangeSelect"
    @event-click="handleEventClick"
    @month-change="handleMonthChange"
  >
    <template #footer>
      <div class="text-center text-sm text-gray-600">
        Custom footer content
      </div>
    </template>
  </Calendar>
</template>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `showWeekNumbers` | `boolean` | `false` | Display week numbers |
| `showWeekends` | `boolean` | `true` | Show weekend columns |
| `highlightToday` | `boolean` | `true` | Highlight current date |
| `highlightWeekends` | `boolean` | `true` | Highlight weekend dates |
| `selectable` | `boolean` | `true` | Allow date selection |
| `selectMode` | `'single' \| 'range' \| 'multiple'` | `'single'` | Selection mode |
| `events` | `CalendarEvent[]` | `[]` | Array of events to display |
| `showEvents` | `boolean` | `true` | Display events on calendar |
| `maxEventsPerDay` | `number` | `3` | Maximum events per day |
| `minDate` | `string` | `undefined` | Minimum selectable date |
| `maxDate` | `string` | `undefined` | Maximum selectable date |
| `disabledDates` | `string[]` | `[]` | Array of disabled dates |
| `disabledDaysOfWeek` | `number[]` | `[]` | Disabled days (0=Sunday) |
| `initialDate` | `string` | `undefined` | Initial calendar date |
| `size` | `'small' \| 'medium' \| 'large'` | `'medium'` | Calendar size |
| `theme` | `'default' \| 'minimal' \| 'professional'` | `'default'` | Visual theme |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `date-select` | `date: string` | Single date selected |
| `date-range-select` | `startDate: string, endDate: string` | Date range selected |
| `multiple-dates-select` | `dates: string[]` | Multiple dates selected |
| `event-click` | `event: CalendarEvent` | Event clicked |
| `month-change` | `year: number, month: number` | Month navigation |
| `view-change` | `view: string` | View mode changed |
| `day-hover` | `date: string` | Day hovered |
| `day-double-click` | `date: string` | Day double-clicked |

## Methods (via ref)

```vue
<script setup>
const calendarRef = ref();

// Navigation
calendarRef.value.goToToday();
calendarRef.value.goToDate('2025-12-25');
calendarRef.value.goToPrevMonth();
calendarRef.value.goToNextMonth();

// Selection
calendarRef.value.clearSelection();
const selected = calendarRef.value.getSelectedDates();

// Events
const events = calendarRef.value.getEventsForDate('2025-10-27');
</script>

<template>
  <Calendar ref="calendarRef" />
</template>
```

## Event Types

### CalendarEvent Interface
```typescript
interface CalendarEvent {
  id: string | number;
  title: string;
  date: string; // YYYY-MM-DD
  time?: string;
  type?: 'appointment' | 'reminder' | 'event' | 'holiday' | 'blocked';
  color?: string;
  description?: string;
  status?: 'confirmed' | 'pending' | 'cancelled' | 'completed';
}
```

### Veterinary-Specific Events
```typescript
interface AppointmentEvent extends CalendarEvent {
  type: 'appointment';
  petName?: string;
  petType?: 'dog' | 'cat' | 'bird' | 'rabbit' | 'other';
  clinicName?: string;
  doctorName?: string;
  duration?: number;
  services?: string[];
  confirmationNumber?: string;
}
```

## Styling & Themes

### Built-in Themes

#### Default Theme
- Standard appearance with blue accents
- Balanced spacing and typography
- Good for general use

#### Minimal Theme
- Clean, minimal design
- Reduced visual elements
- Perfect for embedded use

#### Professional Theme
- Polished, business-like appearance
- Enhanced shadows and borders
- Ideal for client-facing interfaces

### Custom Styling
```vue
<Calendar
  :custom-classes="{
    container: 'my-calendar',
    header: 'my-header',
    day: 'my-day',
    event: 'my-event'
  }"
/>
```

## Responsive Design

The calendar automatically adapts to different screen sizes:

- **Desktop**: Full grid with all features
- **Tablet**: Optimized touch targets
- **Mobile**: Compact view with essential features

## Accessibility Features

- **Keyboard Navigation**: Full keyboard support
- **Screen Readers**: ARIA labels and descriptions
- **Color Contrast**: WCAG compliant color schemes
- **Focus Management**: Proper focus indicators

## Integration Examples

### Appointment Scheduling
```vue
<script setup>
import Calendar from '@/components/calendar/calendar.vue';
import { appointmentDetails } from '@/routes';

const appointments = ref([
  {
    id: 1,
    title: 'Bella - Checkup',
    date: '2025-10-27',
    time: '2:30 PM',
    type: 'appointment',
    status: 'confirmed',
    petName: 'Bella',
    doctorName: 'Dr. Johnson'
  }
]);

const handleEventClick = (event) => {
  // Navigate to appointment details
  router.visit(appointmentDetails(event.id).url);
};
</script>
```

### Availability Checking
```vue
<script setup>
const disabledDates = ref(['2025-12-25', '2025-01-01']); // Holidays
const disabledDays = ref([0]); // Sundays

const checkAvailability = (date) => {
  const events = calendarRef.value.getEventsForDate(date);
  return events.length < 8; // Max 8 appointments per day
};
</script>
```

## Performance Considerations

- **Event Filtering**: Large event arrays are efficiently filtered
- **Virtual Scrolling**: Planned for large month ranges
- **Lazy Loading**: Events can be loaded on-demand
- **Caching**: Month data is cached for better performance

## Browser Support

- **Modern Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Mobile**: iOS Safari 14+, Chrome Mobile 90+
- **Features**: ES2020, CSS Grid, Flexbox required

## Contributing

When extending the calendar component:

1. Maintain TypeScript interfaces in `types.ts`
2. Follow Vue 3 Composition API patterns
3. Ensure accessibility compliance
4. Add tests for new features
5. Update documentation

## License

This component is part of the PetConnect application and follows the project's licensing terms.