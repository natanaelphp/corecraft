<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiGet, fmtTime, satToBTC, hashShort } from '@/lib/painelApi';

const emit = defineEmits<{ 'select-hash': [hash: string] }>();

interface BlockItem {
    height: number;
    hash: string;
    txs: number | null;
    avgfeerate: number | null;
    totalfee: number | undefined;
    time: number;
}

interface RecentData {
    tip: string;
    items: BlockItem[];
}

const recentN = ref('10');
const items = ref<BlockItem[]>([]);
const info = ref('');
const error = ref('');
const isLoading = ref(false);

async function loadRecent() {
    isLoading.value = true;
    error.value = '';
    info.value = 'Carregando...';
    items.value = [];
    try {
        const data = await apiGet<RecentData>(`/api/blocks/recent?n=${encodeURIComponent(recentN.value)}`);
        items.value = data.items;
        info.value = `Tip: ${data.tip} — mostrando ${data.items.length} blocos`;
    } catch (e) {
        error.value = (e as Error).message;
        info.value = '';
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => loadRecent());
</script>

<template>
    <section class="card" style="margin: 14px 0">
        <div class="row">
            <h2>Blocos recentes</h2>
            <div class="row-right">
                <label>
                    N:
                    <select v-model="recentN">
                        <option>5</option>
                        <option>10</option>
                        <option>15</option>
                        <option>25</option>
                    </select>
                </label>
                <button class="btn" :disabled="isLoading" @click="loadRecent">
                    {{ isLoading ? 'Carregando…' : 'Carregar' }}
                </button>
            </div>
        </div>

        <div v-if="info" class="muted" style="margin: 8px 0">{{ info }}</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Height</th>
                        <th>Txs</th>
                        <th>Avg fee rate</th>
                        <th>Total fee</th>
                        <th>Time</th>
                        <th>Hash</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="block in items" :key="block.hash">
                        <td>{{ block.height }}</td>
                        <td>{{ block.txs ?? '-' }}</td>
                        <td>{{ block.avgfeerate ?? '-' }}</td>
                        <td>{{ block.totalfee !== undefined ? satToBTC(block.totalfee) + ' BTC' : '-' }}</td>
                        <td>{{ fmtTime(block.time) }}</td>
                        <td>
                            <a class="hash" href="#" @click.prevent="emit('select-hash', block.hash)">
                                {{ hashShort(block.hash) }}
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="error" class="error">{{ error }}</div>
    </section>
</template>
