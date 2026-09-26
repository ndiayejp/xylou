// Pages légales. Les crochets [À compléter] signalent ce que seul l'éditeur peut fournir ;
// l'ensemble doit être validé par un juriste ou un DPO avant la mise en production (§11).
export default {
    updated: 'Dernière mise à jour : {date}',
    draft: 'Document en cours de validation juridique : certaines informations restent à compléter.',
    toc: 'Sur cette page',
    notice: {
        title: 'Mentions légales',
        description: 'Éditeur, hébergement et contact du service Xylou.',
        intro: 'Informations sur l’éditeur et l’hébergeur du service Xylou.',
        sections: [
            {
                id: 'editeur',
                title: 'Éditeur',
                paragraphs: [
                    'Xylou est édité par [À compléter : raison sociale, forme juridique, capital, adresse du siège, numéro RCS ou SIREN, numéro de TVA].',
                    'Directeur de la publication : [À compléter].',
                ],
            },
            {
                id: 'hebergement',
                title: 'Hébergement',
                paragraphs: [
                    'Le service et ses sauvegardes sont hébergés dans l’Union européenne par [À compléter : nom, adresse et téléphone de l’hébergeur].',
                ],
            },
            {
                id: 'contact',
                title: 'Contact',
                paragraphs: [
                    'Pour toute question sur le service ou sur vos données, écrivez-nous à l’adresse indiquée ci-dessous. Un humain vous répond.',
                ],
            },
            {
                id: 'propriete',
                title: 'Propriété intellectuelle',
                paragraphs: [
                    'Les textes, illustrations, la mascotte Xy et l’interface de Xylou sont protégés. Toute reproduction sans autorisation est interdite.',
                ],
            },
        ],
    },
    privacy: {
        title: 'Politique de confidentialité',
        description:
            'Quelles données Xylou collecte, pourquoi, combien de temps, et comment exercer vos droits.',
        intro: 'Xylou traite des données de mineurs. Nous en collectons le moins possible, uniquement pour l’apprentissage, et vous gardez la main sur tout ce qui est collecté et partagé.',
        sections: [
            {
                id: 'responsable',
                title: 'Responsable du traitement',
                paragraphs: [
                    'Le responsable du traitement est [À compléter : raison sociale de l’éditeur]. Délégué à la protection des données : [À compléter : nom ou service, moyen de contact].',
                ],
            },
            {
                id: 'donnees',
                title: 'Données collectées',
                paragraphs: [
                    'Pour le compte du parent : nom, adresse e-mail, mot de passe (stocké chiffré), et vos consentements horodatés.',
                    'Pour l’enfant, seuls le prénom et le niveau scolaire sont obligatoires. L’année de naissance est facultative : jamais de date complète, jamais de photo, jamais d’adresse e-mail.',
                    'Les passions, objectifs, difficultés et préférences d’apprentissage sont facultatifs et servent uniquement à adapter les activités. Vos observations écrites sont chiffrées.',
                ],
            },
            {
                id: 'finalites',
                title: 'Pourquoi nous les utilisons',
                paragraphs: [
                    'Adapter les activités au niveau et à l’univers de votre enfant, suivre sa progression par compétence, et vous en rendre compte clairement. Aucune publicité, aucune revente, aucun profilage commercial.',
                    'La base légale est votre consentement de parent ou de responsable légal, recueilli à la création du compte et réaffiché si cette politique change.',
                ],
            },
            {
                id: 'ia',
                title: 'Utilisation de l’intelligence artificielle',
                paragraphs: [
                    'L’IA rédige la mise en scène des activités dans l’univers de votre enfant. La compétence, le niveau et les calculs sont fixés par Xylou, jamais par l’IA.',
                    'Aucune donnée identifiante n’est envoyée au fournisseur d’IA : ni prénom, ni e-mail, ni vos observations. Il ne reçoit qu’un profil anonyme (niveau, compétence, univers, préférences).',
                    'Chaque activité générée est signalée comme telle et doit être validée par un adulte avant d’être visible par l’enfant.',
                ],
            },
            {
                id: 'mineurs',
                title: 'Protection des mineurs',
                paragraphs: [
                    'L’enfant n’a pas de compte. Son espace est ouvert par vous, sur votre appareil, et il ne peut pas revenir à votre espace sans votre code parent.',
                    'Aucun classement ni aucune comparaison entre enfants : la progression s’exprime en niveaux de maîtrise, sans note.',
                ],
            },
            {
                id: 'partage',
                title: 'Partage avec un professionnel',
                paragraphs: [
                    'Vous seul décidez de partager un profil avec un enseignant ou un professionnel, pour les éléments que vous choisissez et pour une durée limitée (3 mois par défaut). Vous pouvez retirer l’accès à tout moment ; chaque consultation vous est visible.',
                ],
            },
            {
                id: 'conservation',
                title: 'Durées de conservation',
                paragraphs: [
                    'Les données sont conservées tant que le compte est actif. Un profil supprimé est masqué immédiatement puis effacé définitivement sous 30 jours.',
                    'Le journal de sécurité (connexions, accès sensibles) ne contient aucune donnée personnelle en clair et est conservé un an.',
                ],
            },
            {
                id: 'droits',
                title: 'Vos droits',
                paragraphs: [
                    'Vous pouvez accéder à vos données et à celles de votre enfant, les rectifier, les exporter ou les supprimer depuis les paramètres, ou en nous écrivant.',
                    'Vous pouvez aussi introduire une réclamation auprès de la CNIL (cnil.fr).',
                ],
            },
            {
                id: 'securite',
                title: 'Sécurité et hébergement',
                paragraphs: [
                    'Connexions chiffrées (HTTPS), mots de passe et observations chiffrés, double authentification obligatoire pour les professionnels. Hébergement et sauvegardes chiffrées dans l’Union européenne.',
                ],
            },
        ],
    },
    terms: {
        title: 'Conditions d’utilisation',
        description: 'Les règles d’utilisation du service Xylou.',
        intro: 'En créant un compte, vous acceptez les conditions ci-dessous.',
        sections: [
            {
                id: 'objet',
                title: 'Objet du service',
                paragraphs: [
                    'Xylou propose un accompagnement scolaire personnalisé, du CP à la 3e, pour les familles et les professionnels de l’éducation. Il prolonge le travail de classe et ne remplace pas l’enseignant.',
                ],
            },
            {
                id: 'compte',
                title: 'Compte parent',
                paragraphs: [
                    'Le compte est réservé aux parents et responsables légaux majeurs. Vous déclarez être le parent ou le responsable légal de chaque enfant pour qui vous créez un profil.',
                    'Vous êtes responsable de la confidentialité de votre mot de passe et de votre code parent.',
                ],
            },
            {
                id: 'contenus',
                title: 'Activités générées',
                paragraphs: [
                    'Les activités proposées par l’IA sont relues par un adulte avant d’arriver chez l’enfant. En les validant, vous en acceptez le contenu ; vous pouvez les modifier ou les régénérer.',
                ],
            },
            {
                id: 'offres',
                title: 'Offres et tarifs',
                paragraphs: [
                    '[À compléter : offres, tarifs, modalités de paiement et de résiliation].',
                ],
            },
            {
                id: 'responsabilite',
                title: 'Responsabilité',
                paragraphs: [
                    '[À compléter : limites de responsabilité, disponibilité du service].',
                ],
            },
            {
                id: 'droit',
                title: 'Droit applicable',
                paragraphs: ['[À compléter : droit applicable et juridiction compétente].'],
            },
        ],
    },
    accessibility: {
        title: 'Accessibilité',
        description: 'Déclaration d’accessibilité du service Xylou.',
        intro: 'Xylou vise la conformité au Référentiel général d’amélioration de l’accessibilité (RGAA), niveau AA.',
        sections: [
            {
                id: 'etat',
                title: 'État de conformité',
                paragraphs: [
                    'Xylou est en cours de développement. Chaque écran est contrôlé automatiquement (axe) et conçu pour la navigation au clavier, les lecteurs d’écran et le respect des préférences de mouvement réduit. Un audit complet reste à réaliser : [À compléter : date et résultat de l’audit].',
                ],
            },
            {
                id: 'enfants',
                title: 'Pensé pour les enfants',
                paragraphs: [
                    'Côté enfant : textes d’au moins 18 pixels, grandes zones tactiles, lecture à voix haute et police très lisible en option, jamais de compte à rebours imposé.',
                ],
            },
            {
                id: 'retour',
                title: 'Nous signaler un problème',
                paragraphs: [
                    'Si un contenu ou une fonction vous est inaccessible, écrivez-nous : nous vous proposerons une alternative et corrigerons le problème.',
                ],
            },
        ],
    },
    contact: 'Nous écrire : {email}',
};
