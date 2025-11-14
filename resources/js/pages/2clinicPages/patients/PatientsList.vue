<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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

// Navigate to patient record
const viewPatientRecord = (patientId: number) => {
    router.visit(`/clinic/patient/${patientId}`);
};

// Navigate to add new patient
const addNewPatient = () => {
    router.visit('/clinic/patients/add');
};
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
                <button @click="addNewPatient" class="btn btn-primary">
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

            <!-- Debug Information -->
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                <strong>Debug Info:</strong>
                <br>Patients array length: {{ patients?.length || 0 }}
                <br>First patient: {{ patients?.[0] ? JSON.stringify(patients[0]) : 'None' }}
                <br>Props: {{ JSON.stringify(Object.keys(props)) }}
            </div>

            <!-- Patients Table -->
            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b bg-muted/30">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Patient</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Species & Breed</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Age</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Owner</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Contact</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Last Visit</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Vaccination Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Medical Conditions</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(patient, index) in patients" 
                                :key="patient?.id || `patient-${index}`"
                                class="border-b hover:bg-muted/20 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-lg">🐾</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">{{ patient?.name || 'Unknown' }}</p>
                                            <p class="text-xs text-muted-foreground">ID: {{ patient?.id || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium">{{ patient?.species || 'Unknown' }}</p>
                                        <p class="text-xs text-muted-foreground">{{ patient?.breed || 'Mixed breed' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ patient?.age || 'Unknown' }} years</td>
                                <td class="px-4 py-3 text-sm">{{ patient?.owner_name || 'Unknown' }}</td>
                                <td class="px-4 py-3 text-sm">{{ patient?.owner_phone || 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">{{ patient?.last_visit || 'Never' }}</td>
                                <td class="px-4 py-3">
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
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="patient.medical_conditions?.length" class="flex flex-wrap gap-1">
                                        <span 
                                            v-for="condition in patient.medical_conditions.slice(0, 2)" 
                                            :key="condition"
                                            class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs"
                                        >
                                            {{ condition }}
                                        </span>
                                        <span 
                                            v-if="patient.medical_conditions.length > 2"
                                            class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs"
                                        >
                                            +{{ patient.medical_conditions.length - 2 }} more
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">None</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button @click="viewPatientRecord(patient.id)" class="btn btn-sm btn-primary">View Details</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-if="patients.length === 0" class="text-center py-12">
                    <div class="text-muted-foreground">
                        <p class="text-lg mb-2">No patients found</p>
                        <p>Add your first patient to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>