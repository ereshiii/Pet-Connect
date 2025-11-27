<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { computed, ref, watch } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Building2, Upload, X, Camera } from 'lucide-vue-next';
import PinAddressLocation from '@/pages/2clinicPages/registerClinic/pinAddressLocation.vue';
import { addressData } from '@/utils/philippineAddress';

const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Clinic Profile & Settings',
        href: '/clinic/settings/clinic-profile',
    },
];

const page = usePage();
const clinicRegistration = page.props.clinicRegistration;

const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    clinic_name: clinicRegistration?.clinic_name || '',
    clinic_description: clinicRegistration?.clinic_description || '',
    website: clinicRegistration?.website || '',
    email: clinicRegistration?.email || '',
    phone: clinicRegistration?.phone || '',
    region: clinicRegistration?.region || '',
    province: clinicRegistration?.province || '',
    city: clinicRegistration?.city || '',
    barangay: clinicRegistration?.barangay || '',
    street_address: clinicRegistration?.street_address || '',
    postal_code: clinicRegistration?.postal_code || '',
    latitude: clinicRegistration?.latitude || null,
    longitude: clinicRegistration?.longitude || null,
    clinic_photo: null as File | null,
    remove_photo: false,
});

const currentPhoto = computed(() => {
    if (photoPreview.value) {
        return photoPreview.value;
    }
    return clinicRegistration?.clinic_photo || null;
});

const regions = computed(() => Object.keys(addressData));
const provinces = computed(() => form.region ? Object.keys(addressData[form.region] || {}) : []);
const cities = computed(() => (form.region && form.province) ? Object.keys(addressData[form.region]?.[form.province] || {}) : []);
const barangays = computed(() => (form.region && form.province && form.city) ? addressData[form.region]?.[form.province]?.[form.city] || [] : []);

watch(() => form.region, () => { form.province = ''; form.city = ''; form.barangay = ''; });
watch(() => form.province, () => { form.city = ''; form.barangay = ''; });
watch(() => form.city, () => { form.barangay = ''; });

const handleLocationUpdate = (location: { latitude: number; longitude: number }) => {
    form.latitude = location.latitude;
    form.longitude = location.longitude;
};

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const updatePhotoPreview = () => {
    const file = photoInput.value?.files?.[0];
    
    if (!file) {
        return;
    }

    const reader = new FileReader();
    
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    
    reader.readAsDataURL(file);
    
    form.clinic_photo = file;
    form.remove_photo = false;
};

const removePhoto = () => {
    form.remove_photo = true;
    form.clinic_photo = null;
    photoPreview.value = null;
    
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const submitPhoto = () => {
    form.post('/clinic/settings/appearance/photo', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Clinic photo updated successfully!');
            photoPreview.value = null;
            form.clinic_photo = null;
            form.remove_photo = false;
        },
        onError: (errors) => {
            console.error('Photo update errors:', errors);
            toast.error('Failed to update clinic photo');
        },
    });
};

const cancelPhotoChange = () => {
    form.clinic_photo = null;
    form.remove_photo = false;
    photoPreview.value = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const submit = () => {
    form.patch('/clinic/settings/clinic-profile', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Clinic profile and settings updated successfully!');
        },
        onError: (errors) => {
            console.error('Clinic profile update errors:', errors);
            const errorMessages = Object.values(errors).flat();
            if (errorMessages.length > 0) {
                toast.error(errorMessages[0]);
            } else {
                toast.error('Failed to update clinic profile');
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Clinic Profile" />

        <div class="px-4 py-6">
            <div class="max-w-3xl mx-auto space-y-6">
                <HeadingSmall
                    title="Clinic Profile"
                    description="Manage your clinic's business information and profile photo"
                />

                <!-- Clinic Profile Preview Card -->
                <div class="bg-card border rounded-lg p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Clinic Profile Preview
                    </h3>
                    
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Photo Section -->
                        <div class="flex flex-col items-center space-y-4">
                            <div class="relative">
                                <div 
                                    class="w-32 h-32 rounded-lg overflow-hidden border-2 border-border bg-muted flex items-center justify-center"
                                >
                                    <img 
                                        v-if="currentPhoto" 
                                        :src="currentPhoto" 
                                        :alt="clinicRegistration?.clinic_name || 'Clinic'" 
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="text-muted-foreground text-center p-4">
                                        <Building2 class="h-12 w-12 mx-auto mb-2" />
                                        <p class="text-xs">No photo</p>
                                    </div>
                                </div>
                                
                                <button
                                    v-if="currentPhoto && !photoPreview"
                                    @click="removePhoto"
                                    type="button"
                                    class="absolute -top-2 -right-2 p-1 bg-destructive text-destructive-foreground rounded-full hover:bg-destructive/90 transition-colors"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            
                            <input
                                ref="photoInput"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="updatePhotoPreview"
                            />
                            
                            <div class="flex flex-col gap-2 w-full">
                                <Button
                                    @click="selectNewPhoto"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                >
                                    <Camera class="h-4 w-4 mr-2" />
                                    Change Photo
                                </Button>
                                
                                <div v-if="form.clinic_photo || form.remove_photo" class="flex gap-2">
                                    <Button
                                        @click="submitPhoto"
                                        type="button"
                                        size="sm"
                                        :disabled="form.processing"
                                        class="flex-1"
                                    >
                                        <Upload class="h-4 w-4 mr-2" />
                                        Save
                                    </Button>
                                    <Button
                                        @click="cancelPhotoChange"
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        :disabled="form.processing"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                            
                            <InputError :message="form.errors.clinic_photo" class="mt-2" />
                            
                            <p class="text-xs text-muted-foreground text-center">
                                JPG, PNG or GIF. Max 10MB.
                            </p>
                        </div>
                        
                        <!-- Clinic Info Preview -->
                        <div class="flex-1">
                            <div class="space-y-3">
                                <div>
                                    <h4 class="text-xl font-bold">
                                        {{ form.clinic_name || 'Your Clinic Name' }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex items-center">
                                            <span class="text-yellow-500">★</span>
                                            <span class="ml-1 text-sm font-medium">
                                                {{ clinicRegistration?.rating?.toFixed(1) || '0.0' }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-muted-foreground">
                                            ({{ clinicRegistration?.total_reviews || 0 }} reviews)
                                        </span>
                                    </div>
                                </div>
                                
                                <p
                                    class="text-sm text-muted-foreground overflow-hidden max-w-full"
                                    :title="form.clinic_description || 'Add a description to your clinic profile below.'"
                                    style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"
                                >
                                    {{ form.clinic_description || 'Add a description to your clinic profile below.' }}
                                </p>
                                
                                <div class="text-sm text-muted-foreground">
                                    <p>📍 {{ clinicRegistration?.city || 'City' }}, {{ clinicRegistration?.province || 'Province' }}</p>
                                </div>
                                
                                <div class="pt-2 border-t">
                                    <p class="text-xs text-muted-foreground">
                                        💡 All clinic information including contact and address can be updated below.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consolidated Clinic Settings Form -->
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Clinic Information -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="clinic_name">Clinic Name *</Label>
                                <Input
                                    id="clinic_name"
                                    v-model="form.clinic_name"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="Veterinary Clinic Name"
                                />
                                <InputError :message="form.errors.clinic_name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="clinic_description">Description</Label>
                                <Textarea
                                    id="clinic_description"
                                    v-model="form.clinic_description"
                                    class="mt-1 block w-full bg-white dark:bg-slate-900 text-black dark:text-white"
                                    placeholder="Brief description of your clinic services..."
                                    rows="4"
                                />
                                <InputError :message="form.errors.clinic_description" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="website">Website (Optional)</Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    type="url"
                                    class="mt-1 block w-full"
                                    placeholder="https://yourwebsite.com"
                                />
                                <InputError :message="form.errors.website" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="email">Email *</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="clinic@example.com"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Phone Number *</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="+63 9xx xxx xxxx"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold mb-4">Address Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Region -->
                            <div class="grid gap-2">
                                <Label for="region">Region *</Label>
                                <select
                                    id="region"
                                    v-model="form.region"
                                    class="mt-1 px-3 py-2 border border-input rounded-md bg-white dark:bg-slate-900 text-black dark:text-white text-sm font-normal"
                                    required
                                >
                                    <option value="">Select Region</option>
                                    <option v-for="region in regions" :key="region" :value="region">
                                        {{ region }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.region" />
                            </div>

                            <!-- Province -->
                            <div class="grid gap-2">
                                <Label for="province">Province *</Label>
                                <select
                                    id="province"
                                    v-model="form.province"
                                    class="mt-1 px-3 py-2 border border-input rounded-md bg-white dark:bg-slate-900 text-black dark:text-white text-sm font-normal"
                                    :disabled="!form.region"
                                    required
                                >
                                    <option value="">Select Province</option>
                                    <option v-for="province in provinces" :key="province" :value="province">
                                        {{ province }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.province" />
                            </div>

                            <!-- City -->
                            <div class="grid gap-2">
                                <Label for="city">City *</Label>
                                <select
                                    id="city"
                                    v-model="form.city"
                                    class="mt-1 px-3 py-2 border border-input rounded-md bg-white dark:bg-slate-900 text-black dark:text-white text-sm font-normal"
                                    :disabled="!form.province"
                                    required
                                >
                                    <option value="">Select City</option>
                                    <option v-for="city in cities" :key="city" :value="city">
                                        {{ city }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.city" />
                            </div>

                            <!-- Barangay -->
                            <div class="grid gap-2">
                                <Label for="barangay">Barangay *</Label>
                                <select
                                    id="barangay"
                                    v-model="form.barangay"
                                    class="mt-1 px-3 py-2 border border-input rounded-md bg-white dark:bg-slate-900 text-black dark:text-white text-sm font-normal"
                                    :disabled="!form.city"
                                    required
                                >
                                    <option value="">Select Barangay</option>
                                    <option v-for="barangay in barangays" :key="barangay" :value="barangay">
                                        {{ barangay }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.barangay" />
                            </div>

                            <!-- Street Address -->
                            <div class="grid gap-2 md:col-span-2">
                                <Label for="street_address">Street Address</Label>
                                <Input
                                    id="street_address"
                                    v-model="form.street_address"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., 123 Main Street, Building A"
                                />
                                <InputError :message="form.errors.street_address" />
                            </div>

                            <!-- Postal Code -->
                            <div class="grid gap-2">
                                <Label for="postal_code">Postal Code</Label>
                                <Input
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Postal code"
                                />
                                <InputError :message="form.errors.postal_code" />
                            </div>
                        </div>
                    </div>

                    <!-- Location Pinning -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold mb-4">Clinic Location on Map</h3>
                        <p class="text-sm text-muted-foreground mb-4">Pin your clinic's exact location on the map for accurate directions</p>
                        <PinAddressLocation
                            :initial-latitude="form.latitude"
                            :initial-longitude="form.longitude"
                            @location-update="handleLocationUpdate"
                        />
                        <InputError :message="form.errors.latitude || form.errors.longitude" />
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save All Changes' }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved successfully!
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
