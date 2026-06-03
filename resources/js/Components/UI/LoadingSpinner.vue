<template>
    <div :class="containerClasses">
        <svg :class="spinnerClasses" fill="none" viewBox="0 0 24 24">
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
        <p v-if="text" :class="textClasses">{{ text }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    text: {
        type: String,
        default: "",
    },
    center: {
        type: Boolean,
        default: false,
    },
});

const containerClasses = computed(() => {
    const base = "flex items-center gap-3";
    const center = props.center ? "justify-center" : "";
    return `${base} ${center}`;
});

const spinnerClasses = computed(() => {
    const base = "animate-spin text-blue-600";
    const sizes = {
        sm: "h-4 w-4",
        md: "h-6 w-6",
        lg: "h-8 w-8",
        xl: "h-12 w-12",
    };
    return `${base} ${sizes[props.size]}`;
});

const textClasses = computed(() => {
    const sizes = {
        sm: "text-xs",
        md: "text-sm",
        lg: "text-base",
        xl: "text-lg",
    };
    return `text-gray-600 font-medium ${sizes[props.size]}`;
});
</script>
