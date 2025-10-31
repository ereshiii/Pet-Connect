<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicAppointments, clinicAppointmentDetails } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

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
        status: 'scheduled' | 'completed' | 'cancelled' | 'no-show';
        service_type: string;
        notes?: string;
    }>;
    stats?: {
        today_appointments: number;
        pending_appointments: number;
        completed_today: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    appointments: () => [],
    stats: () => ({
        today_appointments: 0,
        pending_appointments: 0,
        completed_today: 0,
    }),
});

// Navigation function for viewing appointment details
const viewAppointmentDetails = (appointmentId: number) => {
    router.visit(clinicAppointmentDetails(appointmentId).url);
};
</script>

<template>
    <Head title="Appointments Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Appointments</h1>
                    <p class="text-muted-foreground">Manage your clinic appointments</p>
                </div>
                <button class="btn btn-primary">
                    + New Appointment
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Today's Appointments</p>
                            <p class="text-2xl font-bold">{{ stats.today_appointments }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            📅
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending</p>
                            <p class="text-2xl font-bold">{{ stats.pending_appointments }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            ⏳
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Completed Today</p>
                            <p class="text-2xl font-bold">{{ stats.completed_today }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            ✅
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Recent Appointments</h2>
                        <div class="flex gap-2">
                            <select class="btn btn-outline">
                                <option>All Status</option>
                                <option>Scheduled</option>
                                <option>Completed</option>
                                <option>Cancelled</option>
                            </select>
                            <input type="date" class="btn btn-outline" />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-medium text-muted-foreground">Pet & Owner</th>
                                <th class="p-4 font-medium text-muted-foreground">Date & Time</th>
                                <th class="p-4 font-medium text-muted-foreground">Service</th>
                                <th class="p-4 font-medium text-muted-foreground">Status</th>
                                <th class="p-4 font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(appointment, index) in appointments" 
                                :key="appointment?.id || `appointment-${index}`"
                                v-if="appointment"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="p-4">
                                    <div>
                                        <p class="font-medium">{{ appointment.pet_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ appointment.owner_name }}</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div>
                                        <p>{{ appointment.appointment_date }}</p>
                                        <p class="text-sm text-muted-foreground">{{ appointment.appointment_time }}</p>
                                    </div>
                                </td>
                                <td class="p-4">{{ appointment.service_type }}</td>
                                <td class="p-4">
                                    <span 
                                        :class="{
                                            'bg-green-100 text-green-800': appointment.status === 'completed',
                                            'bg-blue-100 text-blue-800': appointment.status === 'scheduled',
                                            'bg-red-100 text-red-800': appointment.status === 'cancelled',
                                            'bg-yellow-100 text-yellow-800': appointment.status === 'no-show'
                                        }"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ appointment.status }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <button @click="viewAppointmentDetails(appointment.id)" 
                                                class="btn btn-sm btn-outline">View</button>
                                        <button class="btn btn-sm btn-outline">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Empty state -->
                            <tr v-if="appointments.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">
                                    No appointments found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>