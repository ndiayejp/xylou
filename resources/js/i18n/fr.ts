// Messages de l'interface (vue-i18n). Les messages du serveur restent dans lang/fr.
export default {
    layout: {
        skipToContent: 'Aller au contenu',
        mainNav: 'Navigation principale',
        homeLink: 'Accueil Xylou',
        more: 'Plus',
        openMenu: 'Ouvrir le menu',
        closeMenu: 'Fermer le menu',
        notifications:
            'Notifications | Notifications, {count} non lue | Notifications, {count} non lues',
        help: 'Aide & contact',
        changeChild: 'Changer d’enfant',
        currentChild: 'Changer d’enfant, actuellement {name}',
        addChild: 'Ajouter un enfant',
        proBadge: 'Pro',
        login: 'Se connecter',
        register: 'Créer un profil',
        sections: 'Sections',
        tagline: 'L’accompagnement scolaire qui s’adapte à l’univers de chaque enfant.',
        copyright: '© {year} Xylou',
        notificationsRegion: 'Messages',
        accountMenu: 'Mon compte',
        profile: 'Mon profil',
        logout: 'Se déconnecter',
    },
    parent: {
        dashboard: {
            title: 'Bonjour {name}',
            emptyTitle: 'Ajoutez votre premier enfant',
            emptyText: 'Son espace, ses activités et sa progression apparaîtront ici.',
        },
    },
    pro: {
        dashboard: {
            title: 'Bonjour {name}',
            emptyTitle: 'Aucun profil partagé pour l’instant',
            emptyText: 'Quand un parent vous invitera, le profil de son enfant apparaîtra ici.',
        },
    },
    profile: {
        title: 'Mon profil',
    },
    auth: {
        fields: {
            name: 'Votre prénom et votre nom',
            email: 'Adresse e-mail',
            password: 'Mot de passe',
            newPassword: 'Nouveau mot de passe',
            passwordConfirmation: 'Confirmez le mot de passe',
            passwordHint:
                '12 caractères minimum. Astuce : une phrase courte que vous retenez facilement.',
            remember: 'Rester connecté sur cet appareil',
        },
        login: {
            title: 'Bon retour sur Xylou',
            description: 'Connectez-vous à votre espace parent ou professionnel.',
            submit: 'Se connecter',
            forgot: 'Mot de passe oublié ?',
            noAccount: 'Pas encore de compte ?',
            register: 'Créer un espace parent',
        },
        register: {
            title: 'Créons votre espace parent',
            description:
                'C’est vous qui gardez la main sur tout ce que votre enfant voit et partage.',
            submit: 'Créer mon espace',
            hasAccount: 'Déjà un compte ?',
            login: 'Se connecter',
        },
        forgot: {
            title: 'Mot de passe oublié',
            description:
                'Indiquez votre adresse e-mail : nous vous enverrons un lien pour en choisir un nouveau.',
            submit: 'Recevoir le lien',
            back: 'Retour à la connexion',
        },
        reset: {
            title: 'Nouveau mot de passe',
            description: 'Choisissez un mot de passe que vous n’utilisez pas ailleurs.',
            submit: 'Enregistrer le mot de passe',
        },
        verify: {
            title: 'Vérifiez votre adresse e-mail',
            description:
                'Nous vous avons envoyé un lien de confirmation. Cliquez dessus pour accéder à votre espace.',
            resend: 'Renvoyer le lien',
            linkSent: 'Un nouveau lien vient de partir vers votre adresse e-mail.',
            logout: 'Se déconnecter',
        },
        confirm: {
            title: 'Confirmez votre mot de passe',
            description:
                'Cette partie est protégée : saisissez à nouveau votre mot de passe pour continuer.',
            submit: 'Confirmer',
        },
    },
    nav: {
        kid: {
            home: 'Accueil',
            activities: 'Mes activités',
            progress: 'Ma progression',
            rewards: 'Mes réussites',
            profile: 'Profil',
        },
        adult: {
            home: 'Accueil',
            children: 'Enfants',
            activities: 'Activités',
            progress: 'Progression',
            reports: 'Bilans',
            library: 'Bibliothèque',
            settings: 'Paramètres',
            generate: 'Générer une activité',
            newReport: 'Nouveau bilan',
        },
        public: {
            how: 'Comment ça marche',
            parents: 'Parents',
            pros: 'Professionnels',
            privacy: 'Confidentialité',
            faq: 'FAQ',
            product: 'Produit',
            trust: 'Confiance',
            help: 'Aide',
            forParents: 'Pour les parents',
            forPros: 'Pour les professionnels',
            pricing: 'Tarifs',
            ai: 'Utilisation de l’IA',
            minors: 'Sécurité des mineurs',
            accessibility: 'Accessibilité',
            contact: 'Contact',
            helpCenter: 'Centre d’aide',
            legal: 'Mentions légales',
        },
    },
};
