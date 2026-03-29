<script setup>
import { ref, watch, onMounted } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Copy, 
    Check, 
    RefreshCcw, 
    ShieldCheck, 
    Key, 
    Lock, 
    Zap,
    Sparkles,
    Eye,
    EyeOff
} from 'lucide-vue-next';
import { useClipboard } from '@vueuse/core';

const algorithms = [
    { id: 'bcrypt', name: 'Bcrypt (Laravel Standard)', description: 'Industry standard, highly secure password hashing.' },
    { id: 'wp', name: 'WordPress Salted', description: 'Compatible with WordPress Portable Hashing ($P$).' },
    { id: 'argon2id', name: 'Argon2id', description: 'Winner of the Password Hashing Competition (PHC).' },
    { id: 'md5', name: 'MD5', description: 'Fast, but considered cryptographically broken.' },
    { id: 'sha1', name: 'SHA-1', description: 'Stronger than MD5, but no longer recommended for secrets.' },
    { id: 'sha256', name: 'SHA-256', description: 'Standard high-bitwidth secure hashing.' },
];

// State
const password = ref('');
const algorithm = ref('bcrypt');
const resultHash = ref('');
const isGenerating = ref(false);
const showPassword = ref(false);
const { copy, copied } = useClipboard();

// Generate Random Password
const generateRandom = () => {
    const length = 16;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~-=";
    let retVal = "";
    for (let i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    password.value = retVal;
};

// Hashing Logic
const performHash = async () => {
    if (!password.value) {
        resultHash.value = '';
        return;
    }

    isGenerating.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/text/password-generator/hash', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                password: password.value,
                algorithm: algorithm.value
            })
        });

        if (response.ok) {
            const data = await response.json();
            resultHash.value = data.hash;
        } else {
            console.error('Hashing failed');
        }
    } catch (error) {
        console.error('Error hashing password:', error);
    } finally {
        isGenerating.value = false;
    }
};

// Auto-hash on change
watch([password, algorithm], () => {
    performHash();
});

// Initial generation
onMounted(() => {
    generateRandom();
});

const handleCopy = () => {
    copy(resultHash.value);
};
</script>

<template>
    <HomeLayout title="Password Generator & Hasher">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="rounded-full bg-emerald-500/10 px-4 py-1.5 text-sm font-semibold text-emerald-400 border border-emerald-500/20 flex items-center gap-2">
                        <ShieldCheck class="h-4 w-4" />
                        Security Tool
                    </div>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl leading-tight">
                    Password <span class="text-emerald-400">Hasher</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-400">
                    Generate secure passwords and hash them using industry-standard algorithms compatible with Laravel, WordPress, and more.
                </p>
            </div>

            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Configuration Card -->
                <Card class="bg-zinc-900 border-zinc-800 shadow-2xl shadow-emerald-500/5 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent pointer-events-none"></div>
                    <CardHeader class="pb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <Key class="h-4 w-4 text-emerald-400" />
                            <CardTitle class="text-lg">Generator Settings</CardTitle>
                        </div>
                        <CardDescription class="text-zinc-500 text-xs italic">All hashing processed securely via backend</CardDescription>
                    </CardHeader>
                    <CardContent class="p-6 pt-0 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password Input -->
                            <div class="space-y-2.5">
                                <Label class="text-zinc-400 text-xs uppercase tracking-wider font-bold">Raw Password</Label>
                                <div class="relative group">
                                    <Input 
                                        v-model="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Type or generate..."
                                        class="bg-zinc-800 border-zinc-700 text-white focus:ring-emerald-500/20 h-12 pr-20 rounded-xl transition-all"
                                    />
                                    <div class="absolute right-1.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-9 w-9 text-zinc-500 hover:text-emerald-400 hover:bg-zinc-900 transition-all rounded-lg"
                                            @click="showPassword = !showPassword"
                                            type="button"
                                        >
                                            <Eye v-if="!showPassword" class="h-4 w-4" />
                                            <EyeOff v-else class="h-4 w-4" />
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-9 w-9 text-zinc-500 hover:text-emerald-400 hover:bg-zinc-900 transition-all rounded-lg"
                                            @click="generateRandom"
                                            type="button"
                                            title="Generate Random"
                                        >
                                            <Sparkles class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-[10px] text-zinc-600 font-mono uppercase tracking-tighter">
                                        Intensity: <span :class="password.length > 12 ? 'text-emerald-500' : 'text-amber-500'">{{ password.length > 12 ? 'High' : 'Medium' }}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Algorithm Selection -->
                            <div class="space-y-2.5">
                                <Label class="text-zinc-400 text-xs uppercase tracking-wider font-bold">Hash Algorithm</Label>
                                <div class="relative">
                                    <select 
                                        v-model="algorithm"
                                        class="w-full h-12 bg-zinc-800 border-zinc-700 rounded-xl text-sm text-zinc-300 focus:ring-emerald-500/20 px-4 appearance-none cursor-pointer outline-none transition-all hover:bg-zinc-800/80"
                                    >
                                        <option v-for="alg in algorithms" :key="alg.id" :value="alg.id">{{ alg.name }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-zinc-500">
                                        <Lock class="h-4 w-4" />
                                    </div>
                                </div>
                                <p class="text-[10px] text-zinc-500 px-1 leading-relaxed">
                                    {{ algorithms.find(a => a.id === algorithm)?.description }}
                                </p>
                            </div>
                        </div>

                        <Button 
                            class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold h-12 rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex gap-2 border-none"
                            @click="performHash"
                            :disabled="isGenerating || !password"
                        >
                            <RefreshCcw :class="['h-4 w-4', isGenerating ? 'animate-spin' : '']" />
                            {{ isGenerating ? 'Computing Hash...' : 'Update Result' }}
                        </Button>
                    </CardContent>
                </Card>

                <!-- Result Card -->
                <Card class="bg-zinc-900 border-zinc-800 overflow-hidden rounded-2xl group shadow-2xl shadow-black/40">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 p-6 border-b border-zinc-800 bg-zinc-950/30 backdrop-blur-md">
                        <div>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Zap class="h-4 w-4 text-emerald-400" />
                                Hashed Output
                            </CardTitle>
                            <CardDescription class="text-zinc-500 text-[10px] mt-0.5 uppercase tracking-widest font-bold">Resulting cryptogram</CardDescription>
                        </div>
                        <Button 
                            size="sm"
                            variant="outline"
                            :class="[
                                'border-zinc-700 min-w-[124px] h-10 transition-all duration-300 font-bold rounded-lg uppercase text-[10px] tracking-widest',
                                copied ? 'border-emerald-500 text-emerald-400 bg-emerald-500/10' : 'text-zinc-300 hover:bg-zinc-800 hover:border-zinc-600'
                            ]"
                            :disabled="!resultHash"
                            @click="handleCopy"
                        >
                            <Check v-if="copied" class="mr-1.5 h-3 w-3" />
                            <Copy v-else class="mr-1.5 h-3 w-3" />
                            {{ copied ? 'Copied' : 'Copy Result' }}
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0 relative flex items-center justify-center min-h-[160px] bg-zinc-950/20">
                        <div v-if="isGenerating" class="flex flex-col items-center gap-4">
                            <RefreshCcw class="h-10 w-10 text-emerald-500 animate-spin opacity-40" />
                            <span class="text-[10px] text-zinc-500 uppercase tracking-widest font-bold animate-pulse">Hashing in progress</span>
                        </div>
                        <div v-else-if="resultHash" class="w-full p-10 break-all font-mono text-emerald-400 text-xl sm:text-2xl text-center select-all cursor-pointer hover:bg-emerald-500/5 transition-all duration-300 active:scale-[0.99]" @click="handleCopy">
                            {{ resultHash }}
                        </div>
                        <div v-else class="text-zinc-700 italic text-sm tracking-wide">
                            Input a password to see hashed result
                        </div>
                        
                        <!-- Metadata -->
                        <div class="absolute bottom-4 right-6 text-[9px] text-zinc-700 font-mono uppercase tracking-[0.2em] pointer-events-none select-none">
                            {{ algorithm }} (1.0)
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Enhanced Info Section -->
            <div class="mt-24 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="group p-8 rounded-2xl bg-zinc-900/40 border border-zinc-800/50 hover:border-emerald-500/20 transition-all duration-500 hover:bg-zinc-900/60">
                    <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <ShieldCheck class="h-5 w-5 text-emerald-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3 text-sm uppercase tracking-wider">Top Tier Security</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Bcrypt and Argon2id are the current gold standards. Argon2id is specifically designed to resist GPU cracking attacks.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/40 border border-zinc-800/50 hover:border-blue-500/20 transition-all duration-500 hover:bg-zinc-900/60">
                    <div class="h-10 w-10 rounded-xl bg-blue-500/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <Lock class="h-5 w-5 text-blue-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3 text-sm uppercase tracking-wider">WP Compatible</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        Our WordPress implementation uses the standard `$P$` portable format, allowing for direct use in WordPress database migrations.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/40 border border-zinc-800/50 hover:border-amber-500/20 transition-all duration-500 hover:bg-zinc-900/60">
                    <div class="h-10 w-10 rounded-xl bg-amber-500/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <Zap class="h-5 w-5 text-amber-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3 text-sm uppercase tracking-wider">Legacy Support</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed italic">
                        MD5 and SHA-1 are included for non-critical use cases and testing legacy systems where standard modern hashes aren't supported.
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
select {
    background-image: none;
}
</style>