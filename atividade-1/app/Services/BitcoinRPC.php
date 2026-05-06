<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class BitcoinRPC
{
    private string $host;
    private int $port;
    private string $wallet;
    private string $network;
    private string $user;
    private string $password;
    private string $url;

    /**
     * Cliente JSON-RPC minimalista.
     * - Usa RPC_USER/RPC_PASS se existir
     * - Senão tenta cookie auth (ideal local)
     */
    public function __construct()
    {
        $this->host = env('RPC_HOST', '127.0.0.1');
        $this->port = (int) env('RPC_PORT', 18443);
        $this->wallet = trim(env('RPC_WALLET', 'minerador'));
        $this->network = trim(env('BTC_NETWORK', 'regtest'));

        $this->user = env('RPC_USER', 'teste');
        $this->password = env('RPC_PASS', 'teste');

        if (empty($this->user) || empty($this->password)) {
            [$this->user, $this->password] = $this->readCookie();
        }

        $this->url = $this->buildUrl();
    }

    private function buildUrl(): string
    {
        // Wallet endpoint é /wallet/<name> (opcional)
        if (!empty($this->wallet)) {
            return "http://{$this->host}:{$this->port}/wallet/{$this->wallet}";
        }

        return "http://{$this->host}:{$this->port}/";
    }

    private function readCookie(): array
    {
        // Descobre caminho padrão do cookie por rede
        $homeDir = getenv('HOME');
        if (!$homeDir) {
            $homeDir = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        $defaultBase = $homeDir ? rtrim($homeDir, '/\\') . DIRECTORY_SEPARATOR . '.bitcoin' : '.bitcoin';
        $base = env('BTC_DATADIR', $defaultBase);
        $net = strtolower($this->network);

        $cookiePath = match ($net) {
            'main' => $base . DIRECTORY_SEPARATOR . '.cookie',
            'testnet' => $base . DIRECTORY_SEPARATOR . 'testnet3' . DIRECTORY_SEPARATOR . '.cookie',
            'regtest' => $base . DIRECTORY_SEPARATOR . 'regtest' . DIRECTORY_SEPARATOR . '.cookie',
            'signet' => $base . DIRECTORY_SEPARATOR . 'signet' . DIRECTORY_SEPARATOR . '.cookie',
            default => $base . DIRECTORY_SEPARATOR . '.cookie',
        };

        if (!file_exists($cookiePath)) {
            throw new BitcoinRPCError(
                "Não achei cookie RPC em {$cookiePath}. " .
                "Defina RPC_USER/RPC_PASS ou ajuste BTC_NETWORK/BTC_DATADIR."
            );
        }

        $content = trim(file_get_contents($cookiePath));

        // formato: user:password
        if (!str_contains($content, ':')) {
            throw new BitcoinRPCError("Cookie inválido em {$cookiePath}");
        }

        return explode(':', $content, 2);
    }

    public function call(string $method, array $params = []): mixed
    {
        $payload = [
            'jsonrpc' => '1.0',
            'id' => 'corecraft-aula1',
            'method' => $method,
            'params' => $params,
        ];

        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url, $payload);
        } catch (ConnectionException $e) {
            throw new BitcoinRPCError("Falha de rede ao chamar RPC: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new BitcoinRPCError("Falha ao chamar RPC: " . $e->getMessage());
        }

        if ($response->status() !== 200) {
            throw new BitcoinRPCError("RPC HTTP {$response->status()}: " . substr($response->body(), 0, 200));
        }

        $data = $response->json();

        if (isset($data['error']) && !is_null($data['error'])) {
            $errorMsg = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
            throw new BitcoinRPCError((string) $errorMsg);
        }

        return $data['result'] ?? null;
    }
}
