<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { clinics, viewMap, clinicDetails, booking } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clinics',
        href: clinics().url,
    },
    
];

// Carousel functionality
const currentSlide = ref(0);
let intervalId: number | null = null;

// Navigation function
const viewClinicDetails = (clinicId: string | number) => {
    router.visit(clinicDetails(clinicId).url);
};

const bookAppointment = (clinic: any) => {
    router.visit(booking().url, {
        data: {
            clinic_id: clinic.id,
            clinic_name: clinic.name,
        },
        preserveScroll: true,
    });
};

const featuredClinics = [
    {
        id: 1,
        name: "PetCare Veterinary Clinic",
        address: "123 Main Street, City Center",
        rating: 4.8,
        stars: "★★★★★",
        status: "Open Now",
        statusColor: "text-green-600 dark:text-green-400",
        distance: "2.3 km",
        bgColor: "bg-blue-100 dark:bg-blue-900",
        textColor: "text-blue-600 dark:text-blue-300"
    },
    {
        id: 2,
        name: "Animal Hospital Plus",
        address: "456 Oak Avenue, Downtown",
        rating: 4.2,
        stars: "★★★★☆",
        status: "Closed",
        statusColor: "text-red-600 dark:text-red-400",
        distance: "1.8 km",
        bgColor: "bg-green-100 dark:bg-green-900",
        textColor: "text-green-600 dark:text-green-300"
    },
    {
        id: 3,
        name: "Happy Paws Veterinary",
        address: "789 Elm Street, Westside",
        rating: 4.9,
        stars: "★★★★★",
        status: "Open Now",
        statusColor: "text-green-600 dark:text-green-400",
        distance: "1.2 km",
        bgColor: "bg-purple-100 dark:bg-purple-900",
        textColor: "text-purple-600 dark:text-purple-300"
    },
    {
        id: 4,
        name: "Metro Animal Hospital",
        address: "321 Pine Road, Central District",
        rating: 4.3,
        stars: "★★★★☆",
        status: "Open Now",
        statusColor: "text-green-600 dark:text-green-400",
        distance: "3.5 km",
        bgColor: "bg-orange-100 dark:bg-orange-900",
        textColor: "text-orange-600 dark:text-orange-300"
    },
    {
        id: 5,
        name: "Sunrise Pet Clinic",
        address: "654 Maple Avenue, Eastside",
        rating: 4.7,
        stars: "★★★★★",
        status: "Closed",
        statusColor: "text-red-600 dark:text-red-400",
        distance: "4.1 km",
        bgColor: "bg-teal-100 dark:bg-teal-900",
        textColor: "text-teal-600 dark:text-teal-300"
    },
    {
        id: 6,
        name: "24/7 Emergency Vet",
        address: "987 Cedar Lane, Hospital District",
        rating: 4.1,
        stars: "★★★★☆",
        status: "Open 24/7",
        statusColor: "text-green-600 dark:text-green-400",
        distance: "5.8 km",
        bgColor: "bg-red-100 dark:bg-red-900",
        textColor: "text-red-600 dark:text-red-300"
    }
];

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % featuredClinics.length;
};

const prevSlide = () => {
    currentSlide.value = currentSlide.value === 0 ? featuredClinics.length - 1 : currentSlide.value - 1;
};

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

const startAutoScroll = () => {
    intervalId = setInterval(nextSlide, 3000); // Auto-scroll every 3 seconds
};

const stopAutoScroll = () => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

onMounted(() => {
    startAutoScroll();
});

onUnmounted(() => {
    stopAutoScroll();
});
</script>

<template>
    <Head title="Clinics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="bg-white rounded-xl border border-sidebar-border/70 dark:border-sidebar-border dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filters</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Service Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option>All services</option>
                            <option>Vaccination</option>
                            <option>Dental Care</option>
                            <option>Surgery</option>
                            <option>Emergency Care</option>
                            <option>Grooming</option>
                        </select>
                    </div>

                    <!-- Minimum Rating Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum rating</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option>Any rating</option>
                            <option>5 stars</option>
                            <option>4+ stars</option>
                            <option>3+ stars</option>
                            <option>2+ stars</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option>All</option>
                            <option>General Practice</option>
                            <option>Specialty Clinic</option>
                            <option>Emergency Hospital</option>
                            <option>Mobile Vet</option>
                        </select>
                    </div>

                    <!-- Distance Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distance</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option>Any</option>
                            <option>Within 5 km</option>
                            <option>Within 10 km</option>
                            <option>Within 25 km</option>
                            <option>Within 50 km</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex justify-between items-center">
                    <div class="flex gap-3">
                        <button class="px-6 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:hover:bg-gray-600">
                            Filter
                        </button>
                        <button class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Clear
                        </button>
                    </div>
                    
                    <!-- View on Map Button -->
                    <button @click="$inertia.visit(viewMap().url)" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        View on Map
                    </button>
                </div>
            </div>
                <!-- Featured Clinic Carousel -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800"
                     @mouseenter="stopAutoScroll" 
                     @mouseleave="startAutoScroll">
                    
                    <!-- Carousel Container -->
                    <div class="relative h-full">
                        <!-- Clinic Cards -->
                        <div v-for="(clinic, index) in featuredClinics" 
                             :key="index"
                             :class="['absolute inset-0 transition-transform duration-500 ease-in-out', 
                                     index === currentSlide ? 'translate-x-0' : 
                                     index < currentSlide ? '-translate-x-full' : 'translate-x-full']">
                            <div class="p-4 h-full flex flex-col">
                                <div class="flex-1">
                                    <div :class="['w-full h-32 rounded-lg mb-3 flex items-center justify-center', clinic.bgColor]">
                                        <span :class="['text-sm', clinic.textColor]">{{ clinic.name }} Image</span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ clinic.name }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ clinic.address }}</p>
                                    <div class="flex items-center mb-2">
                                        <div class="flex text-yellow-400">
                                            {{ clinic.stars }}
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">({{ clinic.rating }})</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mb-3">
                                    <span :class="['text-sm font-medium', clinic.statusColor]">{{ clinic.status }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ clinic.distance }}</span>
                                </div>
                                <!-- Action Buttons -->
                                <div class="flex gap-2">
                                    <button @click="viewClinicDetails(clinic.id)" 
                                            class="flex-1 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-md hover:bg-gray-200 dark:hover:bg-gray-500 text-xs font-medium transition-colors">
                                        View Details
                                    </button>
                                    <button @click="bookAppointment(clinic)" 
                                            class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-xs font-medium transition-colors">
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Navigation Arrows -->
                        <button @click="prevSlide" 
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button @click="nextSlide" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <!-- Slide Indicators -->
                        <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
                            <button v-for="(clinic, index) in featuredClinics" 
                                    :key="index"
                                    @click="goToSlide(index)"
                                    :class="['w-2 h-2 rounded-full transition-colors', 
                                            index === currentSlide ? 'bg-white' : 'bg-white bg-opacity-50']">
                            </button>
                        </div>
                        
                        <!-- Slide Counter -->
                        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                            {{ currentSlide + 1 }} / {{ featuredClinics.length }}
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-white dark:bg-gray-800"
            >
                <!-- Nearby Clinics Section -->
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Nearby Clinics</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Clinic 1 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-purple-100 dark:bg-purple-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-purple-600 dark:text-purple-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Happy Paws Veterinary</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">789 Elm Street, Westside</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★★</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.9)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Open Now</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">1.2 km</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: General Care, Surgery, Emergency
                            </div>
                            <button @click="viewClinicDetails(3)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>

                        <!-- Clinic 2 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-orange-100 dark:bg-orange-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-orange-600 dark:text-orange-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Metro Animal Hospital</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">321 Pine Road, Central District</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★☆</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.3)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Open Now</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">3.5 km</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: Vaccination, Dental, Grooming
                            </div>
                            <button @click="viewClinicDetails(4)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>

                        <!-- Clinic 3 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-teal-100 dark:bg-teal-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-teal-600 dark:text-teal-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Sunrise Pet Clinic</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">654 Maple Avenue, Eastside</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★★</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.7)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-red-600 dark:text-red-400 font-medium">Closed</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">4.1 km</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: Specialty Care, X-Ray, Lab Tests
                            </div>
                            <button @click="viewClinicDetails(5)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>

                        <!-- Clinic 4 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-red-100 dark:bg-red-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-red-600 dark:text-red-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">24/7 Emergency Vet</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">987 Cedar Lane, Hospital District</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★☆</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.1)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Open 24/7</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">5.8 km</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: Emergency, Critical Care, Surgery
                            </div>
                            <button @click="viewClinicDetails(6)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>

                        <!-- Clinic 5 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-indigo-100 dark:bg-indigo-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-indigo-600 dark:text-indigo-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Gentle Care Animal Clinic</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">147 Birch Street, Suburb Area</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★★</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.6)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Open Now</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">2.9 km</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: Wellness, Preventive Care, Boarding
                            </div>
                            <button @click="viewClinicDetails(7)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>

                        <!-- Clinic 6 -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="w-full h-40 bg-pink-100 dark:bg-pink-900 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-pink-600 dark:text-pink-300 text-sm">Clinic Photo</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Paws & Claws Mobile Vet</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Serving Greater Metro Area</p>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400">★★★★☆</div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">(4.4)</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">Available</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Mobile</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Services: Home Visits, Basic Care, Vaccination
                            </div>
                            <button @click="viewClinicDetails(8)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 text-sm">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>