<script setup lang="ts">
import VueCalComponent from './VueCalComponent.vue';
import { ref } from 'vue';

// Example usage of the new vue-cal calendar component
const calendarRef = ref();

// Sample events data in vue-cal format
const sampleEvents = ref([
    {
        id: 1,
        title: 'Bella - Checkup',
        start: new Date('2025-10-27T14:30:00'),
        end: new Date('2025-10-27T15:00:00'),
        content: 'Annual health checkup',
        class: 'vuecal__event--confirmed',
        background: false,
        allDay: false,
        deletable: true,
        resizable: true,
        editable: true,
    },
    {
        id: 2,
        title: 'Max - Vaccination',
        start: new Date('2025-11-03T10:00:00'),
        end: new Date('2025-11-03T10:30:00'),
        content: 'DHPP and Rabies shots',
        class: 'vuecal__event--pending',
        background: false,
        allDay: false,
        deletable: true,
        resizable: true,
        editable: true,
    },
    {
        id: 3,
        title: 'Luna - Dental',
        start: new Date('2025-11-10T14:00:00'),
        end: new Date('2025-11-10T15:30:00'),
        content: 'Dental cleaning procedure',
        class: 'vuecal__event--confirmed',
        background: false,
        allDay: false,
        deletable: true,
        resizable: true,
        editable: true,
    },
    {
        id: 4,
        title: 'Staff Meeting',
        start: new Date('2025-10-30T09:00:00'),
        end: new Date('2025-10-30T10:00:00'),
        content: 'Monthly veterinary staff meeting',
        class: 'vuecal__event--reminder',
        background: false,
        allDay: false,
        deletable: false,
        resizable: false,
        editable: false,
    },
    {
        id: 5,
        title: 'Holiday - Veterans Day',
        start: new Date('2025-11-11'),
        end: new Date('2025-11-11'),
        content: 'Clinic Closed',
        class: 'vuecal__event--holiday',
        background: true,
        allDay: true,
        deletable: false,
        resizable: false,
        editable: false,
    },
    {
        id: 6,
        title: 'Supply Order Reminder',
        start: new Date('2025-11-15T09:00:00'),
        end: new Date('2025-11-15T09:15:00'),
        content: 'Order monthly supplies',
        class: 'vuecal__event--reminder',
        background: false,
        allDay: false,
        deletable: true,
        resizable: false,
        editable: true,
    }
]);

// Calendar configuration
const currentView = ref('month');
const selectedDate = ref(new Date());

// Event handlers
const handleEventClick = (event: any, e: Event) => {
    console.log('Event clicked:', event);
    // You could open a modal, navigate to details, etc.
};

const handleCellClick = (date: Date, e: Event) => {
    console.log('Date selected:', date.toISOString().split('T')[0]);
    selectedDate.value = date;
};

const handleViewChange = (view: any) => {
    console.log('View changed:', view);
    currentView.value = view.id;
};

const handleEventDrop = (event: any, e: Event) => {
    console.log('Event moved:', event);
};

const handleEventResize = (event: any, e: Event) => {
    console.log('Event resized:', event);
};

// Utility methods
const goToToday = () => {
    calendarRef.value?.goToToday();
};

const switchToWeek = () => {
    calendarRef.value?.switchView('week');
};

const switchToMonth = () => {
    calendarRef.value?.switchView('month');
};

const switchToDay = () => {
    calendarRef.value?.switchView('day');
};

const addSampleEvent = () => {
    const newEvent = {
        id: Date.now(),
        title: 'New Appointment',
        start: new Date(),
        end: new Date(Date.now() + 30 * 60000), // 30 minutes later
        content: 'Sample new appointment',
        class: 'vuecal__event--pending',
        background: false,
        allDay: false,
        deletable: true,
        resizable: true,
        editable: true,
    };
    
    sampleEvents.value.push(newEvent);
};
</script>

<template>
    <div class="p-6 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                PetConnect Vue-Cal Calendar Component
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Professional calendar component powered by Vue-Cal for appointments, events, and scheduling
            </p>
        </div>

        <!-- Calendar Controls -->
        <div class="flex flex-wrap gap-2 justify-center">
            <button @click="goToToday" 
                    class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                Go to Today
            </button>
            <button @click="switchToMonth" 
                    class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                Month View
            </button>
            <button @click="switchToWeek" 
                    class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                Week View
            </button>
            <button @click="switchToDay" 
                    class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                Day View
            </button>
            <button @click="addSampleEvent" 
                    class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                Add Event
            </button>
        </div>

        <!-- Current View Info -->
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Current View: <span class="font-medium">{{ currentView }}</span> | 
                Events: <span class="font-medium">{{ sampleEvents.length }}</span> | 
                Selected Date: <span class="font-medium">{{ selectedDate.toDateString() }}</span>
            </p>
        </div>

        <!-- Calendar Component -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <VueCalComponent
                ref="calendarRef"
                :events="sampleEvents"
                :selected-date="selectedDate"
                :active-view="currentView"
                :editable="true"
                :resizable="true"
                :deletable="true"
                :show-time-in-cells="true"
                height="600px"
                @event-click="handleEventClick"
                @cell-click="handleCellClick"
                @view-change="handleViewChange"
                @event-drop="handleEventDrop"
                @event-resize="handleEventResize"
            />
        </div>

        <!-- Usage Instructions -->
        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                Vue-Cal Calendar Features
            </h3>
            <div class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                <p><strong>Multi-View:</strong> Month, Week, and Day views with smooth transitions</p>
                <p><strong>Drag & Drop:</strong> Move events by dragging them to different dates/times</p>
                <p><strong>Resizable Events:</strong> Resize events to change duration</p>
                <p><strong>Interactive:</strong> Click events for details, click cells to create new events</p>
                <p><strong>Responsive:</strong> Automatically adapts to different screen sizes</p>
                <p><strong>Real-time Updates:</strong> Events update immediately when modified</p>
            </div>
        </div>

        <!-- Event Legend -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Confirmed Appointment</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Pending Appointment</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Completed</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Holiday</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-purple-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Reminder</span>
            </div>
        </div>

        <!-- Tips -->
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <h4 class="font-medium text-yellow-800 dark:text-yellow-200 mb-2">Tips:</h4>
            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                <li>• Drag events to reschedule them</li>
                <li>• Resize events by dragging the edges</li>
                <li>• Click on empty cells to create new events</li>
                <li>• Use the view buttons to switch between Month, Week, and Day views</li>
                <li>• Double-click events for detailed information</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
/* Additional styles for the example page */
</style>