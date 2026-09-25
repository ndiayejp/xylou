# ADR 0001 — Monolithe modulaire Laravel + Inertia + Vue 3

- Statut : accepté
- Date : 2026-09-25

## Contexte

Xylou réunit trois espaces (enfant, parent, professionnel) qui partagent les mêmes données, les mêmes règles d'autorisation et le même modèle de progression. L'équipe doit pouvoir maintenir l'ensemble sans expertise d'exploitation poussée.

## Décision

Une seule application Laravel sert l'ensemble. Le front est en Vue 3 + TypeScript, relié à Laravel par Inertia : les pages reçoivent leurs données en props, sans API interne. Une API REST ne sera ajoutée que pour les applications mobiles natives (étape 14).

## Conséquences

- Positives : une seule base de code et un seul runtime serveur ; routage, authentification et autorisations restent dans Laravel ; pas de synchronisation d'état serveur côté client.
- Négatives : le front dépend du cycle de rendu Inertia ; l'application mobile demandera une API dédiée le moment venu.

## Alternatives envisagées

- SPA Vue + API REST : double la surface à maintenir (API, jetons, état serveur côté client).
- Nuxt : ajoute un serveur Node à exploiter en plus de PHP.
- Livewire : moins adapté à une interface enfant riche (animations, hors ligne).
- Microservices : inutile à cette échelle ; le monolithe pourra être découpé par domaine si besoin.
