import { ref } from 'vue';

const confirmState = ref({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
    onConfirm: null,
    onCancel: null,
});

export function useConfirm() {
    const confirm = ({
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        variant = 'danger',
    }) => {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title,
                message,
                confirmText,
                cancelText,
                variant,
                onConfirm: () => {
                    confirmState.value.isOpen = false;
                    resolve(true);
                },
                onCancel: () => {
                    confirmState.value.isOpen = false;
                    resolve(false);
                },
            };
        });
    };

    const close = () => {
        confirmState.value.isOpen = false;
    };

    return {
        confirmState,
        confirm,
        close,
    };
}
