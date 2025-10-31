<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { petsIndex, booking, petsEdit } from '@/routes';
import { type BreadcrumbItem } from '@/types';

// Props from backend
interface Props {
    petId: string | number;
    pet?: {
        id: number;
        name: string;
        species: string;
        breed: string;
        age: string;
        gender: string;
        weight: string;
        color: string;
        microchip_id?: string;
        vaccinated: boolean;
        next_checkup: string;
        last_visit: string;
        owner: {
            name: string;
            email: string;
            phone: string;
            address: string;
        };
        medical_history: Array<{
            id: number;
            date: string;
            type: string;
            description: string;
            veterinarian: string;
            clinic: string;
        }>;
        vaccinations: Array<{
            id: number;
            vaccine: string;
            date: string;
            next_due: string;
            veterinarian: string;
        }>;
        medications: Array<{
            id: number;
            name: string;
            dosage: string;
            frequency: string;
            start_date: string;
            end_date?: string;
            notes?: string;
        }>;
    };
}

const props = withDefaults(defineProps<Props>(), {
    pet: () => ({
        id: 1,
        name: 'Bella',
        species: 'Dog',
        breed: 'Golden Retriever',
        age: '3 years',
        gender: 'Female',
        weight: '28.5 kg',
        color: 'Golden',
        microchip_id: 'MC123456789',
        vaccinated: true,
        next_checkup: '2025-11-15',
        last_visit: '2025-08-20',
        owner: {
            name: 'John Doe',
            email: 'john.doe@email.com',
            phone: '+1 (555) 123-4567',
            address: '123 Main Street, City, State 12345',
        },
        medical_history: [
            {
                id: 1,
                date: '2025-08-20',
                type: 'Routine Checkup',
                description: 'Annual wellness examination. Pet is in excellent health.',
                veterinarian: 'Dr. Sarah Johnson',
                clinic: 'PetCare Veterinary Clinic',
            },
            {
                id: 2,
                date: '2025-06-15',
                type: 'Vaccination',
                description: 'Annual vaccination booster shots administered.',
                veterinarian: 'Dr. Michael Chen',
                clinic: 'Happy Paws Veterinary',
            },
            {
                id: 3,
                date: '2025-03-10',
                type: 'Dental Cleaning',
                description: 'Professional dental cleaning and oral examination.',
                veterinarian: 'Dr. Sarah Johnson',
                clinic: 'PetCare Veterinary Clinic',
            },
        ],
        vaccinations: [
            {
                id: 1,
                vaccine: 'DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)',
                date: '2025-06-15',
                next_due: '2026-06-15',
                veterinarian: 'Dr. Michael Chen',
            },
            {
                id: 2,
                vaccine: 'Rabies',
                date: '2025-06-15',
                next_due: '2026-06-15',
                veterinarian: 'Dr. Michael Chen',
            },
            {
                id: 3,
                vaccine: 'Bordetella',
                date: '2025-01-10',
                next_due: '2026-01-10',
                veterinarian: 'Dr. Sarah Johnson',
            },
        ],
        medications: [
            {
                id: 1,
                name: 'Heartgard Plus',
                dosage: '1 tablet',
                frequency: 'Monthly',
                start_date: '2025-01-01',
                notes: 'Heartworm prevention',
            },
        ],
    }),
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: petsIndex().url,
    },
    {
        title: props.pet?.name || 'Pet Details',
        href: '#',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const bookVisit = () => {
    router.visit(booking().url, {
        data: {
            pet_id: props.pet?.id,
            pet_name: props.pet?.name,
        },
        preserveScroll: true,
    });
};

const editProfile = () => {
    router.visit(petsEdit(props.pet?.id || props.petId).url);
};

const getVaccinationStatus = (nextDue: string) => {
    const dueDate = new Date(nextDue);
    const today = new Date();
    const daysUntilDue = Math.ceil((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysUntilDue < 0) return { status: 'overdue', color: 'text-red-600 bg-red-100', text: 'Overdue' };
    if (daysUntilDue <= 30) return { status: 'due-soon', color: 'text-yellow-600 bg-yellow-100', text: 'Due Soon' };
    return { status: 'current', color: 'text-green-600 bg-green-100', text: 'Current' };
};
</script>

<template>
    <Head :title="`${pet?.name} - Pet Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Pet Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-start gap-6">
                        <!-- Pet Photo Placeholder -->
                        <div class="w-32 h-32 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <span class="text-yellow-600 dark:text-yellow-300 text-lg font-medium">{{ pet?.name }}</span>
                        </div>
                        
                        <!-- Basic Info -->
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ pet?.name }}</h1>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Species:</span>
                                    <span class="ml-2 font-medium">{{ pet?.species }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Breed:</span>
                                    <span class="ml-2 font-medium">{{ pet?.breed }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Age:</span>
                                    <span class="ml-2 font-medium">{{ pet?.age }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Gender:</span>
                                    <span class="ml-2 font-medium">{{ pet?.gender }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Weight:</span>
                                    <span class="ml-2 font-medium">{{ pet?.weight }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Color:</span>
                                    <span class="ml-2 font-medium">{{ pet?.color }}</span>
                                </div>
                                <div v-if="pet?.microchip_id">
                                    <span class="text-gray-500 dark:text-gray-400">Microchip:</span>
                                    <span class="ml-2 font-medium font-mono">{{ pet?.microchip_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button @click="bookVisit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition-colors">
                            📅 Book Appointment
                        </button>
                        <button @click="editProfile" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            ✏️ Edit Profile
                        </button>
                        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            📄 Generate Report
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ pet?.medical_history?.length || 0 }}</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Medical Records</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ pet?.vaccinations?.length || 0 }}</p>
                        <p class="text-sm text-green-700 dark:text-green-300">Vaccinations</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ formatDate(pet?.last_visit || '') }}</p>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Last Visit</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ formatDate(pet?.next_checkup || '') }}</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Next Checkup</p>
                    </div>
                </div>
            </div>

            <!-- Owner Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">👤 Owner Information</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-2">{{ pet?.owner?.name }}</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">📧</span>
                                <span>{{ pet?.owner?.email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">📱</span>
                                <span>{{ pet?.owner?.phone }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">🏠</span>
                                <span>{{ pet?.owner?.address }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">Contact Owner</button>
                    </div>
                </div>
            </div>

            <!-- Vaccination Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">💉 Vaccination Records</h2>
                <div class="space-y-4">
                    <div v-for="vaccination in pet?.vaccinations" :key="vaccination.id" 
                         class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <h3 class="font-semibold">{{ vaccination.vaccine }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Last: {{ formatDate(vaccination.date) }} | Next: {{ formatDate(vaccination.next_due) }}
                            </p>
                            <p class="text-xs text-gray-500">by {{ vaccination.veterinarian }}</p>
                        </div>
                        <span :class="[
                            'px-3 py-1 rounded-full text-xs font-medium',
                            getVaccinationStatus(vaccination.next_due).color
                        ]">
                            {{ getVaccinationStatus(vaccination.next_due).text }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Medical History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">🏥 Medical History</h2>
                <div class="space-y-4">
                    <div v-for="record in pet?.medical_history" :key="record.id" 
                         class="border-l-4 border-blue-500 pl-4 py-3">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold">{{ record.type }}</h3>
                            <span class="text-sm text-gray-500">{{ formatDate(record.date) }}</span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-2">{{ record.description }}</p>
                        <div class="text-sm text-gray-500 space-x-4">
                            <span>🩺 {{ record.veterinarian }}</span>
                            <span>🏥 {{ record.clinic }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Medications -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">💊 Current Medications</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="medication in pet?.medications" :key="medication.id" 
                         class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-semibold mb-2">{{ medication.name }}</h3>
                        <div class="space-y-1 text-sm">
                            <div>
                                <span class="text-gray-500">Dosage:</span>
                                <span class="ml-2">{{ medication.dosage }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Frequency:</span>
                                <span class="ml-2">{{ medication.frequency }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Started:</span>
                                <span class="ml-2">{{ formatDate(medication.start_date) }}</span>
                            </div>
                            <div v-if="medication.end_date">
                                <span class="text-gray-500">Ends:</span>
                                <span class="ml-2">{{ formatDate(medication.end_date) }}</span>
                            </div>
                            <div v-if="medication.notes" class="pt-2">
                                <span class="text-gray-500">Notes:</span>
                                <p class="mt-1 text-gray-700 dark:text-gray-300">{{ medication.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>