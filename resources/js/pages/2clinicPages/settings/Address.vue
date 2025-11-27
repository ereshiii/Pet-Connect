<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { addressData } from '@/utils/philippineAddress';
import PinAddressLocation from '@/pages/2clinicPages/registerClinic/pinAddressLocation.vue';

interface Props {
    clinicRegistration: {
        id: number;
        region: string;
        province: string;
        city: string;
        barangay: string;
        street_address: string;
        postal_code: string;
        latitude: number | null;
        longitude: number | null;
    };
}

const props = defineProps<Props>();
const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Address',
        href: '/clinic/settings/address',
    },
];

const form = useForm({
    region: props.clinicRegistration.region || '',
    province: props.clinicRegistration.province || '',
    city: props.clinicRegistration.city || '',
    barangay: props.clinicRegistration.barangay || '',
    street_address: props.clinicRegistration.street_address || '',
    postal_code: props.clinicRegistration.postal_code || '',
    latitude: props.clinicRegistration.latitude || null,
    longitude: props.clinicRegistration.longitude || null,
});

const handleLocationUpdate = (location: { latitude: number; longitude: number }) => {
    form.latitude = location.latitude;
    form.longitude = location.longitude;
};

const regions = computed(() => Object.keys(addressData));
const provinces = computed(() => form.region ? Object.keys(addressData[form.region] || {}) : []);
const cities = computed(() => (form.region && form.province) ? Object.keys(addressData[form.region]?.[form.province] || {}) : []);
const barangays = computed(() => (form.region && form.province && form.city) ? addressData[form.region]?.[form.province]?.[form.city] || [] : []);

watch(() => form.region, () => { form.province = ''; form.city = ''; form.barangay = ''; });
watch(() => form.province, () => { form.city = ''; form.barangay = ''; });
watch(() => form.city, () => { form.barangay = ''; });

const submit = () => {
    form.patch('/clinic/settings/address', {
        preserveScroll: true,
        onSuccess: () => toast.success('Address updated successfully!'),
        onError: () => toast.error('Failed to update address'),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Address settings" />

        <div class="px-4 py-6">
            <div class="max-w-3xl mx-auto flex flex-col space-y-6">
                <HeadingSmall
                    title="Clinic Address"
                    description="Update your clinic's location"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="region">Region *</Label>
                        <select
                            v-model="form.region"
                            id="region"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            required
                        >
                            <option value="">Select region</option>
                            <option v-for="region in regions" :key="region" :value="region">
                                {{ region }}
                            </option>
                        </select>
                        <InputError :message="form.errors.region" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="province">Province *</Label>
                        <select
                            v-model="form.province"
                            id="province"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            :disabled="!form.region"
                            required
                        >
                            <option value="">Select province</option>
                            <option v-for="province in provinces" :key="province" :value="province">
                                {{ province }}
                            </option>
                        </select>
                        <InputError :message="form.errors.province" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">City *</Label>
                        <select
                            v-model="form.city"
                            id="city"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            :disabled="!form.province"
                            required
                        >
                            <option value="">Select city</option>
                            <option v-for="city in cities" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>
                        <InputError :message="form.errors.city" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="barangay">Barangay *</Label>
                        <select
                            v-model="form.barangay"
                            id="barangay"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            :disabled="!form.city"
                            required
                        >
                            <option value="">Select barangay</option>
                            <option v-for="barangay in barangays" :key="barangay" :value="barangay">
                                {{ barangay }}
                            </option>
                        </select>
                        <InputError :message="form.errors.barangay" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="street_address">Street Address</Label>
                        <Input
                            id="street_address"
                            v-model="form.street_address"
                            type="text"
                            placeholder="Building number, street name"
                        />
                        <InputError :message="form.errors.street_address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="postal_code">Postal Code</Label>
                        <Input
                            id="postal_code"
                            v-model="form.postal_code"
                            type="text"
                            placeholder="4 digits"
                        />
                        <InputError :message="form.errors.postal_code" />
                    </div>

                    <!-- Pin Location on Map -->
                    <div class="pt-6 border-t">
                        <PinAddressLocation
                            :latitude="form.latitude"
                            :longitude="form.longitude"
                            :address="`${form.street_address || ''}, ${form.barangay}, ${form.city}, ${form.province}`"
                            @location-update="handleLocationUpdate"
                        />
                        <InputError :message="form.errors.latitude || form.errors.longitude" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
