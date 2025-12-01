<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard, clinicScheduleManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { Clock, Calendar, Save, AlertCircle, Lock, X } from 'lucide-vue-next';
import { TimePicker } from '@/components/ui/time-picker';

// TypeScript interfaces
interface OperatingHour {
    id?: number;
    day_of_week: string;
    day_display: string;
    is_closed: boolean;
    opening_time: string | null;
    closing_time: string | null;
    break_start_time: string | null;
    break_end_time: string | null;
    formatted_hours: string;
}

interface Clinic {
    id: number;
    name: string;
    email: string;
    phone: string;
}

interface Props {
    clinic: Clinic;
    operatingHours: OperatingHour[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Operating Hours',
        href: clinicScheduleManagement().url,
    },
];

// Days order
const daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

// Generate time slots for TimePicker (every 30 minutes, 24-hour format in 12-hour display)
const generateTimeSlots = () => {
    const slots: string[] = [];
    for (let hour = 0; hour <= 23; hour++) {
        for (let minute = 0; minute < 60; minute += 30) {
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            const displayMinute = minute.toString().padStart(2, '0');
            slots.push(`${displayHour}:${displayMinute} ${period}`);
        }
    }
    return slots;
};

const allTimeSlots = generateTimeSlots();

// Convert 12-hour time (9:00 AM) to 24-hour time (09:00)
const convertTo24Hour = (time12h: string): string => {
    if (!time12h) return '';
    
    // If already in 24-hour format, return as-is
    if (!time12h.includes('AM') && !time12h.includes('PM')) {
        return time12h;
    }
    
    const [time, period] = time12h.split(' ');
    let [hours, minutes] = time.split(':');
    
    if (period === 'PM' && hours !== '12') {
        hours = String(parseInt(hours) + 12);
    } else if (period === 'AM' && hours === '12') {
        hours = '00';
    }
    
    return `${hours.padStart(2, '0')}:${minutes}`;
};

// Convert 24-hour time (09:00) to 12-hour time (9:00 AM)
const convertTo12Hour = (time24h: string): string => {
    if (!time24h) return '';
    
    const [hours, minutes] = time24h.split(':');
    const hour = parseInt(hours);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    
    return `${displayHour}:${minutes} ${period}`;
};

// Initialize form with operating hours from database (convert to 12-hour format for display)
const operatingHoursForm = useForm({
    hours: daysOrder.map(day => {
        const existingHour = props.operatingHours.find(h => h.day_of_week === day);
        return {
            day_of_week: day,
            is_closed: existingHour?.is_closed ?? false,
            opening_time: existingHour?.opening_time ? convertTo12Hour(existingHour.opening_time.substring(0, 5)) : '9:00 AM',
            closing_time: existingHour?.closing_time ? convertTo12Hour(existingHour.closing_time.substring(0, 5)) : '5:00 PM',
        };
    })
});

// Reactive state
const savedMessage = ref(false);

// Methods
const updateOperatingHours = () => {
    // Convert times from 12-hour to 24-hour format before submitting
    const hoursData = operatingHoursForm.hours.map(hour => ({
        ...hour,
        opening_time: hour.is_closed ? null : convertTo24Hour(hour.opening_time),
        closing_time: hour.is_closed ? null : convertTo24Hour(hour.closing_time),
    }));
    
    operatingHoursForm.transform(() => ({ hours: hoursData })).patch('/clinic/schedule/operating-hours', {
        onSuccess: () => {
            savedMessage.value = true;
            setTimeout(() => {
                savedMessage.value = false;
            }, 3000);
        }
    });
};

const copyToAllDays = (sourceIndex: number) => {
    const sourceDay = operatingHoursForm.hours[sourceIndex];
    if (confirm('Copy these hours to all other days?')) {
        operatingHoursForm.hours = operatingHoursForm.hours.map((day, index) => {
            if (index === sourceIndex) return day;
            return {
                ...day,
                is_closed: sourceDay.is_closed,
                opening_time: sourceDay.opening_time,
                closing_time: sourceDay.closing_time,
            };
        });
    }
};

const copyToWeekdays = (sourceIndex: number) => {
    const sourceDay = operatingHoursForm.hours[sourceIndex];
    const weekdayIndices = [0, 1, 2, 3, 4]; // Monday to Friday
    
    if (confirm('Copy these hours to all weekdays (Monday-Friday)?')) {
        weekdayIndices.forEach(index => {
            if (index !== sourceIndex) {
                operatingHoursForm.hours[index] = {
                    ...operatingHoursForm.hours[index],
                    is_closed: sourceDay.is_closed,
                    opening_time: sourceDay.opening_time,
                    closing_time: sourceDay.closing_time,
                };
            }
        });
    }
};

const getDayDisplay = (day: string): string => {
    return day.charAt(0).toUpperCase() + day.slice(1);
};

</script>

<template>
    <Head title="Operating Hours Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-3 md:gap-6 overflow-x-auto rounded-xl p-3 md:p-6">
            <!-- Success Message -->
            <div 
                v-if="savedMessage" 
                class="rounded-lg border bg-card p-3 md:p-4 border-green-500 dark:border-green-700"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 md:gap-3">
                        <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <span class="text-base md:text-xl">✅</span>
                        </div>
                        <div>
                            <p class="text-sm md:text-base font-semibold text-green-800 dark:text-green-300">Success!</p>
                            <p class="text-xs md:text-sm text-green-700 dark:text-green-400">Operating hours updated successfully</p>
                        </div>
                    </div>
                    <button @click="savedMessage = false" class="text-muted-foreground hover:text-foreground">
                        <X class="h-4 w-4 md:h-5 md:w-5" />
                    </button>
                </div>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold text-foreground">Operating Hours</h1>
                    <p class="text-xs md:text-sm text-muted-foreground">
                        Manage your clinic's opening and closing hours for each day of the week
                    </p>
                </div>
                <button 
                    @click="updateOperatingHours"
                    :disabled="operatingHoursForm.processing"
                    class="hidden sm:flex items-center gap-2 px-4 md:px-6 py-2 md:py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm md:text-base"
                >
                    <Save class="h-4 w-4 md:h-5 md:w-5" />
                    {{ operatingHoursForm.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>

            <!-- Floating Save Button for Mobile -->
            <div class="sm:hidden fixed bottom-6 right-6 z-50">
                <button 
                    @click="updateOperatingHours"
                    :disabled="operatingHoursForm.processing"
                    class="flex items-center gap-2 px-5 py-2.5 bg-primary text-primary-foreground rounded-full shadow-lg hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 text-sm"
                >
                    <Save class="h-4 w-4" />
                    {{ operatingHoursForm.processing ? 'Saving...' : 'Save' }}
                </button>
            </div>

            <!-- Operating Hours Form -->
            <form @submit.prevent="updateOperatingHours" class="rounded-lg border bg-card">
                <div class="p-3 md:p-6 border-b">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <h2 class="text-base md:text-lg font-semibold">Weekly Schedule</h2>
                        <div class="flex items-center gap-1.5 md:gap-2 text-xs md:text-sm text-muted-foreground">
                            <AlertCircle class="h-3 w-3 md:h-4 md:w-4" />
                            <span class="hidden sm:inline">Set your clinic's operating hours for each day</span>
                            <span class="sm:hidden">Set operating hours</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 md:p-6 space-y-3 md:space-y-4">
                    <div 
                        v-for="(dayHour, index) in operatingHoursForm.hours" 
                        :key="dayHour.day_of_week"
                        class="p-2 md:p-4 rounded-lg border transition-all duration-200"
                        :class="dayHour.is_closed ? 'bg-muted/50' : 'hover:border-primary'"
                    >
                        <!-- Day Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 md:gap-4 mb-2 md:mb-4">
                            <div class="flex items-center gap-2 md:gap-3">
                                <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-muted flex items-center justify-center flex-shrink-0">
                                    <Clock class="h-4 w-4 md:h-5 md:w-5 text-muted-foreground" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm md:text-base font-semibold">{{ getDayDisplay(dayHour.day_of_week) }}</h3>
                                    <p class="text-xs md:text-sm text-muted-foreground" v-if="!dayHour.is_closed">Click to edit hours</p>
                                    <p class="text-xs md:text-sm text-red-500 dark:text-red-400" v-else>Closed</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                <!-- Copy Options -->
                                <div class="flex gap-1.5 md:gap-2 w-full sm:w-auto">
                                    <button
                                        v-if="!dayHour.is_closed"
                                        type="button"
                                        @click.stop="copyToWeekdays(index)"
                                        class="text-[10px] md:text-xs px-2 md:px-3 py-1 md:py-1.5 border rounded-md hover:bg-muted text-muted-foreground transition-colors flex-1 sm:flex-none whitespace-nowrap"
                                    >
                                        <span class="hidden sm:inline">Copy to Weekdays</span>
                                        <span class="sm:hidden">Weekdays</span>
                                    </button>
                                    <button
                                        v-if="!dayHour.is_closed"
                                        type="button"
                                        @click.stop="copyToAllDays(index)"
                                        class="text-[10px] md:text-xs px-2 md:px-3 py-1 md:py-1.5 border rounded-md hover:bg-muted text-muted-foreground transition-colors flex-1 sm:flex-none whitespace-nowrap"
                                    >
                                        <span class="hidden sm:inline">Copy to All</span>
                                        <span class="sm:hidden">All Days</span>
                                    </button>
                                </div>
                                
                                <!-- Open/Closed Toggle Switch -->
                                <div class="flex items-center gap-1.5 md:gap-2 justify-between sm:justify-start w-full sm:w-auto" @click.stop.prevent @touchstart.stop.prevent>
                                    <span class="text-xs md:text-sm font-medium pointer-events-none" :class="!dayHour.is_closed ? 'text-green-600 dark:text-green-400' : 'text-muted-foreground'">
                                        Open
                                    </span>
                                    <button
                                        type="button"
                                        @click.stop.prevent="operatingHoursForm.hours[index].is_closed = !operatingHoursForm.hours[index].is_closed"
                                        @touchend.stop.prevent="operatingHoursForm.hours[index].is_closed = !operatingHoursForm.hours[index].is_closed"
                                        :class="[
                                            'relative inline-flex h-5 w-9 md:h-6 md:w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 flex-shrink-0 touch-none',
                                            dayHour.is_closed ? 'bg-red-600' : 'bg-green-600'
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-3.5 w-3.5 md:h-4 md:w-4 transform rounded-full bg-white transition-transform pointer-events-none touch-none',
                                                dayHour.is_closed ? 'translate-x-4 md:translate-x-6' : 'translate-x-1'
                                            ]"
                                        />
                                    </button>
                                    <span class="text-xs md:text-sm font-medium pointer-events-none" :class="dayHour.is_closed ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground'">
                                        Closed
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Time Inputs -->
                        <div v-if="!dayHour.is_closed" class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-4">
                            <div>
                                <label class="block text-xs md:text-sm font-medium mb-1">
                                    Opening Time <span class="text-red-500">*</span>
                                </label>
                                <TimePicker
                                    v-model="operatingHoursForm.hours[index].opening_time"
                                    :available-slots="allTimeSlots"
                                    placeholder="Select opening time"
                                />
                            </div>
                            <div>
                                <label class="block text-xs md:text-sm font-medium mb-1">
                                    Closing Time <span class="text-red-500">*</span>
                                </label>
                                <TimePicker
                                    v-model="operatingHoursForm.hours[index].closing_time"
                                    :available-slots="allTimeSlots"
                                    placeholder="Select closing time"
                                />
                            </div>
                        </div>

                        <!-- Closed Message -->
                        <div v-else class="text-center py-4 md:py-8 text-muted-foreground">
                            <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-1.5 md:mb-2">
                                <Lock class="h-5 w-5 md:h-6 md:w-6 text-muted-foreground" />
                            </div>
                            <p class="text-xs md:text-sm">Clinic is closed on this day</p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Help Section -->
            <div class="rounded-lg border bg-card p-3 md:p-6">
                <div class="flex items-center gap-1.5 md:gap-2 mb-2 md:mb-4">
                    <div class="h-6 w-6 md:h-8 md:w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <span class="text-sm md:text-lg">💡</span>
                    </div>
                    <h3 class="text-sm md:text-base font-semibold">Tips & Guidelines</h3>
                </div>
                <ul class="space-y-1.5 md:space-y-2 text-xs md:text-sm text-muted-foreground">
                    <li>• Use "Copy to Weekdays" to quickly set the same hours for Monday through Friday</li>
                    <li>• Use "Copy to All Days" to apply hours to the entire week</li>
                    <li>• Toggle the switch to "Closed" for days when your clinic is not operating</li>
                    <li>• These hours will be displayed to pet owners when they book appointments</li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>