<script setup lang="ts">
import { ref, watch } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Copy, 
    Check, 
    RefreshCcw, 
    Settings2, 
    FileText, 
    AlignLeft, 
    Hash,
    Sparkles,
    CodeXml,
    Type,
    List
} from 'lucide-vue-next';
import { useClipboard } from '@vueuse/core';

// Lorem Ipsum word set
const words = [
    "lorem", "ipsum", "dolor", "sit", "amet", "consectetur", "adipiscing", "elit", "sed", "do", "eiusmod", 
    "tempor", "incididunt", "ut", "labore", "et", "dolore", "magna", "aliqua", "ut", "enim", "ad", "minim", 
    "veniam", "quis", "nostrud", "exercitation", "ullamco", "laboris", "nisi", "ut", "aliquip", "ex", "ea", 
    "commodo", "consequat", "duis", "aute", "irure", "dolor", "in", "reprehenderit", "in", "voluptate", 
    "velit", "esse", "cillum", "dolore", "eu", "fugiat", "nulla", "pariatur", "excepteur", "sint", 
    "occaecat", "cupidatat", "non", "proident", "sunt", "in", "culpa", "qui", "officia", "deserunt", 
    "mollit", "anim", "id", "est", "laborum"
];

const unitTypes = [
    { id: 'paragraphs', name: 'Paragraphs', icon: AlignLeft },
    { id: 'sentences', name: 'Sentences', icon: FileText },
    { id: 'words', name: 'Words', icon: Hash },
    { id: 'list', name: 'List Items', icon: List },
];

const formats = [
    { id: 'plain', name: 'Plain Text', icon: Type },
    { id: 'html', name: 'HTML Tags', icon: CodeXml },
];

const lengthOptions = [
    { id: 'short', name: 'Short', range: [3, 5] },
    { id: 'medium', name: 'Medium', range: [6, 10] },
    { id: 'long', name: 'Long', range: [11, 18] },
];

// State
const count = ref(3);
const unitType = ref('paragraphs');
const format = ref('plain');
const startWithLorem = ref(true);
const sentenceLength = ref('medium'); // words per sentence
const paraLength = ref('medium'); // sentences per paragraph
const generatedText = ref('');
const { copy, copied } = useClipboard();

// Logic
const getRandomWord = () => words[Math.floor(Math.random() * words.length)];

const generateSentence = (type: string = 'medium') => {
    const range = lengthOptions.find(o => o.id === type)?.range || [5, 10];
    const length = Math.floor(Math.random() * (range[1] - range[0] + 1)) + range[0];
    let sentence = Array.from({ length }, getRandomWord).join(' ');
    return sentence.charAt(0).toUpperCase() + sentence.slice(1) + '.';
};

const generateParagraph = (sLen: string = 'medium', pLen: string = 'medium') => {
    const range = lengthOptions.find(o => o.id === pLen)?.range || [3, 6];
    const length = Math.floor(Math.random() * (range[1] - range[0] + 1)) + range[0];
    return Array.from({ length }, () => generateSentence(sLen)).join(' ');
};

const generate = () => {
    let resultItems: string[] = [];
    const num = Math.max(1, Math.min(count.value, 100)); // Limit for safety
    
    if (unitType.value === 'paragraphs') {
        resultItems = Array.from({ length: num }, () => generateParagraph(sentenceLength.value, paraLength.value));
    } else if (unitType.value === 'sentences') {
        resultItems = [Array.from({ length: num }, () => generateSentence(sentenceLength.value)).join(' ')];
    } else if (unitType.value === 'words') {
        resultItems = [Array.from({ length: num }, getRandomWord).join(' ')];
    } else if (unitType.value === 'list') {
        resultItems = Array.from({ length: num }, () => generateSentence('short'));
    }

    // "Start with Lorem Ipsum" logic
    if (startWithLorem.value) {
        const standardStart = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ";
        if (unitType.value === 'words') {
            const standardWords = ["lorem", "ipsum", "dolor", "sit", "amet", "consectetur", "adipiscing", "elit", "sed", "do", "eiusmod", "tempor", "incididunt", "ut", "labore", "et", "dolore", "magna", "aliqua"];
            const remaining = Math.max(0, num - standardWords.length);
            let wordsResult = standardWords.slice(0, num).join(' ');
            if (num > standardWords.length) {
                wordsResult += ' ' + Array.from({ length: remaining }, getRandomWord).join(' ');
            }
            resultItems = [wordsResult];
        } else {
            if (resultItems.length > 0) {
                if (unitType.value === 'list') {
                    resultItems[0] = "Lorem ipsum dolor sit amet " + resultItems[0].toLowerCase();
                } else {
                    resultItems[0] = standardStart + resultItems[0];
                }
            } else {
                resultItems = [standardStart.trim()];
            }
        }
    }

    // Formatting
    if (format.value === 'html') {
        if (unitType.value === 'paragraphs') {
            generatedText.value = resultItems.map(row => `<p>${row}</p>`).join('\n');
        } else if (unitType.value === 'list') {
            generatedText.value = `<ul>\n${resultItems.map(row => `  <li>${row}</li>`).join('\n')}\n</ul>`;
        } else {
            generatedText.value = `<p>${resultItems.join(' ')}</p>`;
        }
    } else {
        if (unitType.value === 'list') {
            generatedText.value = resultItems.map(row => `• ${row}`).join('\n');
        } else {
            generatedText.value = resultItems.join('\n\n');
        }
    }
};

// Initial generation
generate();

// Watch for changes to auto-generate
watch([count, unitType, format, startWithLorem, sentenceLength, paraLength], () => {
    generate();
});

const handleCopy = () => {
    copy(generatedText.value);
};
</script>

<template>
    <HomeLayout title="Lorem Ipsum Generator - Premium Placeholder Text">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div class="rounded-full bg-indigo-500/10 px-4 py-1.5 text-sm font-semibold text-indigo-400 border border-indigo-500/20 flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        Text Utility
                    </div>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Lorem Ipsum <span class="text-indigo-400">Generator</span>
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-400">
                    Generate high-quality placeholder text with custom HTML tags or plain text for your designs.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Settings Panel -->
                <div class="lg:col-span-4">
                    <Card class="bg-zinc-900 border-zinc-800 h-fit sticky top-24 shadow-2xl shadow-indigo-500/5">
                        <CardHeader>
                            <div class="flex items-center gap-2 mb-1">
                                <Settings2 class="h-4 w-4 text-indigo-400" />
                                <CardTitle class="text-lg">Settings</CardTitle>
                            </div>
                            <CardDescription class="text-zinc-500 text-xs">Configure your placeholder text style</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Unit Type & Format -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wide font-bold">Type</Label>
                                    <select 
                                        v-model="unitType"
                                        class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-sm text-zinc-300 focus:ring-indigo-500/20 py-2.5 px-3"
                                    >
                                        <option v-for="t in unitTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wide font-bold">Format</Label>
                                    <select 
                                        v-model="format"
                                        class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-sm text-zinc-300 focus:ring-indigo-500/20 py-2.5 px-3"
                                    >
                                        <option v-for="f in formats" :key="f.id" :value="f.id">{{ f.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="space-y-3 pt-2">
                                <div class="flex justify-between items-center">
                                    <Label for="count" class="text-zinc-400 text-xs uppercase tracking-wide font-bold">Amount</Label>
                                    <span class="text-xs bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded border border-indigo-500/20">
                                        {{ count }} {{ unitType }}
                                    </span>
                                </div>
                                <Input 
                                    id="count"
                                    v-model.number="count"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="bg-zinc-800 border-zinc-700 text-white focus:ring-indigo-500/20 h-11"
                                />
                            </div>

                            <!-- Length Settings -->
                            <div class="space-y-4 pt-2">
                                <div v-if="unitType !== 'words' && unitType !== 'list'" class="space-y-2">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wide font-bold">Sentence Length</Label>
                                    <div class="flex bg-zinc-950/50 p-1 rounded-lg border border-zinc-800">
                                        <button 
                                            v-for="opt in lengthOptions" 
                                            :key="opt.id"
                                            @click="sentenceLength = opt.id"
                                            :class="[
                                                'flex-1 py-1.5 text-xs font-medium rounded-md transition-all',
                                                sentenceLength === opt.id ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'
                                            ]"
                                        >
                                            {{ opt.name }}
                                        </button>
                                    </div>
                                </div>

                                <div v-if="unitType === 'paragraphs'" class="space-y-2">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wide font-bold">Paragraph Length</Label>
                                    <div class="flex bg-zinc-950/50 p-1 rounded-lg border border-zinc-800">
                                        <button 
                                            v-for="opt in lengthOptions" 
                                            :key="opt.id"
                                            @click="paraLength = opt.id"
                                            :class="[
                                                'flex-1 py-1.5 text-xs font-medium rounded-md transition-all',
                                                paraLength === opt.id ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'
                                            ]"
                                        >
                                            {{ opt.name }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Options Switch -->
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-950/50 border border-zinc-800 transition-colors hover:border-zinc-700">
                                <Label for="lorem-start" class="text-zinc-300 cursor-pointer text-sm font-medium">Start with "Lorem ipsum..."</Label>
                                <div @click="startWithLorem = !startWithLorem" class="relative inline-flex h-5 w-9 cursor-pointer items-center rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 ring-offset-zinc-950">
                                    <input 
                                        id="lorem-start"
                                        v-model="startWithLorem"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />
                                    <div :class="[
                                        'absolute inset-0 rounded-full transition-colors duration-200',
                                        startWithLorem ? 'bg-indigo-600' : 'bg-zinc-700'
                                    ]"></div>
                                    <div :class="[
                                        'absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-all duration-200 shadow-sm',
                                        startWithLorem ? 'translate-x-4' : 'translate-x-0'
                                    ]"></div>
                                </div>
                            </div>

                            <Button 
                                class="w-full bg-zinc-50 hover:bg-white text-zinc-950 font-bold h-12 rounded-xl transition-all active:scale-95 group"
                                @click="generate"
                            >
                                <RefreshCcw class="mr-2 h-4 w-4 group-hover:rotate-180 transition-transform duration-500" />
                                Generate New Text
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <!-- Result Panel -->
                <div class="lg:col-span-8">
                    <Card class="bg-zinc-900 border-zinc-800 h-full min-h-[580px] flex flex-col shadow-2xl shadow-black/50 overflow-hidden rounded-2xl">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 p-6 border-b border-zinc-800 bg-zinc-900/50 backdrop-blur-md">
                            <div>
                                <CardTitle class="text-lg flex items-center gap-2">
                                    <AlignLeft class="h-4 w-4 text-indigo-400" />
                                    Generated Text
                                </CardTitle>
                                <CardDescription class="text-zinc-500 text-xs mt-0.5">Click the button on the right to copy results</CardDescription>
                            </div>
                            <Button 
                                size="sm"
                                variant="outline"
                                :class="[
                                    'border-zinc-700 min-w-[124px] h-10 transition-all duration-300 font-semibold rounded-lg',
                                    copied ? 'border-emerald-500/50 text-emerald-400 bg-emerald-500/10' : 'text-zinc-300 hover:bg-zinc-800 hover:border-zinc-600'
                                ]"
                                @click="handleCopy"
                            >
                                <Check v-if="copied" class="mr-2 h-4 w-4" />
                                <Copy v-else class="mr-2 h-4 w-4" />
                                {{ copied ? 'Copied!' : 'Copy to Clipboard' }}
                            </Button>
                        </CardHeader>
                        <CardContent class="flex-grow p-0 relative">
                            <textarea 
                                v-model="generatedText"
                                readonly
                                class="w-full h-full min-h-[480px] bg-transparent border-none p-8 text-zinc-300 font-mono text-sm leading-8 resize-none focus:outline-none scrollbar-thin scrollbar-thumb-zinc-800 selection:bg-indigo-500/30"
                                placeholder="Generated text will appear here..."
                            ></textarea>
                            
                            <!-- Subtle decorative elements -->
                            <div class="absolute bottom-4 right-4 text-[10px] text-zinc-700 font-mono uppercase tracking-widest pointer-events-none">
                                javaradigital • text-gen-v1.0
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- SEO / Info Section -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-indigo-500/30 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-indigo-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <Sparkles class="h-5 w-5 text-indigo-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">Professional Output</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">
                        Industry-standard Latin vocabulary that maintains a natural distribution of letters and word lengths for realistic UI testing.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-emerald-500/30 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <CodeXml class="h-5 w-5 text-emerald-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">HTML Ready</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">
                        One-click HTML formatting including paragraph and list tags, making it easy to drop directly into your source code or CMS editor.
                    </p>
                </div>
                <div class="group p-8 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-purple-500/30 transition-all duration-500">
                    <div class="h-10 w-10 rounded-xl bg-purple-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <Settings2 class="h-5 w-5 text-purple-400" />
                    </div>
                    <h3 class="font-bold text-white mb-3">Custom Controls</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">
                        Fine-tune your placeholder text with granular controls for sentence and paragraph length to match your specific layout needs.
                    </p>
                </div>
            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 10px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #3f3f46;
}

/* Custom switch focus styles */
.peer:focus + div {
    @apply ring-2 ring-indigo-500 ring-offset-2 ring-offset-zinc-950;
}
</style>