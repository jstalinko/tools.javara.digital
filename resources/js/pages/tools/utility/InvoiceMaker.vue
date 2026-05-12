<script setup>
import { ref, computed } from 'vue';
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Button }   from '@/components/ui/button';
import { Input }    from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label }    from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Plus, Download, Trash2, ChevronDown,
    Calendar, Zap, Loader2
} from 'lucide-vue-next';

// ─── State ────────────────────────────────────────────────────────────────────
const invoiceType   = ref('Invoice');
const invoiceNumber = ref('001');
const fromText      = ref('');
const toText        = ref('');
const terms         = ref('Due On Receipt');
const invoiceDate   = ref(
    new Date().toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' })
);
const extraNotes    = ref('');

const showTax      = ref(false);
const showDiscount = ref(false);
const showShipping = ref(false);
const taxRate      = ref(0);
const discountAmt  = ref(0);
const shippingAmt  = ref(0);

const showAdditional = ref(false);
const poNumber       = ref('');
const paymentDetails = ref('');

const typeOptions  = ['Invoice', 'Quote', 'Receipt', 'Credit Note', 'Proforma'];
const termsOptions = ['Due On Receipt', 'Net 7', 'Net 15', 'Net 30', 'Net 60'];

const currencies = [
    { code: 'IDR', symbol: 'Rp', locale: 'id-ID', decimals: 0 },
    { code: 'USD', symbol: '$',  locale: 'en-US', decimals: 2 },
    { code: 'EUR', symbol: '€',  locale: 'de-DE', decimals: 2 },
    { code: 'SGD', symbol: 'S$', locale: 'en-SG', decimals: 2 },
    { code: 'MYR', symbol: 'RM', locale: 'ms-MY', decimals: 2 },
];
const currency = ref(currencies[0]);

const items = ref([{ id: 1, description: '', price: 0, qty: 1 }]);

const addItem    = () => items.value.push({ id: Date.now(), description: '', price: 0, qty: 1 });
const removeItem = (id) => { if (items.value.length > 1) items.value = items.value.filter(i => i.id !== id); };

// ─── Computed ─────────────────────────────────────────────────────────────────
const subtotal       = computed(() => items.value.reduce((s, i) => s + (parseFloat(i.price) || 0) * (parseInt(i.qty) || 0), 0));
const taxAmount      = computed(() => showTax.value      ? subtotal.value * (parseFloat(taxRate.value)  || 0) / 100 : 0);
const discountAmount = computed(() => showDiscount.value ? parseFloat(discountAmt.value) || 0 : 0);
const shippingAmount = computed(() => showShipping.value ? parseFloat(shippingAmt.value) || 0 : 0);
const balanceDue     = computed(() => subtotal.value + taxAmount.value - discountAmount.value + shippingAmount.value);

const fmt = (n) => {
    const c = currency.value;
    return c.symbol + ' ' + n.toLocaleString(c.locale, {
        minimumFractionDigits: c.decimals,
        maximumFractionDigits: c.decimals,
    });
};
const itemAmount = (item) => fmt((parseFloat(item.price) || 0) * (parseInt(item.qty) || 0));

// ─── PDF Download ──────────────────────────────────────────────────────────────
const isDownloading = ref(false);

const downloadPdf = async () => {
    isDownloading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const payload = {
            invoiceType:    invoiceType.value,
            invoiceNumber:  invoiceNumber.value,
            fromText:       fromText.value,
            toText:         toText.value,
            terms:          terms.value,
            invoiceDate:    invoiceDate.value,
            poNumber:       poNumber.value,
            paymentDetails: paymentDetails.value,
            extraNotes:     extraNotes.value,
            items:          items.value,
            showTax:        showTax.value,
            showDiscount:   showDiscount.value,
            showShipping:   showShipping.value,
            taxRate:        taxRate.value,
            discountAmt:    discountAmt.value,
            shippingAmt:    shippingAmt.value,
            currencySymbol: currency.value.symbol,
            currencyCode:   currency.value.code,
            currencyLocale: currency.value.locale,
            currencyDecimals: currency.value.decimals,
        };

        const response = await fetch('/utility/invoice-maker/download', {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'Accept':           'application/pdf',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) throw new Error('PDF generation failed');

        const blob = await response.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `invoice-${invoiceNumber.value}-${new Date().toISOString().slice(0,10)}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } catch (e) {
        alert('Gagal mengunduh PDF. Silakan coba lagi.');
        console.error(e);
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>
    <HomeLayout title="Invoice Maker">
        <div class="min-h-screen bg-zinc-950 text-white font-sans">

            <!-- ══ Hero ════════════════════════════════════════════════════ -->
            <div class="relative overflow-hidden border-b border-zinc-900 bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-950 py-14 px-4 text-center">
                <!-- glow -->
                <div class="pointer-events-none absolute -top-16 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-indigo-600/10 blur-[100px] rounded-full"></div>

                <div class="relative mx-auto w-fit flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[10px] font-bold uppercase tracking-widest text-indigo-400 mb-4">
                    <Zap class="w-3 h-3" /> Free Tool
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-3">
                    Invoice <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Maker</span>
                </h1>
                <p class="text-zinc-400 text-sm max-w-md mx-auto">
                    Create professional invoices instantly — no sign‑up required.
                </p>
            </div>

            <!-- ══ Editor ══════════════════════════════════════════════════ -->
            <div class="mx-auto max-w-5xl px-4 py-10 space-y-6">

                <Card class="bg-zinc-900 border-zinc-800 shadow-2xl">
                    <CardContent class="p-6 space-y-6">

                        <!-- ── Top grid: left | right ─────────────────── -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Left -->
                            <div class="space-y-4">
                                <!-- Type + Currency row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <Label class="text-zinc-400 text-xs uppercase tracking-wider">Type</Label>
                                        <div class="relative">
                                            <select
                                                v-model="invoiceType"
                                                class="w-full h-10 rounded-md border border-zinc-700 bg-zinc-800 text-white text-sm px-3 pr-9 appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                                            >
                                                <option v-for="t in typeOptions" :key="t">{{ t }}</option>
                                            </select>
                                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" />
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label class="text-zinc-400 text-xs uppercase tracking-wider">Currency</Label>
                                        <div class="relative">
                                            <select
                                                v-model="currency"
                                                class="w-full h-10 rounded-md border border-zinc-700 bg-zinc-800 text-white text-sm px-3 pr-9 appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                                            >
                                                <option v-for="c in currencies" :key="c.code" :value="c">
                                                    {{ c.symbol }} {{ c.code }}
                                                </option>
                                            </select>
                                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" />
                                        </div>
                                    </div>
                                </div>

                                <!-- From -->
                                <div class="space-y-1.5">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wider">From</Label>
                                    <Textarea
                                        v-model="fromText"
                                        placeholder="Your name / company name&#10;Address&#10;Email"
                                        rows="4"
                                        class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600 focus:ring-indigo-500 resize-none"
                                    />
                                </div>

                                <!-- To -->
                                <div class="space-y-1.5">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wider">To</Label>
                                    <Textarea
                                        v-model="toText"
                                        placeholder="Client name&#10;Address&#10;Email"
                                        rows="4"
                                        class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600 focus:ring-indigo-500 resize-none"
                                    />
                                </div>
                            </div>

                            <!-- Right -->
                            <div class="space-y-4">
                                <!-- Logo display -->
                                <div class="flex items-center justify-center rounded-lg border border-dashed border-zinc-700 bg-zinc-800/50 min-h-[88px] p-4">
                                    <img src="/javaradigital-logo.png" alt="Javara Digital Logo" class="max-h-14 max-w-full object-contain" />
                                </div>

                                <!-- Invoice Number -->
                                <div class="space-y-1.5">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wider">Invoice Number</Label>
                                    <Input
                                        v-model="invoiceNumber"
                                        class="bg-zinc-800 border-zinc-700 text-white focus:ring-indigo-500"
                                    />
                                </div>

                                <!-- Terms -->
                                <div class="space-y-1.5">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wider">Terms</Label>
                                    <div class="relative">
                                        <select
                                            v-model="terms"
                                            class="w-full h-10 rounded-md border border-zinc-700 bg-zinc-800 text-white text-sm px-3 pr-9 appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                                        >
                                            <option v-for="t in termsOptions" :key="t">{{ t }}</option>
                                        </select>
                                        <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" />
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="space-y-1.5">
                                    <Label class="text-zinc-400 text-xs uppercase tracking-wider">Date</Label>
                                    <div class="relative">
                                        <Input
                                            v-model="invoiceDate"
                                            class="bg-zinc-800 border-zinc-700 text-white pr-10 focus:ring-indigo-500"
                                        />
                                        <Calendar class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Separator class="bg-zinc-800" />

                        <!-- ── Line Items Table ────────────────────────── -->
                        <div class="rounded-lg border border-zinc-800 overflow-hidden">
                            <!-- Header -->
                            <div class="grid gap-3 bg-zinc-800/80 px-4 py-3 text-xs font-bold uppercase tracking-wider text-zinc-400"
                                 style="grid-template-columns: 1fr 140px 90px 100px 36px;">
                                <div>Description</div>
                                <div>Price</div>
                                <div>Qty</div>
                                <div class="text-right">Amount</div>
                                <div></div>
                            </div>

                            <!-- Rows -->
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="grid gap-3 px-4 py-3 border-t border-zinc-800 items-center hover:bg-zinc-800/30 transition-colors"
                                style="grid-template-columns: 1fr 140px 90px 100px 36px;"
                            >
                                <Input
                                    v-model="item.description"
                                    placeholder="Description"
                                    class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600 text-sm h-9"
                                />
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs pointer-events-none">{{ currency.symbol }}</span>
                                    <Input
                                        v-model="item.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="bg-zinc-800 border-zinc-700 text-white pl-6 text-sm h-9 no-spin"
                                    />
                                </div>
                                <Input
                                    v-model="item.qty"
                                    type="number"
                                    min="1"
                                    class="bg-zinc-800 border-zinc-700 text-white text-sm h-9 no-spin"
                                />
                                <div class="text-right font-bold text-white text-sm">
                                    {{ itemAmount(item) }}
                                </div>
                                <button
                                    @click="removeItem(item.id)"
                                    class="flex items-center justify-center w-8 h-8 rounded-md text-zinc-600 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- ── Actions ─────────────────────────────────── -->
                        <div class="flex flex-wrap gap-3 items-center">
                            <Button
                                @click="addItem"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold gap-2"
                            >
                                <Plus class="w-4 h-4" /> New Item
                            </Button>
                            <Button
                                @click="showAdditional = !showAdditional"
                                variant="ghost"
                                class="text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 font-bold gap-1"
                            >
                                <Plus class="w-4 h-4" /> Additional Details
                            </Button>
                        </div>

                        <!-- ── Additional Details ──────────────────────── -->
                        <div v-if="showAdditional" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg bg-zinc-800/40 border border-zinc-800">
                            <div class="space-y-1.5">
                                <Label class="text-zinc-400 text-xs uppercase tracking-wider">PO Number</Label>
                                <Input
                                    v-model="poNumber"
                                    placeholder="PO-12345"
                                    class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-zinc-400 text-xs uppercase tracking-wider">Payment Details</Label>
                                <Textarea
                                    v-model="paymentDetails"
                                    placeholder="Bank details, PayPal, etc."
                                    rows="3"
                                    class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600 resize-none"
                                />
                            </div>
                        </div>

                        <Separator class="bg-zinc-800" />

                        <!-- ── Bottom: Notes | Totals ──────────────────── -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                            <!-- Notes -->
                            <div class="space-y-1.5">
                                <Label class="text-zinc-400 text-xs uppercase tracking-wider">Extra Notes</Label>
                                <Textarea
                                    v-model="extraNotes"
                                    placeholder="Payment instructions, thank-you note, etc."
                                    rows="5"
                                    class="bg-zinc-800 border-zinc-700 text-white placeholder:text-zinc-600 resize-none"
                                />
                            </div>

                            <!-- Totals -->
                            <div class="space-y-3">
                                <!-- Subtotal -->
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-400 font-semibold">Subtotal:</span>
                                    <span class="font-bold text-white">{{ fmt(subtotal) }}</span>
                                </div>

                                <!-- Toggles -->
                                <div class="flex flex-wrap gap-4">
                                    <button
                                        @click="showTax = !showTax"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors"
                                        :class="{ 'line-through opacity-50': showTax }"
                                    >
                                        <Plus class="w-3 h-3" /> Tax
                                    </button>
                                    <button
                                        @click="showDiscount = !showDiscount"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors"
                                        :class="{ 'line-through opacity-50': showDiscount }"
                                    >
                                        <Plus class="w-3 h-3" /> Discount
                                    </button>
                                    <button
                                        @click="showShipping = !showShipping"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors"
                                        :class="{ 'line-through opacity-50': showShipping }"
                                    >
                                        <Plus class="w-3 h-3" /> Shipping
                                    </button>
                                </div>

                                <!-- Tax row -->
                                <div v-if="showTax" class="flex items-center gap-3 text-sm">
                                    <span class="text-zinc-400 flex-1">Tax (%)</span>
                                    <Input
                                        v-model="taxRate"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="bg-zinc-800 border-zinc-700 text-white h-8 w-20 text-right text-xs no-spin"
                                    />
                                    <span class="font-bold text-white w-20 text-right">{{ fmt(taxAmount) }}</span>
                                </div>

                                <!-- Discount row -->
                                <div v-if="showDiscount" class="flex items-center gap-3 text-sm">
                                    <span class="text-zinc-400 flex-1">Discount ($)</span>
                                    <Input
                                        v-model="discountAmt"
                                        type="number"
                                        min="0"
                                        class="bg-zinc-800 border-zinc-700 text-white h-8 w-20 text-right text-xs no-spin"
                                    />
                                    <span class="font-bold text-red-400 w-20 text-right">–{{ fmt(discountAmount) }}</span>
                                </div>

                                <!-- Shipping row -->
                                <div v-if="showShipping" class="flex items-center gap-3 text-sm">
                                    <span class="text-zinc-400 flex-1">Shipping ($)</span>
                                    <Input
                                        v-model="shippingAmt"
                                        type="number"
                                        min="0"
                                        class="bg-zinc-800 border-zinc-700 text-white h-8 w-20 text-right text-xs no-spin"
                                    />
                                    <span class="font-bold text-white w-20 text-right">{{ fmt(shippingAmount) }}</span>
                                </div>

                                <Separator class="bg-zinc-700" />

                                <!-- Balance Due -->
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white text-base">Balance Due</span>
                                    <span class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">
                                        {{ fmt(balanceDue) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </CardContent>
                </Card>

                <!-- ── Print Button ──────────────────────────────────── -->
                <div class="flex justify-end">
                    <Button
                        @click="downloadPdf"
                        :disabled="isDownloading"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 h-11 gap-2 shadow-lg shadow-indigo-500/20 disabled:opacity-60"
                    >
                        <Loader2 v-if="isDownloading" class="w-4 h-4 animate-spin" />
                        <Download v-else class="w-4 h-4" />
                        {{ isDownloading ? 'Generating PDF...' : 'Download PDF' }}
                    </Button>
                </div>

            </div>
        </div>
    </HomeLayout>
</template>

<style scoped>
.no-spin::-webkit-inner-spin-button,
.no-spin::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.no-spin { -moz-appearance: textfield; }

select option { background: #18181b; color: #fff; }
</style>