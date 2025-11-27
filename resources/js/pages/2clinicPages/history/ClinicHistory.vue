<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Calendar, Clock, User, PawPrint, TrendingUp, Filter, Search, CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';

interface Pet {
    id: number;
    name: string;
    type: string;
    breed: string;
    age: number;
    gender: string;
}

interface Owner {
    id: number;
    name: string;
    email: string;
    phone: string;
}

interface Service {
    id: number;
    name: string;
    category: string;
}

interface Appointment {
    id: number;
    appointment_number: string;
    appointment_date: string;
    appointment_time: string;
    formatted_date: string;
    formatted_time: string;
    status: 'completed' | 'cancelled' | 'no_show';
    status_display: string;
    appointment_type: string;
    notes: string;
    actual_cost: number;
    formatted_fee: string;
    pet_name: string;
    owner_name: string;
    pet: Pet;
    owner: Owner;
    service: Service;
    created_at: string;
    updated_at: string;
}

interface Stats {
    total: number;
    completed: number;
    cancelled: number;
    no_show: number;
    revenue: number;
    formatted_revenue: string;
    completion_rate: number;
}

interface Filters {
    category: string;
    date_range: string;
    search: string;
}

interface Clinic {
    id: number;
    name: string;
}

interface Props {
    appointments: Appointment[];
    stats: Stats;
    filters: Filters;
    clinic: Clinic;
}

const props = defineProps<Props>();

// Local filter states
const searchQuery = ref(props.filters.search);
const selectedCategory = ref(props.filters.category);
const selectedDateRange = ref(props.filters.date_range);

// Status badge variants
const statusVariants = {
    completed: 'bg-green-100 text-green-800 border-green-200',
    cancelled: 'bg-gray-100 text-gray-800 border-gray-200',
    no_show: 'bg-red-100 text-red-800 border-red-200',
};

// Status icons
const statusIcons = {
    completed: CheckCircle,
    cancelled: XCircle,
    no_show: AlertCircle,
};

// Filtered appointments (client-side filtering for better UX)
const filteredAppointments = computed(() => {
    let filtered = [...props.appointments];

    // Filter by category
    if (selectedCategory.value !== 'all') {
        filtered = filtered.filter(apt => apt.status === selectedCategory.value);
    }

    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(apt => 
            apt.pet_name.toLowerCase().includes(query) ||
            apt.owner_name.toLowerCase().includes(query) ||
            apt.appointment_number.toLowerCase().includes(query) ||
            apt.service?.name?.toLowerCase().includes(query)
        );
    }

    return filtered;
});

// Handle filter changes
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (selectedCategory.value !== 'all') {
        params.append('category', selectedCategory.value);
    }
    
    if (selectedDateRange.value !== 'all') {
        params.append('date_range', selectedDateRange.value);
    }
    
    if (searchQuery.value.trim()) {
        params.append('search', searchQuery.value.trim());
    }

    const url = `/clinic/history${params.toString() ? '?' + params.toString() : ''}`;
    window.location.href = url;
};

// Format revenue safely
const formatRevenue = (amount: number) => {
    return amount ? `₱${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '₱0.00';
};
</script>

<template>
    <AppSidebarLayout>
        <Head title="Appointment History - Clinic Dashboard" />
        
        <div class="container mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Appointment History</h1>
                    <p class="text-muted-foreground">
                        View and manage completed, cancelled, and no-show appointments
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filter Appointments
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="relative">
                            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by pet, owner, or appointment number..."
                                class="pl-10"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Category Filter -->
                        <select 
                            v-model="selectedCategory"
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="all">All Categories</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>

                        <!-- Date Range Filter -->
                        <select 
                            v-model="selectedDateRange"
                            class="border border-input bg-background px-3 py-2 text-sm ring-offset-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                        </select>

                        <!-- Apply Button -->
                        <Button @click="applyFilters" class="w-full">
                            Apply Filters
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Appointments List -->
            <Card>
                <CardHeader>
                    <CardTitle>Appointment History</CardTitle>
                    <CardDescription>
                        {{ filteredAppointments.length }} of {{ appointments.length }} appointments
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredAppointments.length === 0" class="text-center py-8">
                        <Calendar class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-muted-foreground mb-2">No appointments found</h3>
                        <p class="text-sm text-muted-foreground">
                            Try adjusting your search criteria or date range
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="appointment in filteredAppointments"
                            :key="appointment.id"
                            class="border rounded-lg p-4 hover:bg-muted/50 transition-colors cursor-pointer"
                            @click="$inertia.visit(`/clinic/appointments/${appointment.id}`)"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 space-y-2">
                                    <!-- Header -->
                                    <div class="flex items-center gap-3">
                                        <Badge :class="statusVariants[appointment.status]" class="font-medium">
                                            <component 
                                                :is="statusIcons[appointment.status]" 
                                                class="h-3 w-3 mr-1" 
                                            />
                                            {{ appointment.status_display }}
                                        </Badge>
                                        <span class="text-sm text-muted-foreground">
                                            {{ appointment.appointment_number }}
                                        </span>
                                    </div>

                                    <!-- Pet and Owner Info -->
                                    <div class="flex items-center gap-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <PawPrint class="h-4 w-4 text-muted-foreground" />
                                            <span class="font-medium">{{ appointment.pet_name }}</span>
                                            <span class="text-muted-foreground">
                                                ({{ appointment.pet.type }} - {{ appointment.pet.breed }})
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <span>{{ appointment.owner_name }}</span>
                                        </div>
                                    </div>

                                    <!-- Service and Date Info -->
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-4 w-4" />
                                            <span>{{ appointment.formatted_date }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Clock class="h-4 w-4" />
                                            <span>{{ appointment.formatted_time }}</span>
                                        </div>
                                        <div v-if="appointment.service">
                                            <span class="font-medium">{{ appointment.service.name }}</span>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>