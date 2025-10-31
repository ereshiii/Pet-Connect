<script setup lang="ts">
import ContactInformationController from '@/actions/App/Http/Controllers/Settings/ContactInformationController';
import { edit } from '@/routes/contact-information';
import { Form, Head, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    profile?: {
        phone?: string;
    };
    emergencyContact?: {
        name?: string;
        phone?: string;
        relationship?: string;
        email?: string;
    };
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Contact Information',
        href: edit().url,
    },
];

const page = usePage<{
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    profile?: Props['profile'];
    emergencyContact?: Props['emergencyContact'];
}>();

const relationshipOptions = [
    { value: 'spouse', label: 'Spouse' },
    { value: 'parent', label: 'Parent' },
    { value: 'child', label: 'Child' },
    { value: 'sibling', label: 'Sibling' },
    { value: 'friend', label: 'Friend' },
    { value: 'other', label: 'Other' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Contact Information" />

        <SettingsLayout>
            <div class="flex flex-col space-y-8">
                <!-- Personal Contact Information -->
                <div class="space-y-6">
                    <HeadingSmall
                        title="Personal Contact Information"
                        description="Update your personal contact details"
                    />

                    <Form
                        v-bind="ContactInformationController.update.form()"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="phone">Phone Number</Label>
                            <Input
                                id="phone"
                                type="tel"
                                class="mt-1 block w-full"
                                name="phone"
                                :default-value="page.props.profile?.phone"
                                autocomplete="tel"
                                placeholder="Your phone number"
                            />
                            <InputError class="mt-2" :message="errors.phone" />
                        </div>

                        <!-- Emergency Contact Section -->
                        <div class="space-y-4 pt-4 border-t">
                            <HeadingSmall
                                title="Emergency Contact Information"
                                description="Provide an emergency contact in case of medical emergencies"
                            />

                            <div class="grid gap-2">
                                <Label for="emergency_contact_name">Emergency Contact Name *</Label>
                                <Input
                                    id="emergency_contact_name"
                                    class="mt-1 block w-full"
                                    name="emergency_contact_name"
                                    :default-value="page.props.emergencyContact?.name"
                                    required
                                    autocomplete="name"
                                    placeholder="Full name of emergency contact"
                                />
                                <InputError class="mt-2" :message="errors.emergency_contact_name" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="emergency_contact_phone">Emergency Contact Phone *</Label>
                                    <Input
                                        id="emergency_contact_phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        name="emergency_contact_phone"
                                        :default-value="page.props.emergencyContact?.phone"
                                        required
                                        autocomplete="tel"
                                        placeholder="Emergency contact phone number"
                                    />
                                    <InputError class="mt-2" :message="errors.emergency_contact_phone" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="emergency_contact_relationship">Relationship *</Label>
                                    <select 
                                        id="emergency_contact_relationship"
                                        name="emergency_contact_relationship"
                                        :value="page.props.emergencyContact?.relationship"
                                        required
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Select relationship</option>
                                        <option 
                                            v-for="option in relationshipOptions" 
                                            :key="option.value" 
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="errors.emergency_contact_relationship" />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="emergency_contact_email">Emergency Contact Email</Label>
                                <Input
                                    id="emergency_contact_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    name="emergency_contact_email"
                                    :default-value="page.props.emergencyContact?.email"
                                    autocomplete="email"
                                    placeholder="Emergency contact email (optional)"
                                />
                                <InputError class="mt-2" :message="errors.emergency_contact_email" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                data-test="update-contact-information-button"
                            >
                                Save Contact Information
                            </Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600"
                                >
                                    Contact information saved.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
