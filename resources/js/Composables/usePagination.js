import { computed } from "vue";

export function usePagination(data) {
    const hasPages = computed(() => {
        return data.value && data.value.last_page > 1;
    });

    const hasPrevPage = computed(() => {
        return data.value && data.value.current_page > 1;
    });

    const hasNextPage = computed(() => {
        return data.value && data.value.current_page < data.value.last_page;
    });

    const pageNumbers = computed(() => {
        if (!data.value) return [];

        const current = data.value.current_page;
        const last = data.value.last_page;
        const delta = 2;
        const range = [];
        const rangeWithDots = [];

        for (
            let i = Math.max(2, current - delta);
            i <= Math.min(last - 1, current + delta);
            i++
        ) {
            range.push(i);
        }

        if (current - delta > 2) {
            rangeWithDots.push(1, "...");
        } else {
            rangeWithDots.push(1);
        }

        rangeWithDots.push(...range);

        if (current + delta < last - 1) {
            rangeWithDots.push("...", last);
        } else if (last > 1) {
            rangeWithDots.push(last);
        }

        return rangeWithDots;
    });

    return {
        hasPages,
        hasPrevPage,
        hasNextPage,
        pageNumbers,
    };
}
