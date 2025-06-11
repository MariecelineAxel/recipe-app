export default defineConfig({
  base: './', // ← ajoute cette ligne
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: process.env.PORT || 3000,
    host: true,
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
});
