<script setup lang="ts">
import { computed, onMounted } from 'vue';

interface Props {
    data?: any;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Debug Data',
});

const analyzeValue = (value: any, path: string = '') => {
    const results: any[] = [];
    
    if (value === null || value === undefined) {
        results.push({ path, type: value === null ? 'null' : 'undefined', value: String(value) });
    } else if (typeof value === 'object') {
        if (Array.isArray(value)) {
            results.push({ path, type: 'array', length: value.length });
            value.forEach((item, index) => {
                results.push(...analyzeValue(item, `${path}[${index}]`));
            });
        } else if (value.constructor?.name && value.constructor.name !== 'Object') {
            // It's a component, function, or special object
            results.push({ 
                path, 
                type: value.constructor.name,
                isComponent: typeof value === 'function' || (value.__vccOpts !== undefined),
                stringValue: String(value),
                warning: '⚠️ This will render as [object Object] if used in {{ }}'
            });
        } else {
            results.push({ path, type: 'object', keys: Object.keys(value) });
            for (const [key, val] of Object.entries(value)) {
                results.push(...analyzeValue(val, path ? `${path}.${key}` : key));
            }
        }
    } else {
        results.push({ path, type: typeof value, value: String(value), safe: true });
    }
    
    return results;
};

const analysis = computed(() => analyzeValue(props.data, props.label));

const problemItems = computed(() => 
    analysis.value.filter(item => item.warning || (item.type === 'object' && item.path))
);

onMounted(() => {
    console.group(`🔍 Debug Component: ${props.label}`);
    console.log('Raw Data:', props.data);
    console.log('Type:', typeof props.data);
    console.log('Constructor:', props.data?.constructor?.name);
    console.table(analysis.value);
    
    if (problemItems.value.length > 0) {
        console.warn('⚠️ Items that will render as [object Object]:');
        console.table(problemItems.value);
    }
    
    console.groupEnd();
});
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50 max-w-md bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-400 dark:border-yellow-600 rounded-lg shadow-lg p-4 max-h-96 overflow-auto">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-sm text-yellow-800 dark:text-yellow-200">
                🐛 {{ label }}
            </h3>
            <button 
                @click="$emit('close')"
                class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400"
            >
                ✕
            </button>
        </div>
        
        <div class="space-y-2 text-xs">
            <div class="bg-white dark:bg-gray-800 rounded p-2">
                <div class="font-semibold text-gray-700 dark:text-gray-300">Type: {{ typeof data }}</div>
                <div v-if="data?.constructor" class="text-gray-600 dark:text-gray-400">
                    Constructor: {{ data.constructor.name }}
                </div>
            </div>
            
            <div v-if="problemItems.length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-600 rounded p-2">
                <div class="font-bold text-red-700 dark:text-red-300 mb-1">
                    ⚠️ {{ problemItems.length }} Problem(s) Found
                </div>
                <div v-for="item in problemItems" :key="item.path" class="text-red-600 dark:text-red-400 ml-2">
                    • {{ item.path || 'root' }}: {{ item.type }}
                    <span v-if="item.warning" class="block text-xs ml-4">{{ item.warning }}</span>
                </div>
            </div>
            
            <details class="bg-white dark:bg-gray-800 rounded p-2">
                <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300">
                    Full Analysis ({{ analysis.length }} items)
                </summary>
                <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                    <div 
                        v-for="(item, index) in analysis" 
                        :key="index"
                        class="text-xs p-1 rounded"
                        :class="{
                            'bg-red-50 dark:bg-red-900/20': item.warning,
                            'bg-green-50 dark:bg-green-900/20': item.safe,
                            'bg-gray-50 dark:bg-gray-700/20': !item.warning && !item.safe
                        }"
                    >
                        <span class="font-mono text-gray-600 dark:text-gray-400">{{ item.path || 'root' }}</span>:
                        <span class="font-semibold ml-1" :class="{
                            'text-red-600 dark:text-red-400': item.warning,
                            'text-green-600 dark:text-green-400': item.safe,
                            'text-gray-700 dark:text-gray-300': !item.warning && !item.safe
                        }">{{ item.type }}</span>
                        <span v-if="item.value" class="ml-2 text-gray-500 dark:text-gray-400">
                            = {{ item.value }}
                        </span>
                        <span v-if="item.isComponent" class="ml-2 text-orange-600 dark:text-orange-400">
                            [Component/Function]
                        </span>
                    </div>
                </div>
            </details>
            
            <details class="bg-white dark:bg-gray-800 rounded p-2">
                <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300">
                    Raw JSON
                </summary>
                <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-700 p-2 rounded overflow-x-auto">{{ data }}</pre>
            </details>
        </div>
    </div>
</template>
