<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Bell, Calendar, Check, CheckCheck, Clock, Trash2, CalendarCheck, Star, CalendarX, RefreshCw, BellRing } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { usePushNotifications } from '@/composables/usePushNotifications';
import { useFirebaseNotifications } from '@/composables/useFirebaseNotifications';

const toast = useToast();
const pushNotifications = usePushNotifications();
const { isSupported, permission, enableNotifications, disableNotifications, fcmToken } = useFirebaseNotifications();

// Firebase notification state
const isFirebaseEnabled = computed(() => permission.value === 'granted' && fcmToken.value);
const isTogglingFirebase = ref(false);

const toggleFirebaseNotifications = async (enabled: boolean) => {
    isTogglingFirebase.value = true;
    try {
        if (enabled) {
            await enableNotifications();
        } else {
            await disableNotifications();
        }
    } finally {
        isTogglingFirebase.value = false;
    }
};

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    data: any;
    is_read: boolean;
    read_at: string | null;
    created_at: string;
}

interface Props {
    initialNotifications?: {
        data: Notification[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    unreadCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    initialNotifications: () => ({ data: [], current_page: 1, last_page: 1, per_page: 20, total: 0 }),
    unreadCount: 0,
});

const notifications = ref<Notification[]>(props.initialNotifications?.data || []);
const loading = ref(false);
const currentUnreadCount = ref(props.unreadCount || 0);
const selectedCategory = ref<string>('all');

// Pagination
const notificationsPerPage = ref(10);
const currentPage = ref(1);

// Notification categories
const categories = [
    { id: 'all', label: 'All Notifications', icon: Bell, types: [] },
    { id: 'pending', label: 'Pending Appointments', icon: Clock, types: ['appointment_booked', 'appointment_confirmed'] },
    { id: 'reviews', label: 'New Reviews', icon: Star, types: ['review_received', 'clinic_review_submitted', 'review_reply'] },
    { id: 'bookings', label: 'New Bookings', icon: CalendarCheck, types: ['appointment_booked'] },
    { id: 'reschedule', label: 'Reschedule Requests', icon: RefreshCw, types: ['appointment_rescheduled', 'reschedule_request', 'follow_up_appointment_rescheduled'] },
    { id: 'cancellations', label: 'Cancellation Requests', icon: CalendarX, types: ['appointment_cancelled', 'cancel_request'] },
    { id: 'reminders', label: 'Reminders', icon: Calendar, types: ['appointment_reminder', 'follow_up_appointment_reminder'] },
];

const filteredNotifications = computed(() => {
    if (selectedCategory.value === 'all') {
        return notifications.value;
    }
    
    const category = categories.find(c => c.id === selectedCategory.value);
    if (!category) return notifications.value;
    
    return notifications.value.filter(n => 
        category.types.some(type => n.type.includes(type))
    );
});

// Paginated notifications
const paginatedNotifications = computed(() => {
    const start = (currentPage.value - 1) * notificationsPerPage.value;
    const end = start + notificationsPerPage.value;
    return filteredNotifications.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredNotifications.value.length / notificationsPerPage.value);
});

const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        // Scroll to top of notifications
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const changePerPage = (perPage: number) => {
    notificationsPerPage.value = perPage;
    currentPage.value = 1; // Reset to first page
};

const getCategoryCount = (categoryId: string) => {
    if (categoryId === 'all') {
        return notifications.value.filter(n => !n.is_read).length;
    }
    
    const category = categories.find(c => c.id === categoryId);
    if (!category) return 0;
    
    return notifications.value.filter(n => 
        !n.is_read && category.types.some(type => n.type.includes(type))
    ).length;
};

const hasUnreadNotifications = computed(() => notifications.value.some(n => !n.is_read));

const markAsRead = async (notification: Notification) => {
    if (notification.is_read) return;

    try {
        await axios.post(`/notifications/${notification.id}/mark-as-read`);
        notification.is_read = true;
        notification.read_at = new Date().toISOString();
        currentUnreadCount.value = Math.max(0, currentUnreadCount.value - 1);
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-as-read');
        notifications.value.forEach(n => {
            n.is_read = true;
            n.read_at = new Date().toISOString();
        });
        currentUnreadCount.value = 0;
        toast.success('All notifications marked as read');
    } catch (error) {
        console.error('Failed to mark all as read:', error);
        toast.error('Failed to mark all as read');
    }
};

const deleteNotification = async (notification: Notification) => {
    try {
        await axios.delete(`/notifications/${notification.id}`);
        notifications.value = notifications.value.filter(n => n.id !== notification.id);
        toast.success('Notification deleted');
    } catch (error) {
        console.error('Failed to delete notification:', error);
        toast.error('Failed to delete notification');
    }
};

const handleNotificationClick = (notification: Notification) => {
    markAsRead(notification);
    
    // Navigate based on notification type
    if (notification.data?.appointment_id) {
        router.visit(`/appointments/${notification.data.appointment_id}`);
    }
};

const getNotificationIcon = (type: string) => {
    if (type.includes('appointment')) return Calendar;
    if (type.includes('reminder')) return Clock;
    if (type.includes('review')) return Star;
    if (type.includes('cancel')) return CalendarX;
    if (type.includes('reschedule')) return RefreshCw;
    return Bell;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Push Notifications
const showPushPrompt = ref(false);

onMounted(() => {
    // Check if we should show push notification prompt
    if (pushNotifications.isSupported() && !pushNotifications.isGranted() && !pushNotifications.isDenied()) {
        showPushPrompt.value = true;
    }
    
    // Check subscription status
    pushNotifications.checkSubscription();
});

const enablePushNotifications = async () => {
    const granted = await pushNotifications.requestPermission();
    if (granted) {
        toast.success('Push notifications enabled successfully');
        showPushPrompt.value = false;
    } else {
        toast.error('Failed to enable push notifications');
    }
};

const dismissPushPrompt = () => {
    showPushPrompt.value = false;
};

// Test notification function
const isSendingTest = ref(false);
const sendTestNotification = async () => {
    isSendingTest.value = true;
    try {
        const response = await axios.post('/api/notifications/send', {
            title: 'Test Notification',
            body: 'This is a test push notification from PetConnect! 🐾',
            data: {
                type: 'test',
                timestamp: new Date().toISOString()
            },
            priority: 'high'
        });

        if (response.data.success) {
            toast.success('Test notification sent successfully! Check your device.');
        } else {
            toast.error(response.data.message || 'Failed to send test notification');
        }
    } catch (error: any) {
        console.error('Failed to send test notification:', error);
        toast.error(error.response?.data?.message || 'Failed to send test notification');
    } finally {
        isSendingTest.value = false;
    }
};
</script>

<template>
    <Head title="Notifications" />

    <AppLayout>
        <div class="py-6 sm:py-8 md:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
                <!-- Firebase Push Notification Settings -->
                <div class="mb-4 sm:mb-6">
                    <Card>
                        <CardHeader class="p-4 sm:p-6">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <BellRing class="h-5 w-5 text-primary" />
                                    <div>
                                        <CardTitle class="text-base sm:text-lg">Push Notification Settings</CardTitle>
                                        <CardDescription class="text-xs sm:text-sm mt-0.5">
                                            Manage your notification preferences
                                        </CardDescription>
                                    </div>
                                </div>
                                
                                <!-- Toggle Switch in Header -->
                                <div class="flex items-center gap-2">
                                    <Badge 
                                        v-if="isFirebaseEnabled" 
                                        variant="default" 
                                        class="text-xs"
                                    >
                                        Active
                                    </Badge>
                                    <Badge 
                                        v-else-if="permission === 'denied'" 
                                        variant="destructive" 
                                        class="text-xs"
                                    >
                                        Blocked
                                    </Badge>
                                    <button
                                        @click="toggleFirebaseNotifications(!isFirebaseEnabled)"
                                        :disabled="!isSupported || permission === 'denied' || isTogglingFirebase"
                                        :class="[
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors',
                                            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                            'disabled:cursor-not-allowed disabled:opacity-50',
                                            isFirebaseEnabled ? 'bg-primary' : 'bg-input'
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                                isFirebaseEnabled ? 'translate-x-5' : 'translate-x-0'
                                            ]"
                                        />
                                    </button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent v-if="permission === 'denied' || !isSupported || isFirebaseEnabled" class="p-4 sm:p-6 pt-0 space-y-4">
                            <!-- Test Notification Button -->
                            <div v-if="isFirebaseEnabled" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <Button
                                    @click="sendTestNotification"
                                    :disabled="isSendingTest"
                                    variant="outline"
                                    size="sm"
                                    class="w-full sm:w-auto text-xs sm:text-sm"
                                >
                                    <BellRing class="h-4 w-4 mr-2" />
                                    {{ isSendingTest ? 'Sending...' : 'Send Test Notification' }}
                                </Button>
                                <p class="text-xs text-muted-foreground">
                                    Click to send a test push notification to this device
                                </p>
                            </div>
                            
                            <!-- Status Messages -->
                            <div v-if="permission === 'denied'" class="text-xs text-destructive p-3 bg-destructive/10 rounded-lg border border-destructive/20">
                                ⚠️ Please enable notifications in your browser settings
                            </div>
                            <div v-else-if="!isSupported" class="p-3 bg-muted/50 rounded-lg border border-dashed">
                                <p class="text-xs text-muted-foreground">
                                    ⚠️ Push notifications are not supported in your current browser. Try Chrome, Firefox, or Edge.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Push Notification Prompt -->
                <div v-if="showPushPrompt" class="mb-4 sm:mb-6">
                    <Card class="border-primary/50 bg-primary/5">
                        <CardContent class="p-4 sm:p-6">
                            <div class="flex items-start gap-3 sm:gap-4">
                                <BellRing class="h-5 w-5 sm:h-6 sm:w-6 text-primary flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <h3 class="font-semibold text-sm sm:text-base mb-1">Enable Push Notifications</h3>
                                    <p class="text-xs sm:text-sm text-muted-foreground mb-3 sm:mb-4">
                                        Stay updated with instant notifications for appointments, reminders, and important updates.
                                    </p>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <Button @click="enablePushNotifications" size="sm" class="text-xs sm:text-sm">
                                            Enable Notifications
                                        </Button>
                                        <Button @click="dismissPushPrompt" variant="ghost" size="sm" class="text-xs sm:text-sm">
                                            Maybe Later
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
                    <!-- Sidebar Categories -->
                    <div class="w-full lg:w-64 flex-shrink-0">
                        <Card>
                            <CardHeader class="p-4 sm:p-6 lg:block hidden">
                                <CardTitle class="text-base sm:text-lg">Categories</CardTitle>
                            </CardHeader>
                            <CardContent class="p-0">
                                <!-- Mobile: Horizontal Scroll -->
                                <nav class="lg:hidden flex overflow-x-auto gap-2 p-3 scrollbar-hide">
                                    <button
                                        v-for="category in categories"
                                        :key="category.id"
                                        @click="selectedCategory = category.id"
                                        class="relative flex-shrink-0 flex flex-col items-center justify-center p-3 rounded-lg transition-colors hover:bg-accent/50"
                                        :class="selectedCategory === category.id 
                                            ? 'bg-accent text-accent-foreground border-2 border-primary' 
                                            : 'text-muted-foreground hover:text-foreground border-2 border-transparent'"
                                    >
                                        <div class="relative">
                                            <component
                                                :is="category.icon"
                                                class="h-6 w-6"
                                                :class="selectedCategory === category.id ? 'text-primary' : ''"
                                            />
                                            <Badge 
                                                v-if="getCategoryCount(category.id) > 0"
                                                variant="destructive"
                                                class="absolute -top-2 -right-2 h-5 min-w-5 px-1 flex items-center justify-center text-xs rounded-full"
                                            >
                                                {{ getCategoryCount(category.id) }}
                                            </Badge>
                                        </div>
                                    </button>
                                </nav>
                                
                                <!-- Desktop: Vertical List -->
                                <nav class="hidden lg:block">
                                    <button
                                        v-for="category in categories"
                                        :key="category.id"
                                        @click="selectedCategory = category.id"
                                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium transition-colors hover:bg-accent/50"
                                        :class="selectedCategory === category.id 
                                            ? 'bg-accent text-accent-foreground border-l-4 border-primary' 
                                            : 'text-muted-foreground hover:text-foreground'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <component
                                                :is="category.icon"
                                                class="h-4 w-4"
                                                :class="selectedCategory === category.id ? 'text-primary' : ''"
                                            />
                                            <span>{{ category.label }}</span>
                                        </div>
                                        <Badge 
                                            v-if="getCategoryCount(category.id) > 0"
                                            variant="default"
                                            class="ml-auto text-xs"
                                        >
                                            {{ getCategoryCount(category.id) }}
                                        </Badge>
                                    </button>
                                </nav>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        <Card>
                            <CardHeader class="p-3 sm:p-4 md:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                    <div>
                                        <CardTitle class="text-lg sm:text-xl md:text-2xl">
                                            {{ categories.find(c => c.id === selectedCategory)?.label || 'Notifications' }}
                                        </CardTitle>
                                        <CardDescription class="text-xs sm:text-sm">Stay updated with your appointments and activities</CardDescription>
                                    </div>
                                    <Button
                                        v-if="hasUnreadNotifications"
                                        @click="markAllAsRead"
                                        variant="outline"
                                        size="sm"
                                        class="w-full sm:w-auto text-xs sm:text-sm"
                                    >
                                        <CheckCheck class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-2" />
                                        Mark all as read
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="p-3 sm:p-4 md:p-6">
                                <!-- Pagination Controls -->
                                <div v-if="filteredNotifications.length > 0" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 p-2 sm:p-3 bg-muted/30 rounded-lg border mb-3 sm:mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">Show:</span>
                                        <select 
                                            v-model="notificationsPerPage" 
                                            @change="changePerPage(notificationsPerPage)"
                                            class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md bg-background text-xs sm:text-sm"
                                        >
                                            <option :value="10">10 per page</option>
                                            <option :value="50">50 per page</option>
                                            <option :value="100">100 per page</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex items-center justify-between sm:justify-start gap-2">
                                        <button 
                                            @click="changePage(currentPage - 1)"
                                            :disabled="currentPage === 1"
                                            class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                                        >
                                            Previous
                                        </button>
                                        <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                                            Page {{ currentPage }} of {{ totalPages }}
                                        </span>
                                        <button 
                                            @click="changePage(currentPage + 1)"
                                            :disabled="currentPage === totalPages"
                                            class="flex-1 sm:flex-none px-2 sm:px-3 py-1.5 sm:py-1 border rounded-md hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-medium"
                                        >
                                            Next
                                        </button>
                                    </div>
                                </div>

                                <div v-if="loading" class="text-center py-6 sm:py-8 text-xs sm:text-sm text-muted-foreground">
                                    Loading notifications...
                                </div>

                                <div v-else-if="filteredNotifications.length === 0" class="text-center py-6 sm:py-8 text-muted-foreground">
                                    <component
                                        :is="categories.find(c => c.id === selectedCategory)?.icon || Bell"
                                        class="h-10 w-10 sm:h-12 sm:w-12 mx-auto mb-2 sm:mb-3 opacity-50"
                                    />
                                    <p class="text-xs sm:text-sm">No notifications in this category</p>
                                </div>

                                <div v-else class="space-y-2 sm:space-y-3">
                                    <div
                                        v-for="notification in paginatedNotifications"
                                        :key="notification.id"
                                        class="relative p-3 sm:p-4 border rounded-lg transition-colors hover:bg-accent/50 cursor-pointer group"
                                        :class="{ 'bg-accent/20 border-primary/20': !notification.is_read }"
                                        @click="handleNotificationClick(notification)"
                                    >
                                        <div class="flex gap-2 sm:gap-3 md:gap-4">
                                            <div class="flex-1 min-w-0 space-y-1 sm:space-y-2">
                                                <div class="flex items-start justify-between gap-2 sm:gap-3 md:gap-4">
                                                    <div class="flex-1">
                                                        <h3 
                                                            class="font-semibold text-sm sm:text-base"
                                                            :class="notification.is_read ? 'text-muted-foreground' : 'text-foreground'"
                                                        >
                                                            {{ notification.title }}
                                                        </h3>
                                                        <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 sm:mt-1">
                                                            {{ notification.message }}
                                                        </p>
                                                    </div>

                                                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                                                        <Badge v-if="!notification.is_read" variant="default" class="text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5">New</Badge>
                                                        
                                                        <Button
                                                            v-if="!notification.is_read"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-7 w-7 sm:h-8 sm:w-8 opacity-100 sm:opacity-0 group-hover:opacity-100"
                                                            @click.stop="markAsRead(notification)"
                                                        >
                                                            <Check class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                                        </Button>
                                                        
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-7 w-7 sm:h-8 sm:w-8 opacity-100 sm:opacity-0 group-hover:opacity-100"
                                                            @click.stop="deleteNotification(notification)"
                                                        >
                                                            <Trash2 class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                                        </Button>
                                                    </div>
                                                </div>

                                                <div class="text-[10px] sm:text-xs text-muted-foreground">
                                                    {{ formatDate(notification.created_at) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="!notification.is_read"
                                            class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 sm:w-1 h-8 sm:h-12 bg-primary rounded-r"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
