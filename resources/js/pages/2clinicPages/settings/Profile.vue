<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/clinicSettings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/clinic/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user;

// Photo upload form and preview
const photoForm = useForm({ photo: null, remove: false });
const photoPreview = ref(user.profile?.profile_image_url ? `${user.profile.profile_image_url}?v=${Date.now()}` : null);

const onPhotoChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files && input.files[0];
    if (file) {
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            toast.error('File size must be less than 10MB');
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            toast.error('File must be JPEG, PNG, JPG, GIF, SVG, or WEBP');
            return;
        }

        photoForm.photo = file;
        photoForm.remove = false;
        photoPreview.value = URL.createObjectURL(file);
        toast.info(`Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
    }
};

const submitPhoto = () => {
    if (!photoForm.photo) {
        toast.error('Please select a photo first');
        return;
    }

    console.log('Uploading profile photo:', photoForm.photo.name, photoForm.photo.type, photoForm.photo.size);
    toast.info('Uploading photo...');
    
    // Submit as regular POST with multipart form data
    photoForm.post('/clinic/settings/profile/photo', {
        forceFormData: true,
        preserveState: false,
        onSuccess: (page) => {
            console.log('Profile photo upload successful', page);
            toast.success('Profile photo uploaded successfully!');
            photoForm.reset();
            // Update preview with timestamp to bypass cache
            if (page.props.auth?.user?.profile?.profile_image_url) {
                photoPreview.value = `${page.props.auth.user.profile.profile_image_url}?v=${Date.now()}`;
            }
        },
        onError: (errors) => {
            console.error('Upload errors:', errors);
            if (errors.photo) {
                toast.error(`Upload failed: ${errors.photo}`);
            } else if (errors.message) {
                toast.error(`Upload failed: ${errors.message}`);
            } else {
                toast.error('Upload failed: Unknown error occurred');
            }
        },
        onFinish: () => {
            console.log('Upload request finished');
        }
    });
};

const removePhoto = () => {
    if (!confirm('Remove profile photo?')) return;
    
    console.log('Removing profile photo');
    toast.info('Removing photo...');
    
    photoForm.photo = null;
    photoForm.remove = true;
    photoForm.post('/clinic/settings/profile/photo', {
        forceFormData: true,
        preserveState: false,
        onSuccess: (page) => {
            console.log('Profile photo removed successfully', page);
            toast.success('Profile photo removed successfully!');
            photoForm.reset();
            photoPreview.value = null;
        },
        onError: (errors) => {
            console.error('Remove errors:', errors);
            toast.error('Failed to remove photo');
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name and email address"
                />

                <!-- Profile Photo -->
                <div class="flex items-center gap-4">
                    <label class="relative w-24 h-24 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center cursor-pointer group hover:opacity-80 transition-opacity">
                        <input type="file" name="photo" class="hidden" accept="image/*" @change="onPhotoChange" />
                        <img v-if="photoPreview || user.profile?.profile_image" 
                             :src="photoPreview || `/storage/${user.profile?.profile_image}?v=${Date.now()}`" 
                             alt="Profile" 
                             class="w-full h-full object-cover" />
                        <div v-else class="text-xl text-gray-500">{{ user.profile?.initials || user.name[0] }}</div>
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs font-medium">Change</span>
                        </div>
                    </label>

                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2">
                            <Button 
                                v-if="photoPreview" 
                                type="button" 
                                @click="submitPhoto" 
                                :disabled="photoForm.processing"
                            >
                                {{ photoForm.processing ? 'Uploading...' : 'Upload' }}
                            </Button>
                            <Button 
                                v-if="user.profile?.profile_image" 
                                type="button"
                                variant="outline"
                                @click="removePhoto" 
                                :disabled="photoForm.processing"
                            >
                                {{ photoForm.processing ? 'Removing...' : 'Remove' }}
                            </Button>
                        </div>
                        <p class="text-xs text-muted-foreground">Accepted: jpeg, png, jpg, gif, svg, webp. Max 10MB.</p>
                    </div>
                </div>

                <form
                    method="PATCH"
                    action="/clinic/settings/profile"
                    class="space-y-6"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                href="/email/verification-notification"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit">Save</Button>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
