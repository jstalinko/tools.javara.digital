<script setup>
import { ref, computed } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Type, 
    Hash, 
    FileText, 
    Clock, 
    Languages, 
    Copy, 
    Trash2, 
    Check, 
    ArrowUpCircle, 
    ArrowDownCircle, 
    TextQuote
} from 'lucide-vue-next';

// State
const text = ref('');
const copied = ref(false);

// Metrics Logic
const words = computed(() => {
    const trimmed = text.value.trim();
    return trimmed ? trimmed.split(/\s+/).length : 0;
});

const characters = computed(() => text.value.length);
const charactersNoSpaces = computed(() => text.value.replace(/\s/g, '').length);

const sentences = computed(() => {
    const trimmed = text.value.trim();
    return trimmed ? trimmed.split(/[.!?]+/).filter(Boolean).length : 0;
});

const paragraphs = computed(() => {
    const trimmed = text.value.trim();
    return trimmed ? trimmed.split(/\n\s*\n/).filter(Boolean).length : 0;
});

// Time Estimations
const readingTime = computed(() => {
    const minutes = words.value / 225;
    if (minutes < 1) return '< 1 min';
    return `${Math.ceil(minutes)} min`;
});

const speakingTime = computed(() => {
    const minutes = words.value / 130;
    if (minutes < 1) return '< 1 min';
    return `${Math.ceil(minutes)} min`;
});

// Text Actions
const copyToClipboard = () => {
    navigator.clipboard.writeText(text.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const clearText = () => (text.value = '');

const transformCase = (type) => {
    switch (type) {
        case 'upper':
            text.value = text.value.toUpperCase();
            break;
        case 'lower':
            text.value = text.value.toLowerCase();
            break;
        case 'title':
            text.value = text.value.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
            break;
        case 'sentence':
            text.value = text.value.toLowerCase().replace(/(^\s*\w|[\.\!\?]\s*\w)/g, (c) => c.toUpperCase());
            break;
    }
};

</script>

<template>
    <HomeLayout title="Word Counter - JavaraDigital">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-start p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-indigo-200/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-5xl flex flex-col gap-8">
                
                <!-- Header Area -->
                <div class="text-center w-full">
                    <div class="mx-auto mb-4 w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-black uppercase tracking-widest text-indigo-400">
                        <Hash class="w-3 h-3" />
                        Text Analytics Utility
                    </div>
                    <h1 class="text-white text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                        Word <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-indigo-200">Counter</span>
                    </h1>
                    <p class="text-zinc-500 text-xs mt-1 uppercase tracking-widest font-bold">
                        Detailed metrics and real-time transformations
                    </p>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    
                    <!-- Text Area (Left 3 Columns) -->
                    <div class="lg:col-span-3 space-y-4">
                        <div class="relative group p-1 bg-zinc-900 border border-zinc-800 rounded-[32px] shadow-2xl transition-all hover:bg-zinc-800">
                            <textarea 
                                v-model="text"
                                placeholder="Type or paste your text here..."
                                class="w-full min-h-[500px] bg-transparent text-zinc-300 text-lg leading-relaxed placeholder:text-zinc-800 border-none outline-none focus:ring-0 p-8 font-sans custom-scrollbar"
                            ></textarea>
                            
                            <!-- Floating Copy/Clear controls inside textarea area -->
                            <div class="absolute bottom-6 right-6 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="copyToClipboard" class="p-3 rounded-full bg-zinc-950 border border-zinc-800 text-zinc-400 hover:text-white transition-all shadow-xl">
                                    <Check v-if="copied" class="w-5 h-5" />
                                    <Copy v-else class="w-5 h-5" />
                                </button>
                                <button @click="clearText" class="p-3 rounded-full bg-zinc-950 border border-zinc-800 text-zinc-400 hover:text-red-400 transition-all shadow-xl">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div class="flex flex-wrap items-center justify-center gap-3 p-2 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-xl">
                            <button @click="transformCase('upper')" class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-500 hover:text-white hover:border-zinc-700 font-bold text-[10px] uppercase tracking-widest transition-all">UPPERCASE</button>
                            <button @click="transformCase('lower')" class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-500 hover:text-white hover:border-zinc-700 font-bold text-[10px] uppercase tracking-widest transition-all">lowercase</button>
                            <button @click="transformCase('title')" class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-500 hover:text-white hover:border-zinc-700 font-bold text-[10px] uppercase tracking-widest transition-all">Title Case</button>
                            <button @click="transformCase('sentence')" class="px-4 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-500 hover:text-white hover:border-zinc-700 font-bold text-[10px] uppercase tracking-widest transition-all">Sentence case</button>
                        </div>
                    </div>

                    <!-- Metrics Sidebar (Right 1 Column) -->
                    <div class="w-full space-y-6">
                        
                        <!-- Core Metrics Card -->
                        <div class="p-6 rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-2xl relative overflow-hidden group">
                            <div class="absolute inset-0 bg-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <div class="flex items-center gap-2 text-white font-bold text-[10px] uppercase tracking-[0.2em] mb-8 px-1">
                                <FileText class="w-3.5 h-3.5 text-indigo-400" />
                                Stats Summary
                            </div>

                            <div class="space-y-6">
                                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                                    <span class="text-zinc-500 text-xs font-medium">Words</span>
                                    <span class="text-white text-xl font-black font-mono tracking-tighter">{{ words }}</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                                    <span class="text-zinc-500 text-xs font-medium">Characters</span>
                                    <span class="text-white text-xl font-black font-mono tracking-tighter">{{ characters }}</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                                    <span class="text-zinc-500 text-xs font-medium">Excl. Spaces</span>
                                    <span class="text-white text-lg font-bold font-mono tracking-tighter">{{ charactersNoSpaces }}</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                                    <span class="text-zinc-500 text-xs font-medium">Sentences</span>
                                    <span class="text-white text-lg font-bold font-mono tracking-tighter">{{ sentences }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500 text-xs font-medium">Paragraphs</span>
                                    <span class="text-white text-lg font-bold font-mono tracking-tighter">{{ paragraphs }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Time Estimations Card -->
                        <div class="p-6 rounded-[32px] bg-indigo-600 shadow-2xl shadow-indigo-600/20 relative group overflow-hidden">
                             <!-- Glowing pulse -->
                             <div class="absolute -top-12 -right-12 w-24 h-24 bg-white/20 rounded-full blur-[40px] animate-pulse"></div>
                             
                             <div class="flex items-center gap-2 text-white/80 font-bold text-[10px] uppercase tracking-[0.2em] mb-6 relative z-10">
                                <Clock class="w-3.5 h-3.5" />
                                Estimation
                            </div>

                            <div class="space-y-4 relative z-10">
                                <div>
                                    <div class="text-white/60 text-[9px] font-black uppercase tracking-widest mb-1">Reading Time</div>
                                    <div class="text-white text-2xl font-black">{{ readingTime }}</div>
                                </div>
                                <div>
                                    <div class="text-white/60 text-[9px] font-black uppercase tracking-widest mb-1">Speaking Time</div>
                                    <div class="text-white text-2xl font-black">{{ speakingTime }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Watermark -->
                        <div class="px-4 py-2 rounded-full border border-zinc-900 bg-zinc-950/50 text-center opacity-30 select-none">
                            <span class="text-[9px] font-black text-zinc-500 uppercase tracking-[.4em]">javaradigital</span>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 10px;
}

textarea::selection {
    background: rgba(99, 102, 241, 0.4);
}

textarea {
    resize: none;
}
</style>