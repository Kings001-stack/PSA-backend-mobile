<template>
    <div class="min-h-screen flex bg-slate-50 relative overflow-hidden font-sans">
        <!-- Background Ambient Glows -->
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-blue-400/10 blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-teal-400/10 blur-[100px] pointer-events-none"></div>

        <!-- Left Side - Form Card -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 z-10 w-full lg:w-[480px]">
            <div class="mx-auto w-full max-w-sm lg:w-96 bg-white/80 backdrop-blur-xl border border-slate-100 rounded-3xl p-8 shadow-2xl shadow-slate-200/50">
                <!-- Header / Logo -->
                <div class="text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-tr from-primary to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-primary to-blue-700 bg-clip-text text-transparent tracking-tight">Pharmacy AI</span>
                    </div>
                    <h2 class="mt-8 text-3xl font-extrabold tracking-tight text-slate-900">
                        Welcome Back
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Please enter your details to sign in to your dashboard.
                    </p>
                </div>

                <div class="mt-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    placeholder="Enter your email address"
                                    class="block w-full rounded-xl border border-slate-200 py-3 pl-10 pr-3 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 sm:text-sm"
                                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/10': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                                <a href="#" class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors">Forgot password?</a>
                            </div>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full rounded-xl border border-slate-200 py-3 pl-10 pr-10 text-slate-900 placeholder:text-slate-400 focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 sm:text-sm"
                                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/10': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordVisibility"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                >
                                    <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input
                                id="remember-me"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4.5 w-4.5 rounded border-slate-300 text-primary focus:ring-primary transition-all cursor-pointer"
                            />
                            <label for="remember-me" class="ml-2.5 block text-sm font-medium text-slate-600 cursor-pointer select-none">Remember my credentials</label>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex w-full justify-center items-center rounded-xl bg-gradient-to-r from-primary to-blue-600 hover:from-primary-hover hover:to-blue-700 px-4 py-3 text-sm font-bold leading-6 text-white shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-70 disabled:pointer-events-none cursor-pointer"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Authenticating...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Sign In
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Developer Quick Access Panel -->
                    <div 
                        @click="fillCredentials" 
                        class="mt-8 p-4 bg-slate-50 hover:bg-slate-100/80 border border-slate-100 rounded-2xl transition-all duration-300 cursor-pointer group hover:scale-[1.01] hover:shadow-md hover:shadow-slate-100 relative overflow-hidden"
                    >
                        <div class="absolute right-[-10px] top-[-10px] opacity-5 group-hover:opacity-10 group-hover:scale-110 transition-all duration-300">
                            <svg class="w-24 h-24 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dev Credentials Quick‑Access</p>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex flex-col gap-0.5">
                                <span class="font-semibold text-slate-700 group-hover:text-primary transition-colors">admin@1.test</span>
                                <span class="text-xs text-slate-400 font-mono">password: password</span>
                            </div>
                            <span class="text-xs font-bold text-slate-400 bg-slate-200/50 group-hover:bg-primary/10 group-hover:text-primary px-2.5 py-1 rounded-lg transition-colors">
                                Auto-Fill
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Stunning Hero Image and Blur Glass panel -->
        <div class="hidden lg:block relative w-0 flex-1 h-screen">
            <div class="absolute inset-0 bg-slate-900">
                <img
                    class="absolute inset-0 h-full w-full object-cover opacity-90 transition-transform duration-[10000ms] hover:scale-105"
                    src="/images/pharmacy_login_hero.png"
                    alt="Pharmacy Hero Background"
                />
                <!-- Gradient Overlay for visual consistency -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900/60 via-slate-800/30 to-primary/30 mix-blend-multiply"></div>
                
                <!-- Floating Ambient Glow on Hero -->
                <div class="absolute top-[20%] right-[10%] w-[300px] h-[300px] rounded-full bg-primary/20 blur-[120px] pointer-events-none"></div>

                <!-- Premium Glassmorphism Feature Panel -->
                <div class="absolute bottom-16 left-16 right-16 bg-slate-900/40 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl text-white max-w-xl">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="px-2.5 py-1 text-xs font-extrabold uppercase bg-white/20 border border-white/25 rounded-md tracking-wider">Dashboard Pro v2.0</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-ping"></span>
                        <span class="text-xs font-semibold text-teal-300">Live Services Active</span>
                    </div>
                    <h2 class="text-3xl font-extrabold tracking-tight mb-2.5 bg-gradient-to-r from-white via-white to-blue-200 bg-clip-text text-transparent">
                        Intelligent Pharmacy Management
                    </h2>
                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                        Empowering pharmacies with predictive inventory planning, streamlined refill pipelines, and automated patient consultation analytics—all in one secure console.
                    </p>
                    
                    <!-- Stats Grid for a high-end enterprise feel -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-white/10">
                        <div>
                            <p class="text-2xl font-black text-white">99.8%</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">Dispensation Accuracy</p>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">&lt; 3 Sec</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">Inventory Sync</p>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">ISO 27001</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">Certified Security</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const fillCredentials = () => {
    form.email = "admin@1.test";
    form.password = "password";
};

const submit = () => {
    form.post(route('login', undefined, false, Ziggy), {
        onFinish: () => {
            form.password = "";
        },
    });
};
</script>
