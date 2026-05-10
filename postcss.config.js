import purgecss from '@fullhuman/postcss-purgecss';

const purgeCssPlugin = purgecss({
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    safelist: {
        standard: [
            'active',
            'fade',
            'show',
            'modal-backdrop',
            'modal-open',
            'showing',
            'hiding',
        ],
        deep: [
            /^modal/,
            /^toast/,
            /^tooltip/,
            /^popover/,
            /^tab-pane/,
        ],
    },
});

export default {
    plugins: process.env.NODE_ENV === 'production' ? [purgeCssPlugin] : [],
};