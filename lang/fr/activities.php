<?php

declare(strict_types=1);

return [
    // L'activité a changé entre-temps (autre onglet, suppression définitive…) : la page se recharge.
    'refused' => 'Cette action n’est plus possible sur cette activité. La liste a été mise à jour.',

    'fields' => [
        'title' => 'titre',
        'skill' => 'compétence',
        'items' => 'questions',
        'prompt' => 'énoncé',
        'explanation' => 'explication',
        'value' => 'réponse attendue',
        'tolerance' => 'tolérance',
    ],

    // Pour valider : ce qui manque à une question.
    'editor' => [
        'prompt' => 'Écrivez l’énoncé de la question.',
        'explanation' => 'Ajoutez l’explication montrée à l’enfant quand ce n’est pas encore ça.',
        'value' => 'Indiquez le nombre attendu.',
        'accepted' => 'Indiquez au moins une réponse acceptée.',
        'choices' => 'Proposez au moins deux réponses, toutes remplies.',
        'correct' => 'Choisissez la bonne réponse.',
        'correct_many' => 'Cochez au moins une bonne réponse.',
    ],
];
