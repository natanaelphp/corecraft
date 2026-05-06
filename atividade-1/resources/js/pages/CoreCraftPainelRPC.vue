<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { apiGet } from '@/lib/painelApi'
import KVDisplay from '@/components/painel/KVDisplay.vue'
import RecentBlocksSection from '@/components/painel/RecentBlocksSection.vue'
import BlockConsultCard from '@/components/painel/BlockConsultCard.vue'
import TxConsultCard from '@/components/painel/TxConsultCard.vue'

interface NodeApiData {
    chain: string;
    blocks: number;
    headers: number;
    difficulty: number;
    bestblockhash: string;
    mempool: {
        txcount: number;
        usage: number;
        bytes: number;
        maxmempool: number;
        mempoolminfee: number;
    };
}

const nodeKV = ref<Record<string, string | number>>({});
const mempoolKV = ref<Record<string, string | number>>({});
const nodeError = ref('');
const isLoadingNode = ref(false);
const blockHash = ref('');

async function refreshNode() {
    isLoadingNode.value = true;
    nodeError.value = '';
    try {
        const data = await apiGet<NodeApiData>('/api/node');
        nodeKV.value = {
            chain: data.chain,
            blocks: data.blocks,
            headers: data.headers,
            difficulty: data.difficulty,
            bestblockhash: data.bestblockhash,
        };
        mempoolKV.value = {
            txcount: data.mempool.txcount,
            usage_bytes: data.mempool.usage,
            bytes: data.mempool.bytes,
            maxmempool: data.mempool.maxmempool,
            mempoolminfee: data.mempool.mempoolminfee,
        };
    } catch (e) {
        nodeError.value = (e as Error).message;
    } finally {
        isLoadingNode.value = false;
    }
}

onMounted(() => refreshNode());
</script>

<template>
    <Head title="CoreCraft — Painel RPC" />

    <div class="container">
        <header class="header">
            <div>
                <h1>CoreCraft <span>Painel RPC</span></h1>
                <p>Painel local: <strong>RPC → Backend PHP (Laravel) → Frontend</strong> (sem ZMQ)</p>
            </div>
            <div class="actions">
                <button class="btn primary" :disabled="isLoadingNode" @click="refreshNode">
                    {{ isLoadingNode ? 'Carregando…' : 'Atualizar estado' }}
                </button>
            </div>
        </header>

        <section class="grid">
            <div class="card">
                <h2>Estado do node</h2>
                <KVDisplay :data="nodeKV" />
                <div v-if="nodeError" class="error">{{ nodeError }}</div>
            </div>
            <div class="card">
                <h2>Mempool</h2>
                <KVDisplay :data="mempoolKV" />
            </div>
        </section>

        <RecentBlocksSection @select-hash="(hash) => (blockHash = hash)" />

        <section class="grid">
            <BlockConsultCard v-model="blockHash" />
            <TxConsultCard />
        </section>

        <footer class="footer muted">Aula 1 — RPC como fotografia do estado</footer>
    </div>
</template>

<style>
:root {
    --bg: #0b0f17;
    --card: #111827;
    --muted: #9ca3af;
    --text: #e5e7eb;
    --line: #1f2937;
    --accent: #f59e0b;
    --accent2: #fb7185;
    --error: #ef4444;
}

* { box-sizing: border-box }

body {
    margin: 0;
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
    background:
        radial-gradient(1200px 600px at 20% 10%, rgba(245, 158, 11, .15), transparent 50%),
        radial-gradient(900px 500px at 80% 20%, rgba(251, 113, 133, .12), transparent 55%),
        var(--bg);
    color: var(--text);
}

.container { max-width: 1100px; margin: 0 auto; padding: 24px }

.header {
    display: flex; justify-content: space-between; align-items: flex-end;
    gap: 16px; margin-bottom: 18px;
    border-bottom: 1px solid var(--line); padding-bottom: 16px;
}
.header h1 { margin: 0; font-size: 28px; letter-spacing: .3px }
.header h1 span { color: var(--accent) }
.header p { margin: 6px 0 0; color: var(--muted) }
.actions { display: flex; gap: 10px; align-items: center }

.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 14px 0 }

.card {
    background: linear-gradient(180deg, rgba(255, 255, 255, .03), rgba(255, 255, 255, .01));
    border: 1px solid var(--line); border-radius: 16px; padding: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
}
.card h2 { margin: 0 0 10px; font-size: 18px }

.muted { color: var(--muted); font-size: 13px }

.btn {
    background: rgba(255, 255, 255, .06); color: var(--text);
    border: 1px solid rgba(255, 255, 255, .10); border-radius: 12px;
    padding: 10px 12px; cursor: pointer;
}
.btn:hover { border-color: rgba(245, 158, 11, .45) }
.btn.primary {
    background: linear-gradient(90deg, rgba(245, 158, 11, .95), rgba(251, 113, 133, .75));
    border: none; color: #111827; font-weight: 700;
}
.btn:disabled { opacity: .6; cursor: not-allowed }

.row { display: flex; justify-content: space-between; align-items: center; gap: 12px }
.row-right { display: flex; gap: 10px; align-items: center }

select, input {
    width: 100%; background: rgba(255, 255, 255, .04); color: var(--text);
    border: 1px solid rgba(255, 255, 255, .10); border-radius: 12px;
    padding: 10px 12px; outline: none;
}
label { color: var(--muted); font-size: 13px }
.form { display: flex; gap: 10px }

.table-wrap { overflow: auto; border-radius: 14px; border: 1px solid rgba(255, 255, 255, .08) }
table { width: 100%; border-collapse: collapse; font-size: 13px }
th, td { padding: 10px; border-bottom: 1px solid rgba(255, 255, 255, .06); text-align: left }
th { color: var(--muted); font-weight: 600; background: rgba(17, 24, 39, .35) }
tbody tr:hover { background: rgba(245, 158, 11, .06) }
a.hash { color: var(--accent); text-decoration: none }
a.hash:hover { text-decoration: underline }

.pre {
    margin-top: 10px; padding: 12px; border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08); background: rgba(0, 0, 0, .25);
    overflow: auto; max-height: 280px;
}

.error {
    margin-top: 10px; padding: 10px 12px; border-radius: 12px;
    border: 1px solid rgba(239, 68, 68, .35); background: rgba(239, 68, 68, .08);
    color: #fecaca; font-size: 13px;
}

.footer { margin-top: 18px; text-align: center }

@media (max-width: 900px) {
    .grid { grid-template-columns: 1fr }
    .header { align-items: flex-start; flex-direction: column }
}
</style>
