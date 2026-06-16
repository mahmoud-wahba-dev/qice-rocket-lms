const { addDynamicIconSelectors } = require('@iconify/tailwind');

function buildPxScale(start, end, step = 1, unit = 'rem', factor = 16) {
    const scale = {};
    for (let value = start; value <= end; value += step) {
        scale[`${value}px`] = `${value / factor}${unit}`;
    }
    return scale;
}

module.exports = {
    content: [
        "./resources/views/landing_v1/**/*.blade.php",
        "./resources/js/landing_v1.js",
        "./node_modules/flyonui/dist/js/*.js",
    ],
    important: "#landing-v1-app",
    theme: {
        extend: {
            fontFamily: { ibm: ['var(--font-ibm)'] },
            fontSize: { ...buildPxScale(10, 90) },
            colors: {
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                gold: 'var(--color-gold)',
                blue: 'var(--color-blue)',
                black: 'var(--color-black)',
                '77': 'var(--color-77)',
                'f7': 'var(--color-f7)',
                'e3': 'var(--color-e3)',
                'card-text': 'var(--color-card-text)',
            },
            borderRadius: { ...buildPxScale(4, 100) },
            boxShadow: { glow: "0 0 60px rgba(34, 211, 238, 0.15)" },
        },
    },
    plugins: [
        require('flyonui'),
        require('flyonui/plugin'),
        addDynamicIconSelectors({ prefix: 'icon' }),
    ],
};
