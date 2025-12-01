<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Upload, X, Image as ImageIcon } from 'lucide-vue-next';

interface Props {
    clinicRegistration?: {
        id: number;
        clinic_name: string;
        gallery?: string[];
    };
}

const props = defineProps<Props>();
const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Clinic Gallery',
        href: '/clinic/settings/clinic-gallery',
    },
];

const fileInput = ref<HTMLInputElement | null>(null);
const previewImages = ref<string[]>([]);
const selectedPhotoUrl = ref<string>('');
const isPreviewOpen = ref(false);

const form = useForm({
    photos: [] as File[],
    remove_photos: [] as number[],
});

const existingPhotos = ref<string[]>(props.clinicRegistration?.gallery || []);

const selectPhotos = () => {
    fileInput.value?.click();
};

const openPhotoPreview = (photoUrl: string) => {
    selectedPhotoUrl.value = photoUrl;
    isPreviewOpen.value = true;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    
    if (!files) return;
    
    Array.from(files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
        form.photos.push(file);
    });
};

const removePreview = (index: number) => {
    previewImages.value.splice(index, 1);
    form.photos.splice(index, 1);
};

const removeExisting = (index: number) => {
    form.remove_photos.push(index);
    existingPhotos.value.splice(index, 1);
};

const submit = () => {
    form.post('/clinic/settings/clinic-gallery', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (page) => {
            toast.success('Gallery updated successfully!');
            previewImages.value = [];
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
            // Update existing photos with the new data
            if (page.props.clinicRegistration?.gallery) {
                existingPhotos.value = page.props.clinicRegistration.gallery;
            }
        },
        onError: (errors) => {
            console.error('Gallery update errors:', errors);
            if (errors.photos) {
                toast.error(`Upload failed: ${errors.photos}`);
            } else {
                toast.error('Failed to update gallery');
            }
        },
    });
};

const cancelChanges = () => {
    form.reset();
    previewImages.value = [];
    existingPhotos.value = props.clinicRegistration?.gallery || [];
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Clinic Gallery" />

        <div class="px-3 md:px-4 py-4 md:py-6">
            <div class="max-w-3xl mx-auto space-y-4 md:space-y-6">
                <HeadingSmall
                    title="Clinic Gallery"
                    description="Showcase your clinic with photos"
                />

                <Card>
                    <CardHeader class="p-4 md:p-6">
                        <CardTitle class="text-base md:text-lg">Photo Gallery</CardTitle>
                        <CardDescription class="text-xs md:text-sm">
                            Add photos of your clinic, facilities, and team. These will be visible to pet owners browsing your profile.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 md:space-y-6 p-4 md:p-6">
                        <!-- Upload Button -->
                        <div>
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept="image/*"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <Button @click="selectPhotos" type="button" variant="outline" class="text-xs md:text-sm">
                                <Upload class="h-3 w-3 md:h-4 md:w-4 mr-1.5 md:mr-2" />
                                Add Photos
                            </Button>
                            <p class="text-[10px] md:text-xs text-muted-foreground mt-1.5 md:mt-2">
                                JPG, PNG or GIF. Max 10MB per image. You can upload multiple images at once.
                            </p>
                            <InputError :message="form.errors.photos" class="mt-2" />
                        </div>

                        <!-- Existing Photos -->
                        <div v-if="existingPhotos.length > 0">
                            <h4 class="text-xs md:text-sm font-semibold mb-2 md:mb-3">Current Gallery ({{ existingPhotos.length }} {{ existingPhotos.length === 1 ? 'photo' : 'photos' }})</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-4">
                                <div 
                                    v-for="(photo, index) in existingPhotos" 
                                    :key="index"
                                    class="relative group aspect-square rounded-lg overflow-hidden border cursor-pointer"
                                    @click="openPhotoPreview(photo)"
                                >
                                    <img 
                                        :src="photo" 
                                        :alt="`Gallery photo ${index + 1}`"
                                        class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                    />
                                    <button
                                        @click.stop="removeExisting(index)"
                                        type="button"
                                        class="absolute top-1 right-1 md:top-2 md:right-2 p-1 md:p-1.5 bg-destructive text-destructive-foreground rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-10"
                                    >
                                        <X class="h-3 w-3 md:h-4 md:w-4" />
                                    </button>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors" />
                                </div>
                            </div>
                        </div>

                        <!-- Preview New Photos -->
                        <div v-if="previewImages.length > 0">
                            <h4 class="text-xs md:text-sm font-semibold mb-2 md:mb-3">New Photos to Upload</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-4">
                                <div 
                                    v-for="(preview, index) in previewImages" 
                                    :key="index"
                                    class="relative group aspect-square rounded-lg overflow-hidden border border-dashed"
                                >
                                    <img 
                                        :src="preview" 
                                        :alt="`Preview ${index + 1}`"
                                        class="w-full h-full object-cover"
                                    />
                                    <button
                                        @click="removePreview(index)"
                                        type="button"
                                        class="absolute top-1 right-1 md:top-2 md:right-2 p-1 md:p-1.5 bg-destructive text-destructive-foreground rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <X class="h-3 w-3 md:h-4 md:w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div 
                            v-if="existingPhotos.length === 0 && previewImages.length === 0"
                            class="flex flex-col items-center justify-center py-8 md:py-12 border-2 border-dashed rounded-lg"
                        >
                            <ImageIcon class="h-10 w-10 md:h-12 md:w-12 text-muted-foreground mb-2 md:mb-4" />
                            <p class="text-xs md:text-sm text-muted-foreground text-center px-4">
                                No photos in your gallery yet.<br />
                                Click "Add Photos" to get started.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="form.photos.length > 0 || form.remove_photos.length > 0" class="flex flex-col sm:flex-row gap-2 md:gap-3 pt-3 md:pt-4 border-t">
                            <Button @click="submit" :disabled="form.processing" class="text-xs md:text-sm w-full sm:w-auto">
                                Save Changes
                            </Button>
                            <Button 
                                @click="cancelChanges" 
                                variant="outline"
                                :disabled="form.processing"
                                class="text-xs md:text-sm w-full sm:w-auto"
                            >
                                Cancel
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Info Card -->
                <Card>
                    <CardHeader class="p-4 md:p-6">
                        <CardTitle class="text-xs md:text-sm">Gallery Tips</CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 md:p-6 pt-0">
                        <ul class="space-y-1.5 md:space-y-2 text-xs md:text-sm text-muted-foreground">
                            <li>• Add photos of your clinic's exterior and interior</li>
                            <li>• Showcase your facilities and equipment</li>
                            <li>• Include photos of your team in action</li>
                            <li>• Use high-quality, well-lit images</li>
                            <li>• Avoid photos with sensitive patient information</li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Photo Preview Dialog -->
        <Dialog v-model:open="isPreviewOpen">
            <DialogContent class="max-w-[95vw] sm:max-w-4xl p-0 overflow-hidden">
                <div class="relative w-full h-[60vh] sm:h-[80vh] bg-black flex items-center justify-center">
                    <img 
                        :src="selectedPhotoUrl" 
                        alt="Photo preview"
                        class="max-w-full max-h-full object-contain"
                    />
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
