import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const codespacesName = process.env.CODESPACE_NAME;
const codespacesDomain = process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN;
const codespacesHost = codespacesName && codespacesDomain
    ? `${codespacesName}-5173.${codespacesDomain}`
    : null;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        origin: codespacesHost ? `https://${codespacesHost}` : 'http://localhost:5173',
        ...(codespacesHost
            ? {
                hmr: {
                    host: codespacesHost,
                    protocol: 'wss',
                    clientPort: 443,
                },
            }
            : {}),
    },
});