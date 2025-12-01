<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { petsIndex, booking, petsEdit } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import {
    History,
    FileText,
    Folder,
    ShieldCheck,
    Download,
    Trash2,
    Upload,
    Clock,
    User,
    Building2,
    Edit,
    MoreVertical,
    Camera,
    Info
} from 'lucide-vue-next';

// TypeScript interfaces matching controller response
interface PetType {
    id: number;
    name: string;
    description: string | null;
}

interface PetBreed {
    id: number;
    name: string;
    species: string;
}

interface Owner {
    id: number;
    name: string;
    email: string;
    profile: {
        phone: string | null;
        address: string | null;
    } | null;
}

interface HealthStatus {
    overall: string;
    vaccination_status: string;
    alerts: number;
}

interface Pet {
    id: number;
    name: string;
    breed: PetBreed | null;
    type: PetType | null;
    gender: string;
    gender_display: string;
    age_in_years: number;
    birth_date: string;
    weight: string | null;
    size: string | null;
    size_display: string | null;
    color: string | null;
    markings: string | null;
    microchip_number: string | null;
    is_neutered: boolean;
    special_needs: string | null;
    notes: string | null;
    profile_image: string | null;
    images: string[] | null;
    health_status: HealthStatus;
    display_name: string;
    created_at: string;
    updated_at: string;
    owner: Owner;
}

interface Clinic {
    id: number;
    name: string;
}

interface MedicalRecord {
    id: number;
    date: string;
    appointment_id: number | null;
    // Simplified 4-field structure
    diagnosis: string | null;
    findings: string | null;
    treatment_given: string | null;
    prescriptions: string | null;
    // Metadata
    veterinarian_name: string | null;
    clinic_name: string; // Anonymous clinic name (Clinic A, B, C)
    days_since_visit: number | null;
    clinic: Clinic | null;
}

interface Vaccination {
    id: number;
    vaccine_name: string;
    vaccine_type: string | null;
    administered_date: string;
    next_due_date: string | null;
    veterinarian_name: string | null;
    clinic_name: string | null;
    status: string;
    is_expired: boolean;
    is_due_soon: boolean;
}

interface HealthCondition {
    id: number;
    condition_name: string;
    severity: string | null;
    diagnosis_date: string | null;
    is_active: boolean;
    status: string;
    clinic: Clinic | null;
}

interface Props {
    pet: Pet;
    medical_records: MedicalRecord[];
    vaccinations: Vaccination[];
    health_conditions: HealthCondition[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'My Pets',
        href: petsIndex().url,
    },
    {
        title: props.pet.name || 'Pet Details',
        href: '#',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

// Kebab menu state
const showMenu = ref(false);
const showDeleteConfirm = ref(false);
const showImageViewer = ref(false);
const showImageUpload = ref(false);
const selectedImage = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const closeMenu = () => {
    showMenu.value = false;
};

const openImageViewer = () => {
    showImageViewer.value = true;
};

const closeImageViewer = () => {
    showImageViewer.value = false;
};

const openImageUpload = () => {
    showImageUpload.value = true;
};

const closeImageUpload = () => {
    showImageUpload.value = false;
    selectedImage.value = null;
    imagePreview.value = null;
};

const handleImageSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        selectedImage.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const uploadImage = () => {
    if (!selectedImage.value) {
        console.log('No image selected');
        return;
    }
    
    console.log('Uploading image:', selectedImage.value.name, selectedImage.value.type, selectedImage.value.size);
    console.log('Pet ID:', props.pet?.id || props.petId);
    
    const formData = new FormData();
    formData.append('profile_image', selectedImage.value);
    formData.append('_method', 'PUT');
    
    // Log FormData contents
    for (let pair of formData.entries()) {
        console.log(pair[0], pair[1]);
    }
    
    router.post(`/pets/${props.pet.id}`, formData, {
        forceFormData: true,
        preserveState: false,
        onSuccess: (page) => {
            console.log('Upload successful', page);
            // Close modal immediately
            showImageUpload.value = false;
            selectedImage.value = null;
            imagePreview.value = null;
        },
        onError: (errors) => {
            console.error('Upload failed:', errors);
        },
        onFinish: () => {
            console.log('Upload finished');
        },
    });
};

const editProfile = () => {
    closeMenu();
    router.visit(petsEdit(props.pet.id).url);
};

const confirmDelete = () => {
    closeMenu();
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
};

const deletePet = () => {
    router.delete(petsIndex().url + `/${props.pet.id}`, {
        onSuccess: () => {
            router.visit(petsIndex().url);
        },
    });
};

const generateReport = () => {
    closeMenu();
    // TODO: Implement report generation
    alert('Report generation feature coming soon!');
};

const getVaccinationStatus = (nextDueDate: string | null) => {
    if (!nextDueDate) return { status: 'unknown', color: 'text-gray-600 bg-gray-100', text: 'N/A' };
    
    const dueDate = new Date(nextDueDate);
    const today = new Date();
    const daysUntilDue = Math.ceil((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysUntilDue < 0) return { status: 'overdue', color: 'text-red-600 bg-red-100', text: 'Overdue' };
    if (daysUntilDue <= 30) return { status: 'due-soon', color: 'text-yellow-600 bg-yellow-100', text: 'Due Soon' };
    return { status: 'current', color: 'text-green-600 bg-green-100', text: 'Current' };
};

// Tab management
const activeTab = ref('info');
const tabs = [
    { id: 'info', name: 'Pet Information', icon: Info },
    { id: 'history', name: 'Visit History', icon: History },
    { id: 'documents', name: 'Documents', icon: Folder }
];

// Medical Records Categorization
const selectedRecordType = ref('all');
const uploadingDocument = ref(false);
const documentInput = ref<HTMLInputElement | null>(null);

// Pagination for medical records
const recordsPerPage = ref(10);
const currentPage = ref(1);
const expandedRecords = ref<Set<number>>(new Set());

const toggleRecordExpansion = (recordId: number) => {
    if (expandedRecords.value.has(recordId)) {
        expandedRecords.value.delete(recordId);
    } else {
        expandedRecords.value.add(recordId);
    }
};

const recordTypeCategories = [
    { value: 'checkup', label: 'Checkup', icon: '🩺', iconColor: 'text-blue-600', bgClass: 'bg-blue-50 dark:bg-blue-900/20', badgeClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
    { value: 'vaccination', label: 'Vaccination', icon: '💉', iconColor: 'text-green-600', bgClass: 'bg-green-50 dark:bg-green-900/20', badgeClass: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
    { value: 'treatment', label: 'Treatment', icon: '💊', iconColor: 'text-purple-600', bgClass: 'bg-purple-50 dark:bg-purple-900/20', badgeClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' },
    { value: 'surgery', label: 'Surgery', icon: '🏥', iconColor: 'text-red-600', bgClass: 'bg-red-50 dark:bg-red-900/20', badgeClass: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
    { value: 'emergency', label: 'Emergency', icon: '🚨', iconColor: 'text-orange-600', bgClass: 'bg-orange-50 dark:bg-orange-900/20', badgeClass: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' },
    { value: 'other', label: 'Other', icon: '📋', iconColor: 'text-gray-600', bgClass: 'bg-gray-50 dark:bg-gray-900/20', badgeClass: 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400' },
];

const getRecordTypeStyle = (type: string) => {
    const category = recordTypeCategories.find(cat => cat.value === type);
    return category || recordTypeCategories[recordTypeCategories.length - 1]; // Default to 'other'
};

const getRecordCountByType = (type: string) => {
    if (!props.medical_records) return 0;
    return props.medical_records.filter(record => record.visit_type === type).length;
};

const filteredMedicalRecords = computed(() => {
    if (!props.medical_records) return [];
    return props.medical_records;
});

// Paginated medical records
const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * recordsPerPage.value;
    const end = start + recordsPerPage.value;
    return filteredMedicalRecords.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredMedicalRecords.value.length / recordsPerPage.value);
});

const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        expandedRecords.value.clear(); // Collapse all when changing pages
    }
};

const changePerPage = (perPage: number) => {
    recordsPerPage.value = perPage;
    currentPage.value = 1; // Reset to first page
    expandedRecords.value.clear();
};

// Documents management
const documents = ref<Array<{
    id: number;
    name: string;
    type: string;
    size: string;
    uploaded_date: string;
    url: string;
}>>([
    {
        id: 1,
        name: 'Vaccination Certificate 2025.pdf',
        type: 'PDF',
        size: '2.4 MB',
        uploaded_date: '2025-06-15',
        url: '#'
    }
]);

const openDocumentUpload = () => {
    documentInput.value?.click();
};

const handleDocumentSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    
    if (files && files.length > 0) {
        uploadDocument(files[0]);
    }
};

const uploadDocument = (file: File) => {
    uploadingDocument.value = true;
    
    const formData = new FormData();
    formData.append('document', file);
    formData.append('pet_id', String(props.pet.id));
    
    // Simulate upload
    setTimeout(() => {
        console.log('Document uploaded:', file.name);
        uploadingDocument.value = false;
        // Add to documents list
        documents.value.unshift({
            id: Date.now(),
            name: file.name,
            type: file.type.split('/')[1].toUpperCase(),
            size: `${(file.size / 1024 / 1024).toFixed(2)} MB`,
            uploaded_date: new Date().toISOString().split('T')[0],
            url: '#'
        });
    }, 2000);
};

const downloadDocument = (doc: typeof documents.value[0]) => {
    console.log('Downloading:', doc.name);
    // TODO: Implement actual download
};

const deleteDocument = (docId: number) => {
    if (confirm('Are you sure you want to delete this document?')) {
        documents.value = documents.value.filter(d => d.id !== docId);
    }
};
</script>

<template>
    <Head :title="`${pet.name} - Pet Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl p-3 sm:p-4">
            <!-- Pet Header with Gradient -->
            <div class="relative bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-4 sm:p-6 shadow-lg">
                <div class="flex flex-col md:flex-row items-center md:items-center gap-4 sm:gap-6">
                    <!-- Pet Photo -->
                    <div class="relative group flex-shrink-0">
                        <div @click="openImageUpload" 
                             class="w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 rounded-xl flex items-center justify-center cursor-pointer overflow-hidden ring-4 ring-white/20 shadow-xl hover:ring-white/40 transition-all"
                             :class="pet.profile_image ? '' : 'bg-white/20 backdrop-blur-sm'">
                            <img v-if="pet.profile_image" 
                                 :src="`/storage/${pet.profile_image}`" 
                                 :alt="pet.name"
                                 class="w-full h-full object-cover">
                            <span v-else class="text-white text-lg sm:text-xl font-bold">{{ pet.name.charAt(0) }}</span>
                        </div>
                    </div>
                    
                    <!-- Pet Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">{{ pet.name }}</h1>
                        <p class="text-sm sm:text-base text-blue-100">{{ pet.type?.name || 'Pet' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabbed Content Section -->
            <div class="rounded-lg border bg-card shadow-sm flex flex-col overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b flex-shrink-0">
                    <nav class="flex -mb-px px-3 sm:px-4 md:px-6 overflow-x-auto scrollbar-hide">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'whitespace-nowrap py-3 sm:py-4 px-2 sm:px-4 border-b-2 font-medium text-xs sm:text-sm flex items-center gap-1.5 sm:gap-2',
                                activeTab === tab.id
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" />
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-3 sm:p-4 md:p-6 overflow-y-auto flex-1">
                    <!-- Pet Information Tab -->
                    <div v-if="activeTab === 'info'" class="space-y-4 sm:space-y-6">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-base sm:text-lg font-semibold">Pet Information</h3>
                            
                            <!-- Action Menu -->
                            <div class="relative">
                                <button @click="toggleMenu" 
                                        class="p-2 hover:bg-muted rounded-lg transition-colors">
                                    <MoreVertical class="w-5 h-5" />
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div v-if="showMenu" @click.away="closeMenu" 
                                     class="absolute right-0 mt-2 w-48 bg-card rounded-lg shadow-lg border py-1 z-10">
                                    <button @click="editProfile" 
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-muted flex items-center gap-2">
                                        <Edit class="w-4 h-4" />
                                        Edit Profile
                                    </button>
                                    <hr class="my-1 border">
                                    <button @click="confirmDelete" 
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                        <Trash2 class="w-4 h-4" />
                                        Delete Pet
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Combined Information -->
                        <div class="bg-muted/30 rounded-lg p-4 sm:p-6 border">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 sm:gap-x-8 gap-y-3 sm:gap-y-4">
                                <!-- Basic Details Column -->
                                <div class="space-y-3">
                                    <h4 class="text-xs sm:text-sm font-semibold text-muted-foreground mb-3 pb-2 border-b">Basic Details</h4>
                                    <div class="flex justify-between">
                                        <span class="text-xs sm:text-sm text-muted-foreground">Name:</span>
                                        <span class="text-xs sm:text-sm font-medium">{{ pet.name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Type:</span>
                                        <span class="text-sm font-medium">{{ pet.type?.name || 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Breed:</span>
                                        <span class="text-sm font-medium">{{ pet.breed?.name || 'Mixed Breed' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Gender:</span>
                                        <span class="text-sm font-medium">{{ pet.gender_display }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Age:</span>
                                        <span class="text-sm font-medium">{{ pet.age_in_years }} {{ pet.age_in_years === 1 ? 'year' : 'years' }} old</span>
                                    </div>
                                    <div v-if="pet.birth_date" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Birth Date:</span>
                                        <span class="text-sm font-medium">{{ formatDate(pet.birth_date) }}</span>
                                    </div>
                                    <div v-if="pet.microchip_number" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Microchip:</span>
                                        <span class="text-sm font-medium font-mono">{{ pet.microchip_number }}</span>
                                    </div>
                                </div>

                                <!-- Physical Characteristics Column -->
                                <div class="space-y-3">
                                    <h4 class="text-sm font-semibold text-muted-foreground mb-3 pb-2 border-b">Physical Characteristics</h4>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Weight:</span>
                                        <span class="text-sm font-medium">{{ pet.weight ? `${pet.weight} kg` : 'Not recorded' }}</span>
                                    </div>
                                    <div v-if="pet.size_display" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Size:</span>
                                        <span class="text-sm font-medium">{{ pet.size_display }}</span>
                                    </div>
                                    <div v-if="pet.color" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Color:</span>
                                        <span class="text-sm font-medium">{{ pet.color }}</span>
                                    </div>
                                    <div v-if="pet.markings" class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Markings:</span>
                                        <span class="text-sm font-medium">{{ pet.markings }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Neutered/Spayed:</span>
                                        <span class="text-sm font-medium">{{ pet.is_neutered ? 'Yes' : 'No' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccinations Section -->
                        <div v-if="vaccinations.length > 0">
                            <h4 class="text-sm sm:text-base font-semibold mb-3">Vaccinations</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                <div v-for="vaccination in vaccinations" :key="vaccination.id" 
                                     class="bg-muted/30 rounded-lg p-3 sm:p-4 border">
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 class="font-medium">{{ vaccination.vaccine_name }}</h5>
                                        <span :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            getVaccinationStatus(vaccination.next_due_date).color
                                        ]">
                                            {{ getVaccinationStatus(vaccination.next_due_date).text }}
                                        </span>
                                    </div>
                                    <div class="space-y-1 text-sm text-muted-foreground">
                                        <p>Administered: {{ formatDate(vaccination.administered_date) }}</p>
                                        <p v-if="vaccination.next_due_date">Next Due: {{ formatDate(vaccination.next_due_date) }}</p>
                                        <p v-if="vaccination.clinic_name">Clinic: {{ vaccination.clinic_name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Conditions -->
                        <div v-if="health_conditions.length > 0">
                            <h4 class="text-sm sm:text-base font-semibold mb-3">Health Conditions</h4>
                            <div class="space-y-2 sm:space-y-3">
                                <div v-for="condition in health_conditions" :key="condition.id" 
                                     class="bg-muted/30 rounded-lg p-3 sm:p-4 border">
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 class="font-medium">{{ condition.condition_name }}</h5>
                                        <span :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            condition.is_active ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        ]">
                                            {{ condition.is_active ? 'Active' : 'Resolved' }}
                                        </span>
                                    </div>
                                    <div class="space-y-1 text-sm text-muted-foreground">
                                        <p v-if="condition.severity_display">Severity: {{ condition.severity_display }}</p>
                                        <p v-if="condition.diagnosis_date">Diagnosed: {{ formatDate(condition.diagnosis_date) }}</p>
                                        <p v-if="condition.treatment_plan" class="text-sm mt-2">{{ condition.treatment_plan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visit History Tab -->
                    <div v-if="activeTab === 'history'" class="space-y-4">
                        <!-- Header with total count -->
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Medical History Timeline</h3>
                            <span class="text-sm text-muted-foreground">{{ medical_records.length }} total records</span>
                        </div>

                        <!-- Pagination Controls -->
                        <div v-if="medical_records.length > 0" class="flex flex-wrap items-center justify-between gap-3 p-3 bg-muted/30 rounded-lg border">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground">Show:</span>
                                <select 
                                    v-model="recordsPerPage" 
                                    @change="changePerPage(recordsPerPage)"
                                    class="px-3 py-1 border rounded-md bg-background text-sm"
                                >
                                    <option :value="10">10 per page</option>
                                    <option :value="50">50 per page</option>
                                    <option :value="100">100 per page</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="changePage(currentPage - 1)"
                                    :disabled="currentPage === 1"
                                    class="px-3 py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                >
                                    Previous
                                </button>
                                <span class="text-sm text-muted-foreground">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </span>
                                <button 
                                    @click="changePage(currentPage + 1)"
                                    :disabled="currentPage === totalPages"
                                    class="px-3 py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                        
                        <!-- Timeline -->
                        <div v-if="medical_records.length > 0" class="relative space-y-4">
                            <!-- Timeline line -->
                            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-border"></div>
                            
                            <!-- Records -->
                            <div v-for="record in paginatedRecords" :key="record.id" class="relative pl-16">
                                <!-- Timeline dot -->
                                <div class="absolute left-4 top-6 w-4 h-4 bg-primary rounded-full border-4 border-background ring-2 ring-border"></div>
                                
                                <!-- Record card -->
                                <div class="bg-card border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                    <!-- Card header - always visible -->
                                    <div 
                                        @click="toggleRecordExpansion(record.id)"
                                        class="p-4 cursor-pointer hover:bg-muted/30 transition-colors"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-base">{{ formatDate(record.date) }}</h4>
                                                    <span class="text-xs px-2 py-1 bg-primary/10 text-primary rounded-full">
                                                        Medical Record #{{ record.id }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ record.days_since_visit }} days ago
                                                </p>
                                            </div>
                                            <button class="text-muted-foreground hover:text-foreground">
                                                <svg 
                                                    class="w-5 h-5 transition-transform" 
                                                    :class="{ 'rotate-180': expandedRecords.has(record.id) }"
                                                    fill="none" 
                                                    stroke="currentColor" 
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Preview - Diagnosis snippet -->
                                        <div class="flex items-start gap-2 mt-2">
                                            <span class="text-sm font-medium text-muted-foreground">Diagnosis:</span>
                                            <p class="text-sm line-clamp-1 flex-1">
                                                {{ record.diagnosis || 'Not specified' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Expanded content -->
                                    <div 
                                        v-if="expandedRecords.has(record.id)"
                                        class="px-4 pb-4 space-y-4 border-t bg-muted/10"
                                    >
                                        <!-- Diagnosis -->
                                        <div v-if="record.diagnosis" class="pt-4">
                                            <h5 class="font-medium text-sm mb-2 flex items-center gap-2">
                                                <span class="text-lg">🩺</span>
                                                Diagnosis
                                            </h5>
                                            <p class="text-sm leading-relaxed bg-background p-3 rounded-md border">{{ record.diagnosis }}</p>
                                        </div>
                                        
                                        <!-- Findings -->
                                        <div v-if="record.findings">
                                            <h5 class="font-medium text-sm mb-2 flex items-center gap-2">
                                                <span class="text-lg">🔍</span>
                                                Findings
                                            </h5>
                                            <p class="text-sm leading-relaxed bg-background p-3 rounded-md border whitespace-pre-wrap">{{ record.findings }}</p>
                                        </div>
                                        
                                        <!-- Treatment Given -->
                                        <div v-if="record.treatment_given">
                                            <h5 class="font-medium text-sm mb-2 flex items-center gap-2">
                                                <span class="text-lg">💊</span>
                                                Treatment Given
                                            </h5>
                                            <p class="text-sm leading-relaxed bg-background p-3 rounded-md border whitespace-pre-wrap">{{ record.treatment_given }}</p>
                                        </div>
                                        
                                        <!-- Prescriptions -->
                                        <div v-if="record.prescriptions">
                                            <h5 class="font-medium text-sm mb-2 flex items-center gap-2">
                                                <span class="text-lg">📋</span>
                                                Prescriptions
                                            </h5>
                                            <p class="text-sm leading-relaxed bg-background p-3 rounded-md border whitespace-pre-wrap">{{ record.prescriptions }}</p>
                                        </div>
                                        
                                        <!-- Metadata footer -->
                                        <div class="flex items-center gap-4 text-xs text-muted-foreground pt-3 border-t">
                                            <div v-if="record.veterinarian_name" class="flex items-center gap-2">
                                                <User class="w-4 h-4" />
                                                <span>{{ record.veterinarian_name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Building2 class="w-4 h-4" />
                                                <span>{{ record.clinic_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty state -->
                        <div v-else class="text-center py-12">
                            <History class="w-16 h-16 mx-auto text-muted-foreground opacity-50 mb-4" />
                            <p class="text-base text-muted-foreground">No medical history yet</p>
                            <p class="text-sm text-muted-foreground mt-2">Medical records from clinic visits will appear here</p>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-3 sm:space-y-4">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-base sm:text-lg font-semibold">Pet Documents</h3>
                            <button 
                                @click="openDocumentUpload"
                                :disabled="uploadingDocument"
                                class="px-3 sm:px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 font-medium text-xs sm:text-sm transition-all flex items-center gap-2 disabled:opacity-50"
                            >
                                <Upload class="w-4 h-4" />
                                Upload Document
                            </button>
                            <input 
                                type="file" 
                                ref="documentInput"
                                @change="handleDocumentSelect"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="hidden"
                            />
                        </div>

                        <!-- Uploading Indicator -->
                        <div v-if="uploadingDocument" class="bg-primary/10 border border-primary/20 rounded-lg p-4 flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-primary">Uploading document...</span>
                        </div>

                        <!-- Documents List -->
                        <div v-if="documents.length > 0" class="space-y-2 sm:space-y-3">
                            <div v-for="doc in documents" :key="doc.id"
                                 class="flex items-center justify-between bg-muted/30 rounded-lg p-3 sm:p-4 border hover:bg-muted/50 transition-colors">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="p-2 bg-primary/10 rounded-lg">
                                        <FileText class="w-6 h-6 text-primary" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium">{{ doc.name }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ doc.type }} • {{ doc.size }} • Uploaded {{ formatDate(doc.uploaded_date) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="downloadDocument(doc)"
                                        class="p-2 hover:bg-muted rounded-lg transition-colors"
                                        title="Download"
                                    >
                                        <Download class="w-5 h-5" />
                                    </button>
                                    <button 
                                        @click="deleteDocument(doc.id)"
                                        class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors text-red-600 dark:text-red-400"
                                        title="Delete"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-8 sm:py-12">
                            <FileText class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-muted-foreground opacity-50 mb-3 sm:mb-4" />
                            <p class="text-sm sm:text-base text-muted-foreground mb-2">No documents uploaded yet</p>
                            <p class="text-xs sm:text-sm text-muted-foreground mb-3 sm:mb-4">Upload vaccination certificates, medical reports, and other important documents</p>
                            <button 
                                @click="openDocumentUpload"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 font-medium text-sm transition-all"
                            >
                                <Upload class="w-4 h-4" />
                                Upload First Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="rounded-lg bg-card p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Delete Pet</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete <strong>{{ pet?.name }}</strong>? This action cannot be undone and will permanently remove all associated records.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="cancelDelete" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="deletePet" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium text-sm transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Image Viewer Modal -->
        <div v-if="showImageViewer && pet?.profile_image" @click="closeImageViewer" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
            <div class="relative max-w-4xl max-h-screen">
                <button @click="closeImageViewer" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <img :src="`/storage/${pet.profile_image}`" 
                     :alt="pet.name"
                     class="max-w-full max-h-[90vh] object-contain rounded-lg"
                     @click.stop>
            </div>
        </div>

        <!-- Image Upload Modal -->
        <div v-if="showImageUpload" class="fixed inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="rounded-lg bg-card p-6 max-w-md w-full mx-4 shadow-2xl border">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upload Pet Photo</h3>
                
                <!-- Image Preview -->
                <div v-if="imagePreview" class="mb-4">
                    <img :src="imagePreview" alt="Preview" class="w-full h-64 object-cover rounded-lg">
                </div>
                
                <!-- Change Photo Button -->
                <div class="mb-4">
                    <input type="file" 
                           ref="fileInput"
                           accept="image/*" 
                           @change="handleImageSelect"
                           class="hidden">
                    <button @click="() => fileInput?.click()" type="button" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 font-medium">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ imagePreview ? 'Change Photo' : 'Select Photo' }}
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-2">PNG, JPG, GIF, WEBP up to 10MB</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end">
                    <button @click="closeImageUpload" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium text-sm transition-colors dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="uploadImage" 
                            :disabled="!selectedImage"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>