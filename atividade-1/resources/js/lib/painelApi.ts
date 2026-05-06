export async function apiGet<T>(path: string): Promise<T> {
    const response = await fetch(path);
    const json = await response.json();
    if (!json.ok) {
        const detail = json?.error?.details ? ' | ' + json.error.details : '';
        throw new Error((json?.error?.message ?? 'Erro desconhecido') + detail);
    }
    return json.data as T;
}

export function fmtTime(unix: number | undefined): string {
    if (!unix) return '-';
    return new Date(unix * 1000).toLocaleString();
}

export function satToBTC(sats: number | undefined | null): string {
    if (sats === null || sats === undefined) return '-';
    return (sats / 1e8).toFixed(8);
}

export function hashShort(hash: string): string {
    return hash.slice(0, 16) + '…' + hash.slice(-8);
}
