<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

// Props interface for type safety
interface CalendarEvent {
    id: string | number;
    title: string;
    date: string; // YYYY-MM-DD format
    time?: string;
    type?: 'appointment' | 'reminder' | 'event' | 'holiday' | 'blocked';
    color?: string;
    description?: string;
    status?: 'confirmed' | 'pending' | 'cancelled' | 'completed';
}

interface CalendarProps {
    // Display options
    showWeekNumbers?: boolean;
    showWeekends?: boolean;
    highlightToday?: boolean;
    highlightWeekends?: boolean;
    
    // Selection options
    selectable?: boolean;
    multiSelect?: boolean;
    selectMode?: 'single' | 'range' | 'multiple';
    
    // Event handling
    events?: CalendarEvent[];
    showEvents?: boolean;
    maxEventsPerDay?: number;
    
    // Date constraints
    minDate?: string;
    maxDate?: string;
    disabledDates?: string[];
    disabledDaysOfWeek?: number[]; // 0=Sunday, 1=Monday, etc.
    
    // Initial values
    initialDate?: string;
    initialView?: 'month' | 'week' | 'day';
    
    // Styling options
    size?: 'small' | 'medium' | 'large';
    theme?: 'default' | 'minimal' | 'professional';
}

// Props with defaults
const props = withDefaults(defineProps<CalendarProps>(), {
    showWeekNumbers: false,
    showWeekends: true,
    highlightToday: true,
    highlightWeekends: true,
    selectable: true,
    multiSelect: false,
    selectMode: 'single',
    showEvents: true,
    maxEventsPerDay: 3,
    events: () => [],
    disabledDates: () => [],
    disabledDaysOfWeek: () => [],
    initialView: 'month',
    size: 'medium',
    theme: 'default'
});

// Emits for parent component communication
const emit = defineEmits<{
    'date-select': [date: string];
    'date-range-select': [startDate: string, endDate: string];
    'multiple-dates-select': [dates: string[]];
    'event-click': [event: CalendarEvent];
    'month-change': [year: number, month: number];
    'view-change': [view: string];
    'day-hover': [date: string];
    'day-double-click': [date: string];
}>();

// Reactive state
const currentDate = ref(new Date(props.initialDate || new Date()));
const currentView = ref(props.initialView);
const selectedDates = ref<string[]>([]);
const hoveredDate = ref<string | null>(null);
const rangeStart = ref<string | null>(null);

// Constants
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const dayNamesLong = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Computed properties
const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());
const today = computed(() => new Date().toISOString().split('T')[0]);

const monthTitle = computed(() => {
    return `${monthNames[currentMonth.value]} ${currentYear.value}`;
});

const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    
    const days = [];
    
    // Previous month days
    const prevMonth = new Date(year, month - 1, 0);
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(year, month - 1, prevMonth.getDate() - i);
        days.push({
            date: date.getDate(),
            fullDate: formatDate(date),
            isCurrentMonth: false,
            isPrevMonth: true,
            isNextMonth: false,
            isToday: formatDate(date) === today.value,
            isWeekend: date.getDay() === 0 || date.getDay() === 6,
            isDisabled: isDateDisabled(formatDate(date)),
            isSelected: selectedDates.value.includes(formatDate(date)),
            weekNumber: getWeekNumber(date)
        });
    }
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        days.push({
            date: i,
            fullDate: formatDate(date),
            isCurrentMonth: true,
            isPrevMonth: false,
            isNextMonth: false,
            isToday: formatDate(date) === today.value,
            isWeekend: date.getDay() === 0 || date.getDay() === 6,
            isDisabled: isDateDisabled(formatDate(date)),
            isSelected: selectedDates.value.includes(formatDate(date)),
            weekNumber: getWeekNumber(date)
        });
    }
    
    // Next month days to fill the grid
    const remainingDays = 42 - days.length; // 6 weeks * 7 days
    for (let i = 1; i <= remainingDays; i++) {
        const date = new Date(year, month + 1, i);
        days.push({
            date: i,
            fullDate: formatDate(date),
            isCurrentMonth: false,
            isPrevMonth: false,
            isNextMonth: true,
            isToday: formatDate(date) === today.value,
            isWeekend: date.getDay() === 0 || date.getDay() === 6,
            isDisabled: isDateDisabled(formatDate(date)),
            isSelected: selectedDates.value.includes(formatDate(date)),
            weekNumber: getWeekNumber(date)
        });
    }
    
    return days;
});

const eventsMap = computed(() => {
    const map = new Map<string, CalendarEvent[]>();
    props.events.forEach(event => {
        if (!map.has(event.date)) {
            map.set(event.date, []);
        }
        map.get(event.date)!.push(event);
    });
    return map;
});

const calendarWeeks = computed(() => {
    const weeks = [];
    for (let i = 0; i < calendarDays.value.length; i += 7) {
        weeks.push(calendarDays.value.slice(i, i + 7));
    }
    return weeks;
});

// Utility functions
const formatDate = (date: Date): string => {
    return date.toISOString().split('T')[0];
};

const parseDate = (dateString: string): Date => {
    return new Date(dateString + 'T00:00:00');
};

const getWeekNumber = (date: Date): number => {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d.getTime() - yearStart.getTime()) / 86400000) + 1) / 7);
};

const isDateDisabled = (date: string): boolean => {
    const dateObj = parseDate(date);
    const dayOfWeek = dateObj.getDay();
    
    // Check disabled dates
    if (props.disabledDates.includes(date)) {
        return true;
    }
    
    // Check disabled days of week
    if (props.disabledDaysOfWeek.includes(dayOfWeek)) {
        return true;
    }
    
    // Check min/max dates
    if (props.minDate && date < props.minDate) {
        return true;
    }
    
    if (props.maxDate && date > props.maxDate) {
        return true;
    }
    
    return false;
};

const getEventColor = (event: CalendarEvent): string => {
    if (event.color) return event.color;
    
    switch (event.type) {
        case 'appointment':
            return event.status === 'confirmed' ? 'bg-primary' : 
                   event.status === 'pending' ? 'bg-secondary' : 
                   event.status === 'cancelled' ? 'bg-destructive' : 'bg-accent';
        case 'reminder':
            return 'bg-muted';
        case 'event':
            return 'bg-secondary';
        case 'holiday':
            return 'bg-destructive';
        case 'blocked':
            return 'bg-primary';
        default:
            return 'bg-primary';
    }
};

// Event handlers
const handleDateClick = (day: any) => {
    if (day.isDisabled || !props.selectable) return;
    
    const date = day.fullDate;
    
    if (props.selectMode === 'single') {
        selectedDates.value = [date];
        emit('date-select', date);
    } else if (props.selectMode === 'range') {
        if (!rangeStart.value) {
            rangeStart.value = date;
            selectedDates.value = [date];
        } else {
            const start = rangeStart.value;
            const end = date;
            const range = getDateRange(start, end);
            selectedDates.value = range;
            emit('date-range-select', start, end);
            rangeStart.value = null;
        }
    } else if (props.selectMode === 'multiple') {
        if (selectedDates.value.includes(date)) {
            selectedDates.value = selectedDates.value.filter(d => d !== date);
        } else {
            selectedDates.value.push(date);
        }
        emit('multiple-dates-select', selectedDates.value);
    }
};

const handleDateDoubleClick = (day: any) => {
    if (day.isDisabled) return;
    emit('day-double-click', day.fullDate);
};

const handleDateHover = (day: any) => {
    if (day.isDisabled) return;
    hoveredDate.value = day.fullDate;
    emit('day-hover', day.fullDate);
};

const handleEventClick = (event: CalendarEvent, e: Event) => {
    e.stopPropagation();
    emit('event-click', event);
};

const getDateRange = (start: string, end: string): string[] => {
    const dates = [];
    let startDate = parseDate(start);
    let endDate = parseDate(end);
    
    if (startDate > endDate) {
        [startDate, endDate] = [endDate, startDate];
    }
    
    const currentDate = new Date(startDate);
    while (currentDate <= endDate) {
        dates.push(formatDate(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    return dates;
};

// Navigation methods
const goToPrevMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
    emit('month-change', newDate.getFullYear(), newDate.getMonth());
};

const goToNextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
    emit('month-change', newDate.getFullYear(), newDate.getMonth());
};

const goToToday = () => {
    currentDate.value = new Date();
    emit('month-change', currentDate.value.getFullYear(), currentDate.value.getMonth());
};

const goToDate = (date: string) => {
    currentDate.value = parseDate(date);
    emit('month-change', currentDate.value.getFullYear(), currentDate.value.getMonth());
};

// Public methods exposed to parent
const clearSelection = () => {
    selectedDates.value = [];
    rangeStart.value = null;
};

const getSelectedDates = () => {
    return [...selectedDates.value];
};

const getEventsForDate = (date: string) => {
    return eventsMap.value.get(date) || [];
};

// Expose methods to parent component
defineExpose({
    clearSelection,
    getSelectedDates,
    getEventsForDate,
    goToDate,
    goToToday
});

// Size classes
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'small':
            return {
                calendar: 'text-[0.75rem]',
                day: 'h-8 w-8',
                event: 'text-[0.75rem] px-1 py-0.5'
            };
        case 'large':
            return {
                calendar: 'text-[1rem]',
                day: 'h-12 w-12',
                event: 'text-[0.875rem] px-2 py-1'
            };
        default:
            return {
                calendar: 'text-[0.875rem]',
                day: 'h-10 w-10',
                event: 'text-[0.75rem] px-1.5 py-0.5'
            };
    }
});

// Theme classes
const themeClasses = computed(() => {
    switch (props.theme) {
        case 'minimal':
            return {
                header: 'border-b',
                dayName: 'text-muted-foreground font-normal',
                today: 'bg-accent text-accent-foreground',
                selected: 'bg-primary text-primary-foreground',
                event: 'rounded-sm'
            };
        case 'professional':
            return {
                header: 'bg-muted border-b',
                dayName: 'text-foreground font-semibold',
                today: 'bg-primary text-primary-foreground',
                selected: 'bg-primary text-primary-foreground',
                event: 'rounded-md'
            };
        default:
            return {
                header: 'border-b',
                dayName: 'text-muted-foreground font-medium',
                today: 'bg-primary text-primary-foreground',
                selected: 'bg-primary text-primary-foreground',
                event: 'rounded'
            };
    }
});
</script>

<template>
    <div :class="['calendar-container', sizeClasses.calendar]" class="bg-background rounded-lg">
        <!-- Calendar Header -->
        <div :class="['calendar-header flex items-center justify-between p-4', themeClasses.header]">
            <div class="flex items-center gap-2">
                <button @click="goToPrevMonth" 
                        class="p-2 hover:bg-muted rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                
                <h2 class="text-[1.125rem] font-semibold text-foreground min-w-[200px] text-center">
                    {{ monthTitle }}
                </h2>
                
                <button @click="goToNextMonth" 
                        class="p-2 hover:bg-muted rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="goToToday" 
                        class="px-3 py-1 text-[0.875rem] bg-primary text-primary-foreground rounded-md hover:bg-primary transition-colors">
                    Today
                </button>
            </div>
        </div>
        
        <!-- Day Names Header -->
        <div class="grid grid-cols-7">
            <div v-if="showWeekNumbers" class="p-2 text-center">
                <span :class="['text-[0.75rem] font-medium', themeClasses.dayName]">Week</span>
            </div>
            <div v-for="(day, index) in dayNames" 
                 :key="day"
                 v-show="showWeekends || (index !== 0 && index !== 6)"
                 class="p-2 text-center">
                <span :class="['text-[0.75rem]', themeClasses.dayName]">{{ day }}</span>
            </div>
        </div>
        
        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <div v-for="(week, weekIndex) in calendarWeeks" 
                 :key="weekIndex" 
                 class="grid grid-cols-7 last:border-b-0">
                
                <!-- Week Number -->
                <div v-if="showWeekNumbers" 
                     class="flex items-center justify-center p-1 bg-muted">
                    <span class="text-[0.75rem] text-muted-foreground">{{ week[0]?.weekNumber }}</span>
                </div>
                
                <!-- Calendar Days -->
                <div v-for="(day, dayIndex) in week" 
                     :key="`${weekIndex}-${dayIndex}`"
                     v-show="showWeekends || (dayIndex !== 0 && dayIndex !== 6)"
                     @click="handleDateClick(day)"
                     @dblclick="handleDateDoubleClick(day)"
                     @mouseenter="handleDateHover(day)"
                     :class="[
                         'calendar-day relative p-1 last:border-r-0 cursor-pointer transition-colors min-h-[60px]',
                         {
                             'bg-muted': !day.isCurrentMonth,
                             'hover:bg-muted': !day.isDisabled && day.isCurrentMonth,
                             'cursor-not-allowed opacity-50': day.isDisabled,
                             'bg-destructive': day.isWeekend && highlightWeekends && day.isCurrentMonth,
                         }
                     ]">
                    
                    <!-- Day Number -->
                    <div class="flex items-center justify-center">
                        <span :class="[
                                'flex items-center justify-center rounded-full font-medium transition-all',
                                sizeClasses.day,
                                {
                                    [themeClasses.today]: day.isToday && highlightToday,
                                    [themeClasses.selected]: day.isSelected,
                                    'text-muted-foreground': !day.isCurrentMonth,
                                    'text-foreground': day.isCurrentMonth && !day.isSelected && !day.isToday,
                                    'opacity-75': hoveredDate === day.fullDate && props.selectMode === 'range' && rangeStart,
                                    'bg-muted text-muted-foreground cursor-not-allowed': day.isDisabled
                                }
                            ]">
                            {{ day.date }}
                        </span>
                    </div>
                    
                    <!-- Events -->
                    <div v-if="showEvents && eventsMap.has(day.fullDate)" 
                         class="mt-1 space-y-0.5">
                        <div v-for="(event, eventIndex) in eventsMap.get(day.fullDate)?.slice(0, maxEventsPerDay)" 
                             :key="event.id"
                             @click="handleEventClick(event, $event)"
                             :class="[
                                 'event cursor-pointer text-primary-foreground truncate transition-transform hover:scale-105',
                                 sizeClasses.event,
                                 themeClasses.event,
                                 getEventColor(event)
                             ]"
                             :title="`${event.title}${event.time ? ' at ' + event.time : ''}${event.description ? ' - ' + event.description : ''}`">
                            {{ event.title }}
                        </div>
                        
                        <!-- More events indicator -->
                        <div v-if="eventsMap.get(day.fullDate)!.length > maxEventsPerDay"
                             :class="[
                                 'text-center text-muted-foreground cursor-pointer hover:text-foreground',
                                 sizeClasses.event
                             ]">
                            +{{ eventsMap.get(day.fullDate)!.length - maxEventsPerDay }} more
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendar Footer (optional slot) -->
        <div v-if="$slots.footer" class="calendar-footer p-4">
            <slot name="footer" />
        </div>
    </div>
</template>

<style scoped>
.calendar-container {
    border: 1px solid hsl(var(--border));
}

.calendar-day:hover .event {
    opacity: 0.8;
}

.event {
    color: hsl(var(--primary-foreground));
    font-size: 0.75rem;
    font-weight: 500;
}

/* Animation for selected dates */
.calendar-day .selected-animation {
    animation: selectPulse 0.3s ease-in-out;
}

@keyframes selectPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Responsive design */
@media (max-width: 640px) {
    .calendar-container {
        font-size: 0.75rem;
    }
    
    .calendar-day {
        min-height: 50px;
    }
}
</style>
