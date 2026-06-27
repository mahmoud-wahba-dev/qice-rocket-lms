/**
 * Compress landing_v1 images for production page speed.
 * Run: node scripts/optimize-landing-images.js
 */
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const base = path.join(__dirname, '..', 'public', 'assets', 'landing_v1');

const jobs = [
    { file: 'img/home/hero-bg.webp', width: 1920, quality: 72 },
    { file: 'img/home/news1.webp', width: 800, quality: 78 },
    { file: 'img/home/hero-img3.webp', width: 900, quality: 82 },
];

async function compress({ file, width, quality }) {
    const target = path.join(base, file);
    const output = path.join(base, file.replace(/(\.\w+)$/, '-opt$1'));
    const meta = await sharp(target).metadata();
    const before = fs.statSync(target).size;
    await sharp(target)
        .resize({ width, withoutEnlargement: true })
        .webp({ quality })
        .toFile(output);
    const after = fs.statSync(output).size;
    console.log(`${file}: ${meta.width}x${meta.height} ${before} -> ${after} bytes (${path.basename(output)})`);
    return { meta, output, target };
}

(async () => {
    for (const job of jobs) {
        await compress(job);
    }
    console.log('Done. Use *-opt.webp files (originals kept if locked).');
})().catch((err) => {
    console.error(err);
    process.exit(1);
});
