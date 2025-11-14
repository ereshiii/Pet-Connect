<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

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
    formatted_date: string;
    type: 'checkup' | 'vaccination' | 'surgery' | 'treatment' | 'emergency';
    title: string;
    description: string;
    diagnosis: string; // alias for description
    treatment: string;
    medication: string;
    cost: number;
    formatted_cost: string;
    notes: string;
    veterinarian: string;
    follow_up_date?: string;
    attachments?: string[];
}

interface Patient {
    id: number;
    name: string;
    species: string;
    type: string;
    breed: string;
    age: number;
    weight: number;
    color: string;
    markings?: string;
    microchip_id?: string;
    is_neutered?: boolean;
    special_needs?: string;
    birth_date?: string;
    formatted_birth_date?: string;
    profile_image?: string;
    owner_name: string;
    owner_phone: string;
    owner_email: string;
    emergency_contact?: string;
    allergies?: string[];
    current_medications?: string[];
    medical_conditions?: string[];
    owner?: {
        id: number;
        name: string;
        email: string;
        phone?: string;
        emergency_contact?: string;
    };
}

interface Props {
    patient: Patient;
    medical_records?: MedicalRecord[];
    vaccination_records?: Array<{
        id: number;
        vaccine: string;
        date: string;
        formatted_date: string;
        next_due: string;
        status: string;
        veterinarian: string;
        notes?: string;
        is_expired?: boolean;
        is_due_soon?: boolean;
    }>;
    health_conditions?: Array<{
        id: number;
        type: string;
        type_display: string;
        name: string;
        description?: string;
        severity: string;
        severity_display: string;
        treatment?: string;
        is_active: boolean;
    }>;
    appointments?: Array<{
        id: number;
        date: string;
        formatted_datetime: string;
        status: string;
        type: string;
        service: string;
        notes?: string;
    }>;
}

const props = defineProps<Props>();

// Reactive state for sorting and filtering
const medicalRecordsSort = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);
const vaccinationSort = ref<{ column: string; direction: 'asc' | 'desc' } | null>(null);
const searchQuery = ref('');
const selectedRecordType = ref('all');

// Modal states
const showViewModal = ref(false);
const showEditModal = ref(false);
const selectedRecord = ref<MedicalRecord | null>(null);

// Computed properties for sorted and filtered data
const sortedMedicalRecords = computed(() => {
    if (!props.medical_records) return [];
    
    let filtered = props.medical_records.filter(record => {
        const matchesSearch = searchQuery.value === '' || 
            record.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.treatment?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.medication?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.veterinarian?.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesType = selectedRecordType.value === 'all' || record.type === selectedRecordType.value;
        
        return matchesSearch && matchesType;
    });
    
    if (medicalRecordsSort.value) {
        const { column, direction } = medicalRecordsSort.value;
        filtered.sort((a, b) => {
            let aVal = a[column as keyof typeof a];
            let bVal = b[column as keyof typeof b];
            
            // Handle date sorting
            if (column === 'date' || column === 'formatted_date') {
                aVal = new Date(a.date || a.formatted_date);
                bVal = new Date(b.date || b.formatted_date);
            }
            
            // Handle cost sorting (remove currency symbols and convert to number)
            if (column === 'cost' || column === 'formatted_cost') {
                aVal = parseFloat(String(a.cost || a.formatted_cost || '0').replace(/[^\d.-]/g, ''));
                bVal = parseFloat(String(b.cost || b.formatted_cost || '0').replace(/[^\d.-]/g, ''));
            }
            
            if (aVal === null || aVal === undefined) aVal = '';
            if (bVal === null || bVal === undefined) bVal = '';
            
            if (direction === 'asc') {
                return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            } else {
                return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
            }
        });
    }
    
    return filtered;
});

const sortedVaccinationRecords = computed(() => {
    if (!props.vaccination_records) return [];
    
    let records = [...props.vaccination_records];
    
    if (vaccinationSort.value) {
        const { column, direction } = vaccinationSort.value;
        records.sort((a, b) => {
            let aVal = a[column as keyof typeof a];
            let bVal = b[column as keyof typeof b];
            
            // Handle date sorting
            if (column === 'date' || column === 'formatted_date' || column === 'next_due') {
                aVal = new Date(a.date || a.formatted_date || a.next_due);
                bVal = new Date(b.date || b.formatted_date || b.next_due);
            }
            
            if (aVal === null || aVal === undefined) aVal = '';
            if (bVal === null || bVal === undefined) bVal = '';
            
            if (direction === 'asc') {
                return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            } else {
                return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
            }
        });
    }
    
    return records;
});

// Sorting functions
const sortMedicalRecords = (column: string) => {
    if (medicalRecordsSort.value?.column === column) {
        medicalRecordsSort.value.direction = medicalRecordsSort.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        medicalRecordsSort.value = { column, direction: 'asc' };
    }
};

const sortVaccinationRecords = (column: string) => {
    if (vaccinationSort.value?.column === column) {
        vaccinationSort.value.direction = vaccinationSort.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        vaccinationSort.value = { column, direction: 'asc' };
    }
};

// Helper function to get sort icon
const getSortIcon = (sortState: typeof medicalRecordsSort.value, column: string) => {
    if (!sortState || sortState.column !== column) return '↕️';
    return sortState.direction === 'asc' ? '↑' : '↓';
};

// Modal functions
const viewRecord = (record: MedicalRecord) => {
    selectedRecord.value = record;
    showViewModal.value = true;
};

const editRecord = (record: MedicalRecord) => {
    selectedRecord.value = { ...record }; // Create a copy for editing
    showEditModal.value = true;
};

const closeModals = () => {
    showViewModal.value = false;
    showEditModal.value = false;
    selectedRecord.value = null;
};

const editPatient = () => {
    router.visit(`/clinic/patient/${patient.id}/edit`);
};

const saveRecord = async () => {
    if (!selectedRecord.value) return;
    
    // TODO: Implement the actual save functionality with Inertia.js
    // This would typically make a PUT/PATCH request to update the record
    console.log('Saving record:', selectedRecord.value);
    
    // For now, just close the modal and update the local data
    // In a real implementation, you would:
    // 1. Make an API call to save the record
    // 2. Handle success/error responses
    // 3. Update the local medical_records data
    // 4. Show success/error messages
    
    closeModals();
    
    // Placeholder for the actual implementation:
    // await router.put(`/clinic/patients/${patient.id}/medical-records/${selectedRecord.value.id}`, selectedRecord.value);
};
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
                        <p class="text-muted-foreground">{{ patient.breed }} • {{ patient.species }} • {{ patient.age || 'Age unknown' }} years old</p>
                        <p class="text-sm text-muted-foreground">Patient ID: {{ patient.id }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="editPatient" class="btn btn-outline">✏️ Edit Patient</button>
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
                            <div v-if="patient.weight">
                                <label class="text-sm font-medium text-muted-foreground">Weight</label>
                                <p class="text-sm">{{ patient.weight }} kg</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Color</label>
                                <p class="text-sm">{{ patient.color || 'Not specified' }}</p>
                            </div>
                            <div v-if="patient.markings">
                                <label class="text-sm font-medium text-muted-foreground">Markings</label>
                                <p class="text-sm">{{ patient.markings }}</p>
                            </div>
                            <div v-if="patient.microchip_id">
                                <label class="text-sm font-medium text-muted-foreground">Microchip ID</label>
                                <p class="text-sm">{{ patient.microchip_id }}</p>
                            </div>
                            <div v-if="patient.is_neutered !== undefined">
                                <label class="text-sm font-medium text-muted-foreground">Neutered/Spayed</label>
                                <p class="text-sm">{{ patient.is_neutered ? 'Yes' : 'No' }}</p>
                            </div>
                            <div v-if="patient.birth_date">
                                <label class="text-sm font-medium text-muted-foreground">Date of Birth</label>
                                <p class="text-sm">{{ patient.formatted_birth_date }}</p>
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
                    <div v-if="vaccination_records?.length" class="mt-6 rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Vaccination Records</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b bg-muted/30">
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortVaccinationRecords('vaccine')">
                                            Vaccine {{ getSortIcon(vaccinationSort, 'vaccine') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortVaccinationRecords('formatted_date')">
                                            Date Given {{ getSortIcon(vaccinationSort, 'formatted_date') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortVaccinationRecords('next_due')">
                                            Next Due {{ getSortIcon(vaccinationSort, 'next_due') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortVaccinationRecords('veterinarian')">
                                            Veterinarian {{ getSortIcon(vaccinationSort, 'veterinarian') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="vaccination in sortedVaccinationRecords" 
                                        :key="vaccination.id"
                                        class="border-b hover:bg-muted/20 transition-colors"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium">{{ vaccination.vaccine }}</td>
                                        <td class="px-4 py-3 text-sm">{{ vaccination.formatted_date }}</td>
                                        <td class="px-4 py-3 text-sm">{{ vaccination.next_due }}</td>
                                        <td class="px-4 py-3 text-sm">{{ vaccination.veterinarian }}</td>
                                        <td class="px-4 py-3">
                                            <span 
                                                :class="{
                                                    'bg-green-100 text-green-800': vaccination.status === 'current',
                                                    'bg-yellow-100 text-yellow-800': vaccination.is_due_soon,
                                                    'bg-red-100 text-red-800': vaccination.is_expired
                                                }"
                                                class="px-2 py-1 rounded-full text-xs font-medium"
                                            >
                                                {{ vaccination.is_expired ? 'Expired' : vaccination.is_due_soon ? 'Due Soon' : 'Current' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Medical Records -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Medical Records</h2>
                            <div class="flex gap-3">
                                <!-- Search Input -->
                                <div class="relative">
                                    <input 
                                        v-model="searchQuery"
                                        type="text" 
                                        placeholder="Search records..." 
                                        class="pl-8 pr-3 py-2 border rounded-md text-sm w-64"
                                    >
                                    <svg class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <!-- Type Filter -->
                                <select v-model="selectedRecordType" class="btn btn-outline px-3 py-2 text-sm">
                                    <option value="all">All Records</option>
                                    <option value="checkup">Checkups</option>
                                    <option value="vaccination">Vaccinations</option>
                                    <option value="surgery">Surgeries</option>
                                    <option value="treatment">Treatments</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                        </div>

                        <!-- Debug info -->
                        <div class="mb-4 p-3 bg-gray-100 rounded text-xs">
                            <strong>Debug:</strong> 
                            Raw medical_records: {{ medical_records?.length || 0 }} | 
                            Sorted: {{ sortedMedicalRecords?.length || 0 }} | 
                            Search: "{{ searchQuery }}" | 
                            Type: "{{ selectedRecordType }}"
                        </div>

                        <!-- Medical Records Table -->
                        <div v-if="sortedMedicalRecords && sortedMedicalRecords.length > 0" class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b bg-muted/30">
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('formatted_date')">
                                            Date {{ getSortIcon(medicalRecordsSort, 'formatted_date') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('type')">
                                            Type {{ getSortIcon(medicalRecordsSort, 'type') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('title')">
                                            Title {{ getSortIcon(medicalRecordsSort, 'title') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Description</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('treatment')">
                                            Treatment {{ getSortIcon(medicalRecordsSort, 'treatment') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('medication')">
                                            Medication {{ getSortIcon(medicalRecordsSort, 'medication') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('formatted_cost')">
                                            Cost {{ getSortIcon(medicalRecordsSort, 'formatted_cost') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground cursor-pointer hover:bg-muted/50 transition-colors" @click="sortMedicalRecords('veterinarian')">
                                            Veterinarian {{ getSortIcon(medicalRecordsSort, 'veterinarian') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Follow-up</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="record in sortedMedicalRecords" 
                                        :key="record.id"
                                        class="border-b hover:bg-muted/20 transition-colors"
                                    >
                                        <td class="px-4 py-3 text-sm">{{ record.formatted_date }}</td>
                                        <td class="px-4 py-3">
                                            <span 
                                                :class="{
                                                    'bg-blue-100 text-blue-800': record.type === 'checkup',
                                                    'bg-green-100 text-green-800': record.type === 'vaccination',
                                                    'bg-red-100 text-red-800': record.type === 'surgery',
                                                    'bg-yellow-100 text-yellow-800': record.type === 'treatment',
                                                    'bg-purple-100 text-purple-800': record.type === 'emergency'
                                                }"
                                                class="px-2 py-1 rounded-full text-xs font-medium capitalize"
                                            >
                                                {{ record.type }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ record.title || '-' }}</td>
                                        <td class="px-4 py-3 text-sm max-w-xs">
                                            <div class="truncate" :title="record.description">
                                                {{ record.description || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ record.treatment || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ record.medication || '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">{{ record.formatted_cost || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ record.veterinarian || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ record.follow_up_date || '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                <button 
                                                    @click="viewRecord(record)"
                                                    class="btn btn-sm btn-outline text-xs px-2 py-1 hover:bg-blue-50"
                                                >
                                                    View
                                                </button>
                                                <button 
                                                    @click="editRecord(record)"
                                                    class="btn btn-sm btn-outline text-xs px-2 py-1 hover:bg-green-50"
                                                >
                                                    Edit
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="text-center py-8">
                            <p class="text-muted-foreground">No medical records found</p>
                            <button class="btn btn-primary mt-2">Add First Record</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Medical Record Modal -->
        <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Medical Record Details</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div v-if="selectedRecord" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <p class="text-sm bg-gray-50 p-2 rounded">{{ selectedRecord.formatted_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <p class="text-sm bg-gray-50 p-2 rounded capitalize">{{ selectedRecord.type }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <p class="text-sm bg-gray-50 p-2 rounded">{{ selectedRecord.title || 'No title' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <p class="text-sm bg-gray-50 p-3 rounded min-h-[60px]">{{ selectedRecord.description || 'No description' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Treatment</label>
                            <p class="text-sm bg-gray-50 p-3 rounded min-h-[60px]">{{ selectedRecord.treatment || 'No treatment specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medication</label>
                            <p class="text-sm bg-gray-50 p-2 rounded">{{ selectedRecord.medication || 'None' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost</label>
                            <p class="text-sm bg-gray-50 p-2 rounded font-medium">{{ selectedRecord.formatted_cost || 'No cost recorded' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veterinarian</label>
                            <p class="text-sm bg-gray-50 p-2 rounded">{{ selectedRecord.veterinarian || 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Date</label>
                            <p class="text-sm bg-gray-50 p-2 rounded">{{ selectedRecord.follow_up_date || 'No follow-up scheduled' }}</p>
                        </div>
                        <div v-if="selectedRecord.notes" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <p class="text-sm bg-gray-50 p-3 rounded min-h-[60px]">{{ selectedRecord.notes }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button @click="closeModals" class="btn btn-outline px-4 py-2">Close</button>
                    <button @click="editRecord(selectedRecord!)" class="btn btn-primary px-4 py-2">Edit Record</button>
                </div>
            </div>
        </div>

        <!-- Edit Medical Record Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Edit Medical Record</h3>
                    <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form v-if="selectedRecord" @submit.prevent="saveRecord" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input 
                                v-model="selectedRecord.date" 
                                type="date" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <select 
                                v-model="selectedRecord.type" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                                <option value="checkup">Checkup</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="surgery">Surgery</option>
                                <option value="treatment">Treatment</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input 
                                v-model="selectedRecord.title" 
                                type="text" 
                                required
                                placeholder="Enter record title"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea 
                                v-model="selectedRecord.description" 
                                rows="3"
                                placeholder="Enter detailed description"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            ></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Treatment</label>
                            <textarea 
                                v-model="selectedRecord.treatment" 
                                rows="3"
                                placeholder="Enter treatment details"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medication</label>
                            <input 
                                v-model="selectedRecord.medication" 
                                type="text" 
                                placeholder="Enter medications prescribed"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost (₱)</label>
                            <input 
                                v-model="selectedRecord.cost" 
                                type="number" 
                                step="0.01" 
                                min="0"
                                placeholder="0.00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veterinarian</label>
                            <input 
                                v-model="selectedRecord.veterinarian" 
                                type="text" 
                                placeholder="Enter veterinarian name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Date</label>
                            <input 
                                v-model="selectedRecord.follow_up_date" 
                                type="date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                        </div>
                        <div v-if="selectedRecord.notes !== undefined" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea 
                                v-model="selectedRecord.notes" 
                                rows="3"
                                placeholder="Enter additional notes"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            ></textarea>
                        </div>
                    </div>
                </form>
                
                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button @click="closeModals" type="button" class="btn btn-outline px-4 py-2">Cancel</button>
                    <button @click="saveRecord" class="btn btn-primary px-4 py-2">Save Changes</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>