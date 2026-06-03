<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click="closeOnBackdrop && close()"
            >
                <!-- Enhanced Backdrop with blur and gradient -->
                <div
                    class="fixed inset-0 bg-gradient-to-br from-gray-900/80 via-blue-900/50 to-gray-900/80 backdrop-blur-sm transition-all"
                ></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-300 transform"
                        enter-from-class="opacity-0 translate-y-8 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-200 transform"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 translate-y-8 scale-95"
                    >
                        <div v-if="show" :class="modalClasses" @click.stop>
                            <!-- Decorative gradient header accent -->
                            <div
                                class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700"
                            ></div>

                            <!-- Header -->
                            <div
                                v-if="$slots.header || title"
                                class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white"
                            >
                                <slot name="header">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30"
                                        >
                                            <svg
                                                class="w-5 h-5 text-white"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>
                                        <h3
                                            class="text-xl font-bold text-gray-900 tracking-tight"
                                        >
                                            {{ title }}
                                        </h3>
                                    </div>
                                </slot>
                                <button
                                    v-if="closable"
                                    type="button"
                                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 transition-all duration-200 hover:rotate-90 transform"
                                    @click="close"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="bg-white">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div v-if="$slots.footer">
                                <slot name="footer" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "",
    },
    size: {
        type: String,
        default: "md",
        validator: (value) =>
            ["sm", "md", "lg", "xl", "2xl", "full"].includes(value),
    },
    closable: {
        type: Boolean,
        default: true,
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close", "update:show"]);

const modalClasses = computed(() => {
    const base =
        "relative bg-white rounded-2xl shadow-2xl transform transition-all w-full ring-1 ring-black/5 overflow-hidden";

    const sizes = {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-lg",
        xl: "max-w-xl",
        "2xl": "max-w-2xl",
        full: "max-w-full mx-4",
    };

    return `${base} ${sizes[props.size]}`;
});

const close = () => {
    emit("close");
    emit("update:show", false);
};

// Prevent body scroll when modal is open
watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
    },
);
</script>
