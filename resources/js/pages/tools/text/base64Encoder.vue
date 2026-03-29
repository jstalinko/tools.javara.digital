<script setup>
import { ref } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    Copy, 
    Check, 
    RefreshCcw, 
    Binary, 
    Trash2,
    Sparkles,
    ArrowRightLeft,
    AlertCircle,
    Globe,
    FileText
} from 'lucide-vue-next';
import { useClipboard } from '@vueuse/core';

// State
const input = ref('');
const result = ref('');
const error = ref('');
const isUrlSafe = ref(false);
const { copy, copied } = useClipboard();

const clearAll = () => {
    input.value = '';
    result.value = '';
    error.value = '';
};

const encodeBase64 = () => {
    error.value = '';
    if (!input.value.trim()) return;

    try {
        // Handle UTF-8 encoding properly
        const encoder = new TextEncoder();
        const data = encoder.encode(input.value);
        let b64 = btoa(String.fromCharCode(...data));

        if (isUrlSafe.value) {
            b64 = b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        result.value = b64;
    } catch (e) {
        error.value = 'Encoding failed: ' + e.message;
    }
};

const decodeBase64 = () => {
    error.value = '';
    if (!input.value.trim()) return;

    try {
        let b64 = input.value.trim();

        // Handle URL-safe format back to standard
        if (isUrlSafe.value || b64.includes('-') || b64.includes('_')) {
            b64 = b64.replace(/-/g, '+').replace(/_/g, '/');
            while (b64.length % 4) {
                b64 += '=';
            }
        }

        const binStr = atob(b64);
        const bytes = new Uint8Array(binStr.length);
        for (let i = 0; i < binStr.length; i++) {
            bytes[i] = binStr.charCodeAt(i);
        }

        const decoder = new TextDecoder();
        result.value = decoder.decode(bytes);
    } catch (e) {
        error.value = 'Decoding failed: Invalid Base64 input';
    }
};

const toggleUrlSafe = () => {
    isUrlSafe.value = !isUrlSafe.value;
};

const handleCopy = () => {
    copy(result.value);
};

const handleSwap = () => {
    const temp = input.value;
    input.value = result.value;
    result.value = temp;
    error.value = '';
};
</script>

<template>
    <HomeLayout title="Base64 Encoder & Decoder">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="rounded-full bg-purple-500/10 px-4 py-1.5 text-sm font-semibold text-purple-400 border border-purple-500/20 flex items-center gap-2">
                        <Binary class="h-4 w-4" />
                        Encoding Utility
                    </div>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl leading-tight">
                    Base64 <span class="text-purple-400">Converter</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-400">
                    Securely encode and decode text strings into Base64 format. Fast, private, and processes entirely in your browser.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-stretch">
                <!-- Input Card -->
                <Card class="bg-zinc-900 border-zinc-800 flex flex-col shadow-xl">
                    <CardHeader class="pb-3 border-b border-zinc-800 bg-zinc-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <FileText class="h-4 w-4 text-purple-400" />
                                <CardTitle class="text-base font-bold uppercase tracking-wider text-zinc-200">Source Text</CardTitle>
                            </div>
                            <Button 
                                variant="ghost" 
                                size="sm" 
                                class="text-zinc-500 hover:text-red-400 transition-colors h-8"
                                @click="clearAll"
                            >
                                <Trash2 class="h-3.5 w-3.5 mr-1.5" />
                                Clear
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0 flex-grow relative min-h-[400px]">
                        <textarea 
                            v-model="input"
                            class="w-full h-full min-h-[400px] bg-transparent border-none p-6 text-zinc-300 font-mono text-sm leading-relaxed resize-none focus:outline-none selection:bg-purple-500/30"
                            placeholder="Type or paste your text here..."
                        ></textarea>
                        
                        <!-- Toggle Options Overlay -->
                        <div class="absolute bottom-4 left-4">
                            <button 
                                @click="toggleUrlSafe"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg border text-[10px] font-bold uppercase tracking-wider transition-all"
                                :class="isUrlSafe ? 'bg-purple-500/10 border-purple-500/50 text-purple-400' : 'bg-zinc-800 border-zinc-700 text-zinc-500 hover:text-zinc-300'"
                            >
                                <Globe class="h-3 w-3" />
                                URL-Safe Mode: {{ isUrlSafe ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </CardContent>
                    
                    <!-- Action Bar -->
                    <div class="p-4 border-t border-zinc-800 bg-zinc-900/50 flex gap-3">
                        <Button 
                            class="flex-1 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl h-10 border-none transition-all active:scale-95 flex gap-2"
                            @click="encodeBase64"
                        >
                            <Sparkles class="h-4 w-4" />
                            Encode
                        </Button>
                        <Button 
                            variant="outline"
                            class="flex-1 border-zinc-700 text-zinc-300 hover:bg-zinc-800 h-10 rounded-xl transition-all active:scale-95 flex gap-2"
                            @click="decodeBase64"
                        >
                            <RefreshCcw class="h-4 w-4" />
                            Decode
                        </Button>
                    </div>
                </Card>

                <!-- Result Card -->
                <Card class="bg-zinc-900 border-zinc-800 flex flex-col shadow-xl overflow-hidden">
                    <CardHeader class="pb-3 border-b border-zinc-800 bg-zinc-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ArrowRightLeft class="h-4 w-4 text-emerald-400" />
                                <CardTitle class="text-base font-bold uppercase tracking-wider text-zinc-200">Result Output</CardTitle>
                            </div>
                            <div class="flex gap-2">
                                <Button 
                                    v-if="result"
                                    variant="ghost" 
                                    size="sm" 
                                    class="text-zinc-500 hover:text-purple-400 h-8 font-bold text-[10px] uppercase tracking-wider"
                                    @click="handleSwap"
                                    title="Swap with Input"
                                >
                                    Swap
                                </Button>
                                <Button 
                                    size="sm"
                                    variant="outline"
                                    :class="[
                                        'h-8 transition-all duration-300 font-bold px-3 rounded-lg text-[10px] uppercase tracking-wider',
                                        copied ? 'border-emerald-500 text-emerald-400 bg-emerald-500/10' : 'text-zinc-300 border-zinc-700 hover:bg-zinc-800'
                                    ]"
                                    :disabled="!result"
                                    @click="handleCopy"
                                >
                                    <Check v-if="copied" class="mr-1.5 h-3 w-3" />
                                    <Copy v-else class="mr-1.5 h-3 w-3" />
                                    {{ copied ? 'Copied' : 'Copy Result' }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0 flex-grow relative min-h-[400px] bg-zinc-950/20">
                        <textarea 
                            v-model="result"
                            readonly
                            class="w-full h-full min-h-[400px] bg-transparent border-none p-6 text-emerald-400 font-mono text-sm leading-relaxed resize-none focus:outline-none selection:bg-emerald-500/20"
                            placeholder="Output will appear here..."
                        ></textarea>
                        
                        <!-- Error Alert -->
                        <div v-if="error" class="absolute bottom-6 left-6 right-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 flex items-start gap-3 backdrop-blur-md animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <AlertCircle class="h-5 w-5 text-red-500 shrink-0 mt-0.5" />
                            <div class="text-xs text-red-400 leading-normal font-medium">{{ error }}</div>
                        </div>

                        <!-- Status Label -->
                        <div class="absolute top-4 right-4 text-[9px] text-zinc-700 font-mono uppercase tracking-[0.2em] pointer-events-none select-none">
                            {{ result ? 'Processed' : 'Standby' }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tool Info Grid -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-purple-500/20 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-purple-500/5 flex items-center justify-center mb-6">
                        <Globe class="h-5 w-5 text-purple-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">URL-Safe Handling</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Standard Base64 contains <code>+</code> and <code>/</code>, which can break URLs. Our tool supports URL-safe encoding by swapping these with <code>-</code> and <code>_</code>.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-emerald-500/20 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center mb-6">
                        <ShieldCheck class="h-5 w-5 text-emerald-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3 px-1">Privacy Focused</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Data is never sent to a server. All encoding and decoding happens locally in your browser's memory, ensuring your sensitive data stays private.
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
textarea {
    scrollbar-width: thin;
    scrollbar-color: #27272a transparent;
}
</style>