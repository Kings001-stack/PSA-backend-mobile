<template>
    <div :class="cardClasses">
        <div v-if="$slots.header || title" :class="headerClasses">
            <slot name="header">
                <h3 v-if="title" class="text-lg font-semibold text-gray-900">
                    {{ title }}
                </h3>
            </slot>
        </div>
        <div :class="bodyClasses">
            <slot />
        </div>
        <div v-if="$slots.footer" :class="footerClasses">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
    padding: {
        type: String,
        default: "normal",
        validator: (value) => ["none", "sm", "normal", "lg"].includes(value),
    },
    shadow: {
        type: Boolean,
        default: true,
    },
    border: {
        type: Boolean,
        default: true,
    },
    hover: {
        type: Boolean,
        default: false,
    },
});

const cardClasses = computed(() => {
    const base = "bg-white rounded-xl overflow-hidden";
    const shadowClass = props.shadow ? "shadow-sm" : "";
    const borderClass = props.border ? "border border-gray-200" : "";
    const hoverClass = props.hover
        ? "hover:shadow-md transition-shadow duration-200"
        : "";

    return `${base} ${shadowClass} ${borderClass} ${hoverClass}`;
});

const headerClasses = computed(() => {
    const base = "border-b border-gray-200 bg-white";
    const paddingMap = {
        none: "",
        sm: "px-4 py-3",
        normal: "px-6 py-4",
        lg: "px-8 py-5",
    };
    return `${base} ${paddingMap[props.padding]}`;
});

const bodyClasses = computed(() => {
    const paddingMap = {
        none: "",
        sm: "p-4",
        normal: "p-6",
        lg: "p-8",
    };
    return paddingMap[props.padding];
});

const footerClasses = computed(() => {
    const base = "border-t border-gray-200 bg-gray-50";
    const paddingMap = {
        none: "",
        sm: "px-4 py-3",
        normal: "px-6 py-4",
        lg: "px-8 py-5",
    };
    return `${base} ${paddingMap[props.padding]}`;
});
</script>
