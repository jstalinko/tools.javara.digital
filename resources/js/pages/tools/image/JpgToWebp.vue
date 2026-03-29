<script setup>
import { ref, computed } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Upload, 
    ImageIcon, 
    Download, 
    X, 
    Check, 
    Loader2, 
    Zap, 
    Info, 
    ArrowRight,
    FileType,
    FileArchive,
    Settings
} from 'lucide-vue-next';

// State
const files = ref([]);
const isDragging = ref(false);
const isProcessing = ref(false);
const quality = ref(80);
const batchId = ref(null);
const results = ref([]);
const error = ref('');

// Handlers
const onFileSelect = (e) => {
    const selectedFiles = Array.from(e.target.files);
    addFiles(selectedFiles);
};

const onDrop = (e) => {
    isDragging.value = false;
    const droppedFiles = Array.from(e.dataTransfer.files);
    addFiles(droppedFiles);
};

const addFiles = (newFiles) => {
    const jpgFiles = newFiles.filter(f => f.type === 'image/jpeg' || f.type === 'image/jpg');
    
    if (jpgFiles.length !== newFiles.length) {
        error.value = 'Only JPG/JPEG images are supported. Other files were ignored.';
    }

    if (files.value.length + jpgFiles.length > 10) {
        error.value = 'Maximum 10 images allowed per batch.';
        return;
    }

    jpgFiles.forEach(file => {
        files.value.push({
            file,
            id: Math.random().toString(36).substring(7),
            preview: URL.createObjectURL(file),
            status: 'pending',
            progress: 0,
            result: null
        });
    });
};

const removeFile = (id) => {
    const index = files.value.findIndex(f => f.id === id);
    if (index !== -1) {
        URL.revokeObjectURL(files.value[index].preview);
        files.value.splice(index, 1);
    }
};

const processFiles = async () => {
    if (files.value.length === 0) return;
    
    isProcessing.value = true;
    error.value = '';
    
    const formData = new FormData();
    formData.append('quality', quality.value);
    
    files.value.forEach((f, index) => {
        f.status = 'processing';
        formData.append(`images[${index}]`, f.file);
    });

    try {
        const response = await fetch(route('image.jpg-to-webp.process'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            batchId.value = data.batchId;
            results.value = data.results;
            
            // Map results back to local files
            data.results.forEach((res, index) => {
                if (files.value[index]) {
                    files.value[index].status = 'done';
                    files.value[index].result = res;
                }
            });
        } else {
            error.value = data.message || 'Server error during conversion.';
            files.value.forEach(f => f.status = 'error');
        }
    } catch (e) {
        error.value = 'Failed to connect to the server.';
        files.value.forEach(f => f.status = 'error');
    } finally {
        isProcessing.value = false;
    }
};

const downloadAll = () => {
    if (!batchId.value) return;
    window.location.href = route('image.png-to-webp.download-zip', { id: batchId.value }); // Reuse download ZIP from PNG tool
};

const totalSaving = computed(() => {
    if (results.value.length === 0) return 0;
    const totalReduction = results.value.reduce((acc, curr) => acc + curr.reduction, 0);
    return (totalReduction / results.value.length).toFixed(1);
});

</script>

<template>
    <HomeLayout title="JPG to WebP Converter - JavaraDigital">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-start p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-purple-500/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-4xl flex flex-col gap-10">
                
                <!-- Header -->
                <div class="text-center">
                    <div class="mx-auto mb-4 w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-black uppercase tracking-widest text-indigo-400">
                        <Zap class="w-3 h-3" />
                        Next-Gen Performance
                    </div>
                    <h1 class="text-white text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                        JPG to <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">WebP</span>
                    </h1>
                    <p class="text-zinc-500 text-xs mt-1 uppercase tracking-widest font-bold">
                        Ultra-fast multi-image conversion for JPG/JPEG formats
                    </p>
                </div>

                <!-- Main Conversion Area -->
                <div class="w-full space-y-8">
                    
                    <!-- Drop Zone -->
                    <div 
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="onDrop"
                        class="relative group p-1 bg-zinc-900 border border-zinc-800 rounded-[40px] shadow-2xl transition-all"
                        :class="isDragging ? 'ring-2 ring-indigo-500 bg-zinc-800' : 'hover:border-zinc-700'"
                    >
                        <label class="flex flex-col items-center justify-center py-16 px-6 cursor-pointer rounded-[38px] border-2 border-dashed border-zinc-800 group-hover:bg-zinc-950/50 transition-all">
                            <div class="p-5 rounded-full bg-indigo-500/10 text-indigo-400 mb-6 group-hover:scale-110 transition-transform">
                                <Upload class="w-10 h-10" />
                            </div>
                            <div class="text-center">
                                <span class="text-white text-xl font-bold block mb-2">Drop your JPGs here</span>
                                <span class="text-zinc-500 text-sm font-medium">Or click to browse files (JPG/JPEG only, max 5MB)</span>
                            </div>
                            <input type="file" multiple accept="image/jpeg,image/jpg" @change="onFileSelect" class="hidden" />
                        </label>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="error" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-medium flex items-center gap-3">
                        <X class="w-5 h-5 shrink-0" />
                        {{ error }}
                    </div>

                    <!-- File List -->
                    <div v-if="files.length > 0" class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex items-center justify-between px-2">
                             <h3 class="text-white/80 font-bold text-sm tracking-wide flex items-center gap-2">
                                <FileType class="w-4 h-4 text-indigo-400" />
                                Queued Files ({{ files.length }})
                             </h3>
                             <div v-if="results.length > 0" class="text-[10px] font-black uppercase text-purple-400 bg-purple-400/10 px-3 py-1 rounded-full border border-purple-400/20">
                                Avg. {{ totalSaving }}% Smaller
                             </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="item in files" :key="item.id" class="p-4 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl flex items-center gap-4 relative group overflow-hidden">
                                
                                <img :src="item.preview" class="w-16 h-16 rounded-xl object-cover border border-zinc-800" />
                                
                                <div class="flex-grow min-w-0">
                                    <h4 class="text-white text-xs font-bold truncate mb-1">{{ item.file.name }}</h4>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[9px] text-zinc-600 font-bold uppercase tracking-widest">{{ (item.file.size / 1024).toFixed(0) }} KB</span>
                                        <div v-if="item.status === 'processing'" class="flex items-center gap-1.5 text-indigo-400 text-[9px] font-black uppercase">
                                            <Loader2 class="w-3 h-3 animate-spin" /> Processing
                                        </div>
                                        <div v-if="item.status === 'done'" class="flex items-center gap-1.5 text-emerald-400 text-[9px] font-black uppercase">
                                            <Check class="w-3 h-3" /> Converted
                                        </div>
                                    </div>
                                </div>

                                <div v-if="item.status === 'done'" class="text-right pr-2">
                                    <span class="block text-white text-xs font-black">{{ item.result.size }} KB</span>
                                    <span class="text-emerald-500 text-[8px] font-bold uppercase tracking-tighter">-{{ item.result.reduction }}%</span>
                                </div>

                                <button 
                                    v-if="item.status === 'pending'"
                                    @click="removeFile(item.id)" 
                                    class="p-2 rounded-xl bg-zinc-950 text-zinc-600 hover:text-rose-500 transition-colors"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div v-if="files.length > 0 && results.length === 0" class="p-6 rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-xl space-y-4 animate-in fade-in duration-500">
                        <div class="flex items-center justify-between">
                             <h3 class="text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <Settings class="w-4 h-4 text-indigo-400" />
                                Conversion Quality
                             </h3>
                             <span class="text-indigo-400 text-sm font-black">{{ quality }}%</span>
                        </div>
                        <input 
                            type="range" 
                            v-model="quality" 
                            min="1" 
                            max="100" 
                            class="w-full h-1.5 bg-zinc-950 rounded-lg appearance-none cursor-pointer accent-indigo-500"
                        />
                        <div class="flex justify-between text-[8px] font-black text-zinc-600 uppercase tracking-tighter">
                            <span>Small Size</span>
                            <span>Balanced</span>
                            <span>High Quality</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="files.length > 0" class="flex flex-col sm:flex-row items-center justify-center gap-4 py-8">
                        <button 
                            v-if="results.length === 0"
                            @click="processFiles"
                            :disabled="isProcessing"
                            class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-50 flex items-center justify-center gap-3"
                        >
                            <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin" />
                            <Zap v-else class="w-5 h-5" />
                            {{ isProcessing ? 'Converting...' : 'Start Conversion' }}
                        </button>

                        <button 
                            v-if="results.length > 0"
                            @click="downloadAll"
                            class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-emerald-600/20 flex items-center justify-center gap-3"
                        >
                            <FileArchive class="w-5 h-5" />
                            Download All (ZIP)
                        </button>

                        <button 
                            @click="files = []; results = []; batchId = null;"
                            class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition-all font-black text-sm uppercase tracking-widest"
                        >
                            Clear All
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.shadow-2xl {
    box-shadow: 0 30px 60px -15px rgba(0,0,0,0.6);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
</style>
