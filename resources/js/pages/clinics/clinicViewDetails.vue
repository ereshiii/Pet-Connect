<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { clinics, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Props
interface Props {
    clinicId: string | number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: clinics().url,
    },
    {
        title: 'Clinic Details',
        href: '#',
    },
];

// State
const activeTab = ref('overview');

// Clinic data - in a real app this would be fetched from an API based on clinicId
const clinicData = computed(() => {
    // Mock data based on clinic ID
    const clinics: Record<string, any> = {
        '3': {
            id: 3,
            name: "Happy Paws Veterinary",
            address: "789 Elm Street, Westside",
            phone: "(555) 123-4567",
            email: "info@happypawsvet.com",
            website: "www.happypawsvet.com",
            rating: 4.9,
            stars: "★★★★★",
            status: "Open Now",
            statusColor: "text-green-600 dark:text-green-400",
            distance: "1.2 km",
            description: "Happy Paws Veterinary is a full-service animal hospital providing comprehensive veterinary care for dogs, cats, and exotic pets. Our experienced team of veterinarians and support staff are dedicated to providing the highest quality care with compassion and understanding.",
            services: [
                "General Health Exams",
                "Surgical Procedures", 
                "Emergency Care",
                "Dental Care",
                "Vaccination Programs",
                "Diagnostic Imaging",
                "Laboratory Services",
                "Pet Boarding",
                "Grooming Services"
            ],
            hours: {
                "Monday": "8:00 AM - 6:00 PM",
                "Tuesday": "8:00 AM - 6:00 PM", 
                "Wednesday": "8:00 AM - 6:00 PM",
                "Thursday": "8:00 AM - 6:00 PM",
                "Friday": "8:00 AM - 6:00 PM",
                "Saturday": "9:00 AM - 4:00 PM",
                "Sunday": "10:00 AM - 2:00 PM (Emergency Only)"
            },
            staff: [
                {
                    name: "Dr. Sarah Johnson",
                    title: "Lead Veterinarian",
                    specialties: ["Internal Medicine", "Surgery"],
                    experience: "15 years"
                },
                {
                    name: "Dr. Michael Chen", 
                    title: "Veterinarian",
                    specialties: ["Exotic Pets", "Dental Care"],
                    experience: "8 years"
                },
                {
                    name: "Lisa Rodriguez",
                    title: "Veterinary Technician", 
                    specialties: ["Emergency Care", "Laboratory"],
                    experience: "12 years"
                }
            ],
            reviews: [
                {
                    author: "Emma Wilson",
                    rating: 5,
                    date: "2025-10-20",
                    comment: "Excellent care for my dog Max. The staff is very professional and caring. Dr. Johnson explained everything clearly and my pet felt comfortable throughout the visit."
                },
                {
                    author: "John Smith", 
                    rating: 5,
                    date: "2025-10-15",
                    comment: "Best veterinary clinic in the area! They handled my cat's emergency surgery perfectly. Highly recommend Happy Paws to all pet owners."
                },
                {
                    author: "Maria Garcia",
                    rating: 4,
                    date: "2025-10-10", 
                    comment: "Great service and clean facilities. The only downside is sometimes the wait can be a bit long, but the quality of care makes it worth it."
                }
            ],
            amenities: [
                "Free Parking",
                "Wheelchair Accessible", 
                "Air Conditioning",
                "Separate Cat/Dog Waiting Areas",
                "Online Appointment Booking",
                "24/7 Emergency Hotline",
                "Pet Pharmacy",
                "Digital X-Ray"
            ]
        },
        // Add default clinic for other IDs
        'default': {
            id: props.clinicId,
            name: "Veterinary Clinic",
            address: "123 Main Street, City Center", 
            phone: "(555) 000-0000",
            email: "info@vetclinic.com",
            website: "www.vetclinic.com",
            rating: 4.5,
            stars: "★★★★★",
            status: "Open Now",
            statusColor: "text-green-600 dark:text-green-400",
            distance: "2.0 km",
            description: "A full-service veterinary clinic providing comprehensive care for your pets.",
            services: ["General Care", "Vaccination", "Surgery", "Emergency Care"],
            hours: {
                "Monday": "8:00 AM - 6:00 PM",
                "Tuesday": "8:00 AM - 6:00 PM",
                "Wednesday": "8:00 AM - 6:00 PM", 
                "Thursday": "8:00 AM - 6:00 PM",
                "Friday": "8:00 AM - 6:00 PM",
                "Saturday": "9:00 AM - 4:00 PM",
                "Sunday": "Closed"
            },
            staff: [],
            reviews: [],
            amenities: []
        }
    };
    
    return clinics[props.clinicId.toString()] || clinics['default'];
});

// Methods
const bookAppointment = () => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinicData.value.id,
            clinic_name: clinicData.value.name,
        },
        preserveScroll: true,
    });
};

const continueToBooking = () => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinicData.value.id,
            clinic_name: clinicData.value.name,
        },
        preserveScroll: true,
    });
};

const callClinic = () => {
    window.location.href = `tel:${clinicData.value.phone}`;
};

const emailClinic = () => {
    window.location.href = `mailto:${clinicData.value.email}`;
};

const visitWebsite = () => {
    window.open(`https://${clinicData.value.website}`, '_blank');
};

const getDirections = () => {
    const address = encodeURIComponent(clinicData.value.address);
    window.open(`https://maps.google.com?q=${address}`, '_blank');
};
</script>

<template>
    <Head :title="`${clinicData.name} - Clinic Details`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Clinic Image -->
                    <div class="lg:w-1/3">
                        <div class="w-full h-64 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                            <span class="text-purple-600 dark:text-purple-300 text-lg">{{ clinicData.name }} Photo</span>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="bookAppointment" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                📅 Book Appointment
                            </button>
                            <button @click="callClinic" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                📞 Call Now
                            </button>
                            <button @click="getDirections" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                🗺️ Directions
                            </button>
                            <button @click="visitWebsite" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                🌐 Website
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clinic Info -->
                    <div class="lg:w-2/3">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ clinicData.name }}</h1>
                                <p class="text-gray-600 dark:text-gray-400 mb-2">📍 {{ clinicData.address }}</p>
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400 mr-1">{{ clinicData.stars }}</div>
                                        <span class="text-gray-600 dark:text-gray-400">({{ clinicData.rating }})</span>
                                    </div>
                                    <span :class="['font-medium', clinicData.statusColor]">{{ clinicData.status }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ clinicData.distance }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">{{ clinicData.description }}</p>
                        
                        <!-- Quick Contact Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Phone</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="callClinic">{{ clinicData.phone }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Email</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="emailClinic">{{ clinicData.email }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Website</p>
                                <p class="text-blue-600 dark:text-blue-400 cursor-pointer" @click="visitWebsite">{{ clinicData.website }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'overview'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Overview
                        </button>
                        <button @click="activeTab = 'services'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'services' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Services
                        </button>
                        <button @click="activeTab = 'staff'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'staff' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Staff
                        </button>
                        <button @click="activeTab = 'hours'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'hours' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Hours
                        </button>
                        <button @click="activeTab = 'reviews'"
                                :class="['py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'reviews' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                            Reviews
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">About This Clinic</h3>
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">{{ clinicData.description }}</p>
                                
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Amenities</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="amenity in clinicData.amenities" :key="amenity" 
                                         class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        {{ amenity }}
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Location</h3>
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                    <p class="text-gray-700 dark:text-gray-300 mb-2">📍 {{ clinicData.address }}</p>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">{{ clinicData.distance }} from your location</p>
                                    <button @click="getDirections" 
                                            class="text-blue-600 dark:text-blue-400 text-sm font-medium hover:underline">
                                        Get Directions →
                                    </button>
                                </div>
                                
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Contact Information</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Phone:</span> <span class="text-blue-600 dark:text-blue-400">{{ clinicData.phone }}</span></p>
                                    <p><span class="font-medium">Email:</span> <span class="text-blue-600 dark:text-blue-400">{{ clinicData.email }}</span></p>
                                    <p><span class="font-medium">Website:</span> <span class="text-blue-600 dark:text-blue-400">{{ clinicData.website }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services Tab -->
                    <div v-if="activeTab === 'services'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Services Offered</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="service in clinicData.services" :key="service"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center mb-2">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ service }}</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Professional {{ service.toLowerCase() }} services for your pet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Tab -->
                    <div v-if="activeTab === 'staff'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Our Team</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="member in clinicData.staff" :key="member.name"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
                                <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900 rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-300 text-lg">👨‍⚕️</span>
                                </div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ member.name }}</h4>
                                <p class="text-blue-600 dark:text-blue-400 text-sm mb-2">{{ member.title }}</p>
                                <p class="text-gray-600 dark:text-gray-400 text-xs mb-2">{{ member.experience }} experience</p>
                                <div class="flex flex-wrap justify-center gap-1">
                                    <span v-for="specialty in member.specialties" :key="specialty"
                                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded dark:bg-blue-900 dark:text-blue-200">
                                        {{ specialty }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hours Tab -->
                    <div v-if="activeTab === 'hours'">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Operating Hours</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 max-w-md">
                            <div v-for="(hours, day) in clinicData.hours" :key="day" 
                                 class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ day }}</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ hours }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div v-if="activeTab === 'reviews'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Reviews</h3>
                            <div class="flex items-center">
                                <div class="flex text-yellow-400 mr-2">{{ clinicData.stars }}</div>
                                <span class="text-gray-600 dark:text-gray-400">{{ clinicData.rating }} out of 5</span>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="review in clinicData.reviews" :key="review.author"
                                 class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ review.author }}</h4>
                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400 text-sm mr-2">
                                                {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
                                            </div>
                                            <span class="text-gray-500 dark:text-gray-400 text-sm">{{ review.date }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
