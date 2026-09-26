<script setup lang="ts">
// Seule l'illustration change avec l'univers : la structure de l'écran reste la même (§10.3).
// Couleurs d'illustration propres à chaque scène, hors jetons d'interface.
export type XUniverse = 'space' | 'football' | 'forest';

withDefaults(defineProps<{ universe: XUniverse; label?: string }>(), { label: undefined });

const stripes = [0, 50, 100, 150, 200, 250, 300, 350];
const stars: [number, number, number][] = [
    [150, 40, 3],
    [210, 80, 2.5],
    [40, 120, 2.5],
    [250, 30, 3.5],
    [110, 100, 2],
];
const wheels = [108, 135, 162];
</script>

<template>
    <svg
        viewBox="0 0 400 260"
        preserveAspectRatio="xMidYMid slice"
        class="block size-full"
        :role="label ? 'img' : undefined"
        :aria-label="label"
        :aria-hidden="label ? undefined : 'true'"
        :data-universe="universe"
    >
        <g v-if="universe === 'space'">
            <rect width="400" height="260" fill="#ECEBFF" />
            <circle cx="330" cy="70" r="40" fill="#FFB84D" />
            <ellipse
                cx="330"
                cy="70"
                rx="70"
                ry="14"
                fill="none"
                stroke="#5B5CE2"
                stroke-width="6"
                transform="rotate(-14 330 70)"
            />
            <circle cx="70" cy="56" r="16" fill="#7C83FD" />
            <circle v-for="[cx, cy, r] in stars" :key="`${cx}-${cy}`" :cx :cy :r fill="#FFFFFF" />
            <path d="M0 196 Q90 172 200 192 T400 186 V260 H0z" fill="#F4A27A" />
            <ellipse cx="80" cy="226" rx="22" ry="6" fill="#E3865D" />
            <ellipse cx="320" cy="236" rx="30" ry="7" fill="#E3865D" />
            <path
                d="M60 214 H340"
                stroke="#FFFFFF"
                stroke-width="3"
                stroke-dasharray="8 8"
                stroke-linecap="round"
            />
            <path d="M340 214 V176" stroke="#202238" stroke-width="3" />
            <path d="M340 176 h22 l-6 7 6 7 h-22z" fill="#55C98A" />
            <rect x="118" y="150" width="30" height="6" rx="2" fill="#5B5CE2" />
            <path d="M133 156 V168" stroke="#202238" stroke-width="3" />
            <rect
                x="96"
                y="166"
                width="78"
                height="28"
                rx="9"
                fill="#FFFFFF"
                stroke="#202238"
                stroke-width="3"
            />
            <rect x="150" y="146" width="16" height="12" rx="3" fill="#202238" />
            <path d="M158 158 V166" stroke="#202238" stroke-width="3" />
            <g v-for="cx in wheels" :key="cx">
                <circle :cx cy="198" r="10" fill="#202238" />
                <circle :cx cy="198" r="3.5" fill="#FFB84D" />
            </g>
        </g>

        <g v-else-if="universe === 'football'">
            <rect width="400" height="260" fill="#E4F6EB" />
            <rect v-for="x in stripes" :key="x" :x width="25" height="260" fill="#D3F0DE" />
            <path d="M200 0 V260" stroke="#FFFFFF" stroke-width="4" />
            <circle cx="200" cy="130" r="44" fill="none" stroke="#FFFFFF" stroke-width="4" />
            <path d="M400 70 H350 V190 H400" fill="none" stroke="#FFFFFF" stroke-width="4" />
            <path d="M372 96 h28 v68 h-28z" fill="#FFFFFF" opacity=".6" />
            <path d="M372 96 V164" stroke="#202238" stroke-width="4" />
            <path
                d="M70 200 Q210 60 360 128"
                fill="none"
                stroke="#5B5CE2"
                stroke-width="3"
                stroke-dasharray="8 8"
                stroke-linecap="round"
            />
            <circle cx="360" cy="128" r="18" fill="#FFFFFF" stroke="#202238" stroke-width="3" />
            <path d="M360 118 l8 6 -3 9 h-10 l-3 -9z" fill="#202238" />
            <circle cx="62" cy="150" r="13" fill="#F2C29B" />
            <rect x="46" y="166" width="32" height="38" rx="12" fill="#5B5CE2" />
            <text
                x="62"
                y="192"
                text-anchor="middle"
                fill="#FFFFFF"
                class="font-kid text-[15px] font-extrabold"
            >
                10
            </text>
            <rect x="50" y="202" width="10" height="26" rx="5" fill="#202238" />
            <rect x="66" y="202" width="10" height="26" rx="5" fill="#202238" />
        </g>

        <g v-else>
            <rect width="400" height="260" fill="#FFF1DC" />
            <circle cx="330" cy="60" r="30" fill="#FFB84D" />
            <path d="M0 170 Q120 120 240 160 T400 150 V260 H0z" fill="#BFE3C6" />
            <path d="M0 205 Q150 175 280 200 T400 196 V260 H0z" fill="#9ED3AA" />
            <rect x="54" y="120" width="10" height="60" rx="4" fill="#9A6A48" />
            <circle cx="59" cy="110" r="30" fill="#55C98A" />
            <rect x="116" y="138" width="8" height="46" rx="4" fill="#9A6A48" />
            <circle cx="120" cy="128" r="22" fill="#3FAF74" />
            <rect x="330" y="128" width="10" height="56" rx="4" fill="#9A6A48" />
            <circle cx="335" cy="118" r="28" fill="#3FAF74" />
            <path
                d="M60 222 H330"
                stroke="#FFFFFF"
                stroke-width="3"
                stroke-dasharray="8 8"
                stroke-linecap="round"
            />
            <ellipse cx="200" cy="200" rx="34" ry="16" fill="#F08A4B" />
            <path d="M166 196 Q130 170 140 206 Q150 214 170 206z" fill="#F08A4B" />
            <path d="M140 206 Q136 196 146 190 Q146 204 152 210z" fill="#FFFFFF" />
            <path d="M224 190 l18 -26 l6 20 l14 -2 l-6 18 l-22 6z" fill="#F08A4B" />
            <path d="M242 164 l2 12 l-7 -4z" fill="#202238" />
            <path d="M256 180 l6 2" stroke="#202238" stroke-width="3" stroke-linecap="round" />
            <circle cx="246" cy="186" r="2.5" fill="#202238" />
            <rect x="180" y="210" width="8" height="16" rx="4" fill="#202238" />
            <rect x="212" y="210" width="8" height="16" rx="4" fill="#202238" />
        </g>
    </svg>
</template>
