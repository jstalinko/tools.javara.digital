<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLogo from '@/components/AppLogo.vue';
import { Github, Twitter, Linkedin, Mail, Home } from 'lucide-vue-next';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-zinc-50 selection:bg-indigo-500/30">
        <Head :title="title" />

        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center gap-2 transition-opacity hover:opacity-90">
                      
                      <img src="https://javara.digital/javaradigital-logo.png" class="h-10"/>
                    </a>
                    
                   
                </div>

                <div class="flex items-center gap-4">
                    <Button variant="ghost" class="text-zinc-400 hover:text-white hover:bg-zinc-800/50">
                        Log in
                    </Button>
                    <Button class="bg-indigo-600 hover:bg-indigo-500 text-white border-none shadow-lg shadow-indigo-500/20">
                        Get Started
                    </Button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-900 bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8 mt-20">
            <div class="mx-auto max-w-7xl">
               
                
                <div class="border-t border-zinc-900 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-zinc-500">
                        &copy; {{new Date().getFullYear() }} <a href="https://javara.digital?src=tools" target="_blank">javaradigital</a>  
                    </p>
                    <div class="flex gap-6">
                         <a href="https://javara.digital/whatsapp" class="font-bold text-red-400 hover:text-blue-200 cursor-pointer"> Contact Us </a>
                        <a href="#" class="text-xs text-zinc-500 hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="text-xs text-zinc-500 hover:text-white transition-colors">Terms of Service</a>
                        
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
