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
    {
        title: 'Patient Details',
        href: '#',
    },
];

// Props from backend
interface Patient {
    id: number;
    name: string;
    species: string;
    breed: string;
    color?: string;
    gender: string;
    birth_date: string;
    age: number;
    weight?: number;
    microchip_id?: string;
    notes?: string;
    allergies?: string;
    medical_conditions?: string[];
    vaccination_status: 'up-to-date' | 'overdue' | 'unknown';
    last_visit?: string;
    next_appointment?: string;
    owner_name: string;
    owner_email?: string;
    owner_phone: string;
    owner_address?: string;
    owner_city?: string;
    owner_state?: string;
    owner_zip_code?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    clinic_id: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    patient: Patient;
}

const props = defineProps<Props>();

// Navigate to edit patient
const editPatient = () => {
    router.visit(`/clinic/patient/${props.patient.id}/edit`);
};

// Navigate to patient history
const viewHistory = () => {
    router.visit(`/clinic/patient/${props.patient.id}/history`);
};

// Navigate back to patients list
const backToList = () => {
    router.visit('/clinic/patients');
};

// Format date helper
const formatDate = (dateString: string) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString();
};

// Calculate age from birth date
const calculateAge = (birthDate: string) => {
    if (!birthDate) return 'Unknown';
    const today = new Date();
    const birth = new Date(birthDate);
    const age = Math.floor((today.getTime() - birth.getTime()) / (365.25 * 24 * 60 * 60 * 1000));
    return `${age} years old`;
};
</script>

<template>
    <Head :title="`${patient.name} - Patient Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="backToList" class="btn btn-outline btn-sm">
                        ← Back to Patients
                    </button>
                    <div>
                        <h1 class="text-2xl font-semibold text-foreground">{{ patient.name }}</h1>
                        <p class="text-muted-foreground">{{ patient.species }} • {{ patient.breed }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="viewHistory" class="btn btn-outline">
                        📋 View History
                    </button>
                    <button @click="editPatient" class="btn btn-primary">
                        Edit Patient
                    </button>
                </div>
            </div>

            <!-- Patient Overview Card -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Patient Overview</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-2xl">🐾</span>
                        </div>
                        <div>
                            <p class="font-medium">{{ patient.name }}</p>
                            <p class="text-sm text-muted-foreground">Patient ID: {{ patient.id }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-muted-foreground">Species & Breed</p>
                        <p class="font-medium">{{ patient.species }}</p>
                        <p class="text-sm text-muted-foreground">{{ patient.breed }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-muted-foreground">Age</p>
                        <p class="font-medium">{{ calculateAge(patient.birth_date) }}</p>
                        <p class="text-sm text-muted-foreground">Born: {{ formatDate(patient.birth_date) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-muted-foreground">Gender</p>
                        <p class="font-medium">{{ patient.gender }}</p>
                    </div>
                    
                    <div v-if="patient.color">
                        <p class="text-sm text-muted-foreground">Color</p>
                        <p class="font-medium">{{ patient.color }}</p>
                    </div>
                    
                    <div v-if="patient.weight">
                        <p class="text-sm text-muted-foreground">Weight</p>
                        <p class="font-medium">{{ patient.weight }} kg</p>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Medical Information</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground mb-2">Vaccination Status</p>
                        <span 
                            :class="{
                                'bg-green-100 text-green-800': patient.vaccination_status === 'up-to-date',
                                'bg-red-100 text-red-800': patient.vaccination_status === 'overdue',
                                'bg-gray-100 text-gray-800': patient.vaccination_status === 'unknown'
                            }"
                            class="px-3 py-1 rounded-full text-sm font-medium"
                        >
                            {{ patient.vaccination_status }}
                        </span>
                    </div>
                    
                    <div v-if="patient.microchip_id">
                        <p class="text-sm text-muted-foreground">Microchip ID</p>
                        <p class="font-medium">{{ patient.microchip_id }}</p>
                    </div>
                    
                    <div v-if="patient.medical_conditions?.length" class="md:col-span-2">
                        <p class="text-sm text-muted-foreground mb-2">Medical Conditions</p>
                        <div class="flex flex-wrap gap-2">
                            <span 
                                v-for="condition in patient.medical_conditions" 
                                :key="condition"
                                class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm"
                            >
                                {{ condition }}
                            </span>
                        </div>
                    </div>
                    
                    <div v-if="patient.allergies" class="md:col-span-2">
                        <p class="text-sm text-muted-foreground">Allergies</p>
                        <p class="font-medium">{{ patient.allergies }}</p>
                    </div>
                    
                    <div v-if="patient.notes" class="md:col-span-2">
                        <p class="text-sm text-muted-foreground">Medical Notes</p>
                        <p class="font-medium">{{ patient.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Owner Information -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Owner Information</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">Owner Name</p>
                        <p class="font-medium">{{ patient.owner_name }}</p>
                    </div>
                    
                    <div v-if="patient.owner_email">
                        <p class="text-sm text-muted-foreground">Email</p>
                        <p class="font-medium">{{ patient.owner_email }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-muted-foreground">Phone</p>
                        <p class="font-medium">{{ patient.owner_phone }}</p>
                    </div>
                    
                    <div v-if="patient.emergency_contact_name">
                        <p class="text-sm text-muted-foreground">Emergency Contact</p>
                        <p class="font-medium">{{ patient.emergency_contact_name }}</p>
                        <p class="text-sm text-muted-foreground">{{ patient.emergency_contact_phone }}</p>
                    </div>
                    
                    <div v-if="patient.owner_address" class="md:col-span-2">
                        <p class="text-sm text-muted-foreground">Address</p>
                        <p class="font-medium">
                            {{ patient.owner_address }}<br>
                            {{ patient.owner_city }}, {{ patient.owner_state }} {{ patient.owner_zip_code }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Visit History -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Visit History</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-if="patient.last_visit">
                        <p class="text-sm text-muted-foreground">Last Visit</p>
                        <p class="font-medium">{{ formatDate(patient.last_visit) }}</p>
                    </div>
                    
                    <div v-if="patient.next_appointment">
                        <p class="text-sm text-muted-foreground">Next Appointment</p>
                        <p class="font-medium">{{ formatDate(patient.next_appointment) }}</p>
                    </div>
                </div>
            </div>

            <!-- Record Information -->
            <div class="rounded-lg border bg-card p-6">
                <h2 class="text-lg font-semibold mb-4">Record Information</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">{{ formatDate(patient.created_at) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-muted-foreground">Last Updated</p>
                        <p class="font-medium">{{ formatDate(patient.updated_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>