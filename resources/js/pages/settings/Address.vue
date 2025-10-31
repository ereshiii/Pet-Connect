<script setup lang="ts">
import AddressController from '@/actions/App/Http/Controllers/Settings/AddressController';
import { edit } from '@/routes/address';
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
    address?: {
        address_line_1?: string;
        address_line_2?: string;
        city?: string;
        state?: string;
        postal_code?: string;
        country?: string;
    };
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Address Settings',
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
    address?: Props['address'];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Address Settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Address Information"
                    description="Update your primary address information"
                />

                <Form
                    v-bind="AddressController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="address_line_1">Address Line 1 *</Label>
                        <Input
                            id="address_line_1"
                            class="mt-1 block w-full"
                            name="address_line_1"
                            :default-value="page.props.address?.address_line_1"
                            required
                            autocomplete="address-line1"
                            placeholder="Street address, house number"
                        />
                        <InputError class="mt-2" :message="errors.address_line_1" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address_line_2">Address Line 2</Label>
                        <Input
                            id="address_line_2"
                            class="mt-1 block w-full"
                            name="address_line_2"
                            :default-value="page.props.address?.address_line_2"
                            autocomplete="address-line2"
                            placeholder="Apartment, suite, building (optional)"
                        />
                        <InputError class="mt-2" :message="errors.address_line_2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="city">City *</Label>
                            <Input
                                id="city"
                                class="mt-1 block w-full"
                                name="city"
                                :default-value="page.props.address?.city"
                                required
                                autocomplete="address-level2"
                                placeholder="City"
                            />
                            <InputError class="mt-2" :message="errors.city" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="state">State/Province *</Label>
                            <Input
                                id="state"
                                class="mt-1 block w-full"
                                name="state"
                                :default-value="page.props.address?.state"
                                required
                                autocomplete="address-level1"
                                placeholder="State or Province"
                            />
                            <InputError class="mt-2" :message="errors.state" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="postal_code">Postal Code *</Label>
                            <Input
                                id="postal_code"
                                class="mt-1 block w-full"
                                name="postal_code"
                                :default-value="page.props.address?.postal_code"
                                required
                                autocomplete="postal-code"
                                placeholder="Postal code"
                            />
                            <InputError class="mt-2" :message="errors.postal_code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="country">Country</Label>
                            <Input
                                id="country"
                                class="mt-1 block w-full"
                                name="country"
                                :default-value="page.props.address?.country || 'Philippines'"
                                autocomplete="country"
                                placeholder="Country"
                            />
                            <InputError class="mt-2" :message="errors.country" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-address-button"
                        >
                            Save Address
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
                                Address saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
