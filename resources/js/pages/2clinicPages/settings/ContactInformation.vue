<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    clinicRegistration: {
        id: number;
        email: string;
        phone: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Contact Information',
        href: '/clinic/settings/contact-information',
    },
];

const form = useForm({
    email: props.clinicRegistration.email,
    phone: props.clinicRegistration.phone,
});

const submit = () => {
    form.patch('/clinic/settings/contact-information', {
        preserveScroll: true,
        onSuccess: () => toast.success('Contact information updated successfully!'),
        onError: () => toast.error('Failed to update contact information'),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Contact Information" />

        <div class="px-4 py-6">
            <div class="max-w-3xl mx-auto flex flex-col space-y-6">
                <HeadingSmall
                    title="Contact Information"
                    description="Update how pet owners can reach you"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="email">Email Address *</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
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
                            required
                            placeholder="+63 XXX XXX XXXX"
                        />
                        <InputError :message="form.errors.phone" />
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
