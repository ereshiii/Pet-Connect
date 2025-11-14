<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
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
        title: 'Add New Patient',
        href: '#',
    },
];

// Form for adding new patient
const form = useForm({
    // Pet Information
    name: '',
    species: '',
    breed: '',
    gender: '',
    age: '',
    weight: '',
    color: '',
    markings: '',
    microchip_id: '',
    is_neutered: false,
    birth_date: '',
    special_needs: '',
    
    // Owner Information
    owner_name: '',
    owner_email: '',
    owner_phone: '',
    emergency_contact: '',
    
    // Medical Information
    allergies: '',
    current_medications: '',
    medical_conditions: '',
});

// Form submission
const submitForm = () => {
    form.post('/clinic/patients', {
        onSuccess: () => {
            router.visit(clinicPatients().url);
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};

// Cancel and go back
const cancel = () => {
    router.visit(clinicPatients().url);
};
</script>

<template>
    <Head title="Add New Patient" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Add New Patient</h1>
                    <p class="text-muted-foreground">Enter patient information to create a new medical record</p>
                </div>
            </div>

            <!-- Add Patient Form -->
            <div class="max-w-4xl mx-auto">
                <div class="rounded-lg border bg-card p-6">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Pet Information Section -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4 pb-2 border-b">Pet Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Pet Name *</label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        required 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter pet name"
                                    >
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Species *</label>
                                    <select 
                                        v-model="form.species" 
                                        required 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                        <option value="">Select Species</option>
                                        <option value="dog">Dog</option>
                                        <option value="cat">Cat</option>
                                        <option value="bird">Bird</option>
                                        <option value="rabbit">Rabbit</option>
                                        <option value="reptile">Reptile</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div v-if="form.errors.species" class="text-red-500 text-sm mt-1">{{ form.errors.species }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Gender *</label>
                                    <select 
                                        v-model="form.gender" 
                                        required 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="unknown">Unknown</option>
                                    </select>
                                    <div v-if="form.errors.gender" class="text-red-500 text-sm mt-1">{{ form.errors.gender }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Breed</label>
                                    <input 
                                        v-model="form.breed" 
                                        type="text" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter breed (optional)"
                                    >
                                    <div v-if="form.errors.breed" class="text-red-500 text-sm mt-1">{{ form.errors.breed }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Age (years)</label>
                                    <input 
                                        v-model="form.age" 
                                        type="number" 
                                        min="0" 
                                        max="50" 
                                        step="0.1" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter age"
                                    >
                                    <div v-if="form.errors.age" class="text-red-500 text-sm mt-1">{{ form.errors.age }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Weight (kg)</label>
                                    <input 
                                        v-model="form.weight" 
                                        type="number" 
                                        min="0" 
                                        step="0.1" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter weight"
                                    >
                                    <div v-if="form.errors.weight" class="text-red-500 text-sm mt-1">{{ form.errors.weight }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Color</label>
                                    <input 
                                        v-model="form.color" 
                                        type="text" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter color/markings"
                                    >
                                    <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Birth Date</label>
                                    <input 
                                        v-model="form.birth_date" 
                                        type="date" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                    <div v-if="form.errors.birth_date" class="text-red-500 text-sm mt-1">{{ form.errors.birth_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Microchip ID</label>
                                    <input 
                                        v-model="form.microchip_id" 
                                        type="text" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter microchip ID"
                                    >
                                    <div v-if="form.errors.microchip_id" class="text-red-500 text-sm mt-1">{{ form.errors.microchip_id }}</div>
                                </div>
                                <div class="col-span-full">
                                    <label class="block text-sm font-medium mb-2">Markings/Identifying Features</label>
                                    <textarea 
                                        v-model="form.markings" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Describe any distinctive markings or features"
                                    ></textarea>
                                    <div v-if="form.errors.markings" class="text-red-500 text-sm mt-1">{{ form.errors.markings }}</div>
                                </div>
                                <div class="col-span-full">
                                    <label class="flex items-center space-x-2">
                                        <input v-model="form.is_neutered" type="checkbox" class="rounded">
                                        <span class="text-sm font-medium">Spayed/Neutered</span>
                                    </label>
                                </div>
                                <div class="col-span-full">
                                    <label class="block text-sm font-medium mb-2">Special Needs</label>
                                    <textarea 
                                        v-model="form.special_needs" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                        placeholder="Any special care requirements, disabilities, or behavioral notes"
                                    ></textarea>
                                    <div v-if="form.errors.special_needs" class="text-red-500 text-sm mt-1">{{ form.errors.special_needs }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Owner Information Section -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4 pb-2 border-b">Owner Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Owner Name *</label>
                                    <input 
                                        v-model="form.owner_name" 
                                        type="text" 
                                        required 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter owner's full name"
                                    >
                                    <div v-if="form.errors.owner_name" class="text-red-500 text-sm mt-1">{{ form.errors.owner_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Email</label>
                                    <input 
                                        v-model="form.owner_email" 
                                        type="email" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter email address"
                                    >
                                    <div v-if="form.errors.owner_email" class="text-red-500 text-sm mt-1">{{ form.errors.owner_email }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Phone Number *</label>
                                    <input 
                                        v-model="form.owner_phone" 
                                        type="tel" 
                                        required 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Enter phone number"
                                    >
                                    <div v-if="form.errors.owner_phone" class="text-red-500 text-sm mt-1">{{ form.errors.owner_phone }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Emergency Contact</label>
                                    <input 
                                        v-model="form.emergency_contact" 
                                        type="text" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Emergency contact name and phone"
                                    >
                                    <div v-if="form.errors.emergency_contact" class="text-red-500 text-sm mt-1">{{ form.errors.emergency_contact }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information Section -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4 pb-2 border-b">Medical Information</h2>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Known Allergies</label>
                                    <textarea 
                                        v-model="form.allergies" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                        placeholder="List any known allergies (separate with commas)"
                                    ></textarea>
                                    <div v-if="form.errors.allergies" class="text-red-500 text-sm mt-1">{{ form.errors.allergies }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Current Medications</label>
                                    <textarea 
                                        v-model="form.current_medications" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                        placeholder="List current medications (separate with commas)"
                                    ></textarea>
                                    <div v-if="form.errors.current_medications" class="text-red-500 text-sm mt-1">{{ form.errors.current_medications }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Medical Conditions</label>
                                    <textarea 
                                        v-model="form.medical_conditions" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                        placeholder="List any ongoing medical conditions (separate with commas)"
                                    ></textarea>
                                    <div v-if="form.errors.medical_conditions" class="text-red-500 text-sm mt-1">{{ form.errors.medical_conditions }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 pt-6 border-t">
                            <button type="button" @click="cancel" class="btn btn-outline px-6 py-2">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing" class="btn btn-primary px-6 py-2">
                                <span v-if="form.processing">Adding Patient...</span>
                                <span v-else>Add Patient</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>