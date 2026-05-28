import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";
import tailwindcss from "@tailwindcss/vite";

// 1. Define the absolute path to your local package folder
// (Change this to match your actual local path on Laragon!)
const scramblePath = "C:/laragon/www/scramble";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                `${scramblePath}/resources/js/docs.js`,
            ],
            refresh: [
                "resources/views/**",
                "routes/**",
                `${scramblePath}/resources/views/**`,
            ],
        }),
        tailwindcss(),
    ],
    // 2. Force Vite's filesystem watcher to explicitly track the outside directory
    server: {
        watch: {
            ignored: ["!**/vendor/dedoc/scramble/**"], // Ensure vendor isn't entirely blocked
            usePolling: true, // 👈 Essential for Windows filesystem event tracking across paths
        },
    },
    resolve: {
        alias: {
            alpinejs: path.resolve(
                __dirname,
                "node_modules/alpinejs/dist/module.esm.js",
            ),
        },
    },
    build: {
        outDir: "resources/dist",
    },
});
