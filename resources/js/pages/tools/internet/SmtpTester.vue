<script setup lang="ts">
import { ref } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import axios from 'axios';
import { Mail, CheckCircle, XCircle, Send, Loader2, Globe2 } from 'lucide-vue-next';

// Use Shadcn Select or simple native select (using native select styled nicely for simplicity here, or we can use generic buttons)
const form = ref({
    host: '',
    port: '465',
    encryption: 'ssl',
    username: '',
    password: '',
    from_address: '',
    to_address: '',
    subject: 'Test Email from JavaraDigital Web Tools',
    message: 'This is a test email sent from the JavaraDigital SMTP Tester tool.\n\nIf you are reading this, your SMTP configuration is highly likely correct and working perfectly.\n\nBest Regards,\nJavaraDigital Team',
});

const isTesting = ref(false);
const result = ref<{ success: boolean; message: string } | null>(null);

const testSmtp = async () => {
    isTesting.value = true;
    result.value = null;

    try {
        const response = await axios.post('/internet/smtp-tester/test', form.value);
        result.value = {
            success: true,
            message: response.data.message || 'Email sent successfully!',
        };
    } catch (error: any) {
        result.value = {
            success: false,
            message: error.response?.data?.message || error.message || 'An error occurred while testing SMTP connection.',
        };
    } finally {
        isTesting.value = false;
    }
};

const preFillGmail = () => {
    form.value.host = 'smtp.gmail.com';
    form.value.port = '465';
    form.value.encryption = 'ssl';
};

const preFillMailtrap = () => {
    form.value.host = 'sandbox.smtp.mailtrap.io';
    form.value.port = '2525';
    form.value.encryption = 'tls';
};
</script>

<template>
    <HomeLayout title="SMTP Tester - JavaraDigital">
        <section class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="mb-10 lg:mb-16 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-medium mb-6">
                    <Mail class="w-4 h-4" />
                    <span>Internet Tools</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
                    SMTP Server <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-600">Tester</span>
                </h1>
                <p class="text-lg text-zinc-400 leading-relaxed">
                    Test your SMTP server credentials and connectivity by sending a custom email quickly and securely without writing any code.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Form -->
                <div class="space-y-6">
                    <Card class="bg-zinc-900/60 border-zinc-800 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle class="text-xl text-white flex items-center gap-2">
                                <Globe2 class="w-5 h-5 text-green-400" />
                                SMTP Server Configuration
                            </CardTitle>
                            <CardDescription class="text-zinc-400">
                                Enter your SMTP server details below. We don't save any of your credentials.
                            </CardDescription>
                            
                            <div class="flex gap-2 pt-2">
                                <Button type="button" variant="outline" size="sm" @click="preFillGmail" class="border-zinc-700 bg-zinc-800/50 hover:bg-zinc-800 text-zinc-300">
                                    Use Gmail Defaults
                                </Button>
                                <Button type="button" variant="outline" size="sm" @click="preFillMailtrap" class="border-zinc-700 bg-zinc-800/50 hover:bg-zinc-800 text-zinc-300">
                                    Use Mailtrap Defaults
                                </Button>
                            </div>
                        </CardHeader>
                        
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">SMTP Host <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.host" placeholder="smtp.example.com" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">SMTP Port <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.port" type="number" placeholder="465, 587, 25" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label class="text-zinc-300">Encryption</Label>
                                <div class="relative">
                                    <select v-model="form.encryption" class="flex h-10 w-full items-center justify-between rounded-md border border-zinc-800 bg-zinc-950/50 px-3 py-2 text-sm text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 focus:ring-offset-zinc-950 appearance-none">
                                        <option value="">None</option>
                                        <option value="ssl">SSL</option>
                                        <option value="tls">TLS</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">SMTP Username</Label>
                                    <Input v-model="form.username" placeholder="user@example.com" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">SMTP Password</Label>
                                    <Input v-model="form.password" type="password" placeholder="••••••••" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Email Content & Action -->
                <div class="space-y-6">
                    <Card class="bg-zinc-900/60 border-zinc-800 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle class="text-xl text-white flex items-center gap-2">
                                <Send class="w-5 h-5 text-green-400" />
                                Email Details
                            </CardTitle>
                        </CardHeader>
                        
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">From Email <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.from_address" type="email" placeholder="sender@example.com" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-zinc-300">To Email <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.to_address" type="email" placeholder="recipient@example.com" class="bg-zinc-950/50 border-zinc-800 text-white" />
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label class="text-zinc-300">Subject <span class="text-red-500">*</span></Label>
                                <Input v-model="form.subject" placeholder="Email subject..." class="bg-zinc-950/50 border-zinc-800 text-white" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-zinc-300">Message Body <span class="text-red-500">*</span></Label>
                                <Textarea v-model="form.message" placeholder="Type your email message here..." rows="6" class="bg-zinc-950/50 border-zinc-800 text-white resize-none" />
                            </div>

                            <!-- Form Action -->
                            <div class="pt-4 mt-2 border-t border-zinc-800/50">
                                <Button 
                                    @click="testSmtp" 
                                    :disabled="isTesting || !form.host || !form.port || !form.to_address || !form.from_address"
                                    class="w-full h-12 bg-green-600 hover:bg-green-500 text-white font-semibold transition-all shadow-lg rounded-xl flex items-center justify-center gap-2"
                                >
                                    <Loader2 v-if="isTesting" class="w-5 h-5 animate-spin" />
                                    <template v-else>
                                        <Send class="w-5 h-5" />
                                        Test SMTP Connection
                                    </template>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Result Status -->
                    <div v-if="result" :class="`p-6 rounded-xl border ${result.success ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30'}`">
                        <div class="flex items-start gap-4">
                            <CheckCircle v-if="result.success" class="w-6 h-6 text-green-400 shrink-0 mt-0.5" />
                            <XCircle v-else class="w-6 h-6 text-red-400 shrink-0 mt-0.5" />
                            <div>
                                <h3 :class="`font-bold text-lg mb-1 ${result.success ? 'text-green-400' : 'text-red-400'}`">
                                    {{ result.success ? 'Success!' : 'Connection Failed' }}
                                </h3>
                                <p class="text-zinc-300 whitespace-pre-wrap font-mono text-sm leading-relaxed p-3 rounded-lg overflow-x-auto" :class="result.success ? 'bg-green-500/5' : 'bg-red-500/5'">
                                    {{ result.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </HomeLayout>
</template>