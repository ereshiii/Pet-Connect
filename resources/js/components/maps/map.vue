<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default markers in Leaflet with Vite
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

// Configure default marker icons
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
});

// Props interface
interface MapMarker {
    id: string | number;
    lat: number;
    lng: number;
    title: string;
    description?: string;
    type?: 'clinic' | 'user' | 'emergency' | 'default';
    icon?: string;
}

interface MapProps {
    height?: string;
    width?: string;
    center?: [number, number];
    zoom?: number;
    markers?: MapMarker[];
    showUserLocation?: boolean;
    enableClustering?: boolean;
    enableSearch?: boolean;
    class?: string;
}

// Props with defaults
const props = withDefaults(defineProps<MapProps>(), {
    height: '400px',
    width: '100%',
    center: () => [40.7128, -74.0060], // Default to New York City
    zoom: 13,
    markers: () => [],
    showUserLocation: true,
    enableClustering: false,
    enableSearch: false,
    class: ''
});

// Emits
const emit = defineEmits<{
    markerClick: [marker: MapMarker];
    mapClick: [event: L.LeafletMouseEvent];
    mapReady: [map: L.Map];
    locationFound: [location: GeolocationPosition];
    locationError: [error: GeolocationPositionError];
}>();

// Refs
const mapContainer = ref<HTMLDivElement>();
let map: L.Map | null = null;
let userLocationMarker: L.Marker | null = null;
const mapMarkers = ref<L.Marker[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Custom marker icons
const createCustomIcon = (type: string = 'default') => {
    const icons = {
        clinic: {
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2563eb" width="32" height="32">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    <path d="M10 8h1v1h2V8h1v4h-1v-1h-2v1h-1V8z" fill="white"/>
                </svg>
            `),
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        },
        emergency: {
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#dc2626" width="32" height="32">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    <path d="M10 8h1v1h2V8h1v4h-1v-1h-2v1h-1V8z" fill="white"/>
                </svg>
            `),
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        },
        user: {
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#16a34a" width="32" height="32">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    <circle cx="12" cy="9" r="1.5" fill="white"/>
                </svg>
            `),
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        },
        default: {
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#6b7280" width="32" height="32">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            `),
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        }
    };
    
    return L.icon(icons[type as keyof typeof icons] || icons.default);
};

// Initialize map
const initMap = () => {
    if (!mapContainer.value) return;
    
    try {
        // Create map
        map = L.map(mapContainer.value, {
            center: props.center,
            zoom: props.zoom,
            zoomControl: true,
        });

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // Add markers
        addMarkers();

        // Get user location if enabled
        if (props.showUserLocation) {
            getUserLocation();
        }

        // Map event listeners
        map.on('click', (e: L.LeafletMouseEvent) => {
            emit('mapClick', e);
        });

        // Emit map ready event
        emit('mapReady', map);
        
        isLoading.value = false;
    } catch (err) {
        console.error('Error initializing map:', err);
        error.value = 'Failed to initialize map';
        isLoading.value = false;
    }
};

// Add markers to map
const addMarkers = () => {
    if (!map) return;
    
    // Clear existing markers
    clearMarkers();
    
    if (props.markers.length === 0) return;
    
    props.markers.forEach((markerData) => {
        try {
            const marker = L.marker([markerData.lat, markerData.lng], {
                icon: createCustomIcon(markerData.type)
            }).addTo(map!);
            
            // Create popup content
            const popupContent = `
                <div class="p-2">
                    <h3 class="font-semibold text-sm mb-1">${markerData.title}</h3>
                    ${markerData.description ? `<p class="text-xs text-gray-600">${markerData.description}</p>` : ''}
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            // Marker click event
            marker.on('click', () => {
                emit('markerClick', markerData);
            });
            
            mapMarkers.value.push(marker);
        } catch (error) {
            console.warn('Error adding marker:', error, markerData);
        }
    });
    
    // Only fit bounds on initial load or when specifically requested
    // Avoid automatic bounds fitting during filter changes to prevent jarring movements
    const shouldFitBounds = isLoading.value || mapMarkers.value.length !== props.markers.length;
    
    if (mapMarkers.value.length > 0 && shouldFitBounds) {
        setTimeout(() => {
            try {
                const group = new L.FeatureGroup(mapMarkers.value);
                const bounds = group.getBounds();
                
                // More conservative bounds fitting
                if (bounds.isValid()) {
                    const distance = bounds.getNorthEast().distanceTo(bounds.getSouthWest());
                    
                    if (distance > 50) { // Only fit bounds if markers are spread out (more than 50 meters apart)
                        map!.fitBounds(bounds.pad(0.1), { 
                            maxZoom: 16,
                            animate: true,
                            duration: 0.5
                        });
                    } else if (mapMarkers.value.length === 1) {
                        // For single marker, just center on it without changing zoom too much
                        const currentZoom = map!.getZoom();
                        const singleMarker = props.markers[0];
                        map!.setView([singleMarker.lat, singleMarker.lng], Math.max(currentZoom, 14), {
                            animate: true,
                            duration: 0.5
                        });
                    }
                }
            } catch (error) {
                console.warn('Error fitting bounds:', error);
            }
        }, 100); // Small delay to ensure DOM is ready
    }
};

// Clear all markers
const clearMarkers = () => {
    mapMarkers.value.forEach(marker => {
        if (map) {
            map.removeLayer(marker);
        }
    });
    mapMarkers.value = [];
};

// Get user's current location
const getUserLocation = () => {
    if (!navigator.geolocation) {
        console.warn('Geolocation is not supported by this browser');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        (position: GeolocationPosition) => {
            const { latitude, longitude } = position.coords;
            
            if (map) {
                // Add user location marker
                if (userLocationMarker) {
                    map.removeLayer(userLocationMarker);
                }
                
                userLocationMarker = L.marker([latitude, longitude], {
                    icon: createCustomIcon('user')
                }).addTo(map);
                
                userLocationMarker.bindPopup('<div class="p-2"><strong>Your Location</strong></div>');
                
                // Center map on user location if no markers
                if (props.markers.length === 0) {
                    map.setView([latitude, longitude], props.zoom);
                }
            }
            
            emit('locationFound', position);
        },
        (error: GeolocationPositionError) => {
            console.warn('Error getting user location:', error.message);
            emit('locationError', error);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
};

// Watch for prop changes
let markerUpdateTimeout: NodeJS.Timeout | null = null;

watch(() => props.markers, (newMarkers, oldMarkers) => {
    if (!map) return;
    
    // Clear existing timeout
    if (markerUpdateTimeout) {
        clearTimeout(markerUpdateTimeout);
    }
    
    // Debounce marker updates to prevent rapid re-rendering
    markerUpdateTimeout = setTimeout(() => {
        if (newMarkers) {
            // Check if markers actually changed by comparing length and key properties
            const oldIds = oldMarkers?.map(m => `${m.id}-${m.lat}-${m.lng}`) || [];
            const newIds = newMarkers.map(m => `${m.id}-${m.lat}-${m.lng}`);
            
            const markersChanged = oldIds.length !== newIds.length || 
                                 !oldIds.every((id, index) => id === newIds[index]);
            
            if (markersChanged) {
                console.log('Updating markers:', { oldCount: oldIds.length, newCount: newIds.length });
                addMarkers();
            }
        }
    }, 150); // 150ms debounce
}, { deep: true });

watch(() => props.center, (newCenter) => {
    if (map && newCenter) {
        map.setView(newCenter, props.zoom);
    }
});

watch(() => props.zoom, (newZoom) => {
    if (map && newZoom) {
        map.setZoom(newZoom);
    }
});

// Lifecycle hooks
onMounted(() => {
    // Small delay to ensure DOM is ready
    setTimeout(initMap, 100);
});

onUnmounted(() => {
    // Clear any pending timeouts
    if (markerUpdateTimeout) {
        clearTimeout(markerUpdateTimeout);
    }
    
    if (map) {
        map.remove();
        map = null;
    }
});

// Expose methods for parent components
defineExpose({
    getMap: () => map,
    addMarker: (markerData: MapMarker) => {
        if (map) {
            const marker = L.marker([markerData.lat, markerData.lng], {
                icon: createCustomIcon(markerData.type)
            }).addTo(map);
            
            const popupContent = `
                <div class="p-2">
                    <h3 class="font-semibold text-sm mb-1">${markerData.title}</h3>
                    ${markerData.description ? `<p class="text-xs text-gray-600">${markerData.description}</p>` : ''}
                </div>
            `;
            
            marker.bindPopup(popupContent);
            marker.on('click', () => emit('markerClick', markerData));
            mapMarkers.value.push(marker);
        }
    },
    removeMarker: (markerId: string | number) => {
        const markerIndex = mapMarkers.value.findIndex(marker => 
            (marker as any).markerId === markerId
        );
        if (markerIndex !== -1 && map) {
            map.removeLayer(mapMarkers.value[markerIndex]);
            mapMarkers.value.splice(markerIndex, 1);
        }
    },
    clearAllMarkers: clearMarkers,
    centerOnLocation: (lat: number, lng: number, zoom?: number) => {
        if (map) {
            map.setView([lat, lng], zoom || props.zoom);
        }
    },
    fitBounds: (options?: { padding?: number; maxZoom?: number; animate?: boolean }) => {
        if (map && mapMarkers.value.length > 0) {
            const group = new L.FeatureGroup(mapMarkers.value);
            const bounds = group.getBounds();
            
            if (bounds.isValid()) {
                const opts = {
                    padding: [options?.padding || 20, options?.padding || 20],
                    maxZoom: options?.maxZoom || 16,
                    animate: options?.animate !== false,
                    duration: 0.5
                };
                
                map.fitBounds(bounds, opts);
            }
        }
    }
});
</script>

<template>
    <div class="relative" :class="props.class">
        <!-- Loading State -->
        <div 
            v-if="isLoading" 
            class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg z-10"
        >
            <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading map...</span>
            </div>
        </div>
        
        <!-- Error State -->
        <div 
            v-if="error" 
            class="absolute inset-0 flex items-center justify-center bg-red-50 dark:bg-red-900/20 rounded-lg z-10"
        >
            <div class="text-center">
                <div class="text-red-600 dark:text-red-400 mb-2">
                    <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <p class="text-red-600 dark:text-red-400 text-sm">{{ error }}</p>
            </div>
        </div>
        
        <!-- Map Container -->
        <div 
            ref="mapContainer" 
            :style="{ height: props.height, width: props.width }"
            class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
        ></div>
        
        <!-- Map Controls Slot -->
        <div class="absolute top-2 right-2 z-[1000]">
            <slot name="controls"></slot>
        </div>
        
        <!-- Map Legend Slot -->
        <div class="absolute bottom-2 left-2 z-[1000]">
            <slot name="legend"></slot>
        </div>
    </div>
</template>

<style scoped>
/* Ensure Leaflet controls are properly styled */
:deep(.leaflet-control-container) {
    font-family: inherit;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

:deep(.leaflet-popup-tip) {
    background: white;
}

/* Dark mode styles */
.dark :deep(.leaflet-popup-content-wrapper) {
    background: #374151;
    color: #f9fafb;
}

.dark :deep(.leaflet-popup-tip) {
    background: #374151;
}
</style>
