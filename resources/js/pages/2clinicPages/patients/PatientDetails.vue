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

interface MedicalRecord {
    id: number;
    date: string;
    formatted_date: string;
    type: string;
    title: string;
    description: string;
    treatment: string;
    medication: string;
    veterinarian: string;
    clinic_name: string;
    is_own_clinic: boolean;
    appointment?: {
        id: number;
        appointment_number: string;
        scheduled_at: string;
        service: string | null;
    } | null;
}

interface VaccinationRecord {
    id: number;
    vaccine: string;
    date: string;
    formatted_date: string;
    next_due: string;
    status: string;
    veterinarian: string;
    clinic_name: string;
    is_own_clinic: boolean;
}

interface Props {
    patient: Patient;
    medical_records?: MedicalRecord[];
    vaccination_records?: VaccinationRecord[];
    clinic: {
        id: number;
        name: string;
    };
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

            <!-- Medical Records (Cross-Clinic) -->
            <div v-if="medical_records && medical_records.length > 0" class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Medical History</h2>
                    <span class="text-sm text-muted-foreground">
                        {{ medical_records.length }} record{{ medical_records.length !== 1 ? 's' : '' }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    <div v-for="record in medical_records" :key="record.id" 
                         class="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold">{{ record.title }}</h3>
                                    <span :class="[
                                        'text-xs px-2 py-0.5 rounded-full font-medium',
                                        record.type === 'emergency' ? 'bg-red-100 text-red-800' :
                                        record.type === 'surgery' ? 'bg-purple-100 text-purple-800' :
                                        record.type === 'vaccination' ? 'bg-blue-100 text-blue-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ record.type }}
                                    </span>
                                    <span v-if="record.appointment" 
                                          class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-medium">
                                        📋 {{ record.appointment.appointment_number }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ record.formatted_date }} • Dr. {{ record.veterinarian }}</p>
                            </div>
                            <span :class="[
                                'text-xs px-2.5 py-1 rounded-full font-medium whitespace-nowrap',
                                record.is_own_clinic 
                                    ? 'bg-green-100 text-green-800 border border-green-200' 
                                    : 'bg-amber-100 text-amber-800 border border-amber-200'
                            ]">
                                {{ record.is_own_clinic ? '✓ ' + clinic.name : '🏥 ' + record.clinic_name }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div v-if="record.description">
                                <span class="font-medium">Diagnosis:</span>
                                <p class="text-muted-foreground">{{ record.description }}</p>
                            </div>
                            <div v-if="record.treatment">
                                <span class="font-medium">Treatment:</span>
                                <p class="text-muted-foreground">{{ record.treatment }}</p>
                            </div>
                            <div v-if="record.medication">
                                <span class="font-medium">Medication:</span>
                                <p class="text-muted-foreground">{{ record.medication }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccination Records (Cross-Clinic) -->
            <div v-if="vaccination_records && vaccination_records.length > 0" class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Vaccination History</h2>
                    <span class="text-sm text-muted-foreground">
                        {{ vaccination_records.length }} vaccination{{ vaccination_records.length !== 1 ? 's' : '' }}
                    </span>
                </div>
                
                <div class="space-y-3">
                    <div v-for="vaccination in vaccination_records" :key="vaccination.id"
                         class="border rounded-lg p-4 hover:bg-muted/50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold">{{ vaccination.vaccine }}</h3>
                                    <span :class="[
                                        'text-xs px-2 py-0.5 rounded-full font-medium',
                                        vaccination.status === 'valid' ? 'bg-green-100 text-green-800' :
                                        vaccination.status === 'expired' ? 'bg-red-100 text-red-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ vaccination.status }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground mb-2">
                                    Administered: {{ vaccination.formatted_date }} • Dr. {{ vaccination.veterinarian }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Next due: {{ vaccination.next_due }}
                                </p>
                            </div>
                            <span :class="[
                                'text-xs px-2.5 py-1 rounded-full font-medium whitespace-nowrap',
                                vaccination.is_own_clinic 
                                    ? 'bg-green-100 text-green-800 border border-green-200' 
                                    : 'bg-amber-100 text-amber-800 border border-amber-200'
                            ]">
                                {{ vaccination.is_own_clinic ? '✓ ' + clinic.name : '🏥 ' + vaccination.clinic_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>