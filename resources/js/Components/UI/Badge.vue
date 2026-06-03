<template>
    <span :class="badgeClasses">
        <slot />
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            [
                "default",
                "primary",
                "success",
                "warning",
                "danger",
                "info",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    dot: {
        type: Boolean,
        default: false,
    },
});

const badgeClasses = computed(() => {
    const base = "inline-flex items-center font-medium rounded-full";

    const variants = {
        default: "bg-gray-100 text-gray-800",
        primary: "bg-blue-100 text-blue-800",
        success: "bg-green-100 text-green-800",
        warning: "bg-yellow-100 text-yellow-800",
        danger: "bg-red-100 text-red-800",
        info: "bg-cyan-100 text-cyan-800",
    };

    const sizes = {
        sm: "px-2 py-0.5 text-xs",
        md: "px-2.5 py-0.5 text-xs",
        lg: "px-3 py-1 text-sm",
    };

    return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>
