<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    Search, 
    Sparkles, 
    Type, 
    Image as ImageIcon, 
    Hash, 
    Key, 
    FileJson, 
    StickyNote, 
    RefreshCcw, 
    AlignLeft, 
    QrCode, 
    Zap,
    FileImage,
    MousePointer2,
    TimerIcon,
    Presentation,
    Dices
} from 'lucide-vue-next';

interface Tool {
    name: string;
    description: string;
    icon: any;
    tag?: string;
    href: string;
}

const aiTools: Tool[] = [
    { 
        name: 'Caption Generator', 
        description: 'Generate engaging captions for your social media posts.', 
        icon: Sparkles, 
        tag: 'New',
        href: '#' 
    },
    { 
        name: 'Product Description', 
        description: 'Create compelling product descriptions that sell.', 
        icon: Zap, 
        href: '#' 
    },
];

const textTools: Tool[] = [
    { name: 'Word Counter', description: 'Count words, characters, and reading time instantly.', icon: Hash, href: '/text/word-counter' },
    { name: 'Password Generator', description: 'Generate strong, secure passwords with a strength checker.', icon: Key, href: '/text/password-generator' },
    { name: 'JSON Formatter', description: 'Prettify and validate your JSON data.', icon: FileJson, href: '/text/json-formatter' },
    { name: 'Notepad', description: 'A simple, browser-based notepad for quick notes.', icon: StickyNote, href: '/text/notepad' },
    { name: 'Base64 Encoder', description: 'Encode and decode Base64 strings easily.', icon: RefreshCcw, href: '/text/base64-encoder' },
    { name: 'Lorem Ipsum', description: 'Generate placeholder text for your designs.', icon: AlignLeft, href: '/text/lorem-ipsum' },
    {name:'Test Mengetik' , description: 'Test kecepatan mengetik Anda.' , icon: Type , href: '/text/test-mengetik'}
];

const imageTools: Tool[] = [
    { name: 'JPG to WEBP', description: 'Convert JPG images to the modern WEBP format.', icon: ImageIcon, href: '/image/jpg-to-webp' },
    { name: 'PNG to WEBP', description: 'Convert PNG images to the modern WEBP format.', icon: FileImage, href: '/image/png-to-webp' },
    { name: 'QR Code Generator', description: 'Generate QR codes with custom logos.', icon: QrCode, href: '/image/qr-generator' },
    {name: 'Watermark Image' , description: 'Add watermark to your images.' , icon: FileImage , href: '/image/watermark-image'}
];
const UtilityTools: Tool[] = [
    {name: 'Tasbih Digital' , description: 'Digital Tasbih for counting Zikr and Dhikr.' , icon: TimerIcon , href: '/utility/tasbih-digital'},
    {name:'Papan Skor' , description: 'Papan Skor untuk menghitung skor pertandingan.' , icon: Presentation , href: '/utility/papan-skor'},
    {name:'Kocok Dadu' , description: 'Kocok dadu untuk menentukan pilihan secara acak.' , icon: Dices , href: '/utility/kocok-dadu'}
]

const categories = [
    { name: 'AI Tools', tools: aiTools, icon: Sparkles, color: 'text-purple-400' },
    { name: 'Text Tools', tools: textTools, icon: Type, color: 'text-blue-400' },
    { name: 'Image Tools', tools: imageTools, icon: ImageIcon, color: 'text-emerald-400' },
    {name: 'Utilities' , tools: UtilityTools , icon: MousePointer2 , color: 'text-red-400'}
];
</script>

<template>
    <HomeLayout title="Free webtools, AI tools, Text tools,Image tools">
        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-20 pb-16 lg:pt-32 lg:pb-28">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.15),transparent_50%)]"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_10%_20%,rgba(16,185,129,0.05),transparent_50%)]"></div>
            
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center mb-6">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-400 backdrop-blur-md">
                        <Sparkles class="h-3.5 w-3.5" />
                        Next-Gen Digital Workflow
                    </span>
                </div>
                
                <h1 class="mx-auto max-w-4xl text-5xl font-extrabold tracking-tight sm:text-7xl">
                   JavaraDigital <br />
                    <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Internet Web Tools
                    </span>
                </h1>
                
                <p class="mx-auto mt-8 max-w-2xl text-lg text-zinc-400 leading-relaxed">
                    A curated collection of free, powerful web tools to supercharge your creativity, 
                    development, and content creation. No registration required.
                </p>
                
                <div class="mt-12 flex flex-col sm:flex-row justify-center items-center gap-4">
                    <div class="relative w-full max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5 text-zinc-500 transition-colors group-focus-within:text-indigo-400" />
                        <Input 
                            placeholder="Find a tool (e.g., QR Code, Word Counter...)" 
                            class="pl-11 h-12 bg-zinc-900/50 border-zinc-800 text-zinc-100 placeholder:text-zinc-500 focus:ring-indigo-500/30 transition-all rounded-xl"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Tools Grid -->
        <section id="tools" class="py-16 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-24">
                <div v-for="category in categories" :key="category.name" class="space-y-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-zinc-900 border border-zinc-800">
                                <component :is="category.icon" :class="['h-6 w-6', category.color]" />
                            </div>
                            <h2 class="text-3xl font-bold text-white">{{ category.name }}</h2>
                        </div>
                        <Button variant="link" class="text-zinc-400 hover:text-white">View all</Button>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a 
                            v-for="tool in category.tools" 
                            :key="tool.name" 
                            :href="tool.href"
                            class="group relative block"
                        >
                            <Card class="h-full bg-zinc-900/40 border-zinc-800 hover:border-zinc-700 hover:bg-zinc-800/40 transition-all duration-300 backdrop-blur-sm overflow-hidden rounded-2xl">
                                <CardHeader class="pb-2">
                                    <div class="flex justify-between items-start">
                                        <div class="p-2.5 rounded-xl bg-zinc-900 group-hover:bg-zinc-800 group-hover:scale-110 transition-all duration-300">
                                            <component :is="tool.icon" :class="['h-6 w-6', category.color]" />
                                        </div>
                                        <span v-if="tool.tag" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500 text-white">
                                            {{ tool.tag }}
                                        </span>
                                    </div>
                                    <CardTitle class="mt-4 text-xl font-bold text-white group-hover:text-indigo-300 transition-colors">
                                        {{ tool.name }}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <CardDescription class="text-zinc-400 line-clamp-2">
                                        {{ tool.description }}
                                    </CardDescription>
                                    
                                    <div class="mt-6 flex items-center text-sm font-semibold text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Explore Tool
                                        <MousePointer2 class="ml-2 h-3 w-3" />
                                    </div>
                                </CardContent>
                            </Card>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTAs / Features -->
        <section id="features" class="py-24 bg-zinc-900/20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div class="space-y-4">
                        <div class="mx-auto h-12 w-12 rounded-full bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20">
                            <Zap class="h-6 w-6 text-indigo-400" />
                        </div>
                        <h3 class="text-lg font-bold text-white">Lightning Fast</h3>
                        <p class="text-zinc-400 text-sm">Real-time processing in your browser. No server-side delays.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="mx-auto h-12 w-12 rounded-full bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                            <Sparkles class="h-6 w-6 text-emerald-400" />
                        </div>
                        <h3 class="text-lg font-bold text-white">Privacy First</h3>
                        <p class="text-zinc-400 text-sm">Your data never leaves your device. Secure and anonymous.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="mx-auto h-12 w-12 rounded-full bg-purple-500/10 flex items-center justify-center border border-purple-500/20">
                            <RefreshCcw class="h-6 w-6 text-purple-400" />
                        </div>
                        <h3 class="text-lg font-bold text-white">Always Free</h3>
                        <p class="text-zinc-400 text-sm">No subscriptions, no hidden fees. Just great tools for everyone.</p>
                    </div>
                </div>
            </div>
        </section>
    </HomeLayout>
</template>

<style scoped>
/* Add a subtle animation for the cards appearance */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grid > a {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
}

.grid > a:nth-child(1) { animation-delay: 0.1s; }
.grid > a:nth-child(2) { animation-delay: 0.2s; }
.grid > a:nth-child(3) { animation-delay: 0.3s; }
.grid > a:nth-child(4) { animation-delay: 0.4s; }
.grid > a:nth-child(5) { animation-delay: 0.5s; }
.grid > a:nth-child(6) { animation-delay: 0.6s; }
</style>