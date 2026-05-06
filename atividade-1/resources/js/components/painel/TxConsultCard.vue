<script setup lang="ts">
import { ref } from 'vue';
import { apiGet } from '@/lib/painelApi';

const txidInput = ref('');
const result = ref('');
const error = ref('');
const isLoading = ref(false);

async function consultTx() {
    const txid = txidInput.value.trim();
    if (!txid) { error.value = 'Informe um txid.'; return; }
    isLoading.value = true;
    error.value = '';
    result.value = '';
    try {
        const data = await apiGet(`/api/tx/${encodeURIComponent(txid)}`);
        result.value = JSON.stringify(data, null, 2);
    } catch (e) {
        error.value = (e as Error).message;
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <div class="card">
        <h2>Consultar transação</h2>
        <p class="muted">
            <strong>Observação realista:</strong> isso pode falhar sem <code>txindex=1</code>
            (ou se não estiver na mempool/wallet).
        </p>
        <div class="form">
            <input v-model="txidInput" placeholder="txid..." />
            <button class="btn" :disabled="isLoading" @click="consultTx">
                {{ isLoading ? '…' : 'Consultar' }}
            </button>
        </div>
        <pre v-if="result" class="pre">{{ result }}</pre>
        <div v-if="error" class="error">{{ error }}</div>
    </div>
</template>
