// Page d'accueil publique (maquette « Landing »). Pas de « @ » ni de « | » : caractères spéciaux de vue-i18n.
export default {
    meta: {
        title: 'Accompagnement scolaire personnalisé',
        description:
            'Xylou adapte les activités scolaires aux passions et au niveau de chaque enfant, du CP à la 3e. Chaque activité est validée par un adulte. Sans publicité.',
    },
    hero: {
        eyebrow: 'Accompagnement scolaire personnalisé',
        titleStart: 'Et si les devoirs entraient enfin dans',
        titleHighlight: 'son monde',
        titleEnd: ' ?',
        text: 'Xylou adapte les activités scolaires aux passions et au niveau de chaque enfant pour rendre l’apprentissage plus personnel, plus accessible et plus motivant.',
        cta: 'Créer un profil',
        secondary: 'Découvrir comment ça marche',
        badges: {
            noAds: 'Sans publicité',
            validated: 'Validé par un adulte',
            control: 'Données sous votre contrôle',
        },
        preview: {
            greeting: 'Salut Emma !',
            today: 'Aujourd’hui, on part dans l’espace',
            subject: 'Mathématiques',
            duration: '10 min',
            mission: 'Mission Mars',
            start: 'Commencer',
            universes: 'Univers',
            football: 'Football',
            space: 'Espace',
            animals: 'Animaux',
        },
    },
    promise: {
        eyebrow: 'Notre promesse',
        title: 'Au lieu de demander à l’enfant de s’adapter aux exercices, nous adaptons les exercices à son univers.',
        personal: {
            title: 'Personnel',
            text: 'Ses passions deviennent le décor : football, espace, animaux… L’exercice lui parle dès la première ligne.',
        },
        serious: {
            title: 'Pédagogiquement sérieux',
            text: 'La compétence, le niveau et l’objectif restent identiques. Seuls le contexte et la présentation changent.',
        },
        motivating: {
            title: 'Motivant, sans pression',
            text: 'Indices, feedback bienveillant et réussites personnelles. Aucun classement, aucune comparaison.',
        },
    },
    demo: {
        eyebrow: 'Démonstration',
        title: 'Même exercice. Un autre monde.',
        text: 'Voici comment Xylou transforme un énoncé classique de CM2 sur la vitesse moyenne.',
        classic: {
            label: 'Exercice classique',
            statement:
                'Un véhicule parcourt 240 m en 3 min. Calcule sa vitesse moyenne en mètres par minute.',
            verdict: 'Correct… mais pas très parlant.',
        },
        football: {
            for: 'Pour Lucas, fan de foot',
            title: 'Contre-attaque !',
            statement:
                'Ton ailier remonte 240 m de terrain en 3 minutes de jeu. Quelle est sa vitesse moyenne en m/min ?',
            alt: 'Un terrain de football, un ballon file vers le but',
        },
        space: {
            for: 'Pour Maé, fan d’espace',
            title: 'Mission Mars',
            statement:
                'Le rover doit parcourir 240 mètres en 3 minutes. Quelle est sa vitesse moyenne ?',
            alt: 'Un rover roule sur Mars, une planète à anneaux dans le ciel',
        },
        forest: {
            for: 'Pour Nour, fan d’animaux',
            title: 'Le renard file',
            statement:
                'Le renard traverse 240 m de forêt en 3 minutes. À quelle vitesse moyenne court-il ?',
            alt: 'Un renard traverse une forêt au soleil',
        },
        same: {
            skill: 'Compétence identique : vitesse moyenne',
            level: 'Niveau identique : CM2',
            answer: 'Réponse identique : 80 m/min',
        },
    },
    how: {
        eyebrow: 'Comment ça marche',
        title: 'Quatre étapes, un adulte toujours dans la boucle',
        steps: {
            profile: {
                title: 'Créez son profil',
                text: 'Niveau, classe, objectifs et difficultés observées. 5 minutes, pas plus.',
            },
            universe: {
                title: 'Racontez son univers',
                text: 'Passions, sports, animaux, jeux… Xylou s’en sert comme décor pédagogique.',
            },
            validate: {
                title: 'Validez chaque activité',
                text: 'L’IA propose, vous relisez, modifiez ou régénérez. Rien n’est envoyé sans vous.',
            },
            follow: {
                title: 'Suivez sa progression',
                text: 'Des phrases claires, pas seulement des graphiques : ce qui avance, ce qui coince.',
            },
        },
    },
    audiences: {
        eyebrow: 'Pour qui ?',
        title: 'Chacun trouve sa place',
        kid: {
            title: 'Pour l’enfant',
            items: [
                'Des exercices dans son univers préféré',
                'Des indices quand il bloque, sans jugement',
                '« Pas encore » plutôt qu’un verdict',
                'Des réussites qui célèbrent l’effort',
            ],
        },
        parents: {
            title: 'Pour les parents',
            items: [
                'Comprendre où en est votre enfant, en clair',
                'Des activités courtes, prêtes et validées par vous',
                'Plusieurs enfants, un seul compte',
                'Un bilan à partager avec l’enseignant',
            ],
        },
        pros: {
            title: 'Pour les professionnels',
            items: [
                'Historique et compétences des profils partagés',
                'Difficultés récurrentes repérées',
                'Observations et recommandations d’activités',
                'Bilans exportables en PDF',
            ],
        },
    },
    example: {
        eyebrow: 'Exemple concret',
        title: 'Lucas, 11 ans, n’aimait pas les problèmes. Il adore le football.',
        text: 'Sa mère a indiqué son objectif : reprendre confiance en résolution de problèmes. Xylou lui propose des problèmes courts, en petites étapes, dans le contexte d’un match. La compétence travaillée ne change pas ; la porte d’entrée, si.',
        tags: {
            level: 'Mathématiques · 6e',
            passions: 'Football, espace, LEGO',
            steps: 'Petites étapes',
        },
        preview: {
            title: 'Ce qu’il faut retenir',
            caption: 'Vue parent · aperçu illustratif',
            ai: 'Résumé IA',
            summary:
                '« Cette semaine, Lucas a particulièrement progressé sur les fractions. Il rencontre encore des difficultés lorsqu’il faut comparer deux fractions de dénominateurs différents. »',
            fractions: 'Fractions',
            problems: 'Résolution de problèmes',
            progressing: 'En bonne voie',
            consolidate: 'À consolider',
        },
    },
    trust: {
        title: 'Vos données et celles de votre enfant restent sous votre contrôle.',
        text: 'Conçu pour respecter le RGPD et les principes de protection des mineurs. [Mentions et certifications à confirmer]',
        collect: {
            title: 'Vous décidez de ce qui est collecté',
            text: 'Seules les informations utiles à l’apprentissage. Rien n’est obligatoire au-delà du prénom et du niveau.',
        },
        share: {
            title: 'Partage choisi et réversible',
            text: 'Vous partagez un profil avec un professionnel, pour une durée donnée, et vous pouvez retirer l’accès à tout moment.',
        },
        ai: {
            title: 'IA transparente',
            text: 'Chaque activité générée est signalée comme telle, et relue par un adulte avant d’arriver chez l’enfant.',
        },
        delete: {
            title: 'Supprimer, c’est supprimer',
            text: 'Export et suppression du profil en quelques clics, depuis les paramètres.',
        },
    },
    testimonials: {
        eyebrow: 'Ils en parlent',
        title: 'À quoi ça ressemble au quotidien',
        disclaimer: 'Témoignages fictifs, fournis à titre d’illustration.',
        badge: 'Exemple fictif',
        items: [
            {
                quote: '« Ma fille ouvre ses maths quand l’exercice parle de chevaux. Et je sais exactement ce qu’elle a travaillé. »',
                name: 'Camille',
                role: 'Maman de Jade, CE1 — témoignage illustratif',
            },
            {
                quote: '« Le bilan m’a fait gagner du temps pour préparer l’équipe éducative. Les difficultés récurrentes sont claires. »',
                name: 'M. Diallo',
                role: 'Enseignant — témoignage illustratif',
            },
            {
                quote: '« J’aime le “Pas encore”. Mon fils ose réessayer au lieu d’abandonner. »',
                name: 'Thomas',
                role: 'Papa de Hugo, CM1 — témoignage illustratif',
            },
        ],
    },
    faq: {
        eyebrow: 'FAQ',
        title: 'Questions fréquentes',
        text: 'Une autre question ? Écrivez-nous, un humain vous répond.',
        contact: 'Nous contacter',
        items: [
            {
                question: 'Est-ce que Xylou remplace l’enseignant ?',
                answer: 'Non. Xylou prolonge le travail de classe à la maison, dans un contexte qui motive votre enfant. Les bilans aident justement à dialoguer avec l’enseignant.',
            },
            {
                question: 'Les exercices générés par l’IA sont-ils fiables ?',
                answer: 'La compétence, le niveau et les calculs sont fixés par Xylou, pas par l’IA : elle n’écrit que la mise en scène dans l’univers de votre enfant. Chaque activité est vérifiée automatiquement, puis relue par vous avant d’arriver chez votre enfant.',
            },
            {
                question: 'Dès quel âge ?',
                answer: 'Du CP à la 3e. Les activités suivent le niveau de classe que vous indiquez et s’ajustent au fil des réponses.',
            },
            {
                question: 'Mon enfant peut-il utiliser Xylou seul ?',
                answer: 'Oui, dans son propre espace, que vous ouvrez depuis votre appareil. Il n’a ni compte ni e-mail, et il ne peut pas revenir à votre espace sans votre code parent.',
            },
            {
                question: 'Comment fonctionne le partage avec un professionnel ?',
                answer: 'Vous invitez l’enseignant ou le professionnel, vous choisissez ce qu’il peut voir et pour combien de temps. Vous pouvez retirer l’accès à tout moment, et chaque consultation vous est visible.',
            },
            {
                question: 'Combien ça coûte ?',
                answer: 'Les offres et leurs tarifs seront annoncés prochainement. [Tarifs à compléter]',
            },
        ],
    },
    final: {
        title: 'Prêt à faire entrer les devoirs dans son monde ?',
        text: 'Créez le profil de votre enfant en 5 minutes.',
        cta: 'Créer un profil',
    },
};
