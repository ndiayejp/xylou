import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import NavigationProgress from '../partials/NavigationProgress.vue';

// Inertia diffuse ses événements de navigation sur document (inertia:start, inertia:finish).
function emit(name: 'start' | 'finish') {
    document.dispatchEvent(new CustomEvent(`inertia:${name}`, { detail: { visit: {} } }));
}

describe('NavigationProgress', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('n’apparaît qu’après 250 ms, décorative, puis disparaît à la fin', async () => {
        const wrapper = mount(NavigationProgress);

        emit('start');
        await vi.advanceTimersByTimeAsync(200);
        expect(wrapper.find('[data-navigation-progress]').exists()).toBe(false);

        await vi.advanceTimersByTimeAsync(100);
        const bar = wrapper.get('[data-navigation-progress]');
        expect(bar.attributes('aria-hidden')).toBe('true');
        expect(bar.find('[role]').exists()).toBe(false);

        emit('finish');
        await wrapper.vm.$nextTick();
        expect(wrapper.find('[data-navigation-progress]').exists()).toBe(false);
    });

    it('une navigation rapide ne l’affiche jamais', async () => {
        const wrapper = mount(NavigationProgress);

        emit('start');
        await vi.advanceTimersByTimeAsync(100);
        emit('finish');
        await vi.advanceTimersByTimeAsync(500);

        expect(wrapper.find('[data-navigation-progress]').exists()).toBe(false);
    });
});
