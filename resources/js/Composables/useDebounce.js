import { ref, watch } from 'vue';

export function useDebounce(value, delay = 500) {
    const debouncedValue = ref(value.value);
    let timeout;

    watch(value, (newValue) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            debouncedValue.value = newValue;
        }, delay);
    });

    return debouncedValue;
}

export function debounce(fn, delay = 500) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}
