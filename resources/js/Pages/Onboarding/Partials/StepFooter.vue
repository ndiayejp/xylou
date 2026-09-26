<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';

// Pied de page des écrans de l'onboarding : Retour (lien) et Continuer (envoie le formulaire).
defineProps<{ backHref?: string; form: string; processing: boolean }>();
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
    <XButton type="submit" :form="form" size="lg" :loading="processing">
        {{ $t('onboarding.continue') }}
        <ArrowRight :size="20" aria-hidden="true" />
    </XButton>
</template>
