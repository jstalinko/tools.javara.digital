<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Play, 
    Pause, 
    RotateCcw, 
    Maximize, 
    Minimize, 
    Settings, 
    Trophy,
    ChevronUp,
    ChevronDown,
    Save,
    Trash2,
    Check,
    X,
    Dribbble,
    Wind,
    Target
} from 'lucide-vue-next';

// Presets
const sports = [
    { id: 'football', name: 'Football', icon: Dribbble, defaultTime: 0, countUp: true },
    { id: 'futsal', name: 'Futsal', icon: Dribbble, defaultTime: 1200, countUp: false },
    { id: 'badminton', name: 'Badminton', icon: Wind, defaultTime: 0, countUp: true, points: 21 },
    { id: 'custom', name: 'Custom', icon: Target, defaultTime: 0, countUp: true },
];

// State
const homeScore = ref(0);
const awayScore = ref(0);
const homeName = ref('HOME');
const awayName = ref('AWAY');
const homeSets = ref(0);
const awaySets = ref(0);

const activeSport = ref(sports[0]);
const timerSeconds = ref(activeSport.value.defaultTime);
const isRunning = ref(false);
const isFullscreen = ref(false);
const showSettings = ref(false);

let intervalId = null;

// Persistence
onMounted(() => {
    const saved = localStorage.getItem('score_board_state');
    if (saved) {
        const data = JSON.parse(saved);
        homeScore.value = data.homeScore || 0;
        awayScore.value = data.awayScore || 0;
        homeName.value = data.homeName || 'HOME';
        awayName.value = data.awayName || 'AWAY';
        homeSets.value = data.homeSets || 0;
        awaySets.value = data.awaySets || 0;
    }

    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});

watch([homeScore, awayScore, homeName, awayName, homeSets, awaySets], () => {
    localStorage.setItem('score_board_state', JSON.stringify({
        homeScore: homeScore.value,
        awayScore: awayScore.value,
        homeName: homeName.value,
        awayName: awayName.value,
        homeSets: homeSets.value,
        awaySets: awaySets.value
    }));
});

// Timer Logic
const formatTime = (seconds) => {
    const mins = Math.floor(Math.abs(seconds) / 60);
    const secs = Math.abs(seconds) % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const startTimer = () => {
    if (isRunning.value) return;
    isRunning.value = true;
    intervalId = setInterval(() => {
        if (activeSport.value.countUp) {
            timerSeconds.value++;
        } else {
            if (timerSeconds.value > 0) {
                timerSeconds.value--;
            } else {
                pauseTimer();
                playBuzzer();
            }
        }
    }, 1000);
};

const pauseTimer = () => {
    isRunning.value = false;
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

const resetTimer = () => {
    pauseTimer();
    timerSeconds.value = activeSport.value.defaultTime;
};

const resetAll = () => {
    if (confirm('Reset scores and timer?')) {
        homeScore.value = 0;
        awayScore.value = 0;
        homeSets.value = 0;
        awaySets.value = 0;
        resetTimer();
    }
};

const playBuzzer = () => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.type = 'sawtooth';
        oscillator.frequency.setValueAtTime(100, audioCtx.currentTime);
        gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 1.5);
    } catch (e) {
        console.warn('Audio buzzer error:', e);
    }
};

// Fullscreen Logic
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.error(`Error attempting to enable full-screen mode: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
};

// Sport Preset Handlers
const selectSport = (sport) => {
    activeSport.value = sport;
    resetTimer();
    showSettings.value = false;
};

onUnmounted(() => {
    pauseTimer();
});

</script>

<template>
    <HomeLayout title="Papan Skor Digital">
        <div class="min-h-[90vh] bg-zinc-950 flex flex-col text-white transition-all duration-500 overflow-hidden relative">
            
            <!-- Navbar / Controls Bar -->
            <div v-if="!isFullscreen" class="p-4 border-b border-zinc-900 bg-zinc-950/50 backdrop-blur-xl flex justify-between items-center z-20">
                <div class="flex gap-4">
                    <button 
                        v-for="sport in sports" 
                        :key="sport.id"
                        @click="selectSport(sport)"
                        class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition-all flex items-center gap-2"
                        :class="activeSport.id === sport.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-zinc-900 text-zinc-500 hover:text-zinc-300'"
                    >
                        <component :is="sport.icon" class="w-3.5 h-3.5" />
                        {{ sport.name }}
                    </button>
                </div>

                <div class="flex gap-2">
                    <button @click="resetAll" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-500 hover:text-red-400 transition-colors" title="Reset All">
                        <RotateCcw class="w-5 h-5" />
                    </button>
                    <button @click="toggleFullscreen" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-500 hover:text-indigo-400 transition-colors">
                        <Maximize v-if="!isFullscreen" class="w-5 h-5" />
                        <Minimize v-else class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Main Arena -->
            <div class="flex-grow flex flex-col lg:flex-row p-4 lg:p-8 gap-4 lg:gap-8 relative z-10 items-stretch">
                
                <!-- Home Team -->
                <div class="flex-1 flex flex-col items-center justify-center rounded-[40px] border border-blue-500/10 bg-zinc-900/40 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <input 
                        v-model="homeName"
                        class="bg-transparent text-center text-2xl lg:text-4xl font-extrabold uppercase tracking-widest text-blue-400 border-none focus:outline-none w-full mb-4 z-10"
                    />
                    
                    <div class="relative flex flex-col items-center">
                        <div class="font-digital text-[150px] lg:text-[250px] leading-none text-blue-500 drop-shadow-[0_0_20px_rgba(59,130,246,0.3)] select-none">
                            {{ homeScore }}
                        </div>
                        
                        <div v-if="activeSport.id === 'badminton'" class="mt-4 flex gap-4 z-20">
                            <button @click="homeSets > 0 ? homeSets-- : null" class="px-3 py-1 bg-zinc-800 rounded-lg text-xs font-bold text-zinc-500 hover:bg-zinc-700 transition-colors">-</button>
                            <span class="text-xl font-bold text-blue-300">Sets: {{ homeSets }}</span>
                            <button @click="homeSets++" class="px-3 py-1 bg-zinc-800 rounded-lg text-xs font-bold text-zinc-500 hover:bg-zinc-700 transition-colors">+</button>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8 z-20 opacity-0 group-hover:opacity-100 transition-all duration-300 scale-90">
                        <button @click="homeScore--" class="w-12 h-12 rounded-full bg-zinc-900 border border-zinc-700 flex items-center justify-center text-xl font-bold hover:bg-zinc-800 active:scale-95 transition-all text-zinc-500">-</button>
                        <button @click="homeScore++" class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold hover:bg-blue-500 active:scale-95 transition-all shadow-[0_4px_0_#1e40af,0_8px_15px_rgba(0,0,0,0.4)] active:translate-y-1 active:shadow-[0_1px_0_#1e40af]">+</button>
                    </div>
                </div>

                <!-- Center: Timer & Controls -->
                <div class="w-full lg:w-[400px] flex flex-col items-center justify-center gap-8 py-8">
                    
                    <!-- Timer Display -->
                    <div class="relative flex flex-col items-center group cursor-pointer" @click="isRunning ? pauseTimer() : startTimer()">
                        <div class="text-xs font-bold uppercase tracking-[0.4em] text-zinc-500 mb-4">{{ isRunning ? 'In Progress' : 'Paused' }}</div>
                        <div class="font-digital text-7xl lg:text-9xl text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]">
                            {{ formatTime(timerSeconds) }}
                        </div>
                        <div class="mt-8 flex gap-4 items-center">
                            <button 
                                @click.stop="isRunning ? pauseTimer() : startTimer()"
                                class="w-16 h-16 rounded-full flex items-center justify-center transition-all bg-zinc-900 border border-zinc-800 group-hover:border-indigo-500/50"
                            >
                                <Pause v-if="isRunning" class="w-6 h-6 text-white" />
                                <Play v-else class="w-6 h-6 text-indigo-400" />
                            </button>
                            <button @click.stop="resetTimer" class="p-3 bg-zinc-900 border border-zinc-800 text-zinc-500 hover:text-white rounded-xl transition-all">
                                <RotateCcw class="w-5 h-5" />
                            </button>
                            <button @click.stop="isFullscreen = !isFullscreen; toggleFullscreen()" class="p-3 bg-zinc-900 border border-zinc-800 text-zinc-500 hover:text-white rounded-xl lg:hidden">
                                <Maximize class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- VS Banner -->
                    <div class="px-6 py-2 rounded-full border border-zinc-800 bg-zinc-900/50 text-zinc-500 font-bold tracking-[.3em] text-xs">
                        VERSUS
                    </div>

                    <!-- Additional Context info -->
                     <div class="text-center">
                        <div class="text-[10px] text-zinc-700 font-mono uppercase tracking-[.2em] mb-2">Preset: {{ activeSport.name }}</div>
                        <div class="flex gap-2">
                             <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                             <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse delay-75"></div>
                        </div>
                     </div>
                </div>

                <!-- Away Team -->
                <div class="flex-1 flex flex-col items-center justify-center rounded-[40px] border border-rose-500/10 bg-zinc-900/40 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <input 
                        v-model="awayName"
                        class="bg-transparent text-center text-2xl lg:text-4xl font-extrabold uppercase tracking-widest text-rose-400 border-none focus:outline-none w-full mb-4 z-10"
                    />
                    
                    <div class="relative flex flex-col items-center">
                        <div class="font-digital text-[150px] lg:text-[250px] leading-none text-rose-500 drop-shadow-[0_0_20px_rgba(244,63,94,0.3)] select-none">
                            {{ awayScore }}
                        </div>

                         <div v-if="activeSport.id === 'badminton'" class="mt-4 flex gap-4 z-20">
                            <button @click="awaySets > 0 ? awaySets-- : null" class="px-3 py-1 bg-zinc-800 rounded-lg text-xs font-bold text-zinc-500 hover:bg-zinc-700 transition-colors">-</button>
                            <span class="text-xl font-bold text-rose-300">Sets: {{ awaySets }}</span>
                            <button @click="awaySets++" class="px-3 py-1 bg-zinc-800 rounded-lg text-xs font-bold text-zinc-500 hover:bg-zinc-700 transition-colors">+</button>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8 z-10 opacity-0 group-hover:opacity-100 transition-all duration-300 scale-90">
                        <button @click="awayScore--" class="w-12 h-12 rounded-full bg-zinc-900 border border-zinc-700 flex items-center justify-center text-xl font-bold hover:bg-zinc-800 active:scale-95 transition-all text-zinc-500">-</button>
                        <button @click="awayScore++" class="w-16 h-16 rounded-full bg-rose-600 flex items-center justify-center text-2xl font-bold hover:bg-rose-500 active:scale-95 transition-all shadow-[0_4px_0_#9f1239,0_8px_15px_rgba(0,0,0,0.4)] active:translate-y-1 active:shadow-[0_1px_0_#9f1239]">+</button>
                    </div>
                </div>

            </div>

            <!-- Fullscreen Exit Instruction Overlay -->
            <div v-if="isFullscreen" class="absolute top-4 left-1/2 -translate-x-1/2 opacity-0 hover:opacity-100 transition-opacity duration-500">
                <button @click="toggleFullscreen" class="px-4 py-2 bg-zinc-900/50 border border-white/10 rounded-full text-xs text-white/50 hover:text-white flex items-center gap-2">
                    <Minimize class="w-4 h-4" /> Exit Fullscreen (ESC)
                </button>
            </div>
            
            <!-- Logo overlay in Fullscreen -->
            <div v-if="isFullscreen" class="absolute bottom-6 right-8 text-zinc-800 font-bold opacity-30 select-none">
                javaradigital
            </div>

        </div>
    </HomeLayout>
</template>

<style scoped>
@import url('https://fonts.cdnfonts.com/css/digital');

.font-digital {
    font-family: 'Digital', sans-serif;
}

/* Tabular nums for consistent width */
.font-digital, .font-mono {
    font-variant-numeric: tabular-nums;
}

/* Hide scrollbars */
::-webkit-scrollbar {
  display: none;
}

/* Animations */
@keyframes pulse {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 0.3; }
}

input::selection {
    background: rgba(99, 102, 241, 0.5);
}
</style>