import forms from '@tailwindcss/forms';
import type { Config } from 'tailwindcss';
import defaultTheme from 'tailwindcss/defaultTheme';
import {
    borderRadius,
    avatar,
    boxShadow,
    colors,
    fontFamily,
    fontSize,
    mastery,
    minTouchTarget,
    motion,
    reward,
    subjects,
} from './resources/js/design/tokens';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            colors: { ...colors, subject: subjects, mastery, avatar, reward },
            fontFamily: {
                sans: [fontFamily.sans, ...defaultTheme.fontFamily.sans],
                kid: [fontFamily.kid, ...defaultTheme.fontFamily.sans],
            },
            fontSize,
            borderRadius,
            boxShadow,
            minHeight: { touch: minTouchTarget.adult, 'kid-touch': minTouchTarget.kid },
            minWidth: { touch: minTouchTarget.adult, 'kid-touch': minTouchTarget.kid },
            transitionDuration: motion.duration,
            transitionTimingFunction: motion.easing,
            keyframes: {
                grow: { from: { transform: 'scaleX(0)' }, to: { transform: 'scaleX(1)' } },
                pop: {
                    from: { transform: 'scale(.6)', opacity: '0' },
                    to: { transform: 'scale(1)', opacity: '1' },
                },
                breathe: {
                    '0%, 100%': { transform: 'scale(1)', opacity: '1' },
                    '50%': { transform: 'scale(1.06)', opacity: '.85' },
                },
                shimmer: { to: { backgroundPosition: '-200% 0' } },
                navigation: {
                    from: { transform: 'translateX(-100%)' },
                    to: { transform: 'translateX(300%)' },
                },
            },
            animation: {
                grow: `grow ${motion.duration.fill} ${motion.easing['out-expo']} both`,
                pop: `pop ${motion.duration.validate} ${motion.easing.spring} both`,
                breathe: `breathe ${motion.duration.breathe} ease-in-out infinite`,
                shimmer: 'shimmer 1.4s linear infinite',
                navigation: 'navigation 1.2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
} satisfies Config;
