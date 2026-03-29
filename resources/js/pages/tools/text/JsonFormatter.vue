<script setup>
import { ref } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    Copy, 
    Check, 
    RefreshCcw, 
    FileJson, 
    CodeXml, 
    Trash2,
    Sparkles,
    Braces,
    ArrowRightLeft,
    AlertCircle
} from 'lucide-vue-next';
import { useClipboard } from '@vueuse/core';

// State
const input = ref('');
const result = ref('');
const error = ref('');
const isLoading = ref(false);
const { copy, copied } = useClipboard();

const clearAll = () => {
    input.value = '';
    result.value = '';
    error.value = '';
};

const beautifyJson = () => {
    error.value = '';
    if (!input.value.trim()) return;
    
    try {
        const obj = JSON.parse(input.value);
        result.value = JSON.stringify(obj, null, 4);
    } catch (e) {
        error.value = 'Invalid JSON: ' + e.message;
    }
};

const minifyJson = () => {
    error.value = '';
    if (!input.value.trim()) return;
    
    try {
        const obj = JSON.parse(input.value);
        result.value = JSON.stringify(obj);
    } catch (e) {
        error.value = 'Invalid JSON: ' + e.message;
    }
};

const convertPhpToJson = async () => {
    error.value = '';
    if (!input.value.trim()) return;
    
    isLoading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch('/text/json-formatter/convert-php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                phpArray: input.value
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            result.value = data.json;
        } else {
            error.value = data.error || 'Conversion failed';
            if (data.attempted) {
                console.log('Attempted conversion:', data.attempted);
            }
        }
    } catch (e) {
        error.value = 'Error connecting to server';
    } finally {
        isLoading.value = false;
    }
};

const handleCopy = () => {
    copy(result.value);
};
</script>

<template>
    <HomeLayout title="JSON Formatter & PHP Array Converter">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="rounded-full bg-blue-500/10 px-4 py-1.5 text-sm font-semibold text-blue-400 border border-blue-500/20 flex items-center gap-2">
                        <FileJson class="h-4 w-4" />
                        Data Utility
                    </div>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    JSON <span class="text-blue-400">Formatter</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-400">
                    Beautify messy JSON strings or convert PHP arrays into valid, formatted JSON objects instantly.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-stretch">
                <!-- Input Section -->
                <Card class="bg-zinc-900 border-zinc-800 flex flex-col shadow-xl">
                    <CardHeader class="pb-3 border-b border-zinc-800 bg-zinc-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Braces class="h-4 w-4 text-blue-400" />
                                <CardTitle class="text-base font-bold">Input Data</CardTitle>
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
                            class="w-full h-full min-h-[400px] bg-transparent border-none p-6 text-zinc-300 font-mono text-sm leading-relaxed resize-none focus:outline-none selection:bg-blue-500/30"
                            placeholder="Paste your JSON or PHP array here... (e.g. ['id' => 1])"
                        ></textarea>
                    </CardContent>
                    
                    <!-- Controls -->
                    <div class="p-4 border-t border-zinc-800 bg-zinc-900/50 grid grid-cols-2 gap-3">
                        <Button 
                            class="bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl h-10 border-none transition-all active:scale-95 flex gap-2"
                            @click="beautifyJson"
                        >
                            <Sparkles class="h-4 w-4" />
                            Format Pretty
                        </Button>
                        <Button 
                            variant="outline"
                            class="border-zinc-700 text-zinc-300 hover:bg-zinc-800 h-10 rounded-xl transition-all active:scale-95 flex gap-2"
                            @click="convertPhpToJson"
                            :disabled="isLoading"
                        >
                            <RefreshCcw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
                            PHP to JSON
                        </Button>
                    </div>
                </Card>

                <!-- Output Section -->
                <Card class="bg-zinc-900 border-zinc-800 flex flex-col shadow-xl overflow-hidden">
                    <CardHeader class="pb-3 border-b border-zinc-800 bg-zinc-900/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ArrowRightLeft class="h-4 w-4 text-emerald-400" />
                                <CardTitle class="text-base font-bold">Result</CardTitle>
                            </div>
                            <div class="flex gap-2">
                                <Button 
                                    v-if="result"
                                    variant="ghost" 
                                    size="sm" 
                                    class="text-zinc-500 hover:text-blue-400 h-8 font-bold text-[10px] uppercase tracking-wider"
                                    @click="minifyJson"
                                >
                                    Minify
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
                                    {{ copied ? 'Copied' : 'Copy' }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0 flex-grow relative min-h-[400px] bg-zinc-950/20">
                        <textarea 
                            v-model="result"
                            readonly
                            class="w-full h-full min-h-[400px] bg-transparent border-none p-6 text-emerald-400 font-mono text-sm leading-relaxed resize-none focus:outline-none selection:bg-emerald-500/20"
                            placeholder="Formatted output will appear here..."
                        ></textarea>
                        
                        <!-- Error Overlay -->
                        <div v-if="error" class="absolute bottom-6 left-6 right-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 flex items-start gap-3 backdrop-blur-md animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <AlertCircle class="h-5 w-5 text-red-400 shrink-0 mt-0.5" />
                            <div class="text-xs text-red-200 leading-normal">{{ error }}</div>
                        </div>

                        <!-- Info Metadata -->
                        <div class="absolute top-4 right-4 text-[9px] text-zinc-700 font-mono uppercase tracking-[0.2em] pointer-events-none select-none">
                            {{ result ? 'Valid JSON' : 'Standby' }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Features Info -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-blue-500/20 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-blue-500/5 flex items-center justify-center mb-6">
                        <Sparkles class="h-5 w-5 text-blue-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">Prettify & Validate</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Instantly format unreadable JSON strings into a clean, indented structure. Automatically validates syntax and highlights errors if the input is malformed.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-emerald-500/20 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center mb-6">
                        <CodeXml class="h-5 w-5 text-emerald-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">PHP Array Migration</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Easily convert PHP associative arrays (using either <code>array()</code> or <code>[]</code> syntax) into JSON objects. Ideal for API development and configuration mapping.
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