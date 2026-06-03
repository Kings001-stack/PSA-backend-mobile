<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
    >
        <div class="flex items-center justify-between mb-4">
            <div
                :class="[
                    'w-12 h-12 rounded-lg flex items-center justify-center',
                    iconBgClass,
                ]"
            >
                <slot name="icon">
                    <svg
                        :class="['w-6 h-6', iconColorClass]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                </slot>
            </div>
            <span
                v-if="badge"
                :class="[
                    'text-xs font-medium px-2 py-1 rounded-full',
                    badgeBgClass,
                    badgeTextClass,
                ]"
            >
                {{ badge }}
            </span>
        </div>
        <div class="mt-auto">
            <p class="text-sm font-medium text-gray-500">{{ label }}</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-3xl font-bold text-gray-900">{{ value }}</h3>
                <span
                    v-if="change"
                    :class="[
                        'text-sm font-medium',
                        changePositive ? 'text-green-600' : 'text-red-600',
                    ]"
                >
                    {{ changePositive ? "+" : "" }}{{ change }}%
                </span>
            </div>
            <p v-if="subtitle" class="text-xs text-gray-500 mt-1">
                {{ subtitle }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    badge: {
        type: String,
        default: "",
    },
    change: {
        type: [String, Number],
        default: null,
    },
    changePositive: {
        type: Boolean,
        default: true,
    },
    subtitle: {
        type: String,
        default: "",
    },
    variant: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "red", "yellow", "purple", "gray"].includes(
                value,
            ),
    },
});

const iconBgClass = computed(() => {
    const variants = {
        blue: "bg-blue-50",
        green: "bg-green-50",
        red: "bg-red-50",
        yellow: "bg-yellow-50",
        purple: "bg-purple-50",
        gray: "bg-gray-50",
    };
    return variants[props.variant];
});

const iconColorClass = computed(() => {
    const variants = {
        blue: "text-blue-600",
        green: "text-green-600",
        red: "text-red-600",
        yellow: "text-yellow-600",
        purple: "text-purple-600",
        gray: "text-gray-600",
    };
    return variants[props.variant];
});

const badgeBgClass = computed(() => {
    const variants = {
        blue: "bg-blue-50",
        green: "bg-green-50",
        red: "bg-red-50",
        yellow: "bg-yellow-50",
        purple: "bg-purple-50",
        gray: "bg-gray-50",
    };
    return variants[props.variant];
});

const badgeTextClass = computed(() => {
    const variants = {
        blue: "text-blue-600",
        green: "text-green-600",
        red: "text-red-600",
        yellow: "text-yellow-600",
        purple: "text-purple-600",
        gray: "text-gray-600",
    };
    return variants[props.variant];
});
</script>
