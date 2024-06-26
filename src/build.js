import { mkdir, rm, stat, readdir, writeFile, cp } from 'node:fs/promises';

const PLUGIN_PATH = './src/hljs-enhanced-code-blocks';
const DIST_PATH = './dist/hljs-enhanced-code-blocks';
const PACKAGE_STYLES_PATH = './node_modules/@highlightjs/cdn-assets/styles';
const THEMES_PATH = `${DIST_PATH}/css/hljs-themes`;

try {
    const stats = await stat('./dist');
    if (stats.isDirectory()) {
        await rm('./dist', { recursive: true, force: true });
        await cp(PLUGIN_PATH, DIST_PATH, { recursive: true });
    }
} catch(e) {
    await cp(PLUGIN_PATH, DIST_PATH, { recursive: true });
}

console.log(`Copied ${PLUGIN_PATH} -> ${DIST_PATH}`);

const hljsSrc = './node_modules/@highlightjs/cdn-assets/highlight.min.js';
const hljsDest = `${DIST_PATH}/js/highlight.min.js`;

await cp(hljsSrc, hljsDest);

console.log(`Copied ${hljsSrc} -> ${hljsDest}`);

const minStyles = (await readdir(PACKAGE_STYLES_PATH, { recursive: true }))
    .filter((style) => style.endsWith('.min.css'))
    .map((style) => style.replace('\\', '/').replace('.min.css', ''));

const themeDirectories = (await readdir(PACKAGE_STYLES_PATH, { recursive: true, withFileTypes: true }))
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);

for await (const directory of themeDirectories) {
    await mkdir(`${THEMES_PATH}/${directory}`, { recursive: true });
    console.log(`Made directory: ${THEMES_PATH}/${directory}`);
}

for await (const style of minStyles) {
    const src = `${PACKAGE_STYLES_PATH}/${style}.min.css`;
    const dest = `${THEMES_PATH}/${style}.min.css`;
    await cp(src, dest, { recursive: true });
    console.log(`Copied ${src} -> ${dest}`);
}

const settingsJson = JSON.stringify({ themes: minStyles }, null, 2);
await writeFile(`${DIST_PATH}/settings.json`, settingsJson);

console.log(`Created ${DIST_PATH}/settings.json`);

console.log('Done!');
