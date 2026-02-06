<template>
    <div class="min-h-screen flex bg-white">
        <!-- Left Side - Form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white z-10">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div>
                    <div class="flex items-center gap-2">
                         <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <span class="text-xl font-semibold text-gray-900 tracking-tight">Pharmacy AI</span>
                    </div>
                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-gray-900">
                        Sign in to your account
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Welcome back! Please enter your details.
                    </p>
                </div>

                <div class="mt-10">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                            <div class="mt-2">
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                    :class="{ 'ring-red-300 focus:ring-red-500': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                                <div class="text-sm">
                                    <a href="#" class="font-semibold text-primary hover:text-primary-hover">Forgot password?</a>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
                                />
                                <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input
                                    id="remember-me"
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <label for="remember-me" class="ml-3 block text-sm leading-6 text-gray-700">Remember me</label>
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex w-full justify-center rounded-md bg-primary px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-colors disabled:opacity-70"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Signing in...
                                </span>
                                <span v-else>Sign in</span>
                            </button>
                        </div>
                    </form>

                     <!-- Test Credentials Helper -->
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Dev Credentials</p>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>admin@1.test</span>
                            <span class="font-mono">password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero Image -->
        <div class="hidden lg:block relative w-0 flex-1">
             <div class="absolute inset-0 bg-blue-50">
                 <!-- Abstract Pattern -->
                <svg class="absolute inset-0 h-full w-full text-white" preserveAspectRatio="none" viewBox="0 0 100 100" fill="none">
                     <path d="M0 100 C 20 0 50 0 100 100 Z" fill="currentColor" fill-opacity="0.5"/>
                </svg>
                 <!-- You could use a real image here, e.g. unsplash source -->
                <img
                    class="absolute inset-0 h-full w-full object-cover"
                    src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?q=80&w=2069&auto=format&fit=crop"
                    alt="Pharmacy background"
                />
                 <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-primary/40 mix-blend-multiply"></div>
                 <div class="absolute bottom-0 left-0 p-20 text-white">
                     <h2 class="text-4xl font-bold mb-4">Intelligent Pharmacy Management</h2>
                     <p class="text-lg text-blue-100 max-w-md">Automate your inventory, manage patient consultations, and streamline your workflow with AI.</p>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route('login', undefined, undefined, Ziggy), {
        onFinish: () => {
            form.password = "";
        },
    });
};
</script>
