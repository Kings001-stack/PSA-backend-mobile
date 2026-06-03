import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user || null);
    const tenant = computed(() => page.props.auth?.tenant || null);

    const isAuthenticated = computed(() => !!user.value);
    const isSuperAdmin = computed(() => user.value?.is_super_admin || false);
    const isPharmacist = computed(() => user.value?.is_pharmacist || false);
    const isAdmin = computed(() => user.value?.is_admin || false);

    const hasRole = (role) => {
        return user.value?.role === role;
    };

    const can = (permission) => {
        // Implement permission checking logic here
        // This is a placeholder for future permission system
        return true;
    };

    return {
        user,
        tenant,
        isAuthenticated,
        isSuperAdmin,
        isPharmacist,
        isAdmin,
        hasRole,
        can,
    };
}
