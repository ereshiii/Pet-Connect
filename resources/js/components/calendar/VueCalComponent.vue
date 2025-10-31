<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

// Event interface for type safety
interface CalendarEvent {
    id: string | number;
    title: string;
    start: string | Date;
    end: string | Date;
    class?: string;
    content?: string;
    background?: boolean;
    split?: number;
    allDay?: boolean;
    deletable?: boolean;
    resizable?: boolean;
    editable?: boolean;
    [key: string]: any;
}

interface Props {
    events?: CalendarEvent[];
    view?: string;
    selectedDate?: string | Date;
    locale?: string;
    hideViewSelector?: boolean;
    hideBody?: boolean;
    hideTitleBar?: boolean;
    showAllDayEvents?: boolean;
    splitDays?: number[];
    minSplit?: number;
    maxSplit?: number;
    clickToNavigate?: boolean;
    dblclickToNavigate?: boolean;
    disableViews?: string[];
    startWeekOnSunday?: boolean;
    hideWeekends?: boolean;
    activeView?: string;
    small?: boolean;
    xsmall?: boolean;
    height?: string | number;
    editable?: boolean;
    resizable?: boolean;
    deletable?: boolean;
    showTimeInCells?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    events: () => [],
    view: 'month',
    selectedDate: () => new Date(),
    locale: 'en',
    hideViewSelector: false,
    hideBody: false,
    hideTitleBar: false,
    showAllDayEvents: true,
    splitDays: () => [],
    clickToNavigate: true,
    dblclickToNavigate: true,
    disableViews: () => [],
    startWeekOnSunday: false,
    hideWeekends: false,
    activeView: 'month',
    small: false,
    xsmall: false,
    height: 'auto',
    editable: false,
    resizable: false,
    deletable: false,
    showTimeInCells: false,
});

// Events
const emit = defineEmits<{
    eventClick: [event: CalendarEvent, e: Event];
    eventDblclick: [event: CalendarEvent, e: Event];
    eventDrop: [event: CalendarEvent, e: Event];
    eventResize: [event: CalendarEvent, e: Event];
    eventCreate: [event: CalendarEvent, e: Event];
    eventDelete: [event: CalendarEvent, e: Event];
    cellClick: [date: Date, e: Event];
    cellDblclick: [date: Date, e: Event];
    cellKeypress: [event: any];
    viewChange: [view: any];
    ready: [];
}>();

// Reactive references
const vuecal = ref();

// Computed properties for vue-cal configuration
const calendarConfig = computed(() => ({
    locale: props.locale,
    hideViewSelector: props.hideViewSelector,
    hideBody: props.hideBody,
    hideTitleBar: props.hideTitleBar,
    showAllDayEvents: props.showAllDayEvents,
    splitDays: props.splitDays,
    clickToNavigate: props.clickToNavigate,
    dblclickToNavigate: props.dblclickToNavigate,
    disableViews: props.disableViews,
    startWeekOnSunday: props.startWeekOnSunday,
    hideWeekends: props.hideWeekends,
    activeView: props.activeView,
    small: props.small,
    xsmall: props.xsmall,
    height: props.height,
    editable: props.editable,
    resizable: props.resizable,
    deletable: props.deletable,
    showTimeInCells: props.showTimeInCells,
}));

// Event handlers
const onEventClick = (event: CalendarEvent, e: Event) => {
    emit('eventClick', event, e);
};

const onEventDblclick = (event: CalendarEvent, e: Event) => {
    emit('eventDblclick', event, e);
};

const onEventDrop = (event: CalendarEvent, e: Event) => {
    emit('eventDrop', event, e);
};

const onEventResize = (event: CalendarEvent, e: Event) => {
    emit('eventResize', event, e);
};

const onEventCreate = (event: CalendarEvent, e: Event) => {
    emit('eventCreate', event, e);
};

const onEventDelete = (event: CalendarEvent, e: Event) => {
    emit('eventDelete', event, e);
};

const onCellClick = (date: Date, e: Event) => {
    emit('cellClick', date, e);
};

const onCellDblclick = (date: Date, e: Event) => {
    emit('cellDblclick', date, e);
};

const onCellKeypress = (event: any) => {
    emit('cellKeypress', event);
};

const onViewChange = (view: any) => {
    emit('viewChange', view);
};

const onReady = () => {
    emit('ready');
};

// Public methods
const switchView = (view: string) => {
    if (vuecal.value) {
        vuecal.value.switchView(view);
    }
};

const previous = () => {
    if (vuecal.value) {
        vuecal.value.previous();
    }
};

const next = () => {
    if (vuecal.value) {
        vuecal.value.next();
    }
};

const goToToday = () => {
    if (vuecal.value) {
        vuecal.value.goToToday();
    }
};

const addEvent = (event: CalendarEvent) => {
    if (vuecal.value) {
        vuecal.value.addEvent(event);
    }
};

const updateEvent = (event: CalendarEvent) => {
    if (vuecal.value) {
        vuecal.value.updateEvent(event);
    }
};

const deleteEvent = (eventId: string | number) => {
    if (vuecal.value) {
        vuecal.value.deleteEvent(eventId);
    }
};

// Expose methods for parent components
defineExpose({
    switchView,
    previous,
    next,
    goToToday,
    addEvent,
    updateEvent,
    deleteEvent,
});
</script>

<template>
    <div class="vue-cal-wrapper">
        <VueCal
            ref="vuecal"
            :events="events"
            :selected-date="selectedDate"
            v-bind="calendarConfig"
            @event-click="onEventClick"
            @event-dblclick="onEventDblclick"
            @event-drop="onEventDrop"
            @event-resize="onEventResize"
            @event-create="onEventCreate"
            @event-delete="onEventDelete"
            @cell-click="onCellClick"
            @cell-dblclick="onCellDblclick"
            @cell-keypress="onCellKeypress"
            @view-change="onViewChange"
            @ready="onReady"
        >
            <!-- Custom event content slot -->
            <template #event-content="{ event }">
                <div class="event-content">
                    <div class="event-title">{{ event.title }}</div>
                    <div v-if="event.content" class="event-description text-xs opacity-80">
                        {{ event.content }}
                    </div>
                </div>
            </template>

            <!-- Custom no-event slot -->
            <template #no-event>
                <div class="text-gray-500 text-center py-8">
                    No events for this period
                </div>
            </template>
        </VueCal>
    </div>
</template>

<style>
/* Vue-cal theme customization */
.vue-cal-wrapper {
    --vuecal-primary-color: #3b82f6;
    --vuecal-accent-color: #1d4ed8;
    --vuecal-text-color: #374151;
    --vuecal-bg-color: #ffffff;
    --vuecal-border-color: #e5e7eb;
    --vuecal-header-bg: #f9fafb;
}

.vuecal__menu,
.vuecal__header {
    background-color: var(--vuecal-header-bg);
    border-bottom: 1px solid var(--vuecal-border-color);
}

.vuecal__title-bar {
    background-color: var(--vuecal-header-bg);
    color: var(--vuecal-text-color);
}

.vuecal__cell {
    border-color: var(--vuecal-border-color);
}

.vuecal__cell--today {
    background-color: rgba(59, 130, 246, 0.1);
}

.vuecal__event {
    background-color: var(--vuecal-primary-color);
    border: 1px solid var(--vuecal-accent-color);
    border-radius: 4px;
    color: white;
}

.vuecal__event--appointment {
    background-color: #3b82f6;
}

.vuecal__event--pending {
    background-color: #f59e0b;
}

.vuecal__event--confirmed {
    background-color: #10b981;
}

.vuecal__event--cancelled {
    background-color: #ef4444;
}

.vuecal__event--completed {
    background-color: #6b7280;
}

.vuecal__event--holiday {
    background-color: #dc2626;
}

.vuecal__event--reminder {
    background-color: #8b5cf6;
}

.event-content {
    padding: 2px;
}

.event-title {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.2;
}

.event-description {
    margin-top: 2px;
    line-height: 1.1;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .vue-cal-wrapper {
        --vuecal-text-color: #f3f4f6;
        --vuecal-bg-color: #1f2937;
        --vuecal-border-color: #374151;
        --vuecal-header-bg: #111827;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .vuecal__cell {
        min-height: 60px;
    }
    
    .vuecal__event {
        font-size: 0.75rem;
        padding: 1px 4px;
    }
}
</style>