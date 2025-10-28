<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { schedule, appointmentDetails, rescheduleAppointment, appointmentCalendar } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: schedule().url,
    },
];
</script>

<template>
    <Head title="Schedule" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Comprehensive Schedule Overview -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Schedule Overview</h2>
                        <div class="flex gap-2">
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Book New Appointment
                            </button>
                            <button @click="$inertia.visit(appointmentCalendar().url)" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                📅 Calendar View
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Current Appointment -->
                        <div class="lg:col-span-1">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Today's Appointment</h3>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full dark:bg-green-900 dark:text-green-200">
                                        Confirmed
                                    </span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Bella - Annual Checkup</p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300">Golden Retriever</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Happy Paws Veterinary</p>
                                        <p class="text-xs text-blue-700 dark:text-blue-300">Dr. Sarah Johnson</p>
                                    </div>
                                    
                                    <div class="bg-blue-100 dark:bg-blue-800/50 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Today</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">Oct 27, 2025</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">2:30 PM</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">60 minutes</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <button @click="$inertia.visit(appointmentDetails(1).url)" 
                                                class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-xs font-medium">
                                            View Details
                                        </button>
                                        <button @click="$inertia.visit(rescheduleAppointment(1).url)" 
                                                class="flex-1 border border-blue-300 text-blue-700 py-2 px-3 rounded-md hover:bg-blue-100 text-xs font-medium dark:border-blue-600 dark:text-blue-300 dark:hover:bg-blue-800">
                                            Reschedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upcoming Appointments & Quick Stats -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Upcoming Appointments -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Upcoming Appointments</h4>
                                    <div class="space-y-3">
                                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Max - Vaccination</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">Nov 3, 2025 • 10:00 AM</p>
                                                </div>
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                                    Pending
                                                </span>
                                            </div>
                                            <button @click="$inertia.visit(appointmentDetails(2).url)" 
                                                    class="w-full text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                                View Details →
                                            </button>
                                        </div>
                                        
                                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Luna - Dental Cleaning</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">Nov 10, 2025 • 2:00 PM</p>
                                                </div>
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                    Scheduled
                                                </span>
                                            </div>
                                            <button @click="$inertia.visit(appointmentDetails(3).url)" 
                                                    class="w-full text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                                View Details →
                                            </button>
                                        </div>
                                        
                                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Charlie - Follow-up</p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">Nov 15, 2025 • 11:30 AM</p>
                                                </div>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full dark:bg-green-900 dark:text-green-200">
                                                    Confirmed
                                                </span>
                                            </div>
                                            <button @click="$inertia.visit(appointmentDetails(4).url)" 
                                                    class="w-full text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                                View Details →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Schedule Statistics -->
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Schedule Statistics</h4>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">4</p>
                                                <p class="text-xs text-green-700 dark:text-green-300">This Month</p>
                                            </div>
                                            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">12</p>
                                                <p class="text-xs text-blue-700 dark:text-blue-300">This Year</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Next Available</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">Tomorrow 9:00 AM</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Preferred Clinic</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">Happy Paws Vet</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">Avg. Visit Duration</span>
                                                <span class="text-gray-900 dark:text-gray-100 font-medium">45 minutes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Activity -->
                                <div class="md:col-span-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Recent Activity</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <span class="text-green-500 text-sm">✅</span>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900 dark:text-gray-100">Appointment confirmed for Bella's annual checkup</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">2 hours ago</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <span class="text-yellow-500 text-sm">📅</span>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900 dark:text-gray-100">Reminder sent for Max's upcoming vaccination</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">1 day ago</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <span class="text-blue-500 text-sm">📝</span>
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900 dark:text-gray-100">Luna's dental cleaning appointment scheduled</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">3 days ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex flex-wrap gap-2">
                            <button class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-md hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                Emergency Booking
                            </button>
                            <button class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-md hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800">
                                Routine Checkup
                            </button>
                            <button class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-md hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-200 dark:hover:bg-purple-800">
                                Vaccination Schedule
                            </button>
                            <button class="px-3 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-md hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:hover:bg-orange-800">
                                Export Calendar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>