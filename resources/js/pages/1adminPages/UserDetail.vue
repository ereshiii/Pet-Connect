<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, User as UserIcon, Mail, Phone, Calendar, Building2, Users, Clock, Star, CheckCircle, XCircle, AlertCircle, Briefcase, PawPrint, Info, MapPin, Stethoscope, FileText, Globe } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { computed, ref } from 'vue';

interface Props { user: any; pets?: any[]; appointments?: any[]; clinic?: any; services?: any[]; staff?: any[]; operating_hours?: any[]; subscription?: any; recent_appointments?: any[]; reviews?: any[]; stats?: any; }
const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Admin Dashboard', href: '/admin' }, { title: 'User Management', href: '/admin/user-management' }, { title: 'User Details', href: '#' }];
const goBack = () => window.history.back();
const isPetOwner = computed(() => props.user.account_type === 'user');
const isClinic = computed(() => props.user.account_type === 'clinic');
const activeTab = ref('basic');

const clinicTabs = [
    { id: 'basic', name: 'Basic Information', icon: Info },
    { id: 'address', name: 'Address & Location', icon: MapPin },
    { id: 'hours', name: 'Operating Hours', icon: Clock },
    { id: 'services', name: 'Services', icon: Stethoscope },
    { id: 'staff', name: 'Veterinarians & Staff', icon: Users },
    { id: 'additional', name: 'Certifications and Permits', icon: FileText }
];

const getStatusBadge = (status: string) => { const badges: Record<string, string> = { approved: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', rejected: 'bg-red-100 text-red-800', completed: 'bg-blue-100 text-blue-800', confirmed: 'bg-green-100 text-green-800' }; return badges[status] || 'bg-gray-100 text-gray-800'; };
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
const formatTime = (time: string) => { if (!time) return ''; return new Date(`2000-01-01 ${time}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }); };
const approveClinic = (clinicId: number) => { if (confirm('Approve this clinic?')) router.post(`/admin/clinics/${clinicId}/approve`, {}); };
const rejectClinic = (clinicId: number) => { const reason = prompt('Reason for rejection:'); if (reason) router.post(`/admin/clinics/${clinicId}/reject`, { reason }); };
const getFileName = (path: string) => { if (typeof path === 'object' && path.file_name) return path.file_name; return path.split('/').pop() || path; };
const getFileUrl = (path: string) => { if (typeof path === 'object' && path.file_path) return `/storage/${path.file_path}`; return `/storage/${path}`; };
const viewFile = (path: string) => { window.open(getFileUrl(path), '_blank'); };
const downloadFile = (path: string) => { const link = document.createElement('a'); link.href = getFileUrl(path); link.download = getFileName(path); document.body.appendChild(link); link.click(); document.body.removeChild(link); };
</script>

<template>
<Head title="User Detail" />
<AppLayout :breadcrumbs="breadcrumbs">
<div class="p-6 space-y-6">
<button @click="goBack" class="inline-flex items-center gap-2 text-sm text-primary hover:underline"><ArrowLeft class="h-4 w-4" />Back</button>
<div class="rounded-lg border bg-card p-6">
<div class="flex items-start justify-between">
<div class="flex items-center gap-4">
<div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center"><UserIcon class="h-8 w-8 text-primary" /></div>
<div>
<h1 class="text-2xl font-bold">{{ props.user.profile?.first_name ?? props.user.name }} {{ props.user.profile?.last_name ?? '' }}</h1>
<p class="text-muted-foreground flex items-center gap-2 mt-1"><Mail class="h-4 w-4" />{{ props.user.email }}</p>
<div class="flex items-center gap-2 mt-2">
<Badge :class="getStatusBadge(props.user.account_type)">{{ props.user.account_type === 'user' ? 'Pet Owner' : 'Clinic' }}</Badge>
<Badge v-if="props.user.is_admin" variant="destructive">Admin</Badge>
<Badge v-if="props.user.email_verified_at" variant="outline"><CheckCircle class="h-3 w-3 mr-1" />Verified</Badge>
</div>
</div>
</div>
<div v-if="isClinic && props.clinic?.status === 'pending'" class="flex gap-2">
<button @click="approveClinic(props.clinic.id)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><CheckCircle class="h-4 w-4 inline mr-2" />Approve</button>
<button @click="rejectClinic(props.clinic.id)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"><XCircle class="h-4 w-4 inline mr-2" />Reject</button>
</div>
</div>
</div>

<template v-if="isPetOwner">
<div v-if="pets && pets.length > 0" class="rounded-lg border bg-card"><div class="p-4 border-b"><h2 class="font-semibold flex items-center gap-2"><PawPrint class="h-5 w-5" />Pets ({{ pets.length }})</h2></div><div class="divide-y"><div v-for="pet in pets" :key="pet.id" class="p-4"><h3 class="font-semibold">{{ pet.name }}</h3><p class="text-sm text-muted-foreground">{{ pet.species }}  {{ pet.breed?.name ?? 'Unknown' }}</p></div></div></div>
</template>

<template v-if="isClinic && clinic">
<div v-if="clinic.status === 'pending'" class="rounded-lg border-2 border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 p-4">
<div class="flex items-center gap-3">
<AlertCircle class="h-5 w-5 text-yellow-600" />
<div>
<h3 class="font-semibold text-yellow-900 dark:text-yellow-200">Pending Approval</h3>
<p class="text-sm text-yellow-800 dark:text-yellow-300">Review clinic details below for approval.</p>
</div>
</div>
</div>

<!-- Clinic Details with Tabs -->
<div class="rounded-lg border bg-card">
<div class="border-b">
<nav class="flex space-x-8 px-6" aria-label="Tabs">
<button
v-for="tab in clinicTabs"
:key="tab.id"
@click="activeTab = tab.id"
:class="[
'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
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
<!-- Basic Information Tab -->
<div v-if="activeTab === 'basic'" class="space-y-6">
<div v-if="clinic.clinic_description" class="p-4 bg-muted/30 rounded-lg">
<p class="text-sm font-semibold text-muted-foreground mb-2">Clinic Description</p>
<p class="text-sm leading-relaxed">{{ clinic.clinic_description }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-4">
<div>
<p class="text-sm text-muted-foreground mb-1">Clinic Name</p>
<p class="font-semibold text-lg">{{ clinic.clinic_name }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Email</p>
<p class="font-medium flex items-center gap-2">
<Mail class="h-4 w-4" />
{{ clinic.email }}
</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Phone</p>
<p class="font-medium flex items-center gap-2">
<Phone class="h-4 w-4" />
{{ clinic.phone }}
</p>
</div>
<div v-if="clinic.website">
<p class="text-sm text-muted-foreground mb-1">Website</p>
<a :href="clinic.website" target="_blank" class="font-medium text-primary hover:underline flex items-center gap-2">
<Globe class="h-4 w-4" />
{{ clinic.website }}
</a>
</div>
</div>

<div class="space-y-4">
<div>
<p class="text-sm text-muted-foreground mb-1">Emergency Clinic</p>
<Badge :variant="clinic.is_emergency_clinic ? 'destructive' : 'outline'">
{{ clinic.is_emergency_clinic ? 'Yes - 24/7 Emergency Services' : 'No' }}
</Badge>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Status</p>
<Badge :class="getStatusBadge(clinic.status)">{{ clinic.status }}</Badge>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Registration Date</p>
<p class="font-medium flex items-center gap-2">
<Calendar class="h-4 w-4" />
{{ formatDate(clinic.created_at) }}
</p>
</div>
<div v-if="subscription">
<p class="text-sm text-muted-foreground mb-1">Subscription Plan</p>
<Badge variant="secondary">{{ subscription.plan_name }}</Badge>
</div>
</div>
</div>

<div v-if="clinic.rejection_reason" class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg">
<p class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">Rejection Reason</p>
<p class="text-sm text-red-700 dark:text-red-300">{{ clinic.rejection_reason }}</p>
</div>
</div>

<!-- Address & Location Tab -->
<div v-if="activeTab === 'address'" class="space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-4">
<h3 class="font-semibold text-lg mb-4">Complete Address</h3>
<div>
<p class="text-sm text-muted-foreground mb-1">Street Address</p>
<p class="font-medium">{{ clinic.street_address }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Barangay</p>
<p class="font-medium">{{ clinic.barangay }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">City/Municipality</p>
<p class="font-medium">{{ clinic.city }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Province</p>
<p class="font-medium">{{ clinic.province }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Region</p>
<p class="font-medium">{{ clinic.region }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Country</p>
<p class="font-medium">{{ clinic.country }}</p>
</div>
<div>
<p class="text-sm text-muted-foreground mb-1">Postal Code</p>
<p class="font-medium">{{ clinic.postal_code }}</p>
</div>
</div>

<div class="space-y-4">
<h3 class="font-semibold text-lg mb-4">Location Coordinates</h3>
<div class="p-4 bg-muted/30 rounded-lg">
<p class="text-sm text-muted-foreground mb-2">GPS Coordinates</p>
<div class="space-y-1">
<p class="font-mono text-sm">Latitude: {{ clinic.latitude }}</p>
<p class="font-mono text-sm">Longitude: {{ clinic.longitude }}</p>
</div>
</div>
<div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
<p class="text-sm text-blue-900 dark:text-blue-200">
<MapPin class="h-4 w-4 inline mr-1" />
Location pinned on map during registration
</p>
</div>
</div>
</div>
</div>

<!-- Operating Hours Tab -->
<div v-if="activeTab === 'hours'" class="space-y-4">
<h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
<Clock class="h-5 w-5" />
Weekly Operating Hours
</h3>
<div v-if="clinic.is_emergency_clinic" class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg mb-4">
<p class="text-sm font-semibold text-red-900 dark:text-red-200">
<AlertCircle class="h-4 w-4 inline mr-1" />
Emergency Clinic - Available 24/7
</p>
</div>
<div v-if="operating_hours && operating_hours.length > 0" class="space-y-2">
<div v-for="hours in operating_hours" :key="hours.day_of_week" 
class="flex justify-between items-center p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors">
<span class="font-semibold capitalize text-base">{{ hours.day_of_week }}</span>
<div v-if="hours.is_closed" class="flex items-center gap-2">
<XCircle class="h-4 w-4 text-muted-foreground" />
<span class="text-muted-foreground">Closed</span>
</div>
<div v-else class="flex items-center gap-2">
<Clock class="h-4 w-4 text-primary" />
<span class="font-medium">{{ formatTime(hours.opening_time) }} - {{ formatTime(hours.closing_time) }}</span>
</div>
</div>
</div>
<div v-else class="text-center py-8 text-muted-foreground">
<Clock class="h-12 w-12 mx-auto mb-2 opacity-50" />
<p>No operating hours set</p>
</div>
</div>

<!-- Services Tab -->
<div v-if="activeTab === 'services'" class="space-y-4">
<div class="flex items-center justify-between mb-4">
<h3 class="font-semibold text-lg flex items-center gap-2">
<Stethoscope class="h-5 w-5" />
Services Offered
</h3>
<Badge variant="secondary">{{ services?.length ?? 0 }} Services</Badge>
</div>
<div v-if="services && services.length > 0" class="space-y-3">
<div v-for="service in services" :key="service.id" 
class="p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors">
<div class="flex items-start justify-between">
<div class="flex-1">
<h4 class="font-semibold text-base mb-1">{{ service.name }}</h4>
<p class="text-sm text-muted-foreground mb-3">{{ service.description }}</p>
<div class="flex flex-wrap items-center gap-2">
<Badge variant="outline">{{ service.category }}</Badge>
<span class="text-xs text-muted-foreground flex items-center gap-1">
<Clock class="h-3 w-3" />
{{ service.duration_minutes }} minutes
</span>
<Badge v-if="service.is_emergency_service" variant="destructive" class="text-xs">
Emergency Service
</Badge>
<Badge v-if="service.requires_appointment" variant="secondary" class="text-xs">
Appointment Required
</Badge>
</div>
</div>
</div>
</div>
</div>
<div v-else class="text-center py-12 text-muted-foreground">
<Stethoscope class="h-16 w-16 mx-auto mb-3 opacity-50" />
<p class="text-lg">No services listed</p>
</div>
</div>

<!-- Veterinarians & Staff Tab -->
<div v-if="activeTab === 'staff'" class="space-y-4">
<div class="flex items-center justify-between mb-4">
<h3 class="font-semibold text-lg flex items-center gap-2">
<Users class="h-5 w-5" />
Veterinarians & Staff
</h3>
<Badge variant="secondary">{{ staff?.length ?? 0 }} Members</Badge>
</div>
<div v-if="staff && staff.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div v-for="member in staff" :key="member.id" 
class="p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors">
<div class="space-y-3">
<div class="flex items-start justify-between">
<h4 class="font-semibold text-base">{{ member.name }}</h4>
<Badge variant="outline" class="text-xs">Veterinarian</Badge>
</div>
<div class="space-y-2">
<div class="flex items-center gap-2 text-sm">
<Mail class="h-3.5 w-3.5 text-muted-foreground" />
<span class="text-muted-foreground">{{ member.email }}</span>
</div>
<div v-if="member.phone" class="flex items-center gap-2 text-sm">
<Phone class="h-3.5 w-3.5 text-muted-foreground" />
<span class="text-muted-foreground">{{ member.phone }}</span>
</div>
<div v-if="member.license_number" class="p-2 bg-muted/30 rounded text-xs">
<span class="text-muted-foreground">License: </span>
<span class="font-mono font-semibold">{{ member.license_number }}</span>
</div>
</div>
<div v-if="member.specializations && member.specializations.length > 0" class="pt-2 border-t">
<p class="text-xs text-muted-foreground mb-2">Specializations:</p>
<div class="flex flex-wrap gap-1">
<Badge v-for="(spec, idx) in member.specializations" :key="idx" variant="secondary" class="text-xs">
{{ spec }}
</Badge>
</div>
</div>
</div>
</div>
</div>
<div v-else class="text-center py-12 text-muted-foreground">
<Users class="h-16 w-16 mx-auto mb-3 opacity-50" />
<p class="text-lg">No staff members listed</p>
</div>
</div>

<!-- Certifications and Permits Tab -->
<div v-if="activeTab === 'additional'" class="space-y-6">
<div v-if="clinic.specialties && clinic.specialties.length > 0">
<h3 class="font-semibold text-lg mb-3">Clinic Specialties</h3>
<div class="flex flex-wrap gap-2">
<Badge v-for="(specialty, idx) in clinic.specialties" :key="idx" variant="secondary" class="text-sm px-3 py-1">
{{ specialty }}
</Badge>
</div>
</div>

<div v-if="clinic.certifications && clinic.certifications.length > 0" :class="clinic.specialties?.length > 0 ? 'pt-6 border-t' : ''">
<h3 class="font-semibold text-lg mb-4">Certifications & Permits</h3>
<div class="space-y-3">
<div v-for="(cert, idx) in clinic.certifications" :key="idx" 
class="p-4 bg-muted/30 rounded-lg border hover:border-primary/50 transition-colors">
<div class="flex items-center justify-between gap-4">
<div class="flex items-center gap-3 flex-1 min-w-0">
<FileText class="h-5 w-5 text-primary flex-shrink-0" />
<div class="min-w-0">
<p class="text-sm font-medium truncate">{{ getFileName(cert) }}</p>
<p class="text-xs text-muted-foreground">Certification Document</p>
</div>
</div>
<div class="flex items-center gap-2 flex-shrink-0">
<button @click="viewFile(cert)" 
class="px-3 py-1.5 text-xs font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors flex items-center gap-1">
<Globe class="h-3.5 w-3.5" />
View
</button>
<button @click="downloadFile(cert)" 
class="px-3 py-1.5 text-xs font-medium bg-muted text-foreground rounded-md hover:bg-muted/80 transition-colors">
Download
</button>
</div>
</div>
</div>
</div>
</div>

<div v-if="reviews && reviews.length > 0" class="pt-6 border-t">
<h3 class="font-semibold text-lg mb-3 flex items-center gap-2">
<Star class="h-5 w-5" />
Recent Reviews
<Badge variant="secondary">{{ reviews.length }}</Badge>
</h3>
<div class="space-y-3">
<div v-for="review in reviews" :key="review.id" 
class="p-4 rounded-lg border bg-card">
<div class="flex items-start justify-between mb-2">
<div class="flex items-center gap-2">
<div class="flex items-center">
<Star v-for="n in 5" :key="n" 
:class="n <= review.rating ? 'text-yellow-500 fill-yellow-500' : 'text-gray-300'"
class="h-4 w-4" />
</div>
<span class="text-sm text-muted-foreground">{{ review.reviewer_email }}</span>
</div>
<span class="text-xs text-muted-foreground">{{ formatDate(review.created_at) }}</span>
</div>
<p class="text-sm">{{ review.review_text }}</p>
</div>
</div>
</div>

<div v-if="!clinic.specialties?.length && !clinic.certifications?.length && !reviews?.length" 
class="text-center py-12 text-muted-foreground">
<FileText class="h-16 w-16 mx-auto mb-3 opacity-50" />
<p class="text-lg">No certifications or permits available</p>
</div>
</div>
</div>
</div>
</template>
</div>
</AppLayout>
</template>
