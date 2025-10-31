<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinic Dashboard',
        href: '#',
    },
    {
        title: 'Patients',
        href: '#',
    },
    {
        title: 'Patient Record',
        href: '#',
    },
];

// Props from backend
interface MedicalRecord {
    id: number;
    date: string;
    type: 'checkup' | 'vaccination' | 'surgery' | 'treatment' | 'emergency';
    diagnosis: string;
    treatment: string;
    medications?: string[];
    notes: string;
    veterinarian: string;
    attachments?: string[];
}

interface Patient {
    id: number;
    name: string;
    species: string;
    breed: string;
    age: number;
    weight: number;
    color: string;
    microchip_id?: string;
    owner_name: string;
    owner_phone: string;
    owner_email: string;
    emergency_contact?: string;
    medical_records: MedicalRecord[];
    allergies?: string[];
    current_medications?: string[];
    vaccination_records?: Array<{
        vaccine: string;
        date: string;
        next_due: string;
        veterinarian: string;
    }>;
}

interface Props {
    patient: Patient;
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="`${patient.name} - Patient Record`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Patient Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-2xl">🐾</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold">{{ patient.name }}</h1>
                        <p class="text-muted-foreground">{{ patient.breed }} • {{ patient.species }} • {{ patient.age }} years old</p>
                        <p class="text-sm text-muted-foreground">Patient ID: {{ patient.id }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline">📄 Export Record</button>
                    <button class="btn btn-primary">✏️ Add Record</button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Patient Information -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Patient Information</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Weight</label>
                                <p class="text-sm">{{ patient.weight }} kg</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Color</label>
                                <p class="text-sm">{{ patient.color }}</p>
                            </div>
                            <div v-if="patient.microchip_id">
                                <label class="text-sm font-medium text-muted-foreground">Microchip ID</label>
                                <p class="text-sm">{{ patient.microchip_id }}</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h3 class="font-medium mb-3">Owner Information</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Name</label>
                                <p class="text-sm">{{ patient.owner_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Phone</label>
                                <p class="text-sm">{{ patient.owner_phone }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Email</label>
                                <p class="text-sm">{{ patient.owner_email }}</p>
                            </div>
                            <div v-if="patient.emergency_contact">
                                <label class="text-sm font-medium text-muted-foreground">Emergency Contact</label>
                                <p class="text-sm">{{ patient.emergency_contact }}</p>
                            </div>
                        </div>

                        <!-- Allergies -->
                        <div v-if="patient.allergies?.length" class="mt-4">
                            <h3 class="font-medium mb-2">Allergies</h3>
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="allergy in patient.allergies" 
                                    :key="allergy"
                                    class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs"
                                >
                                    {{ allergy }}
                                </span>
                            </div>
                        </div>

                        <!-- Current Medications -->
                        <div v-if="patient.current_medications?.length" class="mt-4">
                            <h3 class="font-medium mb-2">Current Medications</h3>
                            <div class="space-y-1">
                                <p 
                                    v-for="medication in patient.current_medications" 
                                    :key="medication"
                                    class="text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded"
                                >
                                    {{ medication }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Vaccination Records -->
                    <div v-if="patient.vaccination_records?.length" class="mt-6 rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Vaccination Records</h2>
                        <div class="space-y-3">
                            <div 
                                v-for="vaccination in patient.vaccination_records" 
                                :key="vaccination.vaccine"
                                class="border-l-4 border-green-500 pl-3"
                            >
                                <p class="font-medium">{{ vaccination.vaccine }}</p>
                                <p class="text-sm text-muted-foreground">Given: {{ vaccination.date }}</p>
                                <p class="text-sm text-muted-foreground">Next Due: {{ vaccination.next_due }}</p>
                                <p class="text-xs text-muted-foreground">By: {{ vaccination.veterinarian }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Records -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Medical Records</h2>
                            <select class="btn btn-outline">
                                <option>All Records</option>
                                <option>Checkups</option>
                                <option>Vaccinations</option>
                                <option>Surgeries</option>
                                <option>Treatments</option>
                                <option>Emergency</option>
                            </select>
                        </div>

                        <div class="space-y-4">
                            <div 
                                v-for="record in patient.medical_records" 
                                :key="record.id"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <span 
                                            :class="{
                                                'bg-blue-100 text-blue-800': record.type === 'checkup',
                                                'bg-green-100 text-green-800': record.type === 'vaccination',
                                                'bg-red-100 text-red-800': record.type === 'surgery',
                                                'bg-yellow-100 text-yellow-800': record.type === 'treatment',
                                                'bg-purple-100 text-purple-800': record.type === 'emergency'
                                            }"
                                            class="px-2 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ record.type }}
                                        </span>
                                        <span class="text-sm text-muted-foreground">{{ record.date }}</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline">View Details</button>
                                </div>

                                <div class="space-y-2">
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Diagnosis</label>
                                        <p class="text-sm">{{ record.diagnosis }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-muted-foreground">Treatment</label>
                                        <p class="text-sm">{{ record.treatment }}</p>
                                    </div>
                                    <div v-if="record.medications?.length">
                                        <label class="text-sm font-medium text-muted-foreground">Medications</label>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <span 
                                                v-for="medication in record.medications" 
                                                :key="medication"
                                                class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"
                                            >
                                                {{ medication }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="record.notes">
                                        <label class="text-sm font-medium text-muted-foreground">Notes</label>
                                        <p class="text-sm">{{ record.notes }}</p>
                                    </div>
                                    <p class="text-xs text-muted-foreground">Veterinarian: {{ record.veterinarian }}</p>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-if="patient.medical_records.length === 0" class="text-center py-8">
                                <p class="text-muted-foreground">No medical records found</p>
                                <button class="btn btn-primary mt-2">Add First Record</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>