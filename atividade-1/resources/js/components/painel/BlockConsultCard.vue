<script setup lang="ts">
import { ref, watch } from 'vue';
import { apiGet } from '@/lib/painelApi';

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const hashInput = ref(props.modelValue);
const result = ref('');
const error = ref('');
const isLoading = ref(false);

// Quando o pai muda o hash (clique na tabela), consulta automaticamente
watch(
    () => props.modelValue,
    (val) => {
        hashInput.value = val;
        if (val) consultBlock();
    },
);

async function consultBlock() {
    const h = hashInput.value.trim();
    if (!h) { error.value = 'Informe um blockhash.'; return; }
    isLoading.value = true;
    error.value = '';
    result.value = '';
    try {
        const data = await apiGet(`/api/block/${encodeURIComponent(h)}`);
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
        <h2>Consultar bloco</h2>
        <p class="muted">Digite um hash de bloco e veja um resumo.</p>
        <div class="form">
            <input
                v-model="hashInput"
                placeholder="blockhash..."
                @input="emit('update:modelValue', hashInput)"
            />
            <button class="btn" :disabled="isLoading" @click="consultBlock">
                {{ isLoading ? '…' : 'Consultar' }}
            </button>
        </div>
        <pre v-if="result" class="pre">{{ result }}</pre>
        <div v-if="error" class="error">{{ error }}</div>
    </div>
</template>
