<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { history } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'History',
        href: history().url,
    },
];
</script>

<template>
    <Head title="History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Comprehensive Booking History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Booking History</h2>
                        <div class="flex gap-2">
                            <select class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option>All Pets</option>
                                <option>Bella</option>
                                <option>Max</option>
                                <option>Luna</option>
                                <option>Charlie</option>
                            </select>
                            <select class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <option>Last 6 Months</option>
                                <option>Last Month</option>
                                <option>Last 3 Months</option>
                                <option>Last Year</option>
                                <option>All Time</option>
                            </select>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Export History
                            </button>
                        </div>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">47</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Total Visits</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">12</p>
                            <p class="text-sm text-green-700 dark:text-green-300">This Year</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">3</p>
                            <p class="text-sm text-purple-700 dark:text-purple-300">Clinics Visited</p>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">$2,340</p>
                            <p class="text-sm text-orange-700 dark:text-orange-300">Total Spent</p>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Appointments</h3>
                        
                        <!-- Completed Appointment 1 -->
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Luna - Dental Cleaning</h4>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full dark:bg-green-900 dark:text-green-200">
                                            Completed
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Oct 20, 2025 • 2:00 PM</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Clinic & Doctor</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Happy Paws Veterinary</p>
                                            <p class="text-gray-600 dark:text-gray-400">Dr. Sarah Johnson</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Cost</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">$180.00</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Notes:</strong> Dental cleaning completed successfully. Minor tartar buildup removed. Recommended dental treats for maintenance.</p>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col gap-2">
                                    <button class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                        View Details
                                    </button>
                                    <button class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completed Appointment 2 -->
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Max - Vaccination Update</h4>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full dark:bg-green-900 dark:text-green-200">
                                            Completed
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Oct 15, 2025 • 10:30 AM</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Clinic & Doctor</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">PetCare Central</p>
                                            <p class="text-gray-600 dark:text-gray-400">Dr. Michael Chen</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Cost</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">$95.00</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-md">
                                        <p class="text-sm text-green-900 dark:text-green-100"><strong>Notes:</strong> Annual vaccinations updated. DHPP and Rabies vaccines administered. Next vaccination due in 12 months.</p>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col gap-2">
                                    <button class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                        View Details
                                    </button>
                                    <button class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completed Appointment 3 -->
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Bella - Emergency Visit</h4>
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full dark:bg-red-900 dark:text-red-200">
                                            Emergency
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Oct 8, 2025 • 7:45 PM</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Clinic & Doctor</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">24/7 Animal Emergency</p>
                                            <p class="text-gray-600 dark:text-gray-400">Dr. Emily Rodriguez</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Cost</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">$320.00</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-md">
                                        <p class="text-sm text-red-900 dark:text-red-100"><strong>Notes:</strong> Emergency visit for digestive upset. X-rays taken, foreign object ruled out. Prescribed medication and special diet. Follow-up recommended in 3 days.</p>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col gap-2">
                                    <button class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                        View Details
                                    </button>
                                    <button class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cancelled Appointment -->
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors opacity-75">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Charlie - Grooming Session</h4>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full dark:bg-gray-700 dark:text-gray-300">
                                            Cancelled
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Date & Time</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Oct 5, 2025 • 11:00 AM</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Clinic & Doctor</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">Pampered Pets Spa</p>
                                            <p class="text-gray-600 dark:text-gray-400">Grooming Team</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600 dark:text-gray-400">Cost</p>
                                            <p class="text-gray-900 dark:text-gray-100 font-medium">$0.00</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Reason:</strong> Cancelled by owner due to scheduling conflict. No cancellation fee applied (24+ hours notice).</p>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col gap-2">
                                    <button class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                        Rebook
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Load More / Pagination -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Showing 4 of 47 appointments</p>
                            <div class="flex gap-2">
                                <button class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Previous
                                </button>
                                <button class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Load More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>