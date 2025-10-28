<script setup lang="ts">
import Calendar from './calendar.vue';
import { ref } from 'vue';

// Example usage of the calendar component
const calendarRef = ref();

// Sample events data
const sampleEvents = ref([
    {
        id: 1,
        title: 'Bella - Checkup',
        date: '2025-10-27',
        time: '2:30 PM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Annual health checkup'
    },
    {
        id: 2,
        title: 'Max - Vaccination',
        date: '2025-11-03',
        time: '10:00 AM',
        type: 'appointment',
        status: 'pending',
        description: 'DHPP and Rabies shots'
    },
    {
        id: 3,
        title: 'Luna - Dental',
        date: '2025-11-10',
        time: '2:00 PM',
        type: 'appointment',
        status: 'confirmed',
        description: 'Dental cleaning procedure'
    },
    {
        id: 4,
        title: 'Staff Meeting',
        date: '2025-10-30',
        time: '9:00 AM',
        type: 'event',
        color: 'bg-purple-500',
        description: 'Monthly veterinary staff meeting'
    },
    {
        id: 5,
        title: 'Holiday',
        date: '2025-11-11',
        type: 'holiday',
        description: 'Veterans Day - Clinic Closed'
    },
    {
        id: 6,
        title: 'Reminder',
        date: '2025-11-15',
        type: 'reminder',
        description: 'Order monthly supplies'
    }
]);

// Calendar configuration
const calendarConfig = ref({
    showWeekNumbers: false,
    showWeekends: true,
    highlightToday: true,
    highlightWeekends: true,
    selectable: true,
    selectMode: 'single',
    showEvents: true,
    maxEventsPerDay: 3,
    size: 'medium',
    theme: 'default',
    minDate: '2025-10-01',
    maxDate: '2026-12-31',
    disabledDaysOfWeek: [], // Could disable Sundays: [0]
});

// Event handlers
const handleDateSelect = (date: string) => {
    console.log('Date selected:', date);
};

const handleEventClick = (event: any) => {
    console.log('Event clicked:', event);
    // You could open a modal, navigate to details, etc.
};

const handleMonthChange = (year: number, month: number) => {
    console.log('Month changed:', year, month);
    // Load events for new month, etc.
};

// Utility methods
const clearSelection = () => {
    calendarRef.value?.clearSelection();
};

const goToToday = () => {
    calendarRef.value?.goToToday();
};

const getSelectedDates = () => {
    return calendarRef.value?.getSelectedDates() || [];
};
</script>

<template>
    <div class="p-6 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                PetConnect Calendar Component
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Reusable calendar component for appointments, events, and scheduling
            </p>
        </div>

        <!-- Calendar Controls -->
        <div class="flex flex-wrap gap-2 justify-center">
            <button @click="goToToday" 
                    class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                Go to Today
            </button>
            <button @click="clearSelection" 
                    class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                Clear Selection
            </button>
            <button @click="console.log(getSelectedDates())" 
                    class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                Log Selected Dates
            </button>
        </div>

        <!-- Configuration Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Calendar Size
                </label>
                <select v-model="calendarConfig.size" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <option value="small">Small</option>
                    <option value="medium">Medium</option>
                    <option value="large">Large</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Theme
                </label>
                <select v-model="calendarConfig.theme" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <option value="default">Default</option>
                    <option value="minimal">Minimal</option>
                    <option value="professional">Professional</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Selection Mode
                </label>
                <select v-model="calendarConfig.selectMode" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <option value="single">Single Date</option>
                    <option value="range">Date Range</option>
                    <option value="multiple">Multiple Dates</option>
                </select>
            </div>
        </div>

        <!-- Calendar Component -->
        <Calendar
            ref="calendarRef"
            :show-week-numbers="calendarConfig.showWeekNumbers"
            :show-weekends="calendarConfig.showWeekends"
            :highlight-today="calendarConfig.highlightToday"
            :highlight-weekends="calendarConfig.highlightWeekends"
            :selectable="calendarConfig.selectable"
            :select-mode="calendarConfig.selectMode"
            :show-events="calendarConfig.showEvents"
            :max-events-per-day="calendarConfig.maxEventsPerDay"
            :events="sampleEvents"
            :size="calendarConfig.size"
            :theme="calendarConfig.theme"
            :min-date="calendarConfig.minDate"
            :max-date="calendarConfig.maxDate"
            :disabled-days-of-week="calendarConfig.disabledDaysOfWeek"
            @date-select="handleDateSelect"
            @event-click="handleEventClick"
            @month-change="handleMonthChange"
        >
            <template #footer>
                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>Click dates to select • Double-click for details • Click events for more info</p>
                </div>
            </template>
        </Calendar>

        <!-- Usage Instructions -->
        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                How to Use This Calendar Component
            </h3>
            <div class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                <p><strong>Basic Usage:</strong> Import and use with minimal props for simple date selection</p>
                <p><strong>With Events:</strong> Pass an array of events to display appointments, reminders, etc.</p>
                <p><strong>Customizable:</strong> Adjust size, theme, selection mode, and display options</p>
                <p><strong>Accessible:</strong> Full keyboard navigation and screen reader support</p>
                <p><strong>Responsive:</strong> Automatically adapts to different screen sizes</p>
            </div>
        </div>

        <!-- Event Legend -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-blue-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Confirmed Appointment</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Pending Appointment</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-purple-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Event</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Holiday</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-orange-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Reminder</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styles for the example page */
</style>