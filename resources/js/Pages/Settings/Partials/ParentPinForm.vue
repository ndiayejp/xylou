<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { KeyRound } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XInput from '@/Components/ui/XInput.vue';
import XTag from '@/Components/ui/XTag.vue';

defineProps<{ enabled: boolean }>();

const form = useForm({ pin: '', pin_confirmation: '' });

const save = () =>
    form.put(route('parent-pin.update'), {
        preserveScroll: true,
        onFinish: () => form.reset(),
    });
const remove = () => router.delete(route('parent-pin.destroy'), { preserveScroll: true });
</script>

<template>
    <XCard as="section" padding="lg" class="flex flex-col gap-5" aria-labelledby="code-parent">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="code-parent" class="text-h3">{{ $t('security.parentPin.title') }}</h2>
            <XTag :tone="enabled ? 'green' : 'neutral'" :icon="KeyRound">
                {{ enabled ? $t('security.parentPin.enabled') : $t('security.parentPin.disabled') }}
            </XTag>
        </div>
        <p class="text-muted">{{ $t('security.parentPin.description') }}</p>

        <form class="flex max-w-sm flex-col gap-4" novalidate @submit.prevent="save">
            <XInput
                v-model="form.pin"
                type="password"
                :label="$t('security.parentPin.pin')"
                :error="form.errors.pin"
                inputmode="numeric"
                maxlength="6"
                autocomplete="new-password"
                required
            />
            <XInput
                v-model="form.pin_confirmation"
                type="password"
                :label="$t('security.parentPin.pinConfirmation')"
                inputmode="numeric"
                maxlength="6"
                autocomplete="new-password"
                required
            />
            <div class="flex flex-wrap gap-2">
                <XButton type="submit" :loading="form.processing">
                    {{ $t('security.parentPin.save') }}
                </XButton>
                <XButton v-if="enabled" variant="ghost" @click="remove">
                    {{ $t('security.parentPin.remove') }}
                </XButton>
            </div>
        </form>
    </XCard>
</template>
