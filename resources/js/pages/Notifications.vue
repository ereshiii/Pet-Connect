<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Bell, Calendar, Check, CheckCheck, Clock, Trash2, CalendarCheck, Star, CalendarX, RefreshCw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const toast = useToast();

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

// Notification categories
const categories = [
    { id: 'all', label: 'All Notifications', icon: Bell, types: [] },
    { id: 'pending', label: 'Pending Appointments', icon: Clock, types: ['appointment_booked', 'appointment_confirmed'] },
    { id: 'reviews', label: 'New Reviews', icon: Star, types: ['review_received', 'review_submitted'] },
    { id: 'bookings', label: 'New Bookings', icon: CalendarCheck, types: ['appointment_booked'] },
    { id: 'reschedule', label: 'Reschedule Requests', icon: RefreshCw, types: ['appointment_rescheduled', 'reschedule_request'] },
    { id: 'cancellations', label: 'Cancellation Requests', icon: CalendarX, types: ['appointment_cancelled', 'cancel_request'] },
    { id: 'reminders', label: 'Reminders', icon: Calendar, types: ['appointment_reminder'] },
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
</script>

<template>
    <Head title="Notifications" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6">
                    <!-- Sidebar Categories -->
                    <div class="w-64 flex-shrink-0">
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-lg">Categories</CardTitle>
                            </CardHeader>
                            <CardContent class="p-0">
                                <nav class="space-y-1">
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
                                                class="h-5 w-5"
                                                :class="selectedCategory === category.id ? 'text-primary' : ''"
                                            />
                                            <span>{{ category.label }}</span>
                                        </div>
                                        <Badge 
                                            v-if="getCategoryCount(category.id) > 0"
                                            variant="default"
                                            class="ml-auto"
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
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="text-2xl">
                                            {{ categories.find(c => c.id === selectedCategory)?.label || 'Notifications' }}
                                        </CardTitle>
                                        <CardDescription>Stay updated with your appointments and activities</CardDescription>
                                    </div>
                                    <Button
                                        v-if="hasUnreadNotifications"
                                        @click="markAllAsRead"
                                        variant="outline"
                                        size="sm"
                                    >
                                        <CheckCheck class="h-4 w-4 mr-2" />
                                        Mark all as read
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div v-if="loading" class="text-center py-8 text-muted-foreground">
                                    Loading notifications...
                                </div>

                                <div v-else-if="filteredNotifications.length === 0" class="text-center py-8 text-muted-foreground">
                                    <component
                                        :is="categories.find(c => c.id === selectedCategory)?.icon || Bell"
                                        class="h-12 w-12 mx-auto mb-3 opacity-50"
                                    />
                                    <p>No notifications in this category</p>
                                </div>

                                <div v-else class="space-y-2">
                                    <div
                                        v-for="notification in filteredNotifications"
                                        :key="notification.id"
                                        class="relative p-4 border rounded-lg transition-colors hover:bg-accent/50 cursor-pointer group"
                                        :class="{ 'bg-accent/20 border-primary/20': !notification.is_read }"
                                        @click="handleNotificationClick(notification)"
                                    >
                                        <div class="flex gap-4">
                                            <div class="flex-shrink-0 mt-1">
                                                <component
                                                    :is="getNotificationIcon(notification.type)"
                                                    class="h-6 w-6"
                                                    :class="notification.is_read ? 'text-muted-foreground' : 'text-primary'"
                                                />
                                            </div>

                                            <div class="flex-1 min-w-0 space-y-2">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-1">
                                                        <h3 
                                                            class="font-semibold text-base"
                                                            :class="notification.is_read ? 'text-muted-foreground' : 'text-foreground'"
                                                        >
                                                            {{ notification.title }}
                                                        </h3>
                                                        <p class="text-sm text-muted-foreground mt-1">
                                                            {{ notification.message }}
                                                        </p>
                                                    </div>

                                                    <div class="flex items-center gap-2 flex-shrink-0">
                                                        <Badge v-if="!notification.is_read" variant="default">New</Badge>
                                                        
                                                        <Button
                                                            v-if="!notification.is_read"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 opacity-0 group-hover:opacity-100"
                                                            @click.stop="markAsRead(notification)"
                                                        >
                                                            <Check class="h-4 w-4" />
                                                        </Button>
                                                        
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 opacity-0 group-hover:opacity-100"
                                                            @click.stop="deleteNotification(notification)"
                                                        >
                                                            <Trash2 class="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>

                                                <div class="text-xs text-muted-foreground">
                                                    {{ formatDate(notification.created_at) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="!notification.is_read"
                                            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-12 bg-primary rounded-r"
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
