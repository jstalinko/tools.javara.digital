<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Dices, 
    Volume2, 
    VolumeX, 
    History,
    RotateCcw,
    Zap,
    Trash2
} from 'lucide-vue-next';

// State
const diceCount = ref(2);
const results = ref([1, 1]);
const isShaking = ref(false);
const soundEnabled = ref(true);
const history = ref([]);
const showHistory = ref(false);

// Audio Engine (Procedural)
const playRollSound = () => {
    if (!soundEnabled.value) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.type = 'square';
        oscillator.frequency.setValueAtTime(Math.random() * 100 + 50, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.05, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.1);
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.1);
    } catch (e) {}
};

const playLandSound = () => {
    if (!soundEnabled.value) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(200, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.3);
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.3);
    } catch (e) {}
};

// Map results to CSS 3D Cube rotations
const getDiceRotation = (val) => {
    switch (val) {
        case 1: return 'rotateX(0deg) rotateY(0deg)';
        case 2: return 'rotateX(-90deg) rotateY(0deg)';
        case 3: return 'rotateX(0deg) rotateY(-90deg)';
        case 4: return 'rotateX(0deg) rotateY(90deg)';
        case 5: return 'rotateX(90deg) rotateY(0deg)';
        case 6: return 'rotateX(180deg) rotateY(0deg)';
        default: return 'rotateX(0deg) rotateY(0deg)';
    }
};

// Roll Logic
const roll = () => {
    if (isShaking.value) return;
    
    isShaking.value = true;
    let shakes = 0;
    const maxShakes = 12;
    
    const interval = setInterval(() => {
        results.value = Array.from({ length: diceCount.value }, () => Math.floor(Math.random() * 6) + 1);
        playRollSound();
        shakes++;
        
        if (shakes >= maxShakes) {
            clearInterval(interval);
            isShaking.value = false;
            playLandSound();
            addToHistory(results.value);
        }
    }, 150);
};

const addToHistory = (res) => {
    history.value.unshift({
        id: Date.now(),
        values: [...res],
        total: res.reduce((a, b) => a + b, 0),
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    });
    if (history.value.length > 10) history.value.pop();
    localStorage.setItem('dice_history', JSON.stringify(history.value));
};

onMounted(() => {
    const saved = localStorage.getItem('dice_history');
    if (saved) history.value = JSON.parse(saved);
});

const total = computed(() => results.value.reduce((a, b) => a + b, 0));

const resetHistory = () => {
    history.value = [];
    localStorage.removeItem('dice_history');
};
</script>

<template>
    <HomeLayout title="Kocok Dadu 3D">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-center p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] bg-rose-500/5 blur-[120px] rounded-full animate-pulse delay-500"></div>
            </div>

            <div class="z-10 w-full max-w-4xl flex flex-col items-center">
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="mx-auto mb-4 w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-[10px] font-bold uppercase tracking-widest text-rose-400">
                        <Zap class="w-3 h-3" />
                        Probability Tool
                    </div>
                    <h1 class="text-white text-4xl font-extrabold tracking-tight sm:text-5xl">
                        Kocok <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 via-indigo-400 to-purple-400">Dadu 3D</span>
                    </h1>
                </div>

                <!-- Settings Controls -->
                <div class="flex flex-wrap items-center justify-center gap-4 mb-16 p-2 rounded-2xl bg-zinc-900 border border-zinc-800 shadow-xl">
                    <div class="flex bg-zinc-950 p-1 rounded-xl border border-zinc-800">
                        <button 
                            v-for="n in [1, 2, 3]" 
                            :key="n"
                            @click="diceCount = n; results = Array(n).fill(1)"
                            class="px-5 py-1.5 rounded-lg text-xs font-bold transition-all uppercase tracking-wider"
                            :class="diceCount === n ? 'bg-indigo-600 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                        >
                            {{ n }} {{ n === 1 ? 'Die' : 'Dice' }}
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button @click="soundEnabled = !soundEnabled" class="p-2.5 rounded-xl border border-zinc-800 bg-zinc-950 text-zinc-500">
                            <Volume2 v-if="soundEnabled" class="w-5 h-5" />
                            <VolumeX v-else class="w-5 h-5" />
                        </button>
                        <button @click="showHistory = !showHistory" class="p-2.5 rounded-xl border border-zinc-800 bg-zinc-950 text-zinc-500">
                            <History class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Dice Arena (The Table) -->
                <div class="relative w-full py-20 flex flex-col items-center">
                    
                    <!-- 3D Scene -->
                    <div class="flex flex-wrap justify-center gap-16 lg:gap-24 mb-16 perspective-1200 pb-12">
                        <div 
                            v-for="(val, index) in results" 
                            :key="index"
                            class="die-wrapper"
                            :class="{ 'shaking-3d': isShaking }"
                        >
                            <div 
                                class="die-cube transition-transform duration-500"
                                :style="{ transform: isShaking ? '' : getDiceRotation(val) }"
                            >
                                <!-- Face 1 (Front) -->
                                <div class="face f1"> <div class="pip center"></div> </div>
                                <!-- Face 6 (Back) -->
                                <div class="face f6">
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div><div class="pip"></div></div>
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div><div class="pip"></div></div>
                                </div>
                                <!-- Face 2 (Bottom) -->
                                <div class="face f2"> <div class="pip tl"></div> <div class="pip br"></div> </div>
                                <!-- Face 5 (Top) -->
                                <div class="face f5">
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div></div>
                                    <div class="pip center"></div>
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div></div>
                                </div>
                                <!-- Face 3 (Left) -->
                                <div class="face f3"> <div class="pip tl"></div> <div class="pip center"></div> <div class="pip br"></div> </div>
                                <!-- Face 4 (Right) -->
                                <div class="face f4">
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div></div>
                                    <div class="pip-column"><div class="pip"></div><div class="pip"></div></div>
                                </div>
                            </div>
                            <!-- Shadow underneath -->
                            <div class="floor-shadow shadow-indigo-500/20" :class="{ 'expanding': isShaking }"></div>
                        </div>
                    </div>

                    <!-- Roll Action & Result -->
                    <div class="relative flex flex-col items-center gap-10">
                         <button 
                            @click="roll"
                            :disabled="isShaking"
                            class="w-32 h-32 rounded-full border-[10px] border-zinc-950 bg-indigo-600 flex flex-col items-center justify-center active:bg-indigo-700 transition-all shadow-[0_8px_0_#312e81,0_15px_30px_rgba(0,0,0,0.6)] active:translate-y-1 active:shadow-[0_1px_0_#312e81] relative z-20 group disabled:opacity-50"
                        >
                            <Dices class="w-8 h-8 text-white mb-1 group-hover:rotate-12 transition-transform" />
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/80">Kocok</span>
                        </button>

                        <div class="h-12 flex items-center justify-center">
                            <div v-if="!isShaking" class="flex items-center gap-3 animate-in zoom-in-50 duration-500">
                                 <span class="text-zinc-600 text-[10px] font-bold uppercase tracking-[.4em]">Total:</span>
                                 <span class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white to-zinc-600 font-mono tracking-tighter">{{ total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Slider -->
                <transition name="history-slide">
                    <div v-if="showHistory" class="fixed inset-y-0 right-0 w-80 bg-zinc-900/98 backdrop-blur-xl border-l border-white/5 z-50 p-6 flex flex-col shadow-2xl">
                        <div class="flex items-center justify-between mb-8">
                             <h3 class="text-white font-bold flex items-center gap-2">History</h3>
                             <button @click="showHistory = false" class="text-zinc-500 hover:text-white transition-colors">Close</button>
                        </div>
                        <div class="flex-grow overflow-y-auto space-y-3 pr-2">
                             <div v-for="item in history" :key="item.id" class="p-4 rounded-xl bg-zinc-950 border border-white/5 flex items-center justify-between">
                                  <div class="flex gap-1.5 font-bold text-xs text-indigo-400">
                                      {{ item.values.join(' • ') }}
                                  </div>
                                  <div class="text-right">
                                      <div class="text-xl font-black text-white">{{ item.total }}</div>
                                      <div class="text-[9px] text-zinc-600 uppercase">{{ item.time }}</div>
                                  </div>
                             </div>
                        </div>
                        <button v-if="history.length > 0" @click="resetHistory" class="mt-6 w-full py-2.5 rounded-xl border border-red-500/20 text-red-400 text-xs font-bold hover:bg-red-500/10 transition-all">Clear History</button>
                    </div>
                </transition>

            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.perspective-1200 { perspective: 1200px; }

.die-wrapper {
    width: 100px;
    height: 100px;
    position: relative;
    transform-style: preserve-3d;
}

.die-cube {
    width: 64px;
    height: 64px;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -32px;
    margin-left: -32px;
    transform-style: preserve-3d;
}

.die-cube .face {
    position: absolute;
    width: 100%;
    height: 100%;
    background: #efefef;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: flex;
    padding: 6px;
    box-sizing: border-box;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
}

/* 3D Translation to form a cube */
.f1 { transform: rotateY(0deg) translateZ(32px); }
.f6 { transform: rotateY(180deg) translateZ(32px); }
.f3 { transform: rotateY(-90deg) translateZ(32px); }
.f4 { transform: rotateY(90deg) translateZ(32px); }
.f5 { transform: rotateX(90deg) translateZ(32px); }
.f2 { transform: rotateX(-90deg) translateZ(32px); }

/* Pips Styling */
.pip { width: 12px; height: 12px; border-radius: 50%; background: #1a1a1a; margin: 1px; }
.face { justify-content: center; align-items: center; }
.pip-column { display: flex; flex-direction: column; justify-content: space-between; height: 100%; }
.tl { align-self: flex-start; }
.br { align-self: flex-end; }
.center { align-self: center; }

/* 3D Chaotic Shake Animation */
@keyframes shake-3d {
    0% { transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg) translateZ(10px); }
    25% { transform: rotateX(180deg) rotateY(90deg) rotateZ(45deg) translateZ(30px) translateY(-10px); }
    50% { transform: rotateX(360deg) rotateY(270deg) rotateZ(180deg) translateZ(10px) translateY(10px); }
    75% { transform: rotateX(540deg) rotateY(450deg) rotateZ(270deg) translateZ(30px) translateY(-5px); }
    100% { transform: rotateX(720deg) rotateY(720deg) rotateZ(360deg) translateZ(10px); }
}

.shaking-3d .die-cube {
    animation: shake-3d 0.6s linear infinite;
}

.floor-shadow {
    position: absolute;
    bottom: -15px;
    left: 50%;
    width: 50px;
    height: 10px;
    background: rgba(0,0,0,0.4);
    border-radius: 50%;
    transform: translateX(-50%);
    filter: blur(4px);
    transition: all 0.3s;
}

.expanding {
    width: 70px;
    opacity: 0.2;
    filter: blur(10px);
}

.history-slide-enter-active, .history-slide-leave-active {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.history-slide-enter-from, .history-slide-leave-to {
    transform: translateX(100%);
}
</style>