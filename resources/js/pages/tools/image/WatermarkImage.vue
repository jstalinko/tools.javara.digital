<script setup>
import { ref, watch, onMounted } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Upload, 
    FileImage, 
    Download, 
    X, 
    Type,
    Settings,
    Image as ImageIcon,
    LayoutGrid,
    MoveUpLeft,
    MoveUpRight,
    MoveDownLeft,
    MoveDownRight,
    AlignCenter
} from 'lucide-vue-next';

const baseImage = ref(null);
const baseImagePreview = ref('');
const baseImageElement = ref(null);

const mode = ref('text'); // text, image
const textWatermark = ref('JavaraDigital');
const logoFile = ref(null);
const logoPreview = ref('');
const logoElement = ref(null);

const position = ref('middle');
const opacity = ref(50);
const watermarkSize = ref(15); 

const isDraggingBase = ref(false);
const isDraggingLogo = ref(false);
const canvasRef = ref(null);
const downloadUrl = ref('');

const loadBaseImage = (file) => {
    baseImage.value = file;
    baseImagePreview.value = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
        baseImageElement.value = img;
        drawPreview();
    };
    img.src = baseImagePreview.value;
};

const loadLogoImage = (file) => {
    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
        logoElement.value = img;
        drawPreview();
    };
    img.src = logoPreview.value;
};

const onBaseDrop = (e) => {
    isDraggingBase.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) loadBaseImage(file);
};

const onBaseSelect = (e) => {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) loadBaseImage(file);
};

const onLogoDrop = (e) => {
    isDraggingLogo.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) loadLogoImage(file);
};

const onLogoSelect = (e) => {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) loadLogoImage(file);
};

const clearAll = () => {
    baseImage.value = null;
    baseImagePreview.value = '';
    baseImageElement.value = null;
    downloadUrl.value = '';
};

const drawPreview = () => {
    if (!baseImageElement.value || !canvasRef.value) return;

    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    
    canvas.width = baseImageElement.value.width;
    canvas.height = baseImageElement.value.height;
    
    // Draw base image
    ctx.drawImage(baseImageElement.value, 0, 0);

    // Common properties
    ctx.globalAlpha = opacity.value / 100;
    
    const cw = canvas.width;
    const ch = canvas.height;
    let x = 0;
    let y = 0;

    let padding = cw * 0.03; // 3% padding

    if (mode.value === 'text') {
        const fontSize = (cw * watermarkSize.value) / 100;
        ctx.font = `bold ${fontSize}px sans-serif`;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.9)'; // whiteish text
        // Adding a shadow for better visibility on white images
        ctx.shadowColor = "rgba(0, 0, 0, 0.8)";
        ctx.shadowBlur = Math.max(5, fontSize / 5);
        ctx.shadowOffsetX = Math.max(2, fontSize / 15);
        ctx.shadowOffsetY = Math.max(2, fontSize / 15);

        ctx.textBaseline = "middle";
        
        switch (position.value) {
            case 'top-left':
                ctx.textAlign = 'left';
                x = padding;
                y = padding + (fontSize / 2);
                break;
            case 'top-right':
                ctx.textAlign = 'right';
                x = cw - padding;
                y = padding + (fontSize / 2);
                break;
            case 'bottom-left':
                ctx.textAlign = 'left';
                x = padding;
                y = ch - padding - (fontSize / 2);
                break;
            case 'bottom-right':
                ctx.textAlign = 'right';
                x = cw - padding;
                y = ch - padding - (fontSize / 2);
                break;
            case 'middle':
            default:
                ctx.textAlign = 'center';
                x = cw / 2;
                y = ch / 2;
                break;
        }

        ctx.fillText(textWatermark.value, x, y);
    } else if (mode.value === 'image' && logoElement.value) {
        const lwOriginal = logoElement.value.width;
        const lhOriginal = logoElement.value.height;
        const lw = (cw * watermarkSize.value) / 100; 
        const lh = (lhOriginal / lwOriginal) * lw;

        switch (position.value) {
            case 'top-left':
                x = padding;
                y = padding;
                break;
            case 'top-right':
                x = cw - lw - padding;
                y = padding;
                break;
            case 'bottom-left':
                x = padding;
                y = ch - lh - padding;
                break;
            case 'bottom-right':
                x = cw - lw - padding;
                y = ch - lh - padding;
                break;
            case 'middle':
            default:
                x = (cw - lw) / 2;
                y = (ch - lh) / 2;
                break;
        }
        ctx.drawImage(logoElement.value, x, y, lw, lh);
    }

    downloadUrl.value = canvas.toDataURL('image/png');
};

watch([mode, textWatermark, position, opacity, watermarkSize], () => {
    if (baseImageElement.value) drawPreview();
});

const positions = [
    { id: 'top-left', icon: MoveUpLeft, label: 'Top Left' },
    { id: 'top-right', icon: MoveUpRight, label: 'Top Right' },
    { id: 'middle', icon: AlignCenter, label: 'Middle' },
    { id: 'bottom-left', icon: MoveDownLeft, label: 'Bottom Left' },
    { id: 'bottom-right', icon: MoveDownRight, label: 'Bottom Right' }
];

const downloadImage = () => {
    if (!downloadUrl.value) return;
    const a = document.createElement('a');
    a.href = downloadUrl.value;
    a.download = `watermarked_${baseImage.value ? baseImage.value.name : 'image.png'}`;
    a.click();
};
</script>

<template>
    <HomeLayout title="Watermark Image - JavaraDigital">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-start p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-emerald-500/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-5xl flex flex-col gap-10">
                <!-- Header -->
                <div class="text-center">
                    <div class="mx-auto mb-4 w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-black uppercase tracking-widest text-indigo-400">
                        <FileImage class="w-3 h-3" />
                        Protect Your Media - Let Everyone Know
                    </div>
                    <h1 class="text-white text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                        Watermark <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Image</span>
                    </h1>
                    <p class="text-zinc-500 text-xs mt-1 uppercase tracking-widest font-bold">
                        Add text or custom logo as a watermark securely and fast
                    </p>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Left Column / Preview -->
                    <div class="lg:col-span-8 flex flex-col gap-6">
                        <div v-if="!baseImage" 
                            @dragover.prevent="isDraggingBase = true"
                            @dragleave.prevent="isDraggingBase = false"
                            @drop.prevent="onBaseDrop"
                            class="w-full relative group p-1 bg-zinc-900 border border-zinc-800 rounded-[40px] shadow-2xl transition-all h-[500px] flex items-center justify-center"
                            :class="isDraggingBase ? 'ring-2 ring-indigo-500 bg-zinc-800' : 'hover:border-zinc-700'"
                        >
                            <label class="flex flex-col items-center justify-center w-full h-full cursor-pointer rounded-[38px] border-2 border-dashed border-zinc-800 group-hover:bg-zinc-950/50 transition-all">
                                <div class="p-5 rounded-full bg-indigo-500/10 text-indigo-400 mb-6 group-hover:scale-110 transition-transform">
                                    <Upload class="w-10 h-10" />
                                </div>
                                <div class="text-center px-4">
                                    <span class="text-white text-xl font-bold block mb-2">Upload Product Image</span>
                                    <span class="text-zinc-500 text-sm font-medium">Drop your base image here or click to browse</span>
                                </div>
                                <input type="file" accept="image/*" @change="onBaseSelect" class="hidden" />
                            </label>
                        </div>

                        <!-- Canvas Preview Wrapper -->
                        <div v-show="baseImage" class="w-full rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-2xl overflow-hidden p-4 relative flex flex-col pt-4 min-h-[500px]">
                             <div class="flex items-center justify-between px-4 pb-4 border-b border-zinc-800 mb-4">
                                <span class="text-white text-sm font-bold flex items-center gap-2">
                                    <LayoutGrid class="w-4 h-4 text-emerald-400" />
                                    Preview Result
                                </span>
                                <button @click="clearAll" class="text-zinc-500 hover:text-rose-400 p-1 bg-zinc-950 rounded-lg">
                                    <X class="w-4 h-4" />
                                </button>
                             </div>
                             <div class="w-full flex-grow relative bg-[url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAMUExURf///8zMzP///8zMzI2/xHAAAAAWSURBVBjTYwABQ1ABDEYVjFwMDGA1AAAh+AEE332DQQAAAABJRU5ErkJggg==')] rounded-2xl overflow-hidden shadow-inner flex items-center justify-center min-h-[300px]">
                                <canvas ref="canvasRef" class="max-w-full max-h-[600px] object-contain shadow-lg"></canvas>
                             </div>
                        </div>

                    </div>

                    <!-- Right Column / Controls -->
                    <div class="lg:col-span-4 space-y-4 lg:space-y-6" :class="{ 'opacity-50 pointer-events-none': !baseImage }">
                        
                        <!-- Mode Selector -->
                        <div class="p-6 rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-xl space-y-6">
                            <h3 class="text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <Settings class="w-4 h-4 text-indigo-400" />
                                Mode & Input
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-2 bg-zinc-950 p-1.5 rounded-2xl border border-zinc-800/50">
                                <button 
                                    @click="mode = 'text'" 
                                    class="py-3 px-4 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all"
                                    :class="mode === 'text' ? 'bg-indigo-500 text-white shadow-lg' : 'text-zinc-500 hover:text-white'"
                                >
                                    <Type class="w-4 h-4" /> Text
                                </button>
                                <button 
                                    @click="mode = 'image'" 
                                    class="py-3 px-4 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all"
                                    :class="mode === 'image' ? 'bg-indigo-500 text-white shadow-lg' : 'text-zinc-500 hover:text-white'"
                                >
                                    <ImageIcon class="w-4 h-4" /> Image
                                </button>
                            </div>

                            <!-- Text Mode Content -->
                            <div v-if="mode === 'text'" class="space-y-2 animate-in fade-in duration-300">
                                <label class="text-zinc-500 text-[10px] font-bold uppercase tracking-wider pl-1">Watermark Text</label>
                                <input 
                                    type="text" 
                                    v-model="textWatermark"
                                    class="w-full h-12 bg-zinc-950 border border-zinc-800 rounded-xl px-4 text-white text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all"
                                    placeholder="Enter your watermark text..."
                                />
                            </div>

                            <!-- Image Mode Content -->
                            <div v-if="mode === 'image'" class="space-y-4 animate-in fade-in duration-300">
                                <div v-if="!logoFile"
                                    @dragover.prevent="isDraggingLogo = true"
                                    @dragleave.prevent="isDraggingLogo = false"
                                    @drop.prevent="onLogoDrop"
                                    class="relative border-2 border-dashed border-zinc-800 rounded-2xl p-6 text-center cursor-pointer hover:bg-zinc-950/50 hover:border-zinc-700 transition-all"
                                    :class="isDraggingLogo ? 'border-indigo-500 bg-zinc-950' : ''"
                                >
                                    <input type="file" accept="image/*" @change="onLogoSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                    <ImageIcon class="w-8 h-8 text-indigo-400 mx-auto mb-3" />
                                    <p class="text-white text-sm font-bold">Select Logo</p>
                                    <p class="text-zinc-500 text-[10px] uppercase mt-1">PNG/Transparent recommended</p>
                                </div>
                                <div v-else class="flex items-center gap-4 bg-zinc-950 border border-zinc-800 p-3 rounded-2xl relative">
                                    <img :src="logoPreview" class="w-12 h-12 object-contain bg-zinc-900 rounded-xl max-w-full border border-zinc-800" />
                                    <div class="flex-grow overflow-hidden">
                                        <p class="text-white text-xs font-bold truncate">{{ logoFile.name }}</p>
                                    </div>
                                    <button @click="logoFile = null; logoElement = null; logoPreview = ''; drawPreview();" class="text-zinc-500 hover:text-rose-500 p-2 shrink-0 bg-zinc-900 rounded-lg">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Position Selector -->
                        <div class="p-6 rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-xl space-y-5">
                            <h3 class="text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <LayoutGrid class="w-4 h-4 text-emerald-400" />
                                Position
                            </h3>
                            <div class="grid grid-cols-3 grid-rows-3 gap-2 w-full aspect-square bg-zinc-950 p-2 rounded-2xl relative border border-zinc-800/50 max-h-[160px] mx-auto">
                                <button 
                                    v-for="pos in positions" 
                                    :key="pos.id"
                                    @click="position = pos.id"
                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl transition-all h-full w-full"
                                    :class="[
                                        position === pos.id ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/50 shadow-inner' : 'text-zinc-600 hover:text-white hover:bg-zinc-900 border border-transparent',
                                        pos.id === 'middle' ? 'col-start-2 row-start-2' : '',
                                        pos.id === 'top-left' ? 'col-start-1 row-start-1' : '',
                                        pos.id === 'top-right' ? 'col-start-3 row-start-1' : '',
                                        pos.id === 'bottom-left' ? 'col-start-1 row-start-3' : '',
                                        pos.id === 'bottom-right' ? 'col-start-3 row-start-3' : ''
                                    ]"
                                >
                                    <component :is="pos.icon" class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Opacity & Size Adjustments -->
                        <div class="p-6 rounded-[32px] bg-zinc-900 border border-zinc-800 shadow-xl space-y-6">
                            <!-- Opacity -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-zinc-400 text-[10px] font-bold uppercase tracking-widest">Opacity</h3>
                                    <span class="text-white text-xs font-black">{{ opacity }}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    v-model.number="opacity" 
                                    min="5" 
                                    max="100" 
                                    class="w-full h-1.5 bg-zinc-950 rounded-lg appearance-none cursor-pointer accent-indigo-500"
                                />
                            </div>

                            <!-- Size -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-zinc-400 text-[10px] font-bold uppercase tracking-widest">Scale Size</h3>
                                    <span class="text-white text-xs font-black">{{ watermarkSize }}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    v-model.number="watermarkSize" 
                                    min="5" 
                                    max="50" 
                                    class="w-full h-1.5 bg-zinc-950 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                                />
                            </div>
                        </div>

                        <!-- Download Button -->
                        <button 
                            @click="downloadImage"
                            :disabled="!downloadUrl"
                            class="w-full px-6 py-5 rounded-[24px] bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-50 flex items-center justify-center gap-3 disabled:cursor-not-allowed"
                        >
                            <Download class="w-5 h-5" />
                            Download Ready Image
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
</style>