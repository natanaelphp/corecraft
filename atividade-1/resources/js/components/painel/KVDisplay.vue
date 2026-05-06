<script setup lang="ts">
defineProps<{ data: Record<string, string | number> }>();
</script>

<template>
    <div class="kv">
        <div v-for="(value, key) in data" :key="key">
            <div class="k">{{ key }}</div>
            <div class="v">{{ value }}</div>
        </div>
    </div>
</template>

<style scoped>
.kv {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px
}
.kv div {
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 12px;
    padding: 10px;
    background: rgba(17, 24, 39, .35);
}
.kv .k {
    color: var(--muted);
    font-size: 12px
}
.kv .v {
    margin-top: 6px;
    font-size: 15px;
    word-break: break-all
}
</style>
