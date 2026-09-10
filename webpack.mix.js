const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | QIEC — restored Mix pipeline (was missing from repo)
 |--------------------------------------------------------------------------
 | Rebuilds the gitignored compiled bundles from sources:
 *  admin (Stisla-based):  sass/admin/app.scss + js/admin
 *  design_1 (panel/web):  sass/design_1 + js/design_1
 | Third-party + Stisla files that have no local source are copied verbatim.
 */

mix.options({ processCssUrls: false, terser: { extractComments: false } });

// ── Admin CSS ────────────────────────────────────────────────
// style.css = genuine Stisla base (layout) + project customizations on top.
// (app.scss alone contains no Stisla layout rules.)
mix.sass('resources/sass/admin/app.scss', 'public/assets/admin/css/admin-app.css');
mix.styles(
    ['resources/sass/admin/stisla-base.css', 'public/assets/admin/css/admin-app.css'],
    'public/assets/admin/css/style.css'
);

// ── Admin JS ─────────────────────────────────────────────────
mix.js('resources/js/admin/admin.js', 'public/assets/admin/js/admin.min.js');
mix.js(
    'resources/js/admin/parts/ai-content-generator.js',
    'public/assets/admin/js/parts/ai-content-generator.min.js'
);

// ── design_1 CSS ─────────────────────────────────────────────
mix.sass('resources/sass/design_1/app.scss', 'public/assets/design_1/css/app.min.css');
mix.sass('resources/sass/design_1/panel.scss', 'public/assets/design_1/css/panel.min.css');
mix.sass('resources/sass/design_1/rtl-app.scss', 'public/assets/design_1/css/rtl-app.min.css');

// ── design_1 JS ──────────────────────────────────────────────
mix.js('resources/js/design_1/app.js', 'public/assets/design_1/js/app.min.js');
mix.js('resources/js/design_1/panel/public.js', 'public/assets/design_1/js/panel/public.min.js');

const partsDir = 'resources/js/design_1/parts';
fs.readdirSync(partsDir).forEach((file) => {
    if (file.endsWith('.js')) {
        const name = path.basename(file, '.js');
        mix.js(`${partsDir}/${file}`, `public/assets/design_1/js/parts/${name}.min.js`);
    }
});

// ── Third-party vendors (exact public paths expected by layouts) ─
mix.copy('node_modules/jquery/dist/jquery.min.js', 'public/assets/admin/vendor/jquery/jquery-3.3.1.min.js');
mix.copy('node_modules/popper.js/dist/umd/popper.min.js', 'public/assets/admin/vendor/poper/popper.min.js');
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/assets/admin/vendor/bootstrap/bootstrap.min.css');
mix.copy('node_modules/bootstrap/dist/js/bootstrap.min.js', 'public/assets/admin/vendor/bootstrap/bootstrap.min.js');
mix.copy('node_modules/moment/min/moment.min.js', 'public/assets/admin/vendor/moment/moment.min.js');
mix.copy('node_modules/daterangepicker/daterangepicker.js', 'public/assets/admin/vendor/daterangepicker/daterangepicker.min.js');
mix.copy('node_modules/daterangepicker/daterangepicker.css', 'public/assets/admin/vendor/daterangepicker/daterangepicker.min.css');
mix.copy('node_modules/jquery.nicescroll/jquery.nicescroll.js', 'public/assets/admin/vendor/nicescroll/jquery.nicescroll.min.js');

mix.copy('node_modules/select2/dist/js/select2.min.js', 'public/assets/default/vendors/select2/select2.min.js');
mix.copy('node_modules/select2/dist/css/select2.min.css', 'public/assets/default/vendors/select2/select2.min.css');
mix.copy('node_modules/sweetalert2/dist/sweetalert2.min.js', 'public/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js');
mix.copy('node_modules/jquery-toast-plugin/dist/jquery.toast.min.js', 'public/assets/default/vendors/toast/jquery.toast.min.js');
mix.copy('node_modules/jquery-toast-plugin/dist/jquery.toast.min.css', 'public/assets/default/vendors/toast/jquery.toast.min.css');
mix.copy('node_modules/@fortawesome/fontawesome-free/css/all.min.css', 'public/assets/vendors/fontawesome/css/all.min.css');
mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/assets/vendors/fontawesome/webfonts');

mix.then(() => {
    const adminCss = 'public/assets/admin/css';
    const adminJs = 'public/assets/admin/js';

    // Stisla originals (downloaded from stisla@2.3.0 — no local source in repo).
    // NOTE: custom.css is intentionally EXCLUDED: it is built from
    // resources/sass/admin/qiec-theme.scss (QIEC theme) and must never
    // be overwritten by the Stisla placeholder.
    fs.copyFileSync('/tmp/stisla.js', `${adminJs}/stisla.js`);
    fs.copyFileSync('/tmp/stisla-vendor/scripts.js', `${adminJs}/scripts.js`);
    fs.copyFileSync('/tmp/stisla-vendor/components.css', `${adminCss}/components.css`);
    fs.copyFileSync('/tmp/stisla-vendor/rtl.css', `${adminCss}/rtl.css`);
    // extra.min.css has no known source; keep as empty placeholder (layout-tolerated)
    fs.writeFileSync(`${adminCss}/extra.min.css`, '/* qiec: no source — placeholder */\n');

    // remove intermediate sass output (already merged into style.css)
    for (const f of [`${adminCss}/admin-app.css`, `${adminCss}/admin-app.css.map`]) {
        if (fs.existsSync(f)) {
            fs.unlinkSync(f);
        }
    }

    console.log('Qice admin vendor sync done');
});
