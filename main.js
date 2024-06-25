import { mkdir, rm, stat, readdir, writeFile } from 'node:fs/promises';

const DIST_PATH = './dist';

const styles = await readdir('./node_modules/highlight.js/styles', { recursive: true });
const minStyles = styles
    .filter((value) => value.endsWith('.min.css'))
    .map((value) => value.replace('\\', '/').replace('.min.css', ''));

const json = JSON.stringify({ themes: minStyles }, null, 2);

try {
    const stats = await stat(DIST_PATH);
    if (stats.isDirectory()) {
        console.log(`DIRECTORY EXISTS: '${DIST_PATH}'`);
        await rm(DIST_PATH, { recursive: true, force: true });
        await mkdir(DIST_PATH);
    }
} catch(e) {
    await mkdir(DIST_PATH);
}

await writeFile(`${DIST_PATH}/styles.json`, json);

console.log('Done!');
