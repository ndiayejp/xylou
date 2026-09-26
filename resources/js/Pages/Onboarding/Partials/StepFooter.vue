<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';

// Pied de page des écrans de l'onboarding : Retour (lien), action secondaire (slot), Continuer (envoi).
withDefaults(
    defineProps<{ backHref?: string; form: string; processing: boolean; submitLabel?: string }>(),
    { backHref: undefined, submitLabel: undefined },
);
</script>

<template>
    <Link
        v-if="backHref"
        :href="backHref"
        class="inline-flex h-14 items-center gap-2 rounded-button px-5 font-bold text-primary-strong hover:bg-tint"
    >
        <ArrowLeft :size="20" aria-hidden="true" />
        {{ $t('onboarding.back') }}
    </Link>
    <span v-else />
    <div class="flex flex-wrap items-center gap-5">
        <slot name="secondary" />
        <XButton type="submit" :form="form" size="lg" :loading="processing">
            {{ submitLabel ?? $t('onboarding.continue') }}
            <ArrowRight :size="20" aria-hidden="true" />
        </XButton>
    </div>
</template>
