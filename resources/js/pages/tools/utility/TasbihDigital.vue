<script setup>
import { ref, watch, onMounted } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    RotateCcw, 
    RefreshCw, 
    Volume2, 
    VolumeX, 
    Smartphone, 
    Palette, 
    Moon, 
    Sun,
    Zap
} from 'lucide-vue-next';

// State
const count = ref(0);
const reminderLimit = ref(33); // Normal limit is 33 or 99
const soundEnabled = ref(true);
const vibrationEnabled = ref(true);
const isNightMode = ref(true);

// Persistence
onMounted(() => {
    const savedCount = localStorage.getItem('tasbih_count');
    if (savedCount !== null) {
        count.value = parseInt(savedCount, 10);
    }
});

watch(count, (newVal) => {
    localStorage.setItem('tasbih_count', newVal.toString());
});

// Handlers
const increment = () => {
    count.value++;
    provideFeedback();
};

const reset = () => {
    if (confirm('Reset Tasbih?')) {
        count.value = 0;
        provideFeedback(true);
    }
};

const toggleMode = () => {
    // Cycle through standard limits: 33 -> 99 -> 100 -> 33
    if (reminderLimit.value === 33) reminderLimit.value = 99;
    else if (reminderLimit.value === 99) reminderLimit.value = 100;
    else if (reminderLimit.value === 100) reminderLimit.value = 33;
};

const provideFeedback = (isReset = false) => {
    // Vibration
    if (vibrationEnabled.value && navigator.vibrate) {
        navigator.vibrate(isReset ? [50, 50, 50] : 15);
    }

    // Sound (Simple Beep)
    if (soundEnabled.value) {
        playBeep(isReset ? 440 : 880, 0.05);
    }

    // Special reminder feedback
    if (!isReset && count.value > 0 && count.value % reminderLimit.value === 0) {
        if (navigator.vibrate) navigator.vibrate([100, 100, 100]);
        playBeep(1200, 0.2); // Longer beep for limit reached
    }
};

const playBeep = (freq, duration) => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + duration);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + duration);
    } catch (e) {
        console.warn('Audio context error:', e);
    }
};
</script>

<template>
    <HomeLayout title="Tasbih Digital">
        <!-- Main Background matching javaradigital theme -->
        <div class="min-h-[80vh] bg-zinc-950 flex flex-col items-center justify-center p-4 sm:p-8 font-sans transition-all duration-700 relative overflow-hidden">
            
            <!-- Background Decoration -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-purple-500/10 blur-[120px] rounded-full"></div>
            </div>

            <!-- Content Area -->
            <div class="z-10 flex flex-col items-center w-full max-w-lg">
                <!-- Centered Header Area -->
                <div class="text-center mb-10 w-full">
                    <div class="mx-auto mb-4 w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-bold uppercase tracking-widest text-indigo-400">
                        <Zap class="w-3 h-3" />
                        Spiritual Utility
                    </div>
                    <h1 class="text-white text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Tasbih <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Digital</span>
                    </h1>
                    <p class="text-zinc-500 text-xs mt-2 uppercase tracking-[0.2em] font-medium">
                        Target Limit: <span class="text-indigo-400 font-bold ml-1">{{ reminderLimit }}</span>
                    </p>
                </div>

                <!-- Handheld Device Shape - Premium Style -->
                <div class="relative w-72 h-[420px] bg-zinc-900 rounded-[50px] shadow-2xl flex flex-col items-center p-8 border border-zinc-800/50 group">
                    
                    <!-- Internal Glass Effect -->
                    <div class="absolute inset-0 rounded-[49px] bg-gradient-to-b from-white/[0.03] to-transparent pointer-events-none"></div>

                    <!-- LCD Display Container -->
                    <div class="w-full mt-4 h-24 bg-zinc-950 rounded-2xl border-2 border-zinc-800 shadow-[0_0_20px_rgba(0,0,0,0.5)] flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-500/5 transition-opacity" :style="{ opacity: count % 10 === 0 ? 0.2 : 0.05 }"></div>
                        
                        <!-- The Count with the requested Digital font -->
                        <div class="font-digital text-indigo-500 text-6xl tracking-widest drop-shadow-[0_0_8px_rgba(99,102,241,0.5)] select-none">
                            {{ count.toString().padStart(5, '0') }}
                        </div>
                    </div>

                    <!-- Secondary Controls -->
                    <div class="w-full mt-10 flex justify-between items-center px-4">
                        <!-- Reset Button -->
                        <button 
                            @click="reset"
                            title="Reset"
                            class="w-12 h-12 rounded-full border border-zinc-800 bg-zinc-900/50 flex items-center justify-center text-zinc-500 hover:text-red-400 hover:border-red-500/20 transition-all shadow-lg active:scale-90"
                        >
                            <RotateCcw class="w-5 h-5" />
                        </button>

                        <!-- Cycle Mode Button -->
                        <button 
                            @click="toggleMode"
                            title="Set limit"
                            class="w-12 h-12 rounded-full border border-zinc-800 bg-zinc-900/50 flex items-center justify-center text-zinc-500 hover:text-indigo-400 hover:border-indigo-500/20 transition-all shadow-lg active:scale-90"
                        >
                            <RefreshCw class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Main Action Area -->
                    <div class="mt-auto mb-4 relative flex flex-col items-center">
                        <!-- Ripple Effect Placeholder -->
                        <div v-if="count > 0" class="absolute inset-0 -m-4 bg-indigo-500/10 rounded-full animate-ping pointer-events-none opacity-20"></div>
                        
                        <!-- Main Counting Button -->
                        <button 
                            @click="increment"
                            class="w-32 h-32 rounded-full border-[10px] border-zinc-950 bg-indigo-600 flex items-center justify-center active:bg-indigo-700 transition-all shadow-[0_8px_0_#312e81,0_15px_30px_rgba(0,0,0,0.6)] active:translate-y-1 active:shadow-[0_2px_0_#312e81,0_5px_10px_rgba(0,0,0,0.4)] relative z-20 group"
                        >
                            <!-- Subtle finger print or icon could go here -->
                             <Zap class="w-8 h-8 text-white/50 group-hover:scale-110 transition-transform" />
                        </button>
                    </div>
                </div>

                <!-- Footer Tools Panel -->
                <div class="mt-12 flex items-center gap-2 p-1.5 rounded-2xl bg-zinc-900/50 border border-zinc-800/50 backdrop-blur-sm">
                    <button 
                        @click="soundEnabled = !soundEnabled"
                        class="p-3 rounded-xl hover:bg-zinc-800 transition-colors"
                        :class="soundEnabled ? 'text-indigo-400' : 'text-zinc-600'"
                        title="Toggle Sound"
                    >
                        <Volume2 v-if="soundEnabled" class="w-5 h-5" />
                        <VolumeX v-else class="w-5 h-5" />
                    </button>
                    
                    <button 
                        @click="vibrationEnabled = !vibrationEnabled"
                        class="p-3 rounded-xl hover:bg-zinc-800 transition-colors"
                        :class="vibrationEnabled ? 'text-indigo-400' : 'text-zinc-600'"
                        title="Toggle Vibration"
                    >
                        <Smartphone class="w-5 h-5" />
                    </button>

                    <div class="w-px h-6 bg-zinc-800 mx-1"></div>

                    <button class="p-3 rounded-xl hover:bg-zinc-800 transition-colors text-zinc-600 hover:text-indigo-300">
                        <Palette class="w-5 h-5" />
                    </button>

                    <button 
                        @click="isNightMode = !isNightMode"
                        class="p-3 rounded-xl hover:bg-zinc-800 transition-colors text-zinc-600 hover:text-yellow-500"
                        title="Toggle Day/Night"
                    >
                        <Moon v-if="isNightMode" class="w-5 h-5" />
                        <Sun v-else class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
@import url('https://fonts.cdnfonts.com/css/digital');

.font-digital {
    font-family: 'Digital', sans-serif;
}

/* Texture or subtle lighting for the device */
.shadow-2xl {
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.7);
}

/* Center piece refinement */
button {
    -webkit-tap-highlight-color: transparent;
}
</style>