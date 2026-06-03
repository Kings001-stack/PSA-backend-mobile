<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-semibold text-gray-700 mb-2"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            :step="step"
            :class="inputClasses"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
        />
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-2 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substr(2, 9)}`,
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
    type: {
        type: String,
        default: "text",
    },
    label: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
    hint: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    step: {
        type: String,
        default: undefined,
    },
});

defineEmits(["update:modelValue", "blur", "focus"]);

const inputClasses = computed(() => {
    const base =
        "block w-full rounded-xl border-2 shadow-sm transition-all duration-200 py-2.5 px-3.5 text-sm";

    const states = props.error
        ? "border-red-300 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-500/10"
        : "border-gray-200 bg-white text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-blue-300";

    const disabled = props.disabled
        ? "bg-gray-100 text-gray-500 cursor-not-allowed opacity-60"
        : "";

    return `${base} ${states} ${disabled}`;
});
</script>
