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
    Pill,
    Download,
    Trash2,
    Upload,
    Clock,
    User,
    Building2,
    Edit,
    MoreVertical,
    Camera
} from 'lucide-vue-next';

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
    medical_records?: Array<any>;
    vaccinations?: Array<any>;
    health_conditions?: Array<any>;
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
    
    router.post(`/pets/${props.pet?.id || props.petId}`, formData, {
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
    router.visit(petsEdit(props.pet?.id || props.petId).url);
};

const confirmDelete = () => {
    closeMenu();
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
};

const deletePet = () => {
    router.delete(petsIndex().url + `/${props.pet?.id || props.petId}`, {
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

const getVaccinationStatus = (nextDue: string) => {
    const dueDate = new Date(nextDue);
    const today = new Date();
    const daysUntilDue = Math.ceil((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysUntilDue < 0) return { status: 'overdue', color: 'text-red-600 bg-red-100', text: 'Overdue' };
    if (daysUntilDue <= 30) return { status: 'due-soon', color: 'text-yellow-600 bg-yellow-100', text: 'Due Soon' };
    return { status: 'current', color: 'text-green-600 bg-green-100', text: 'Current' };
};

// Tab management
const activeTab = ref('history');
const tabs = [
    { id: 'history', name: 'Visit History', icon: History },
    { id: 'medical', name: 'Medical Records', icon: FileText },
    { id: 'documents', name: 'Documents', icon: Folder }
];

// Medical Records Categorization
const selectedRecordType = ref('all');

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
    return props.medical_records.filter(record => record.record_type === type).length;
};

const filteredMedicalRecords = computed(() => {
    if (!props.medical_records) return [];
    if (selectedRecordType.value === 'all') return props.medical_records;
    return props.medical_records.filter(record => record.record_type === selectedRecordType.value);
});

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

const uploadingDocument = ref(false);
const documentInput = ref<HTMLInputElement | null>(null);

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
    formData.append('pet_id', String(props.pet?.id || props.petId));
    
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
    <Head :title="`${pet?.name} - Pet Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Pet Header with Gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6 shadow-lg">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Pet Photo -->
                    <div class="relative group flex-shrink-0">
                        <div @click="openImageViewer" 
                             class="w-24 h-24 md:w-32 md:h-32 rounded-xl flex items-center justify-center cursor-pointer overflow-hidden ring-4 ring-white/20 shadow-xl"
                             :class="pet?.profile_image ? '' : 'bg-white/20 backdrop-blur-sm'">
                            <img v-if="pet?.profile_image" 
                                 :src="`/storage/${pet.profile_image}`" 
                                 :alt="pet.name"
                                 class="w-full h-full object-cover">
                            <span v-else class="text-white text-xl font-bold">{{ pet?.name?.charAt(0) }}</span>
                        </div>
                        
                        <!-- Edit Photo Overlay -->
                        <button @click="openImageUpload" 
                             class="absolute bottom-0 right-0 p-2 bg-white text-primary rounded-lg shadow-lg hover:bg-white/90 transition-all transform hover:scale-105">
                            <Camera class="w-4 h-4" />
                        </button>
                    </div>
                    
                    <!-- Pet Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ pet?.name }}</h1>
                                <div class="flex flex-wrap items-center gap-4 text-blue-100">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">{{ pet?.breed || 'Mixed Breed' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-200"></span>
                                        <span class="text-sm">{{ pet?.age || 'Age unknown' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-200"></span>
                                        <span class="text-sm">{{ pet?.gender }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Menu -->
                            <div class="relative flex-shrink-0">
                                <button @click="toggleMenu" 
                                        class="p-2 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 hover:bg-white/20 transition-colors">
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
                        
                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                                <p class="text-xs text-blue-100 mb-1">Species</p>
                                <p class="font-semibold text-white">{{ pet?.species }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                                <p class="text-xs text-blue-100 mb-1">Weight</p>
                                <p class="font-semibold text-white">{{ pet?.weight || 'Not set' }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                                <p class="text-xs text-blue-100 mb-1">Color</p>
                                <p class="font-semibold text-white">{{ pet?.color || 'Not specified' }}</p>
                            </div>
                            <div v-if="pet?.microchip_id" class="bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20">
                                <p class="text-xs text-blue-100 mb-1">Microchip</p>
                                <p class="font-semibold text-white font-mono text-sm">{{ pet?.microchip_id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Content Section -->
            <div class="rounded-lg border bg-card shadow-sm">
                <!-- Tab Navigation -->
                <div class="border-b">
                    <nav class="flex -mb-px px-6">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm flex items-center gap-2',
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
                <div class="p-6">
                    <!-- Visit History Tab -->
                    <div v-if="activeTab === 'history'" class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Visit History</h3>
                            <span class="text-sm text-muted-foreground">{{ pet?.medical_history?.length || 0 }} visits</span>
                        </div>
                        
                        <div v-if="pet?.medical_history && pet.medical_history.length > 0" class="space-y-4">
                            <div v-for="record in pet.medical_history" :key="record.id" 
                                 class="bg-muted/30 rounded-lg p-4 border hover:bg-muted/50 transition-colors">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ record.type }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ formatDate(record.date) }}</p>
                                    </div>
                                    <span class="px-3 py-1 bg-primary/10 text-primary text-xs rounded-full font-medium">
                                        Completed
                                    </span>
                                </div>
                                
                                <p class="text-sm mb-3 leading-relaxed">{{ record.description }}</p>
                                
                                <div class="flex items-center gap-4 text-sm text-muted-foreground pt-3 border-t">
                                    <div class="flex items-center gap-2">
                                        <User class="w-4 h-4" />
                                        <span>{{ record.veterinarian }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Building2 class="w-4 h-4" />
                                        <span>{{ record.clinic }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <History class="w-16 h-16 mx-auto text-muted-foreground opacity-50 mb-4" />
                            <p class="text-muted-foreground">No visit history yet</p>
                            <p class="text-sm text-muted-foreground mt-2">Medical records from clinic visits will appear here</p>
                        </div>
                    </div>

                    <!-- Medical Records Tab -->
                    <div v-if="activeTab === 'medical'" class="space-y-6">
                        <!-- Medical Records by Category -->
                        <div v-if="medical_records && medical_records.length > 0">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold">Medical Records</h3>
                                <span class="text-sm text-muted-foreground">{{ medical_records.length }} record{{ medical_records.length !== 1 ? 's' : '' }}</span>
                            </div>

                            <!-- Categories Sidebar & Records -->
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <!-- Categories -->
                                <div class="lg:col-span-1">
                                    <div class="bg-card border rounded-lg p-4 space-y-2 sticky top-4">
                                        <h4 class="text-sm font-semibold mb-3 text-muted-foreground">Categories</h4>
                                        <button 
                                            @click="selectedRecordType = 'all'"
                                            :class="[
                                                'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-between',
                                                selectedRecordType === 'all' ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-muted'
                                            ]"
                                        >
                                            <span>All Records</span>
                                            <span class="text-xs bg-muted px-2 py-0.5 rounded-full">{{ medical_records.length }}</span>
                                        </button>
                                        <button 
                                            v-for="type in recordTypeCategories"
                                            :key="type.value"
                                            @click="selectedRecordType = type.value"
                                            :class="[
                                                'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-between',
                                                selectedRecordType === type.value ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-muted'
                                            ]"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span :class="type.iconColor">{{ type.icon }}</span>
                                                <span>{{ type.label }}</span>
                                            </div>
                                            <span class="text-xs bg-muted px-2 py-0.5 rounded-full">
                                                {{ getRecordCountByType(type.value) }}
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Records List -->
                                <div class="lg:col-span-3 space-y-4">
                                    <div v-for="record in filteredMedicalRecords" :key="record.id"
                                         class="bg-card border rounded-lg p-5 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-start gap-3 flex-1">
                                                <div :class="[
                                                    'p-2 rounded-lg',
                                                    getRecordTypeStyle(record.record_type).bgClass
                                                ]">
                                                    <span class="text-2xl">{{ getRecordTypeStyle(record.record_type).icon }}</span>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <h4 class="font-semibold">{{ record.visit_type_display || getRecordTypeStyle(record.record_type).label }}</h4>
                                                        <span :class="[
                                                            'px-2 py-0.5 rounded-full text-xs font-medium',
                                                            getRecordTypeStyle(record.record_type).badgeClass
                                                        ]">
                                                            {{ getRecordTypeStyle(record.record_type).label }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                                        <div class="flex items-center gap-1">
                                                            <Clock class="w-4 h-4" />
                                                            <span>{{ formatDate(record.visit_date || record.date) }}</span>
                                                        </div>
                                                        <div v-if="record.veterinarian_name" class="flex items-center gap-1">
                                                            <User class="w-4 h-4" />
                                                            <span>{{ record.veterinarian_name }}</span>
                                                        </div>
                                                        <div v-if="record.clinic?.clinic_name" class="flex items-center gap-1">
                                                            <Building2 class="w-4 h-4" />
                                                            <span>{{ record.clinic.clinic_name }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Record Details -->
                                        <div class="space-y-3 text-sm">
                                            <div v-if="record.diagnosis" class="bg-muted/30 rounded-lg p-3">
                                                <span class="font-semibold text-xs text-muted-foreground uppercase">Diagnosis</span>
                                                <p class="mt-1">{{ record.diagnosis }}</p>
                                            </div>
                                            <div v-if="record.treatment_provided || record.treatment" class="bg-muted/30 rounded-lg p-3">
                                                <span class="font-semibold text-xs text-muted-foreground uppercase">Treatment</span>
                                                <p class="mt-1">{{ record.treatment_provided || record.treatment }}</p>
                                            </div>
                                            <div v-if="record.medications_prescribed" class="bg-muted/30 rounded-lg p-3">
                                                <span class="font-semibold text-xs text-muted-foreground uppercase">Medications</span>
                                                <p class="mt-1">{{ record.medications_prescribed }}</p>
                                            </div>
                                            <div v-if="record.chief_complaint" class="bg-muted/30 rounded-lg p-3">
                                                <span class="font-semibold text-xs text-muted-foreground uppercase">Chief Complaint</span>
                                                <p class="mt-1">{{ record.chief_complaint }}</p>
                                            </div>
                                        </div>

                                        <!-- Footer Info -->
                                        <div v-if="record.next_visit_date || record.weight_recorded" class="mt-4 pt-4 border-t flex items-center gap-4 text-sm text-muted-foreground">
                                            <div v-if="record.weight_recorded">
                                                <span class="font-medium">Weight:</span> {{ record.weight_recorded }} kg
                                            </div>
                                            <div v-if="record.next_visit_date">
                                                <span class="font-medium">Next Visit:</span> {{ formatDate(record.next_visit_date) }}
                                            </div>
                                            <div v-if="record.cost_formatted" class="ml-auto">
                                                <span class="font-medium">Cost:</span> {{ record.cost_formatted }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State for Filtered Results -->
                                    <div v-if="filteredMedicalRecords.length === 0" class="text-center py-12 bg-muted/30 rounded-lg">
                                        <FileText class="w-12 h-12 mx-auto text-muted-foreground opacity-50 mb-3" />
                                        <p class="text-muted-foreground">No {{ selectedRecordType === 'all' ? '' : selectedRecordType }} records found</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State - No Medical Records -->
                        <div v-else class="text-center py-12 bg-muted/30 rounded-lg">
                            <FileText class="w-16 h-16 mx-auto text-muted-foreground opacity-50 mb-4" />
                            <p class="text-lg font-medium mb-2">No Medical Records</p>
                            <p class="text-sm text-muted-foreground">Medical records from clinic visits will appear here</p>
                        </div>

                        <!-- Vaccinations Section -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <ShieldCheck class="w-5 h-5 text-primary" />
                                Vaccinations
                            </h3>
                            
                            <div v-if="pet?.vaccinations && pet.vaccinations.length > 0" class="space-y-3">
                                <div v-for="vax in pet.vaccinations" :key="vax.id" 
                                     class="bg-card border rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-semibold mb-1">{{ vax.vaccine }}</h4>
                                            <div class="text-sm text-muted-foreground space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span>Last administered:</span>
                                                    <span class="font-medium text-foreground">{{ formatDate(vax.date) }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span>Next due:</span>
                                                    <span class="font-medium text-foreground">{{ formatDate(vax.next_due) }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <User class="w-4 h-4" />
                                                    <span>{{ vax.veterinarian }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getVaccinationStatus(vax.next_due).color]">
                                            {{ getVaccinationStatus(vax.next_due).text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-8 bg-muted/30 rounded-lg">
                                <p class="text-muted-foreground">No vaccination records</p>
                            </div>
                        </div>

                        <!-- Current Medications Section -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                <Pill class="w-5 h-5 text-primary" />
                                Current Medications
                            </h3>
                            
                            <div v-if="pet?.medications && pet.medications.length > 0" class="space-y-3">
                                <div v-for="med in pet.medications" :key="med.id" 
                                     class="bg-card border rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="font-semibold">{{ med.name }}</h4>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-xs font-medium">
                                            Active
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-muted-foreground">Dosage:</span>
                                            <span class="ml-2 font-medium">{{ med.dosage }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted-foreground">Frequency:</span>
                                            <span class="ml-2 font-medium">{{ med.frequency }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted-foreground">Started:</span>
                                            <span class="ml-2 font-medium">{{ formatDate(med.start_date) }}</span>
                                        </div>
                                        <div v-if="med.end_date">
                                            <span class="text-muted-foreground">Ends:</span>
                                            <span class="ml-2 font-medium">{{ formatDate(med.end_date) }}</span>
                                        </div>
                                    </div>
                                    <p v-if="med.notes" class="text-sm text-muted-foreground mt-3 pt-3 border-t">
                                        {{ med.notes }}
                                    </p>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-8 bg-muted/30 rounded-lg">
                                <p class="text-muted-foreground">No active medications</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Pet Documents</h3>
                            <button 
                                @click="openDocumentUpload"
                                :disabled="uploadingDocument"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 font-medium text-sm transition-all flex items-center gap-2 disabled:opacity-50"
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
                        <div v-if="documents.length > 0" class="space-y-3">
                            <div v-for="doc in documents" :key="doc.id"
                                 class="flex items-center justify-between bg-muted/30 rounded-lg p-4 border hover:bg-muted/50 transition-colors">
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
                        <div v-else class="text-center py-12">
                            <FileText class="w-16 h-16 mx-auto text-muted-foreground opacity-50 mb-4" />
                            <p class="text-muted-foreground mb-2">No documents uploaded yet</p>
                            <p class="text-sm text-muted-foreground mb-4">Upload vaccination certificates, medical reports, and other important documents</p>
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