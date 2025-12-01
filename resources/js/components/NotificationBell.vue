<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Bell, Check, CheckCheck, Trash2, Calendar, Clock } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useNotificationPolling } from '@/composables/useNotificationPolling';

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

// Use the polling composable (checks every 30 seconds)
const { unreadCount, latestNotifications, refresh } = useNotificationPolling();

const notifications = ref<Notification[]>([]);
const isOpen = ref(false);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications/recent');
        notifications.value = response.data.notifications;
        // Don't override unreadCount from polling, just update full list
        if (response.data.unread_count !== undefined && !latestNotifications.value.length) {
            unreadCount.value = response.data.unread_count;
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

const markAsRead = async (notification: Notification) => {
    if (notification.is_read) return;

    try {
        await axios.post(`/notifications/${notification.id}/mark-as-read`);
        notification.is_read = true;
        notification.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        await refresh(); // Refresh polling data
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
        unreadCount.value = 0;
        await refresh(); // Refresh polling data
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

const deleteNotification = async (notification: Notification) => {
    try {
        await axios.delete(`/notifications/${notification.id}`);
        notifications.value = notifications.value.filter(n => n.id !== notification.id);
        if (!notification.is_read) {
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
        await refresh(); // Refresh polling data
    } catch (error) {
        console.error('Failed to delete notification:', error);
    }
};

const handleNotificationClick = (notification: Notification) => {
    markAsRead(notification);
    
    // Navigate based on notification type
    if (notification.data?.appointment_id) {
        const isClinic = notification.type.includes('clinic');
        const route = isClinic 
            ? `/clinic/appointments/${notification.data.appointment_id}`
            : `/appointments/${notification.data.appointment_id}`;
        router.visit(route);
    }
    
    isOpen.value = false;
};

const getNotificationIcon = (type: string) => {
    if (type.includes('appointment')) return Calendar;
    if (type.includes('reminder')) return Clock;
    return Bell;
};

const formatTimeAgo = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);
    
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    const weeks = Math.floor(days / 7);
    if (weeks < 4) return `${weeks}w ago`;
    return date.toLocaleDateString();
};

onMounted(() => {
    fetchNotifications();
    // Polling composable handles automatic 30-second updates
});
</script>

<template>
    <DropdownMenu v-model:open="isOpen">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <Bell class="h-5 w-5" />
                <Badge
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 h-5 min-w-5 px-1 flex items-center justify-center text-xs"
                    variant="destructive"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        
        <DropdownMenuContent align="end" class="w-[calc(100vw-2rem)] sm:w-96 max-w-96">
            <DropdownMenuLabel class="flex items-center justify-between px-3 sm:px-4">
                <span class="text-sm sm:text-base">Notifications</span>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    @click="markAllAsRead"
                    class="h-7 text-xs px-2 sm:px-3"
                >
                    <CheckCheck class="h-3 w-3 sm:mr-1" />
                    <span class="hidden sm:inline">Mark all read</span>
                </Button>
            </DropdownMenuLabel>
            
            <DropdownMenuSeparator />
            
            <div class="max-h-[60vh] sm:max-h-[400px] overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-4 text-center text-muted-foreground text-sm">
                    No notifications yet
                </div>
                
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="relative px-3 sm:px-4 py-2.5 sm:py-3 hover:bg-accent/50 cursor-pointer transition-colors group"
                    :class="{ 'bg-accent/20': !notification.is_read }"
                    @click="handleNotificationClick(notification)"
                >
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <p
                                class="text-xs sm:text-sm font-medium leading-tight pr-1 flex-1"
                                :class="notification.is_read ? 'text-muted-foreground' : 'text-foreground'"
                            >
                                {{ notification.title }}
                            </p>
                            
                            <div class="flex items-center gap-0.5 sm:gap-1 flex-shrink-0">
                                <Button
                                    v-if="!notification.is_read"
                                    variant="ghost"
                                    size="icon"
                                    class="h-6 w-6 sm:h-7 sm:w-7 opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                    @click.stop="markAsRead(notification)"
                                >
                                    <Check class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                </Button>
                                
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-6 w-6 sm:h-7 sm:w-7 opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                    @click.stop="deleteNotification(notification)"
                                >
                                    <Trash2 class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                </Button>
                            </div>
                        </div>
                        
                        <p class="text-xs sm:text-sm text-muted-foreground line-clamp-2 leading-relaxed">
                            {{ notification.message }}
                        </p>
                        
                        <p class="text-[10px] sm:text-xs text-muted-foreground">
                            {{ formatTimeAgo(notification.created_at) }}
                        </p>
                    </div>
                    
                    <div
                        v-if="!notification.is_read"
                        class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-10 sm:h-12 bg-primary rounded-r"
                    />
                </div>
            </div>
            
            <DropdownMenuSeparator v-if="notifications.length > 0" />
            
            <DropdownMenuItem
                v-if="notifications.length > 0"
                class="justify-center text-xs sm:text-sm text-primary cursor-pointer py-3"
                @click="router.visit('/notifications')"
            >
                View all notifications
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
