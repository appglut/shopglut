import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

const page = (name) => resolve(__dirname, `assets/src/pages/index-${name}.jsx`);

export default defineConfig({
  plugins: [react()],
  // Relative URLs: assets are served from the plugin folder, not the site root.
  base: './',
  build: {
    outDir: 'resources',
    emptyOutDir: true,
    // PHP reads .vite/manifest.json to load only the CSS of the current page's entry.
    manifest: true,
    rollupOptions: {
      input: {
        dashboard: page('dashboard'),
        modules: page('modules'),
        layouts: page('layouts'),
        panel: page('panel'),
        tools: page('tools'),
        app: page('app'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        // Split the big libraries from our own code: the browser caches them across plugin updates.
        manualChunks(id) {
          if (!id.includes('node_modules')) return undefined;
          if (/node_modules\/(react|react-dom|scheduler)\//.test(id)) return 'vendor-react';
          if (/node_modules\/(@fortawesome)\//.test(id)) return 'vendor-fontawesome';
          if (/node_modules\/(antd|@ant-design|rc-[^/]+|@rc-component|@ctrl|dayjs|@emotion|stylis)\//.test(id)) return 'vendor-antd';
          return undefined;
        },
        assetFileNames: 'assets/[name][extname]',
      },
    },
    sourcemap: false,
    minify: 'esbuild',
  },
  server: { hmr: false },
});
