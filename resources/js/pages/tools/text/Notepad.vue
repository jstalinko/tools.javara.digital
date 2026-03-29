<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Save, 
    Link as LinkIcon, 
    Copy, 
    Check, 
    FileText, 
    Clock, 
    ExternalLink,
    AlertCircle,
    ChevronLeft,
    Share2
} from 'lucide-vue-next';

const props = defineProps({
    note: Object,
});

// State
const title = ref(props.note?.title || '');
const content = ref(props.note?.content || '');
const isSaved = ref(!!props.note);
const savedUuid = ref(props.note?.uuid || '');
const isLoading = ref(false);
const copied = ref(false);
const error = ref('');

// Full URL for sharing
const shareUrl = computed(() => {
    if (!savedUuid.value) return '';
    return `${window.location.origin}/text/notepad/${savedUuid.value}`;
});

// Handlers
const saveNote = async () => {
    if (!content.value.trim()) {
        error.value = 'Note content is required.';
        return;
    }

    isLoading.value = true;
    error.value = '';

    try {
        const response = await fetch(route('text.notepad.save'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                title: title.value || 'Untitled',
                content: content.value,
            }),
        });

        const data = await response.json();
        
        if (response.ok) {
            savedUuid.value = data.uuid;
            isSaved.value = true;
            // Update the browser URL without refreshing
            window.history.pushState({}, '', `/text/notepad/${data.uuid}`);
        } else {
            error.value = data.message || 'Something went wrong while saving.';
        }
    } catch (e) {
        error.value = 'Failed to connect to the server.';
    } finally {
        isLoading.value = false;
    }
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(shareUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const createNew = () => {
    title.value = '';
    content.value = '';
    isSaved.value = false;
    savedUuid.value = '';
    error.value = '';
    window.history.pushState({}, '', '/text/notepad');
};

</script>

<template>
    <HomeLayout :title="isSaved ? `Viewing: ${title}` : 'Digital Notepad'">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-start p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] bg-zinc-500/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-4xl flex flex-col gap-8">
                
                <!-- Header Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div v-if="isSaved" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest text-emerald-400 mb-2">
                            <Check class="w-3 h-3" />
                            Note Saved Securely
                        </div>
                        <h1 class="text-white text-3xl font-extrabold tracking-tight">
                            {{ isSaved ? 'Stored Note' : 'Digital Notepad' }}
                        </h1>
                        <p class="text-zinc-500 text-xs mt-1 uppercase tracking-widest font-bold">
                            {{ isSaved ? `Ref: ${savedUuid.substring(0, 8)}...` : 'Quick & private temporary notes' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button 
                            v-if="isSaved"
                            @click="createNew"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all font-bold text-xs"
                        >
                            <FileText class="w-4 h-4" />
                            New Note
                        </button>
                        
                        <button 
                            v-if="!isSaved"
                            @click="saveNote"
                            :disabled="isLoading"
                            class="flex items-center gap-2 px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition-all font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-600/20"
                        >
                            <Save v-if="!isLoading" class="w-4 h-4" />
                            <div v-else class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ isLoading ? 'Saving...' : 'Save Note' }}
                        </button>
                    </div>
                </div>

                <!-- Error Alert -->
                <div v-if="error" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center gap-3 text-red-400 text-sm font-medium animate-in fade-in slide-in-from-top-4">
                    <AlertCircle class="w-5 h-5" />
                    {{ error }}
                </div>

                <!-- Link/Sharing Bar (Visible after save) -->
                <div v-if="isSaved" class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 backdrop-blur-sm flex flex-col sm:flex-row items-center justify-between gap-4 animate-in zoom-in-95">
                    <div class="flex items-center gap-3 truncate w-full sm:w-auto">
                        <div class="p-2 rounded-lg bg-indigo-600/20 text-indigo-400">
                            <Share2 class="w-5 h-5" />
                        </div>
                        <div class="truncate">
                            <div class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-1">Shareable Secret Link</div>
                            <div class="text-zinc-400 text-xs font-mono truncate">{{ shareUrl }}</div>
                        </div>
                    </div>
                    <button 
                        @click="copyToClipboard"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-all font-bold text-xs shadow-lg shadow-indigo-600/20"
                    >
                        <Check v-if="copied" class="w-4 h-4" />
                        <Copy v-else class="w-4 h-4" />
                        {{ copied ? 'Copied!' : 'Copy Link' }}
                    </button>
                </div>

                <!-- Editor Section -->
                <div class="flex-grow flex flex-col p-6 sm:p-10 rounded-[40px] bg-zinc-900 border border-zinc-800 shadow-2xl relative">
                    
                    <!-- Form overlay when saved -->
                    <div v-if="isSaved" class="absolute inset-0 bg-transparent rounded-[40px] z-20" title="Saved notes are read-only"></div>

                    <div class="space-y-6">
                        <!-- Title Field -->
                        <div class="space-y-2">
                            <label class="text-[10px] text-zinc-600 font-bold uppercase tracking-[.3em] px-1">Note Title (Optional)</label>
                            <input 
                                v-model="title"
                                :placeholder="isSaved ? '' : 'Untitled Note'"
                                class="w-full bg-transparent text-white text-2xl font-bold placeholder:text-zinc-800 border-none outline-none focus:ring-0 px-1"
                                :readonly="isSaved"
                            />
                        </div>

                        <!-- Divider -->
                        <div class="h-px bg-zinc-800/50 w-full"></div>

                        <!-- Content Field -->
                        <div class="space-y-2">
                             <label class="text-[10px] text-zinc-600 font-bold uppercase tracking-[.3em] px-1">Note Content</label>
                            <textarea 
                                v-model="content"
                                placeholder="Start writing something important..."
                                class="w-full min-h-[400px] bg-transparent text-zinc-300 text-lg leading-relaxed placeholder:text-zinc-800 border-none outline-none focus:ring-0 px-1 font-sans custom-scrollbar"
                                :readonly="isSaved"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Watermark -->
                    <div class="mt-8 flex items-center justify-between text-zinc-700">
                        <div class="flex items-center gap-2 text-[10px] font-black tracking-widest uppercase">
                            <Clock class="w-3.5 h-3.5" />
                            Secret & Secure
                        </div>
                        <div class="text-[10px] font-black tracking-widest uppercase opacity-30">javaradigital</div>
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

textarea::selection, input::selection {
    background: rgba(99, 102, 241, 0.4);
}

textarea {
    resize: none;
}
</style>