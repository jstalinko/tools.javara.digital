<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { 
    Timer, 
    Type, 
    RotateCcw, 
    Trophy, 
    Zap, 
    Check, 
    AlertCircle,
    ChevronRight,
    Languages
} from 'lucide-vue-next';

// Highly accurate curated Indonesian Word Pool (Top 300+ common words)
const CURATED_WORDS = [
    "saya", "anda", "kita", "mereka", "dengan", "adalah", "untuk", "dalam", "bahwa", "tidak",
    "pada", "yang", "telah", "dari", "akan", "bisa", "ada", "dan", "ini", "itu", "sangat",
    "lebih", "baru", "sudah", "sedang", "harus", "ingin", "perlu", "masih", "belum", "kapan",
    "siapa", "mengapa", "bagaimana", "dimana", "kemana", "dari", "mana", "lagi", "saja", "atau",
    "jika", "maka", "tetapi", "namun", "seperti", "hanya", "serta", "karena", "meskipun", "sehingga",
    "supaya", "agar", "demi", "bagi", "tentang", "menurut", "sesuai", "bersama", "terus", "langsung",
    "segera", "kemudian", "setelah", "sebelum", "ketika", "saat", "waktu", "hari", "bulan", "tahun",
    "orang", "rumah", "jalan", "kota", "negara", "dunia", "hidup", "mati", "makan", "minum",
    "tidur", "duduk", "berdiri", "jalan", "lari", "lompat", "baca", "tulis", "dengar", "lihat",
    "rasa", "bau", "pegang", "ambil", "beri", "bawa", "simpan", "buang", "cari", "temukan",
    "buka", "tutup", "nyala", "mati", "besar", "kecil", "tinggi", "rendah", "panjang", "pendek",
    "berat", "ringan", "keras", "lunak", "panas", "dingin", "basah", "kering", "terang", "gelap",
    "bersih", "kotor", "baru", "lama", "cepat", "lambat", "baik", "buruk", "benar", "salah",
    "kaya", "miskin", "kuat", "lemah", "sehat", "sakit", "senang", "sedih", "marah", "takut",
    "malu", "kaget", "benci", "cinta", "kangen", "ingat", "lupa", "tahu", "mengerti", "paham",
    "pikir", "percaya", "yakin", "mungkin", "pasti", "hampir", "semua", "setiap", "beberapa", "sedikit",
    "banyak", "pertama", "kedua", "ketiga", "terakhir", "pagi", "siang", "sore", "malam", "fajar",
    "senja", "kemarin", "besok", "lusa", "minggu", "senin", "selasa", "rabu", "kamis", "jumat",
    "sabtu", "ahad", "kepala", "rambut", "mata", "telinga", "hidung", "mulut", "gigi", "lidah",
    "leher", "bahu", "tangan", "jari", "kaki", "perut", "dada", "punggung", "kulit", "darah",
    "tulang", "otak", "hati", "paru", "jantung", "ayah", "ibu", "kakak", "adik", "paman",
    "bibi", "nenek", "kakek", "anak", "sepupu", "teman", "guru", "murid", "dokter", "polisi",
    "tentara", "petani", "nelayan", "pedagang", "supir", "pilot", "atlet", "penulis", "pelukis", "musisi",
    "buku", "pena", "pensil", "kertas", "meja", "kursi", "lampu", "pintu", "jendela", "dinding",
    "atap", "lantai", "kunci", "tas", "dompet", "uang", "hp", "laptop", "baju", "celana",
    "sepatu", "kaos", "topi", "kacamata", "jam", "cincin", "kalung", "gelang", "anting", "sisir",
    "sikat", "sabun", "sampo", "odol", "handuk", "piring", "gelas", "sendok", "garpu", "pisau",
    "wajan", "panci", "kompor", "kulkas", "meja", "lemari", "kasur", "bantal", "guling", "selimut"
];

// State
const timer = ref(60);
const initialTime = 60;
const isStarted = ref(false);
const isFinished = ref(false);
const words = ref([]);
const currentWordIndex = ref(0);
const userInput = ref('');
const totalChars = ref(0);
const correctChars = ref(0);
const errorCount = ref(0);
const intervalId = ref(null);
const inputRef = ref(null);

// Calculations
const kpm = computed(() => {
    if (!isStarted.value || timer.value === initialTime) return 0;
    const timeSpent = (initialTime - timer.value) / 60;
    return Math.round((correctChars.value / 5) / timeSpent) || 0;
});

const accuracy = computed(() => {
    if (totalChars.value === 0) return 0;
    return Math.round((correctChars.value / totalChars.value) * 100);
});

// Setup
const generateWords = () => {
    words.value = Array.from({ length: 150 }, () => ({
        text: CURATED_WORDS[Math.floor(Math.random() * CURATED_WORDS.length)],
        status: 'pending' // pending, correct, incorrect, active
    }));
    words.value[0].status = 'active';
};

const startTest = () => {
    if (isStarted.value) return;
    isStarted.value = true;
    intervalId.value = setInterval(() => {
        if (timer.value > 0) {
            timer.value--;
        } else {
            finishTest();
        }
    }, 1000);
};

const finishTest = () => {
    clearInterval(intervalId.value);
    isStarted.value = false;
    isFinished.value = true;
};

const restartTest = () => {
    clearInterval(intervalId.value);
    timer.value = initialTime;
    isStarted.value = false;
    isFinished.value = false;
    currentWordIndex.value = 0;
    userInput.value = '';
    totalChars.value = 0;
    correctChars.value = 0;
    errorCount.value = 0;
    generateWords();
    setTimeout(() => inputRef.value?.focus(), 50);
};

const handleInput = (e) => {
    if (isFinished.value) return;
    if (!isStarted.value) startTest();

    const value = e.target.value;
    const currentWord = words.value[currentWordIndex.value].text;

    // Word completed (Space pressed)
    if (value.endsWith(' ')) {
        const typedWord = value.trim();
        if (typedWord === currentWord) {
            words.value[currentWordIndex.value].status = 'correct';
            correctChars.value += currentWord.length + 1; // +1 for the space
        } else {
            words.value[currentWordIndex.value].status = 'incorrect';
            errorCount.value++;
        }
        
        totalChars.value += currentWord.length + 1;
        currentWordIndex.value++;
        if (currentWordIndex.value < words.value.length) {
            words.value[currentWordIndex.value].status = 'active';
        }
        userInput.value = '';
        
        // Auto-scroll logic if needed
        scrollToActive();
    }
};

const scrollToActive = () => {
    const activeEl = document.querySelector('.word.active');
    if (activeEl) {
        activeEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

onMounted(() => {
    generateWords();
    inputRef.value?.focus();
});

onUnmounted(() => {
    clearInterval(intervalId.value);
});

</script>

<template>
    <HomeLayout title="Test Mengetik Bahasa Indonesia">
        <div class="min-h-[85vh] bg-zinc-950 flex flex-col items-center justify-start p-4 sm:p-8 relative overflow-hidden transition-all duration-700">
            
            <!-- Glow background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-indigo-300/5 blur-[120px] rounded-full animate-pulse delay-700"></div>
            </div>

            <div class="z-10 w-full max-w-5xl flex flex-col gap-10">
                
                <!-- Stats Overlay -->
                <div class="grid grid-cols-3 gap-4 md:gap-8">
                    <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl flex flex-col items-center">
                        <div class="text-[10px] font-black uppercase text-zinc-600 tracking-widest mb-2 flex items-center gap-1.5">
                            <Zap class="w-3 h-3 text-indigo-400" />
                            KPM (WPM)
                        </div>
                        <div class="text-3xl md:text-5xl font-black text-white font-mono">{{ kpm }}</div>
                    </div>
                    <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl flex flex-col items-center">
                        <div class="text-[10px] font-black uppercase text-zinc-600 tracking-widest mb-2 flex items-center gap-1.5">
                            <Check class="w-3 h-3 text-emerald-400" />
                            Accuracy
                        </div>
                        <div class="text-3xl md:text-5xl font-black text-white font-mono">{{ accuracy }}%</div>
                    </div>
                    <div class="p-6 rounded-3xl bg-zinc-900 border border-zinc-800 shadow-xl flex flex-col items-center relative overflow-hidden">
                        <div v-if="timer <= 10 && isStarted" class="absolute inset-0 bg-red-500/5 animate-pulse"></div>
                        <div class="text-[10px] font-black uppercase text-zinc-600 tracking-widest mb-2 flex items-center gap-1.5">
                            <Timer class="w-3 h-3 text-indigo-400" />
                            Time Left
                        </div>
                        <div class="text-3xl md:text-5xl font-black text-white font-mono" :class="timer <= 10 ? 'text-red-500' : 'text-white'">{{ timer }}s</div>
                    </div>
                </div>

                <!-- Test Area -->
                <div class="relative w-full p-8 md:p-12 rounded-[40px] bg-zinc-900 border border-zinc-800 shadow-2xl group transition-all" :class="{ 'blur-sm grayscale opacity-50': isFinished }">
                    
                    <!-- Hidden Input -->
                    <input 
                        ref="inputRef"
                        v-model="userInput"
                        @input="handleInput"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-default"
                        :disabled="isFinished"
                        autocomplete="off"
                        autofocus
                    />

                    <!-- Words Display -->
                    <div class="flex flex-wrap gap-x-3 gap-y-4 text-2xl md:text-3xl font-medium tracking-tight h-48 overflow-hidden select-none" @click="inputRef?.focus()">
                        <span 
                            v-for="(word, index) in words" 
                            :key="index"
                            class="word rounded px-1 transition-all duration-200"
                            :class="{
                                'text-white active ring-2 ring-indigo-500/50 bg-indigo-500/10 shadow-[0_0_20px_rgba(99,102,241,0.2)]': word.status === 'active',
                                'text-zinc-600': word.status === 'pending',
                                'text-emerald-500': word.status === 'correct',
                                'text-red-500 border-b-2 border-red-500/50': word.status === 'incorrect'
                            }"
                        >
                            {{ word.text }}
                        </span>
                    </div>

                    <!-- Overlay for starting -->
                    <div v-if="!isStarted && !isFinished" class="absolute inset-0 flex items-center justify-center bg-zinc-950/20 backdrop-blur-sm rounded-[40px] pointer-events-none">
                        <div class="flex flex-col items-center gap-4">
                            <div class="p-4 rounded-full bg-indigo-500/20 text-indigo-400 animate-bounce">
                                <Languages class="w-8 h-8" />
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Mulai mengetik untuk menguji kecepatan</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-center flex-col items-center gap-6">
                    <button 
                        @click="restartTest"
                        class="flex items-center gap-2 px-8 py-3 rounded-2xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-800 transition-all font-bold text-sm shadow-xl active:scale-95"
                    >
                        <RotateCcw class="w-4 h-4" />
                        Ulangi Tes
                    </button>

                    <div class="flex items-center gap-2 text-[10px] font-black tracking-widest uppercase opacity-30 select-none">
                         Typing Engine v1.1 • Curated Indonesian Pool
                    </div>
                </div>

                <!-- Result Modal -->
                <transition name="modal">
                    <div v-if="isFinished" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-md">
                        <div class="w-full max-w-lg bg-zinc-900 border border-zinc-800 rounded-[40px] p-10 shadow-3xl text-center space-y-8">
                            <div class="inline-flex p-4 rounded-full bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                <Trophy class="w-12 h-12" />
                            </div>
                            
                            <div>
                                <h2 class="text-3xl font-black text-white mb-2">Tes Selesai!</h2>
                                <p class="text-zinc-500 text-sm font-medium">Luar biasa! Inilah hasil pencapaian Anda.</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-6 rounded-3xl bg-zinc-950 border border-zinc-800 shadow-inner">
                                    <div class="text-[10px] font-black uppercase text-zinc-600 tracking-widest mb-1">Skor KPM</div>
                                    <div class="text-4xl font-black text-indigo-400">{{ kpm }}</div>
                                </div>
                                <div class="p-6 rounded-3xl bg-zinc-950 border border-zinc-800 shadow-inner">
                                    <div class="text-[10px] font-black uppercase text-zinc-600 tracking-widest mb-1">Akurasi</div>
                                    <div class="text-4xl font-black text-emerald-400">{{ accuracy }}%</div>
                                </div>
                            </div>

                            <button 
                                @click="restartTest"
                                class="w-full py-4 rounded-3xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm uppercase tracking-widest transition-all shadow-lg shadow-indigo-600/20 active:scale-95"
                            >
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </transition>

            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.word {
    display: inline-block;
}

.word.active {
    scroll-margin: 100px;
}

/* Modal Animation */
.modal-enter-active, .modal-leave-active {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 0;
}
</style>
