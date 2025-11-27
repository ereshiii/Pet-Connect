<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    modelValue?: string;
    minDate?: string;
    maxDate?: string;
    disabled?: boolean;
    placeholder?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select date',
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isOpen = ref(false);
const selectedDate = ref(props.modelValue || '');
const currentMonth = ref(new Date());

// Initialize current month based on selected date or today
if (props.modelValue) {
    currentMonth.value = new Date(props.modelValue);
} else {
    currentMonth.value = new Date();
}

watch(() => props.modelValue, (newValue) => {
    selectedDate.value = newValue || '';
    if (newValue) {
        currentMonth.value = new Date(newValue);
    }
});

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Helper function to format date as YYYY-MM-DD in local timezone
const formatLocalDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const formattedDate = computed(() => {
    if (!selectedDate.value) return '';
    const [year, month, day] = selectedDate.value.split('-');
    const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
    return date.toLocaleDateString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
});

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const prevLastDay = new Date(year, month, 0);
    
    const firstDayOfWeek = firstDay.getDay();
    const lastDateOfMonth = lastDay.getDate();
    const prevLastDate = prevLastDay.getDate();
    
    const days = [];
    
    // Previous month days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(year, month - 1, prevLastDate - i);
        days.push({
            date: prevLastDate - i,
            month: 'prev',
            fullDate: formatLocalDate(date)
        });
    }
    
    // Current month days
    for (let i = 1; i <= lastDateOfMonth; i++) {
        const date = new Date(year, month, i);
        days.push({
            date: i,
            month: 'current',
            fullDate: formatLocalDate(date)
        });
    }
    
    // Next month days
    const remainingDays = 42 - days.length; // 6 rows * 7 days
    for (let i = 1; i <= remainingDays; i++) {
        const date = new Date(year, month + 1, i);
        days.push({
            date: i,
            month: 'next',
            fullDate: formatLocalDate(date)
        });
    }
    
    return days;
});

const isToday = (dateStr: string) => {
    const today = formatLocalDate(new Date());
    return dateStr === today;
};

const isSelected = (dateStr: string) => {
    return dateStr === selectedDate.value;
};

const isDisabled = (dateStr: string) => {
    if (!dateStr) return true;
    if (props.minDate && dateStr < props.minDate) return true;
    if (props.maxDate && dateStr > props.maxDate) return true;
    return false;
};

const selectDate = (dateStr: string) => {
    if (isDisabled(dateStr)) return;
    selectedDate.value = dateStr;
    emit('update:modelValue', dateStr);
    isOpen.value = false;
};

const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1);
};

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1);
};

const goToToday = () => {
    const today = formatLocalDate(new Date());
    if (!isDisabled(today)) {
        selectDate(today);
        currentMonth.value = new Date();
    }
};

const toggleCalendar = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
    }
};

// Close calendar when clicking outside
const calendarRef = ref<HTMLElement>();
const handleClickOutside = (event: MouseEvent) => {
    if (calendarRef.value && !calendarRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', handleClickOutside);
    } else {
        document.removeEventListener('click', handleClickOutside);
    }
});
</script>

<template>
    <div ref="calendarRef" class="relative">
        <!-- Date Input Button -->
        <button
            type="button"
            @click="toggleCalendar"
            :disabled="disabled"
            :class="cn(
                'w-full px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
                'flex items-center justify-between gap-2',
                disabled && 'opacity-50 cursor-not-allowed',
                props.class
            )"
        >
            <span :class="selectedDate ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'">
                {{ formattedDate || placeholder }}
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </button>

        <!-- Calendar Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg p-4"
            >
                <!-- Month Navigation -->
                <div class="flex items-center justify-between mb-4">
                    <button
                        type="button"
                        @click="previousMonth"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
                    </div>

                    <button
                        type="button"
                        @click="nextMonth"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Days of Week -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div
                        v-for="day in daysOfWeek"
                        :key="day"
                        class="text-center text-xs font-medium text-gray-600 dark:text-gray-400 py-2"
                    >
                        {{ day }}
                    </div>
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-1">
                    <button
                        v-for="(day, index) in calendarDays"
                        :key="index"
                        type="button"
                        @click="selectDate(day.fullDate)"
                        :disabled="isDisabled(day.fullDate)"
                        :class="cn(
                            'aspect-square flex items-center justify-center rounded-lg text-sm transition-all',
                            day.month === 'current' 
                                ? 'text-gray-900 dark:text-gray-100' 
                                : 'text-gray-400 dark:text-gray-600',
                            isToday(day.fullDate) && !isSelected(day.fullDate) && 
                                'bg-blue-100 dark:bg-blue-900/30 font-semibold',
                            isSelected(day.fullDate) && 
                                'bg-blue-600 text-white hover:bg-blue-700 font-semibold',
                            !isSelected(day.fullDate) && !isDisabled(day.fullDate) && 
                                'hover:bg-gray-100 dark:hover:bg-gray-700',
                            isDisabled(day.fullDate) && 
                                'opacity-40 cursor-not-allowed'
                        )"
                    >
                        {{ day.date }}
                    </button>
                </div>

                <!-- Today Button -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="goToToday"
                        class="w-full py-2 px-4 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                    >
                        Today
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
