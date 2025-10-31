<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard, clinicScheduleManagement } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { 
    Calendar,
    Clock,
    Users,
    DollarSign,
    ChevronLeft,
    ChevronRight,
    Plus,
    Settings,
    BarChart3,
    AlertCircle
} from 'lucide-vue-next';

// TypeScript interfaces
interface WeekDate {
    date: string;
    formatted_date: string;
    day_name: string;
    day_short: string;
    is_today: boolean;
    is_weekend: boolean;
}

interface OperatingHour {
    day: string;
    day_display: string;
    is_closed: boolean;
    opening_time: string | null;
    closing_time: string | null;
    break_start_time: string | null;
    break_end_time: string | null;
    formatted_hours: string;
}

interface Appointment {
    id: number;
    time: string;
    formatted_time: string;
    duration: number;
    status: string;
    status_display: string;
    appointment_type: string;
    patient_name: string | null;
    owner_name: string | null;
    service: string;
    staff: string | null;
    notes: string | null;
    is_slot: boolean;
}

interface TimeSlot {
    time: string;
    formatted_time: string;
    is_available: boolean;
    is_break: boolean;
    is_booked: boolean;
    is_past: boolean;
}

interface Staff {
    id: number;
    name: string;
    role: string;
    role_display: string;
    specializations: string[];
}

interface Service {
    id: number;
    name: string;
    duration: number;
    price: number;
    formatted_price: string;
    category: string;
}

interface Stats {
    total_slots: number;
    booked_slots: number;
    available_slots: number;
    utilization_rate: number;
    total_appointments: number;
    confirmed_appointments: number;
    pending_appointments: number;
}

interface Clinic {
    id: number;
    name: string;
}

interface Props {
    clinic: Clinic;
    currentWeek: WeekDate[];
    operatingHours: Record<string, OperatingHour>;
    appointments: Record<string, Appointment[]>;
    availableSlots: Record<string, TimeSlot[]>;
    staff: Staff[];
    services: Service[];
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Schedule Management',
        href: clinicScheduleManagement().url,
    },
];

// Reactive state
const selectedDate = ref(props.currentWeek.find(d => d.is_today)?.date || props.currentWeek[0].date);
const viewMode = ref<'week' | 'day'>('week');
const showOperatingHoursModal = ref(false);
const showCreateSlotModal = ref(false);

// Forms
const operatingHoursForm = useForm({
    hours: Object.values(props.operatingHours).map(hour => ({
        day_of_week: hour.day,
        is_closed: hour.is_closed,
        opening_time: hour.opening_time ? hour.opening_time.substring(0, 5) : '09:00',
        closing_time: hour.closing_time ? hour.closing_time.substring(0, 5) : '17:00',
        break_start_time: hour.break_start_time ? hour.break_start_time.substring(0, 5) : '',
        break_end_time: hour.break_end_time ? hour.break_end_time.substring(0, 5) : '',
    }))
});

const createSlotForm = useForm({
    appointment_date: selectedDate.value,
    appointment_time: '09:00',
    duration: 30,
    service_id: null as number | null,
    staff_id: null as number | null,
    notes: '',
    is_blocked: false,
});

// Computed properties
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const currentWeekStart = computed(() => {
    return props.currentWeek[0]?.formatted_date || '';
});

const currentWeekEnd = computed(() => {
    return props.currentWeek[6]?.formatted_date || '';
});

const selectedDayData = computed(() => {
    return props.currentWeek.find(d => d.date === selectedDate.value);
});

const selectedDayAppointments = computed(() => {
    return props.appointments[selectedDate.value] || [];
});

const selectedDaySlots = computed(() => {
    return props.availableSlots[selectedDate.value] || [];
});

// Methods
const navigateWeek = (direction: 'prev' | 'next') => {
    const currentStart = props.currentWeek[0].date;
    const newStart = new Date(currentStart);
    newStart.setDate(newStart.getDate() + (direction === 'next' ? 7 : -7));
    
    router.get(clinicScheduleManagement().url, {
        start_date: newStart.toISOString().split('T')[0]
    });
};

const goToToday = () => {
    router.get(clinicScheduleManagement().url);
};

const updateOperatingHours = () => {
    operatingHoursForm.patch('/clinic/schedule/operating-hours', {
        onSuccess: () => {
            showOperatingHoursModal.value = false;
        }
    });
};

const createAppointmentSlot = () => {
    createSlotForm.post('/clinic/schedule/appointments', {
        onSuccess: () => {
            showCreateSlotModal.value = false;
            createSlotForm.reset();
        }
    });
};

const getStatusColor = (status: string): string => {
    const colors = {
        'available': 'bg-green-100 text-green-800 border-green-200',
        'blocked': 'bg-gray-100 text-gray-800 border-gray-200',
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'confirmed': 'bg-blue-100 text-blue-800 border-blue-200',
        'in_progress': 'bg-purple-100 text-purple-800 border-purple-200',
        'completed': 'bg-green-100 text-green-800 border-green-200',
        'cancelled': 'bg-red-100 text-red-800 border-red-200',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getSlotColor = (slot: TimeSlot): string => {
    if (slot.is_past) return 'bg-gray-50 text-gray-400 border-gray-200';
    if (slot.is_break) return 'bg-orange-50 text-orange-600 border-orange-200';
    if (slot.is_booked) return 'bg-blue-50 text-blue-600 border-blue-200';
    if (slot.is_available) return 'bg-green-50 text-green-600 border-green-200 hover:bg-green-100 cursor-pointer';
    return 'bg-gray-50 text-gray-500 border-gray-200';
};
</script>

<template>
    <Head title="Schedule Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Schedule Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage clinic appointments and availability for {{ props.clinic.name }}</p>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="showOperatingHoursModal = true"
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 text-sm font-medium"
                    >
                        <Settings class="h-4 w-4" />
                        Operating Hours
                    </button>
                    <button 
                        @click="showCreateSlotModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                    >
                        <Plus class="h-4 w-4" />
                        Create Slot
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Slots</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.total_slots }}</p>
                        </div>
                        <Calendar class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Available Slots</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.available_slots }}</p>
                        </div>
                        <Clock class="h-8 w-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Booked Slots</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.booked_slots }}</p>
                        </div>
                        <Users class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Utilization Rate</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.utilization_rate }}%</p>
                        </div>
                        <BarChart3 class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                    </div>
                </div>
            </div>

            <!-- Date Navigation -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <button 
                            @click="navigateWeek('prev')"
                            class="p-2 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ currentWeekStart }} - {{ currentWeekEnd }}
                        </span>
                        <button 
                            @click="navigateWeek('next')"
                            class="p-2 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                    <button 
                        @click="goToToday"
                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                    >
                        Today
                    </button>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="viewMode = 'week'"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-md',
                            viewMode === 'week' 
                                ? 'bg-blue-600 text-white' 
                                : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
                        ]"
                    >
                        Week View
                    </button>
                    <button 
                        @click="viewMode = 'day'"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-md',
                            viewMode === 'day' 
                                ? 'bg-blue-600 text-white' 
                                : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
                        ]"
                    >
                        Day View
                    </button>
                </div>
            </div>

            <!-- Week View -->
            <div v-if="viewMode === 'week'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Time</span>
                    </div>
                    <div 
                        v-for="day in props.currentWeek" 
                        :key="day.date"
                        class="p-4 text-center border-l border-gray-200 dark:border-gray-700"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/20': day.is_today }"
                    >
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ day.day_short }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ day.formatted_date }}</div>
                    </div>
                </div>

                <!-- Time slots grid -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-for="timeSlot in selectedDaySlots" :key="timeSlot.time" class="grid grid-cols-8 border-b border-gray-100 dark:border-gray-700">
                        <div class="p-2 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
                            {{ timeSlot.formatted_time }}
                        </div>
                        <div 
                            v-for="day in props.currentWeek" 
                            :key="`${day.date}-${timeSlot.time}`"
                            class="p-2 border-l border-gray-100 dark:border-gray-700 min-h-16"
                        >
                            <!-- Show appointments for this time slot -->
                            <div 
                                v-for="appointment in (props.appointments[day.date] || []).filter(apt => apt.time === timeSlot.time)"
                                :key="appointment.id"
                                :class="['p-2 rounded text-xs', getStatusColor(appointment.status)]"
                            >
                                <div class="font-medium">{{ appointment.patient_name || 'Available' }}</div>
                                <div v-if="appointment.patient_name" class="text-xs opacity-75">{{ appointment.service }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day View -->
            <div v-if="viewMode === 'day'" class="grid gap-6 lg:grid-cols-4">
                <!-- Day selector -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Select Day</h3>
                        <div class="space-y-2">
                            <button 
                                v-for="day in props.currentWeek" 
                                :key="day.date"
                                @click="selectedDate = day.date"
                                :class="[
                                    'w-full p-3 text-left rounded-md border',
                                    selectedDate === day.date 
                                        ? 'bg-blue-50 border-blue-200 text-blue-900 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-100' 
                                        : 'border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700',
                                    day.is_today && 'ring-2 ring-blue-500'
                                ]"
                            >
                                <div class="font-medium">{{ day.day_name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ day.formatted_date }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ (props.appointments[day.date] || []).length }} appointments
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Day schedule -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ selectedDayData?.day_name }} - {{ selectedDayData?.formatted_date }}
                            </h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ selectedDayAppointments.length }} appointments
                            </div>
                        </div>

                        <!-- Time slots for selected day -->
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div 
                                v-for="slot in selectedDaySlots" 
                                :key="slot.time"
                                :class="['p-3 rounded-md border', getSlotColor(slot)]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="font-medium">{{ slot.formatted_time }}</div>
                                    <div class="text-xs">
                                        <span v-if="slot.is_past">Past</span>
                                        <span v-else-if="slot.is_break">Break</span>
                                        <span v-else-if="slot.is_booked">Booked</span>
                                        <span v-else-if="slot.is_available">Available</span>
                                        <span v-else>Blocked</span>
                                    </div>
                                </div>

                                <!-- Show appointment if booked -->
                                <div 
                                    v-for="appointment in selectedDayAppointments.filter(apt => apt.time === slot.time)"
                                    :key="appointment.id"
                                    class="mt-2 p-2 bg-white dark:bg-gray-700 rounded border"
                                >
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ appointment.patient_name || 'Available Slot' }}
                                    </div>
                                    <div v-if="appointment.patient_name" class="text-sm text-gray-600 dark:text-gray-400">
                                        Owner: {{ appointment.owner_name }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Service: {{ appointment.service }}
                                    </div>
                                    <div v-if="appointment.staff" class="text-sm text-gray-600 dark:text-gray-400">
                                        Staff: {{ appointment.staff }}
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(appointment.status)]">
                                            {{ appointment.status_display }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ appointment.duration }}min</span>
                                    </div>
                                </div>
                            </div>

                            <!-- No slots message -->
                            <div v-if="selectedDaySlots.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <Clock class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>No time slots available for this day.</p>
                                <p class="text-sm">Check operating hours or add available slots.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Quick Actions</h2>
                <div class="grid gap-4 md:grid-cols-4">
                    <button class="flex items-center gap-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 text-sm">
                        <Calendar class="h-4 w-4" />
                        View All Appointments
                    </button>
                    <button 
                        @click="showOperatingHoursModal = true"
                        class="flex items-center gap-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 text-sm"
                    >
                        <Clock class="h-4 w-4" />
                        Set Break Time
                    </button>
                    <button class="flex items-center gap-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 text-sm">
                        <AlertCircle class="h-4 w-4" />
                        Block Day Off
                    </button>
                    <button class="flex items-center gap-2 p-3 border border-gray-300 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 text-sm">
                        <BarChart3 class="h-4 w-4" />
                        Schedule Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Operating Hours Modal -->
        <div v-if="showOperatingHoursModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Operating Hours</h3>
                
                <form @submit.prevent="updateOperatingHours">
                    <div class="space-y-4">
                        <div 
                            v-for="(dayHour, index) in operatingHoursForm.hours" 
                            :key="dayHour.day_of_week"
                            class="p-4 border border-gray-200 dark:border-gray-600 rounded-md"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <label class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ dayHour.day_of_week.charAt(0).toUpperCase() + dayHour.day_of_week.slice(1) }}
                                </label>
                                <input 
                                    v-model="operatingHoursForm.hours[index].is_closed"
                                    type="checkbox"
                                    class="rounded"
                                />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Closed</span>
                            </div>
                            
                            <div v-if="!dayHour.is_closed" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Opening Time</label>
                                    <input 
                                        v-model="operatingHoursForm.hours[index].opening_time"
                                        type="time"
                                        class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Closing Time</label>
                                    <input 
                                        v-model="operatingHoursForm.hours[index].closing_time"
                                        type="time"
                                        class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Break Start (Optional)</label>
                                    <input 
                                        v-model="operatingHoursForm.hours[index].break_start_time"
                                        type="time"
                                        class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Break End (Optional)</label>
                                    <input 
                                        v-model="operatingHoursForm.hours[index].break_end_time"
                                        type="time"
                                        class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button"
                            @click="showOperatingHoursModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="operatingHoursForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ operatingHoursForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Slot Modal -->
        <div v-if="showCreateSlotModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Create Appointment Slot</h3>
                
                <form @submit.prevent="createAppointmentSlot">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                            <input 
                                v-model="createSlotForm.appointment_date"
                                type="date"
                                required
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time</label>
                            <input 
                                v-model="createSlotForm.appointment_time"
                                type="time"
                                required
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (minutes)</label>
                            <select 
                                v-model="createSlotForm.duration"
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                            >
                                <option value="15">15 minutes</option>
                                <option value="30">30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service (Optional)</label>
                            <select 
                                v-model="createSlotForm.service_id"
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                            >
                                <option :value="null">General Consultation</option>
                                <option v-for="service in props.services" :key="service.id" :value="service.id">
                                    {{ service.name }} ({{ service.duration }}min)
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Staff (Optional)</label>
                            <select 
                                v-model="createSlotForm.staff_id"
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                            >
                                <option :value="null">Any Available Staff</option>
                                <option v-for="staffMember in props.staff" :key="staffMember.id" :value="staffMember.id">
                                    {{ staffMember.name }} ({{ staffMember.role_display }})
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea 
                                v-model="createSlotForm.notes"
                                rows="3"
                                class="w-full p-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700"
                                placeholder="Optional notes for this slot..."
                            ></textarea>
                        </div>
                        
                        <div class="flex items-center">
                            <input 
                                v-model="createSlotForm.is_blocked"
                                type="checkbox"
                                class="rounded mr-2"
                            />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Block this slot (make unavailable for booking)</label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button"
                            @click="showCreateSlotModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="createSlotForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ createSlotForm.processing ? 'Creating...' : 'Create Slot' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>