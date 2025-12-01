<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { 
    Calendar as CalendarIcon, 
    Search,
    RotateCw,
    Filter,
    List,
    ChevronLeft,
    ChevronRight,
    Plus,
    AlertCircle,
    User,
    X
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Appointments',
        href: clinicAppointments().url,
    },
];

// Props from backend
interface Props {
    appointments?: Array<{
        id: number;
        pet_name: string;
        owner_name: string;
        appointment_date: string;
        appointment_time: string;
        status: 'completed' | 'cancelled' | 'no-show' | 'pending' | 'confirmed' | 'in_progress';
        service_type: string;
        appointment_type?: 'scheduled' | 'walk-in';
        priority?: 'low' | 'normal' | 'high' | 'critical';
        is_follow_up?: boolean;
        notes?: string;
        created_at?: string;
        updated_at?: string;
    }>;
    userRole?: 'user' | 'clinic' | 'admin';
    clinicId?: number;
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    userRole: 'clinic',
    clinicId: undefined,
});

// Real-time update functionality
const isAutoRefreshEnabled = ref(true);
const refreshInterval = ref<number | null>(null);
const lastUpdated = ref(new Date());
const isRefreshing = ref(false);

// View mode state
const viewMode = ref<'calendar' | 'list'>('list');

// Calendar state
const currentDate = ref(new Date());
const calendarView = ref<'month' | 'week' | 'day'>('month');

// Filter states
const selectedStatus = ref('all');
const selectedTab = ref<'all' | 'emergency'>('all');
const searchQuery = ref('');
const urlParams = new URLSearchParams(window.location.search);
const currentDateFilter = ref(urlParams.get('date') || 'upcoming');

// Walk-in modal state
const showWalkInModal = ref(false);
const walkInForm = ref({
    owner_id: null as number | null,
    pet_id: null as number | null,
    new_owner_email: '',
    new_owner_name: '',
    new_owner_phone: '',
    new_pet_name: '',
    new_pet_species: 'dog' as 'dog' | 'cat' | 'bird' | 'rabbit' | 'other',
    new_pet_breed: '',
    new_pet_date_of_birth: '',
    reason: '',
    notes: '',
    priority: 'normal' as 'low' | 'normal' | 'high' | 'critical',
});
const isSearchingOwners = ref(false);
const searchResults = ref<any[]>([]);
const ownerSearchQuery = ref('');
const isSubmittingWalkIn = ref(false);

// Helper function to check if appointment is past due and not completed
const isPendingCompletion = (apt: any) => {
    const now = new Date();
    const appointmentDateTime = new Date(`${apt.appointment_date}T${apt.appointment_time}`);
    return appointmentDateTime < now && apt.status !== 'completed' && apt.status !== 'cancelled';
};

// Computed filtered appointments
const filteredAppointments = computed(() => {
    let filtered = [...(props.appointments || [])];

    // Filter by tab (emergency vs all)
    if (selectedTab.value === 'emergency') {
        filtered = filtered.filter(apt => apt.appointment_type === 'walk-in');
    }

    if (selectedStatus.value !== 'all') {
        if (selectedStatus.value === 'pending_completion') {
            filtered = filtered.filter(apt => isPendingCompletion(apt));
        } else {
            filtered = filtered.filter(apt => apt.status === selectedStatus.value);
        }
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(apt => 
            apt.pet_name?.toLowerCase().includes(query) ||
            apt.owner_name?.toLowerCase().includes(query) ||
            apt.service_type?.toLowerCase().includes(query)
        );
    }

    // Apply date filter
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    
    switch (currentDateFilter.value) {
        case 'today':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate.toDateString() === today.toDateString();
            });
            break;
        case 'upcoming':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate >= today;
            });
            break;
        case 'past':
            filtered = filtered.filter(apt => {
                const aptDate = new Date(apt.appointment_date);
                return aptDate < today;
            });
            break;
    }

    return filtered;
});

// Refresh appointments
const refreshAppointments = () => {
    isRefreshing.value = true;
    lastUpdated.value = new Date();
    
    router.reload({
        only: ['appointments'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Auto-refresh functions
const toggleAutoRefresh = () => {
    isAutoRefreshEnabled.value = !isAutoRefreshEnabled.value;
    
    if (isAutoRefreshEnabled.value) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
};

const startAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
    
    refreshInterval.value = window.setInterval(() => {
        if (isAutoRefreshEnabled.value && !document.hidden) {
            refreshAppointments();
        }
    }, 30000); // Refresh every 30 seconds
};

const stopAutoRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

const manualRefresh = () => {
    refreshAppointments();
};

// Navigation
const viewAppointmentDetails = (appointmentId: number) => {
    router.visit(clinicAppointmentDetails(appointmentId).url);
};

// Status badge styling
const getStatusBadgeClass = (status: string) => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-medium';
    
    switch (status) {
        case 'pending':
            return `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400`;
        case 'confirmed':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400`;
        case 'in_progress':
            return `${baseClasses} bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400`;
        case 'completed':
            return `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400`;
        case 'cancelled':
            return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400`;
        case 'no-show':
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400`;
    }
};

// Role detection
const isClinic = computed(() => props.userRole === 'clinic');
const isPetOwner = computed(() => props.userRole === 'user');
const isAdmin = computed(() => props.userRole === 'admin');

// View toggle
const toggleView = () => {
    viewMode.value = viewMode.value === 'calendar' ? 'list' : 'calendar';
};

// Calendar navigation functions
const goToPreviousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const goToNextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Calendar computed properties
const formatMonthYear = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

// Convert appointments to calendar events
const calendarEvents = computed(() => {
    return (props.appointments || []).map(apt => ({
        id: apt.id,
        title: apt.pet_name,
        date: apt.appointment_date,
        time: apt.appointment_time,
        status: apt.status,
        metadata: {
            petName: apt.pet_name,
            ownerName: apt.owner_name,
            serviceName: apt.service_type,
            clinicName: apt.owner_name // For clinic view
        }
    }));
});

// Get days in current month for calendar view
const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    const firstDayOfWeek = firstDay.getDay();
    
    const days: any[] = [];
    
    // Previous month days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        days.push({
            date: new Date(year, month - 1, daysInPrevMonth - i),
            isCurrentMonth: false
        });
    }
    
    // Current month days
    for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push({
            date: new Date(year, month, i),
            isCurrentMonth: true
        });
    }
    
    // Next month days to fill the grid
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({
            date: new Date(year, month + 1, i),
            isCurrentMonth: false
        });
    }
    
    return days;
});

// Get events for a specific date
const getEventsForDate = (date: Date): any[] => {
    const dateStr = date.toISOString().split('T')[0];
    return calendarEvents.value.filter(event => event.date === dateStr);
};

// Format time
const formatTime = (time: string) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
};

// Walk-in modal functions
const openWalkInModal = () => {
    showWalkInModal.value = true;
    resetWalkInForm();
};

const closeWalkInModal = () => {
    showWalkInModal.value = false;
    resetWalkInForm();
};

const resetWalkInForm = () => {
    walkInForm.value = {
        owner_id: null,
        pet_id: null,
        new_owner_email: '',
        new_owner_name: '',
        new_owner_phone: '',
        new_pet_name: '',
        new_pet_species: 'dog',
        new_pet_breed: '',
        new_pet_date_of_birth: '',
        reason: '',
        notes: '',
        priority: 'normal',
    };
    searchResults.value = [];
    ownerSearchQuery.value = '';
};

const searchOwners = async () => {
    if (!ownerSearchQuery.value || ownerSearchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    
    isSearchingOwners.value = true;
    
    try {
        const response = await fetch(`/api/owners/search?q=${encodeURIComponent(ownerSearchQuery.value)}`);
        const data = await response.json();
        searchResults.value = data.owners || [];
    } catch (error) {
        console.error('Error searching owners:', error);
        searchResults.value = [];
    } finally {
        isSearchingOwners.value = false;
    }
};

const selectOwner = (owner: any) => {
    walkInForm.value.owner_id = owner.id;
    walkInForm.value.new_owner_name = owner.name;
    walkInForm.value.new_owner_email = owner.email;
    walkInForm.value.new_owner_phone = owner.phone;
    searchResults.value = [];
    ownerSearchQuery.value = owner.name;
};

const submitWalkIn = async () => {
    if (!props.clinicId) {
        alert('Clinic ID is required');
        return;
    }
    
    if (!walkInForm.value.reason) {
        alert('Please provide a reason for the walk-in visit');
        return;
    }
    
    if (!walkInForm.value.owner_id && !walkInForm.value.new_owner_email) {
        alert('Please select an existing owner or provide new owner information');
        return;
    }
    
    if (!walkInForm.value.pet_id && !walkInForm.value.new_pet_name) {
        alert('Please select an existing pet or provide new pet information');
        return;
    }
    
    isSubmittingWalkIn.value = true;
    
    try {
        router.post(`/clinic/appointments/walk-in`, walkInForm.value, {
            onSuccess: () => {
                closeWalkInModal();
                refreshAppointments();
            },
            onError: (errors) => {
                console.error('Walk-in creation failed:', errors);
                alert('Failed to create walk-in appointment. Please check the form and try again.');
            },
            onFinish: () => {
                isSubmittingWalkIn.value = false;
            }
        });
    } catch (error) {
        console.error('Error creating walk-in:', error);
        isSubmittingWalkIn.value = false;
    }
};

const getPriorityBadgeClass = (priority: string) => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-semibold';
    
    switch (priority) {
        case 'critical':
            return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 animate-pulse`;
        case 'high':
            return `${baseClasses} bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400`;
        case 'normal':
            return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400`;
        case 'low':
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400`;
    }
};

// Lifecycle
onMounted(() => {
    startAutoRefresh();
});

onUnmounted(() => {
    stopAutoRefresh();
});
</script>

<template>
    <Head title="Appointments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-3 sm:gap-4 overflow-x-auto rounded-xl p-3 sm:p-4">
            <!-- Header -->
            <div class="rounded-lg border bg-card p-3 sm:p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 sm:mb-6">
                    <div>
                        <h1 class="text-lg sm:text-xl md:text-2xl font-semibold mb-2 flex items-center gap-2">
                            <CalendarIcon class="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
                            Appointments
                        </h1>
                        <p class="text-xs sm:text-sm text-muted-foreground">
                            {{ isClinic ? 'Manage and view your clinic appointments' : 'Manage and view your pet appointments' }}
                        </p>
                    </div>
                    
                    <!-- Control Panel -->
                    <div class="flex flex-col gap-2 sm:gap-3 mt-3 sm:mt-4 md:mt-0">
                        <!-- Create Walk-in Button (Clinic only) -->
                        <button 
                            v-if="isClinic"
                            @click="openWalkInModal"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium flex items-center gap-2 transition-colors"
                        >
                            <Plus class="h-4 w-4" />
                            Emergency Walk-In
                        </button>
                        
                        <!-- View Mode Toggle -->
                        <div class="flex items-center gap-0.5 sm:gap-1 bg-muted rounded-md p-0.5 sm:p-1">
                            <button 
                                @click="viewMode = 'calendar'"
                                class="px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center gap-2"
                                :class="viewMode === 'calendar' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                            >
                                <CalendarIcon class="h-4 w-4" />
                                Calendar
                            </button>
                            <button 
                                @click="viewMode = 'list'"
                                class="px-3 py-1.5 text-xs sm:text-sm rounded transition-colors flex items-center gap-2"
                                :class="viewMode === 'list' ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground hover:text-foreground'"
                            >
                                <List class="h-4 w-4" />
                                List
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Navigation -->
                <div class="mt-4 border-b">
                    <div class="flex gap-4">
                        <button
                            @click="selectedTab = 'all'"
                            class="px-4 py-2 text-sm font-medium transition-colors border-b-2"
                            :class="selectedTab === 'all' 
                                ? 'border-primary text-primary' 
                                : 'border-transparent text-muted-foreground hover:text-foreground'"
                        >
                            All Appointments
                        </button>
                        <button
                            v-if="isClinic"
                            @click="selectedTab = 'emergency'"
                            class="px-4 py-2 text-sm font-medium transition-colors border-b-2 flex items-center gap-2"
                            :class="selectedTab === 'emergency' 
                                ? 'border-red-600 text-red-600' 
                                : 'border-transparent text-muted-foreground hover:text-foreground'"
                        >
                            <AlertCircle class="h-4 w-4" />
                            Emergency Walk-Ins
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calendar/List View Container -->
            <div class="rounded-lg border bg-card">
                <!-- List View -->
                <div v-if="viewMode === 'list'">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b">
                                <tr class="text-left text-sm text-muted-foreground">
                                    <th class="pb-3 pl-6 font-medium">Pet & Owner</th>
                                    <th class="pb-3 font-medium">Date & Time</th>
                                    <th class="pb-3 font-medium">Service</th>
                                    <th class="pb-3 font-medium">Type</th>
                                    <th class="pb-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="(appointment, index) in filteredAppointments" 
                                    :key="appointment?.id || `appointment-${index}`"
                                    @click="viewAppointmentDetails(appointment.id)"
                                    class="border-b hover:bg-muted/50 transition-colors cursor-pointer"
                                >
                                    <td class="py-4 pl-6">
                                        <div>
                                            <p class="font-medium">{{ appointment.pet_name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ appointment.owner_name }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <div>
                                            <p class="font-medium">{{ appointment.appointment_date }}</p>
                                            <p class="text-sm text-muted-foreground">{{ formatTime(appointment.appointment_time) }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <span class="font-medium">{{ appointment.service_type }}</span>
                                        <div v-if="appointment.notes" class="text-sm text-muted-foreground mt-1">
                                            {{ appointment.notes.substring(0, 50) }}{{ appointment.notes.length > 50 ? '...' : '' }}
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex flex-col gap-1">
                                            <span v-if="appointment.appointment_type === 'walk-in'" class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 w-fit">
                                                Walk-In
                                            </span>
                                            <span v-if="appointment.is_follow_up" class="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 w-fit">
                                                Follow-Up
                                            </span>
                                            <span v-if="appointment.priority" :class="getPriorityBadgeClass(appointment.priority)">
                                                {{ appointment.priority.toUpperCase() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6">
                                        <span :class="getStatusBadgeClass(appointment.status)">
                                            {{ appointment.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                        </span>
                                    </td>
                                </tr>
                                
                                <!-- Empty state -->
                                <tr v-if="filteredAppointments.length === 0 && !isRefreshing">
                                    <td colspan="5" class="py-8 text-center text-muted-foreground">
                                        No appointments found
                                    </td>
                                </tr>

                                <!-- Loading state -->
                                <tr v-if="isRefreshing">
                                    <td colspan="5" class="py-8 text-center text-muted-foreground">
                                        <div class="flex items-center justify-center gap-2">
                                            <RotateCw class="h-4 w-4 animate-spin" />
                                            Checking for new appointments...
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Calendar View -->
                <div v-else class="p-3 sm:p-4 md:p-6">
                    <!-- Calendar Header -->
                    <div class="mb-4 sm:mb-6 flex items-center justify-between">
                        <h2 class="text-base sm:text-lg font-semibold">{{ formatMonthYear }}</h2>\n
                        <div class="flex items-center gap-2">
                            <button 
                                @click="goToToday"
                                class="px-3 py-1.5 text-sm border rounded-md hover:bg-muted transition-colors"
                            >
                                Today
                            </button>
                            <button 
                                @click="goToPreviousMonth"
                                class="p-2 border rounded-md hover:bg-muted transition-colors"
                            >
                                <ChevronLeft class="h-4 w-4" />
                            </button>
                            <button 
                                @click="goToNextMonth"
                                class="p-2 border rounded-md hover:bg-muted transition-colors"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">\n
                    <!-- Weekday headers -->
                    <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                         :key="day"
                         class="text-center text-sm font-medium text-muted-foreground py-2">
                        {{ day }}
                    </div>

                    <!-- Calendar days -->
                    <div v-for="(day, index) in daysInMonth" 
                         :key="index"
                         class="min-h-[100px] border rounded-md p-2"
                         :class="[
                             day.isCurrentMonth ? 'bg-background' : 'bg-muted/20',
                             day.date.toDateString() === new Date().toDateString() ? 'ring-2 ring-primary' : ''
                         ]">
                        <div class="text-sm font-medium mb-1"
                             :class="day.isCurrentMonth ? 'text-foreground' : 'text-muted-foreground'">
                            {{ day.date.getDate() }}
                        </div>
                        
                        <!-- Appointments for this day -->
                        <div class="space-y-1">
                            <div v-for="event in getEventsForDate(day.date)" 
                                 :key="event.id"
                                 @click="viewAppointmentDetails(event.id)"
                                 class="text-xs p-1 rounded cursor-pointer hover:opacity-80 transition-opacity"
                                 :class="[
                                     event.status === 'scheduled' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                     event.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                     event.status === 'confirmed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                     event.status === 'in_progress' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400' :
                                     event.status === 'pending_completion' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                     event.status === 'completed' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                                     'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                 ]">
                                <div class="font-medium truncate">{{ event.time }}</div>
                                <div class="truncate">{{ event.title }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
        <!-- Walk-In Modal -->
        <div v-if="showWalkInModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeWalkInModal">
            <div class="bg-background rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-background border-b px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <AlertCircle class="h-5 w-5 text-red-600" />
                        Emergency Walk-In Appointment
                    </h2>
                    <button @click="closeWalkInModal" class="p-2 hover:bg-muted rounded-md transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitWalkIn" class="p-6 space-y-6">
                    <!-- Owner Search/Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Pet Owner</label>
                        <div class="relative">
                            <input
                                v-model="ownerSearchQuery"
                                @input="searchOwners"
                                type="text"
                                placeholder="Search existing owner or enter new owner name..."
                                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                            />
                            <User class="absolute right-3 top-2.5 h-5 w-5 text-muted-foreground" />
                        </div>
                        
                        <!-- Search Results -->
                        <div v-if="searchResults.length > 0" class="mt-2 border rounded-md divide-y max-h-40 overflow-y-auto">
                            <button
                                v-for="owner in searchResults"
                                :key="owner.id"
                                type="button"
                                @click="selectOwner(owner)"
                                class="w-full px-3 py-2 text-left hover:bg-muted transition-colors"
                            >
                                <div class="font-medium">{{ owner.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ owner.email }}</div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- New Owner Details (if creating new) -->
                    <div v-if="!walkInForm.owner_id" class="space-y-4 p-4 bg-muted/20 rounded-md">
                        <p class="text-sm font-medium">New Owner Information</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email *</label>
                                <input
                                    v-model="walkInForm.new_owner_email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone</label>
                                <input
                                    v-model="walkInForm.new_owner_phone"
                                    type="tel"
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pet Information -->
                    <div class="space-y-4 p-4 bg-muted/20 rounded-md">
                        <p class="text-sm font-medium">Pet Information</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Pet Name *</label>
                                <input
                                    v-model="walkInForm.new_pet_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Species *</label>
                                <select
                                    v-model="walkInForm.new_pet_species"
                                    required
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                >
                                    <option value="dog">Dog</option>
                                    <option value="cat">Cat</option>
                                    <option value="bird">Bird</option>
                                    <option value="rabbit">Rabbit</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Breed</label>
                                <input
                                    v-model="walkInForm.new_pet_breed"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Date of Birth</label>
                                <input
                                    v-model="walkInForm.new_pet_date_of_birth"
                                    type="date"
                                    :max="new Date().toISOString().split('T')[0]"
                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Visit Details -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Reason for Visit *</label>
                        <textarea
                            v-model="walkInForm.reason"
                            required
                            rows="3"
                            placeholder="Describe the reason for this emergency visit..."
                            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Priority Level *</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="priority in ['low', 'normal', 'high', 'critical']"
                                :key="priority"
                                type="button"
                                @click="walkInForm.priority = priority as any"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                :class="walkInForm.priority === priority 
                                    ? getPriorityBadgeClass(priority) 
                                    : 'border hover:bg-muted'"
                            >
                                {{ priority.toUpperCase() }}
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Additional Notes</label>
                        <textarea
                            v-model="walkInForm.notes"
                            rows="2"
                            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary"
                        ></textarea>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-3 justify-end pt-4 border-t">
                        <button
                            type="button"
                            @click="closeWalkInModal"
                            class="px-4 py-2 border rounded-md hover:bg-muted transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingWalkIn"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-colors disabled:opacity-50"
                        >
                            {{ isSubmittingWalkIn ? 'Creating...' : 'Create Walk-In Appointment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>\n

<style scoped>
/* Custom animations for real-time updates */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.new-appointment {
    animation: slideIn 0.3s ease-out;
}

/* Critical priority pulse animation */
@keyframes criticalPulse {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    50% {
        opacity: 0.9;
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
    }
}

.animate-pulse {
    animation: criticalPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

