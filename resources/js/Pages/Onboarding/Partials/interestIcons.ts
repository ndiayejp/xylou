import {
    Blocks,
    Bone,
    Car,
    FlaskConical,
    Flag,
    Gamepad2,
    Leaf,
    Mic,
    Music,
    Orbit,
    Palette,
    PawPrint,
    Puzzle,
    Volleyball,
    Waves,
} from '@lucide/vue';
import type { Component } from 'vue';

// Pictogramme de chaque passion du référentiel (clé `interests.key`).
export const interestIcons: Record<string, Component> = {
    football: Volleyball,
    space: Orbit,
    lego: Blocks,
    animals: PawPrint,
    dinosaurs: Bone,
    music: Music,
    drawing: Palette,
    video_games: Gamepad2,
    cars: Car,
    ocean: Waves,
    horse_riding: Flag,
    experiments: FlaskConical,
    puzzles: Puzzle,
    singing_dancing: Mic,
    nature: Leaf,
};
