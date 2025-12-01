import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

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

export function useNotificationPolling(intervalMs: number = 30000) {
    const unreadCount = ref(0);
    const latestNotifications = ref<Notification[]>([]);
    const isPolling = ref(false);
    let pollingInterval: NodeJS.Timeout | null = null;

    /**
     * Fetch latest notifications and unread count
     */
    const fetchNotifications = async () => {
        try {
            const response = await axios.get('/api/notifications/unread-count');
            
            if (response.data) {
                unreadCount.value = response.data.unread_count || 0;
                latestNotifications.value = response.data.latest || [];
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    };

    /**
     * Start polling for notifications
     */
    const startPolling = () => {
        if (isPolling.value) return;

        isPolling.value = true;
        
        // Fetch immediately on start
        fetchNotifications();

        // Then poll at regular intervals
        pollingInterval = setInterval(() => {
            fetchNotifications();
        }, intervalMs);
    };

    /**
     * Stop polling for notifications
     */
    const stopPolling = () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        isPolling.value = false;
    };

    /**
     * Manually refresh notifications
     */
    const refresh = async () => {
        await fetchNotifications();
    };

    // Auto-start polling when mounted
    onMounted(() => {
        startPolling();
    });

    // Cleanup on unmount
    onUnmounted(() => {
        stopPolling();
    });

    return {
        unreadCount,
        latestNotifications,
        isPolling,
        startPolling,
        stopPolling,
        refresh,
    };
}
