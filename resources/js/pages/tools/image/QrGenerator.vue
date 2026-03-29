<script setup>
import { ref, onMounted, watch } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import QRCodeStyling from 'qr-code-styling';
import { 
    QrCode, 
    Link, 
    Upload, 
    X, 
    Download, 
    Type,
    Sparkles,
    Settings,
    FileDown,
    LayoutGrid,
    CircleDashed
} from 'lucide-vue-next';

// State
const qrText = ref('https://javara.digital');
const logoFile = ref(null);
const logoBase64 = ref('');
const qrContainer = ref(null);
const margin = ref(10);
const dotColor = ref('#1a1a1a');
const cornerColor = ref('#1a1a1a');
const dotType = ref('rounded'); // 'dots', 'rounded', 'classy', 'classy-rounded', 'square', 'extra-rounded'
const cornerType = ref('dot'); // 'dot', 'square'

let qrCode = null;

// QR Code Options
const qrOptions = {
    width: 300,
    height: 300,
    type: 'svg',
    data: qrText.value,
    image: '',
    dotsOptions: {
        color: '#1a1a1a',
        type: 'rounded'
    },
    backgroundOptions: {
        color: '#ffffff',
    },
    imageOptions: {
        crossOrigin: 'anonymous',
        margin: 5,
        imageSize: 0.4
    },
    cornersSquareOptions: {
        type: 'extra-rounded',
        color: '#1a1a1a'
    },
    cornersDotOptions: {
        type: 'dot',
        color: '#1a1a1a'
    }
};

onMounted(() => {
    qrCode = new QRCodeStyling(qrOptions);
    if (qrContainer.value) {
        qrCode.append(qrContainer.value);
    }
});

// Watchers
watch([qrText, logoBase64, margin, dotColor, cornerColor, dotType, cornerType], () => {
    if (!qrCode) return;
    
    qrCode.update({
        data: qrText.value || ' ',
        image: logoBase64.value,
        margin: margin.value,
        dotsOptions: {
            color: dotColor.value,
            type: dotType.value
        },
        cornersSquareOptions: {
            color: cornerColor.value,
            type: 'extra-rounded'
        },
        cornersDotOptions: {
            color: cornerColor.value,
            type: cornerType.value
        }
    });
});

// Handlers
const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = (event) => {
        logoBase64.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

const clearLogo = () => {
    logoFile.value = null;
    logoBase64.value = '';
};

const download = (ext) => {
    if (!qrCode) return;
    qrCode.download({
        name: `qr-code-${Date.now()}`,
        extension: ext
    });
};

</script>

<template>
    <HomeLayout title="QR Code Generator - JavaraDigital">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-center p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-purple-500/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-6xl flex flex-col items-center gap-12">
                <!-- Header -->
                <div class="text-center w-full max-w-2xl px-4">
                    <div class="mx-auto mb-6 w-fit flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[11px] font-black uppercase tracking-widest text-indigo-400">
                        <Sparkles class="w-3.5 h-3.5" />
                        Custom QR Experience
                    </div>
                    <h1 class="text-white text-4xl md:text-6xl font-extrabold tracking-tight mb-4">
                        QR Code <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Generator</span>
                    </h1>
                    <p class="text-zinc-500 text-sm md:text-base font-medium max-w-lg mx-auto leading-relaxed">
                        Create high-quality QR codes with custom logos, unique dot patterns, and premium styling.
                    </p>
                </div>

                <!-- Generator Layout -->
                <div class="w-full flex flex-col lg:flex-row gap-8 items-start justify-center">
                    
                    <!-- Sidebar Controls -->
                    <div class="w-full lg:w-[400px] flex flex-col gap-6">
                        
                        <!-- Content Input -->
                        <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl space-y-4">
                            <div class="flex items-center gap-2 text-white font-bold text-sm tracking-wide">
                                <Link class="w-4 h-4 text-indigo-400" />
                                QR CONTENT
                            </div>
                            <textarea 
                                v-model="qrText"
                                placeholder="Enter URL or text..."
                                class="w-full h-24 bg-zinc-950 border border-zinc-800 rounded-2xl p-4 text-zinc-300 text-sm focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-all resize-none"
                            ></textarea>
                        </div>

                        <!-- Logo Upload -->
                        <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl space-y-4">
                            <div class="flex items-center gap-2 text-white font-bold text-sm tracking-wide">
                                <Upload class="w-4 h-4 text-indigo-400" />
                                CENTER LOGO (OPTIONAL)
                            </div>
                            
                            <div v-if="!logoBase64" class="relative group">
                                <label class="flex flex-col items-center justify-center h-32 w-full bg-zinc-950 border-2 border-dashed border-zinc-800 rounded-2xl cursor-pointer hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all">
                                    <Upload class="w-6 h-6 text-zinc-600 group-hover:text-indigo-400 mb-2" />
                                    <span class="text-xs text-zinc-500 font-bold group-hover:text-zinc-300 uppercase tracking-widest">Select Image</span>
                                    <input type="file" @change="handleLogoUpload" class="hidden" accept="image/*" />
                                </label>
                            </div>

                            <div v-else class="flex flex-col items-center gap-4">
                                <div class="relative w-24 h-24 rounded-2xl overflow-hidden border border-zinc-700 bg-zinc-950 p-2 group">
                                    <img :src="logoBase64" class="w-full h-full object-contain" />
                                    <button @click="clearLogo" class="absolute inset-0 bg-red-600/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <X class="w-6 h-6" />
                                    </button>
                                </div>
                                <span class="text-[10px] text-zinc-600 font-bold uppercase tracking-widest">Click to remove</span>
                            </div>
                        </div>

                        <!-- Advanced Styling -->
                        <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl space-y-6">
                            <div class="flex items-center gap-2 text-white font-bold text-sm tracking-wide">
                                <Settings class="w-4 h-4 text-indigo-400" />
                                STYLE SETTINGS
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black uppercase text-zinc-600 tracking-wider">Pattern</label>
                                    <select v-model="dotType" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-zinc-300 outline-none">
                                        <option value="rounded">Rounded</option>
                                        <option value="dots">Dots</option>
                                        <option value="square">Square</option>
                                        <option value="extra-rounded">Extra-Rounded</option>
                                        <option value="classy">Classy</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black uppercase text-zinc-600 tracking-wider">Corners</label>
                                    <select v-model="cornerType" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-zinc-300 outline-none">
                                        <option value="dot">Rounded-Dot</option>
                                        <option value="square">Modern-Square</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Main Preview Panel -->
                    <div class="flex-1 w-full flex flex-col items-center justify-center gap-8">
                        
                        <!-- The QR Output -->
                        <div class="relative group p-8 lg:p-12 rounded-[40px] bg-white shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] transition-all transform hover:scale-[1.02] duration-500 overflow-hidden">
                             <!-- Subtle Inner glow/overlay from javaradigital logo color could go here -->
                             <div ref="qrContainer" class="relative z-10 w-[250px] h-[250px] md:w-[300px] md:h-[300px] flex items-center justify-center"></div>
                             
                             <!-- Background pattern from image -->
                             <div class="absolute inset-0 bg-neutral-100 opacity-20 pointer-events-none"></div>
                        </div>

                        <!-- Export Actions -->
                        <div class="flex items-center gap-3 p-3 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-2xl">
                            <button @click="download('png')" class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shadow-indigo-600/20">
                                <Download class="w-4 h-4" />
                                Download PNG
                            </button>
                            <button @click="download('svg')" class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-zinc-950 border border-zinc-800 text-zinc-400 hover:text-white hover:border-zinc-600 font-bold text-xs uppercase tracking-[0.2em] transition-all active:scale-95">
                                <FileDown class="w-4 h-4" />
                                Download SVG
                            </button>
                        </div>

                        <!-- Tip -->
                        <div class="flex items-center gap-2 text-zinc-600 text-[10px] font-black tracking-widest uppercase">
                            <CircleDashed class="w-3 h-3 animate-spin duration-[3000ms]" />
                            High Dynamic QR Technology
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
/* Responsive tweaks and custom shadows */
.shadow-xl {
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
}

textarea::selection {
    background: rgba(99, 102, 241, 0.4);
}

/* Custom scrollbar for textarea if long text */
textarea::-webkit-scrollbar {
    width: 6px;
}
textarea::-webkit-scrollbar-track {
    background: transparent;
}
textarea::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 10px;
}
</style>