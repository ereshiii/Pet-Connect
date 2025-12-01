<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { clinicDashboard, clinicPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

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

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

const paginatedPatients = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.patients.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(props.patients.length / itemsPerPage.value);
});

const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const changePerPage = (perPage: number) => {
    itemsPerPage.value = perPage;
    currentPage.value = 1;
};

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
                <!-- <button @click="addNewPatient" class="btn btn-primary" disabled>
                    + Add New Patient
                </button> -->
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                <input 
                    type="search" 
                    placeholder="Search patients..." 
                    class="flex-1 px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xs sm:text-sm bg-background border-border"
                />
                <select class="w-full sm:w-auto px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xs sm:text-sm bg-background border-border">
                    <option>All Species</option>
                    <option>Dog</option>
                    <option>Cat</option>
                    <option>Bird</option>
                    <option>Other</option>
                </select>
            </div>

            <!-- Pagination Controls -->
            <div v-if="patients.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">Show:</span>
                    <select 
                        v-model="itemsPerPage" 
                        @change="changePerPage(itemsPerPage)"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md bg-background text-xs sm:text-sm"
                    >
                        <option :value="10">10 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between sm:justify-start gap-2">
                    <button 
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Previous
                    </button>
                    <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button 
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                    >
                        Next
                    </button>
                </div>
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(patient, index) in paginatedPatients" 
                                :key="patient?.id || `patient-${index}`"
                                @click="viewPatientRecord(patient.id)"
                                class="border-b hover:bg-muted/20 transition-colors cursor-pointer"
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