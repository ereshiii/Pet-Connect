                                            <script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { clinicDashboard, clinicPatients } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

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
        title: 'Edit Patient',
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

// Form data
const form = useForm({
    name: props.patient.name,
    species: props.patient.species,
    breed: props.patient.breed,
    color: props.patient.color || '',
    gender: props.patient.gender,
    birth_date: props.patient.birth_date,
    weight: props.patient.weight || '',
    microchip_id: props.patient.microchip_id || '',
    notes: props.patient.notes || '',
    allergies: props.patient.allergies || '',
    vaccination_status: props.patient.vaccination_status,
    owner_name: props.patient.owner_name,
    owner_email: props.patient.owner_email || '',
    owner_phone: props.patient.owner_phone,
    owner_address: props.patient.owner_address || '',
    owner_city: props.patient.owner_city || '',
    owner_state: props.patient.owner_state || '',
    owner_zip_code: props.patient.owner_zip_code || '',
    emergency_contact_name: props.patient.emergency_contact_name || '',
    emergency_contact_phone: props.patient.emergency_contact_phone || '',
});

// Form validation
const errors = computed(() => form.errors);

// Medical conditions management
const medicalConditions = ref<string[]>(props.patient.medical_conditions || []);
const newCondition = ref('');

const addMedicalCondition = () => {
    if (newCondition.value.trim()) {
        medicalConditions.value.push(newCondition.value.trim());
        newCondition.value = '';
    }
};

const removeMedicalCondition = (index: number) => {
    medicalConditions.value.splice(index, 1);
};

// Submit form
const updatePatient = () => {
    // Include medical conditions in form data
    const formData = {
        ...form.data(),
        medical_conditions: medicalConditions.value
    };

    form.patch(`/clinic/patient/${props.patient.id}`, {
        data: formData,
        onSuccess: () => {
            router.visit(`/clinic/patient/${props.patient.id}`);
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
        }
    });
};

// Cancel and go back
const cancelEdit = () => {
    router.visit(`/clinic/patient/${props.patient.id}`);
};

// Navigate back to patients list
const backToList = () => {
    router.visit('/clinic/patients');
};

// Species options
const speciesOptions = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Hamster', 'Guinea Pig', 'Ferret', 'Reptile', 'Fish', 'Other'];

// Gender options
const genderOptions = ['Male', 'Female', 'Unknown'];

// Vaccination status options
const vaccinationOptions = [
    { value: 'up-to-date', label: 'Up to Date' },
    { value: 'overdue', label: 'Overdue' },
    { value: 'unknown', label: 'Unknown' }
];
</script>

<template>
    <Head :title="`Edit ${patient.name} - Patient`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="backToList" class="btn btn-outline btn-sm">
                        ← Back to Patients
                    </button>
                    <div>
                        <h1 class="text-2xl font-semibold text-foreground">Edit Patient</h1>
                        <p class="text-muted-foreground">{{ patient.name }} - ID: {{ patient.id }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="updatePatient" class="space-y-6">
                <!-- Patient Information -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Patient Information</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Patient Name *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Species *
                            </label>
                            <select
                                v-model="form.species"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.species }"
                            >
                                <option value="">Select Species</option>
                                <option v-for="species in speciesOptions" :key="species" :value="species">
                                    {{ species }}
                                </option>
                            </select>
                            <p v-if="errors.species" class="text-red-500 text-sm mt-1">{{ errors.species }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Breed *
                            </label>
                            <input
                                v-model="form.breed"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.breed }"
                            />
                            <p v-if="errors.breed" class="text-red-500 text-sm mt-1">{{ errors.breed }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Color
                            </label>
                            <input
                                v-model="form.color"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Gender *
                            </label>
                            <select
                                v-model="form.gender"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.gender }"
                            >
                                <option value="">Select Gender</option>
                                <option v-for="gender in genderOptions" :key="gender" :value="gender">
                                    {{ gender }}
                                </option>
                            </select>
                            <p v-if="errors.gender" class="text-red-500 text-sm mt-1">{{ errors.gender }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Birth Date *
                            </label>
                            <input
                                v-model="form.birth_date"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.birth_date }"
                            />
                            <p v-if="errors.birth_date" class="text-red-500 text-sm mt-1">{{ errors.birth_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Weight (kg)
                            </label>
                            <input
                                v-model="form.weight"
                                type="number"
                                step="0.1"
                                min="0"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Microchip ID
                            </label>
                            <input
                                v-model="form.microchip_id"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Medical Information</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Vaccination Status
                            </label>
                            <select
                                v-model="form.vaccination_status"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                                <option v-for="option in vaccinationOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Medical Conditions
                            </label>
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <input
                                        v-model="newCondition"
                                        type="text"
                                        placeholder="Add medical condition"
                                        class="flex-1 px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        @keyup.enter="addMedicalCondition"
                                    />
                                    <button
                                        type="button"
                                        @click="addMedicalCondition"
                                        class="btn btn-outline btn-sm"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div v-if="medicalConditions.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(condition, index) in medicalConditions"
                                        :key="index"
                                        class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm flex items-center gap-2"
                                    >
                                        {{ condition }}
                                        <button
                                            type="button"
                                            @click="removeMedicalCondition(index)"
                                            class="text-orange-600 hover:text-orange-800"
                                        >
                                            ×
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Allergies
                            </label>
                            <textarea
                                v-model="form.allergies"
                                rows="3"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="List any known allergies..."
                            ></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Medical Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Additional medical notes and observations..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Owner Information -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Owner Information</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Owner Name *
                            </label>
                            <input
                                v-model="form.owner_name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.owner_name }"
                            />
                            <p v-if="errors.owner_name" class="text-red-500 text-sm mt-1">{{ errors.owner_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Email
                            </label>
                            <input
                                v-model="form.owner_email"
                                type="email"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.owner_email }"
                            />
                            <p v-if="errors.owner_email" class="text-red-500 text-sm mt-1">{{ errors.owner_email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Phone Number *
                            </label>
                            <input
                                v-model="form.owner_phone"
                                type="tel"
                                required
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-red-500': errors.owner_phone }"
                            />
                            <p v-if="errors.owner_phone" class="text-red-500 text-sm mt-1">{{ errors.owner_phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Emergency Contact Name
                            </label>
                            <input
                                v-model="form.emergency_contact_name"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Emergency Contact Phone
                            </label>
                            <input
                                v-model="form.emergency_contact_phone"
                                type="tel"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Address
                            </label>
                            <input
                                v-model="form.owner_address"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Street address"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                City
                            </label>
                            <input
                                v-model="form.owner_city"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                State
                            </label>
                            <input
                                v-model="form.owner_state"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                ZIP Code
                            </label>
                            <input
                                v-model="form.owner_zip_code"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 justify-end">
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="btn btn-outline"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Patient</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>