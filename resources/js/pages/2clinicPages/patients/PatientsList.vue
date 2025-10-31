<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { clinicDashboard, clinicPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: clinicDashboard().url,
    },
    {
        title: 'Patients',
        href: clinicPatients().url,
    },
];

// Props from backend
interface Patient {
    id: number;
    name: string;
    species: string;
    breed: string;
    age: number;
    owner_name: string;
    owner_phone: string;
    last_visit: string;
    next_appointment?: string;
    medical_conditions?: string[];
    vaccination_status: 'up-to-date' | 'overdue' | 'unknown';
}

interface Props {
    patients?: Patient[];
    total_patients?: number;
    recent_visits?: number;
}

const props = withDefaults(defineProps<Props>(), {
    patients: () => [],
    total_patients: 0,
    recent_visits: 0,
});
</script>

<template>
    <Head title="Patient Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Patient Records</h1>
                    <p class="text-muted-foreground">Manage patient information and medical records</p>
                </div>
                <button class="btn btn-primary">
                    + Add New Patient
                </button>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Patients</p>
                            <p class="text-2xl font-bold">{{ total_patients }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            🐾
                        </div>
                    </div>
                </div>
                
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Recent Visits (30 days)</p>
                            <p class="text-2xl font-bold">{{ recent_visits }}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            📊
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="flex gap-4">
                <input 
                    type="search" 
                    placeholder="Search patients..." 
                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <select class="btn btn-outline">
                    <option>All Species</option>
                    <option>Dog</option>
                    <option>Cat</option>
                    <option>Bird</option>
                    <option>Other</option>
                </select>
                <select class="btn btn-outline">
                    <option>Vaccination Status</option>
                    <option>Up to Date</option>
                    <option>Overdue</option>
                    <option>Unknown</option>
                </select>
            </div>

            <!-- Patients Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="(patient, index) in patients" 
                    :key="patient?.id || `patient-${index}`"
                    v-if="patient"
                    class="rounded-lg border bg-card p-6 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-lg">{{ patient.name }}</h3>
                            <p class="text-muted-foreground">{{ patient.breed }} • {{ patient.age }} years</p>
                        </div>
                        <span 
                            :class="{
                                'bg-green-100 text-green-800': patient.vaccination_status === 'up-to-date',
                                'bg-red-100 text-red-800': patient.vaccination_status === 'overdue',
                                'bg-gray-100 text-gray-800': patient.vaccination_status === 'unknown'
                            }"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                        >
                            {{ patient.vaccination_status }}
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Owner:</span>
                            <span class="text-sm">{{ patient.owner_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Phone:</span>
                            <span class="text-sm">{{ patient.owner_phone }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Last Visit:</span>
                            <span class="text-sm">{{ patient.last_visit }}</span>
                        </div>
                        <div v-if="patient.next_appointment" class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">Next:</span>
                            <span class="text-sm">{{ patient.next_appointment }}</span>
                        </div>
                    </div>

                    <!-- Medical Conditions -->
                    <div v-if="patient.medical_conditions?.length" class="mb-4">
                        <p class="text-sm font-medium mb-2">Medical Conditions:</p>
                        <div class="flex flex-wrap gap-1">
                            <span 
                                v-for="condition in patient.medical_conditions" 
                                :key="condition"
                                class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs"
                            >
                                {{ condition }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button class="btn btn-sm btn-primary flex-1">View Record</button>
                        <button class="btn btn-sm btn-outline">Edit</button>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="patients.length === 0" class="col-span-full text-center py-12">
                    <div class="text-muted-foreground">
                        <p class="text-lg mb-2">No patients found</p>
                        <p>Add your first patient to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>