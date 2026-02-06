<template>
    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-8rem)] gap-6">
            <!-- Sidebar: Conversation History -->
            <div 
                class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col overflow-hidden transition-all duration-300 ease-in-out absolute md:relative z-20 h-full"
                :class="isSidebarOpen ? 'w-72 translate-x-0' : 'w-0 -translate-x-full md:translate-x-0 md:w-0 border-0'"
            >
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-semibold text-gray-900">Recent Chats</h3>
                    <button 
                        @click="isSidebarOpen = false"
                        class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-500 transition-colors"
                        title="Close History"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <!-- New Chat Button (Small) -->
                     <button 
                        @click="startNewChat"
                        class="p-1.5 hover:bg-gray-100 rounded-lg text-primary transition-colors ml-auto mr-2"
                        title="New Chat"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-1 w-72">
                    <div v-if="conversations.length === 0" class="p-4 text-center text-sm text-gray-500 italic">
                        No previous chats found.
                    </div>
                    <button
                        v-for="conv in conversations"
                        :key="conv.id"
                        @click="loadConversation(conv.session_id)"
                        class="w-full text-left p-3 rounded-lg text-sm transition-all group relative"
                        :class="sessionId === conv.session_id ? 'bg-primary/5 text-primary border-l-4 border-primary' : 'hover:bg-gray-50 text-gray-700'"
                    >
                        <div class="font-medium truncate pr-4">
                            {{ conv.title || 'Chat ' + conv.session_id.substring(0, 8) }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ new Date(conv.created_at).toLocaleDateString() }}
                        </div>
                    </button>
                </div>
            </div>

            <!-- Toggle Button (Visible when sidebar is closed) -->
            <button 
                v-if="!isSidebarOpen"
                @click="isSidebarOpen = true"
                class="absolute left-6 top-24 z-10 bg-white p-2 rounded-lg shadow-sm border border-gray-200 text-gray-500 hover:text-primary transition-colors md:hidden lg:block lg:relative lg:left-0 lg:top-0 h-fit"
                title="View History"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>

            <!-- Main Chat Area -->
            <div class="flex-1 flex flex-col h-full">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900">AI Pharmacy Assistant</h2>
                    <p class="text-sm text-gray-500">Ask questions about medications, pharmacy services, and general health information.</p>
                </div>

                <!-- Safety Notice -->
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800">Important Notice</p>
                            <p class="text-xs text-amber-700 mt-1">This AI assistant provides general information only. For medical advice, dosage questions, or emergencies, please speak with our pharmacist directly.</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Container -->
                <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    <!-- Messages Area -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <!-- Welcome Message -->
                        <div v-if="messages.length === 0 && !isLoading" class="flex flex-col items-center justify-center h-full text-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">How can I help you today?</h3>
                            <p class="text-sm text-gray-500 mt-2 max-w-md">Ask me about medications, pharmacy services, or general health information.</p>
                            
                            <!-- Quick Actions -->
                            <div class="flex flex-wrap gap-2 mt-6 justify-center">
                                <button 
                                    v-for="suggestion in suggestions" 
                                    :key="suggestion"
                                    @click="sendMessage(suggestion)"
                                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors"
                                >
                                    {{ suggestion }}
                                </button>
                            </div>
                        </div>

                        <!-- Message Bubbles -->
                        <template v-for="msg in messages" :key="msg.id">
                            <!-- User Message -->
                            <div v-if="msg.role === 'user'" class="flex justify-end">
                                <div class="max-w-[70%] bg-primary text-white px-4 py-3 rounded-2xl rounded-br-md">
                                    <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                                </div>
                            </div>
                            
                            <!-- Assistant Message -->
                            <div v-else-if="msg.role === 'assistant'" class="flex justify-start">
                                <div class="flex items-start gap-3 max-w-[70%]">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <div class="bg-gray-200/50 px-4 py-3 rounded-2xl rounded-bl-md">
                                        <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ msg.content }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- System/Escalation Message -->
                            <div v-else-if="msg.role === 'system'" class="flex justify-center">
                                <div class="max-w-[80%] bg-amber-50 border border-amber-200 px-4 py-3 rounded-lg shadow-sm">
                                    <div class="flex items-start gap-2">
                                        <div class="w-2 h-full bg-amber-400 rounded-full shrink-0"></div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Pharmacy Guidance</span>
                                            </div>
                                            <p class="text-sm text-amber-800 leading-relaxed">{{ msg.content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Loading Indicator -->
                        <div v-if="isLoadingMessages" class="flex items-center justify-center h-48">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Retrieving chat history...</span>
                            </div>
                        </div>

                        <!-- Typing Indicator -->
                        <div v-if="isLoading" class="flex justify-start">
                            <div class="flex items-start gap-3 px-1">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <div class="bg-gray-100 px-4 py-3 rounded-2xl rounded-bl-md">
                                    <div class="flex gap-1.5 py-1">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="border-t border-gray-100 p-4 bg-gray-50/50">
                        <form @submit.prevent="sendMessage(inputMessage)" class="flex gap-3 max-w-4xl mx-auto">
                            <input
                                v-model="inputMessage"
                                type="text"
                                placeholder="Message our AI pharmacy assistant..."
                                class="flex-1 rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-20 px-4 py-3 text-sm transition-all shadow-sm"
                                :disabled="isLoading || isLoadingMessages"
                            />
                            <button
                                type="submit"
                                :disabled="!inputMessage.trim() || isLoading || isLoadingMessages"
                                class="px-5 py-3 bg-primary text-white rounded-xl font-medium text-sm hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2 shadow-sm active:scale-95"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span>Send</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, nextTick } from 'vue';
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";
import { router } from '@inertiajs/vue3';

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const props = defineProps({
    conversations: Array,
});

const messages = ref([]);
const inputMessage = ref('');
const isLoading = ref(false);
const isLoadingMessages = ref(false);
const sessionId = ref(null);
const messagesContainer = ref(null);
const isSidebarOpen = ref(false);

const suggestions = [
    "What are your pharmacy hours?",
    "Do you offer home delivery?",
    "How can I refill my prescription?",
    "Do you accept Medicare insurance?",
];

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const startNewChat = () => {
    messages.value = [];
    sessionId.value = null;
    inputMessage.value = '';
};

const loadConversation = async (id) => {
    if (sessionId.value === id) return;
    
    sessionId.value = id;
    messages.value = [];
    isLoadingMessages.value = true;
    
    try {
        const response = await fetch(route('chat.messages', { session_id: id }));
        const data = await response.json();
        messages.value = data.messages;
        scrollToBottom();
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        isLoadingMessages.value = false;
    }
};

const sendMessage = async (messageText) => {
    const text = typeof messageText === 'string' ? messageText : inputMessage.value;
    if (!text.trim()) return;

    // Add user message to UI
    const userMessage = {
        id: Date.now(),
        role: 'user',
        content: text,
        created_at: new Date().toISOString(),
    };
    messages.value.push(userMessage);
    inputMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        const isNewSession = !sessionId.value;
        const response = await fetch(route('chat.send'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                message: text,
                session_id: sessionId.value,
            }),
        });

        const data = await response.json();
        
        if (data.session_id) {
            sessionId.value = data.session_id;
        }

        // reload conversations list if this was the start of a new chat
        if (isNewSession) {
            router.reload({ only: ['conversations'] });
        }

        // Add assistant or system message
        const replyMessage = {
            id: Date.now() + 1,
            role: data.escalation ? 'system' : 'assistant',
            content: data.message,
            created_at: new Date().toISOString(),
        };
        messages.value.push(replyMessage);
        
        // If it was a new conversation, we might want to refresh the sidebar
        // For now, we'll just keep the current session active
    } catch (error) {
        console.error('Chat error:', error);
        messages.value.push({
            id: Date.now() + 1,
            role: 'assistant',
            content: 'I apologize, but I encountered a technical issue. Please try again or contact the pharmacy support.',
            created_at: new Date().toISOString(),
        });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>
