# Prévisualisation CRO Méo

Cette maquette statique est stockée dans le dépôt Git, dans `cro-preview/`, afin que ses changements soient visibles dans GitHub Desktop. Elle utilise HTML, CSS et JavaScript sans intégration WordPress ni paiement réel.

## Portée

- La direction artistique reprend les ressources du thème Méo (logo, polices, icônes, couleurs) et la structure visuelle observée sur la page d’accueil le 8 octobre 2026.
- Les images de la première section sont chargées depuis les URLs publiques de meo.fr. Les photos et les promotions peuvent changer sur le site réel.
- Les noms et prix des six cafés de démonstration proviennent de l’export produits fourni. Les commandes ne sont pas utilisées.
- Le parcours achat unique et le parcours abonnement mènent à un panier et à une confirmation simulés.
- Une proposition d’abonnement apparaît dans le panier d’achat unique à partir de **2 articles et 20 €**. Ce seuil est une hypothèse de maquette à valider.
- Le seuil de **20 €** pour la simulation d’abonnement et les fréquences proposées ne sont pas des conditions commerciales publiées.

## Isolation du thème

Le thème WordPress ne référence aucun fichier de ce dossier. Le script de publication `resources/build/release/release.js` utilise la liste explicite `config.json > release.include`, qui n’inclut pas `cro-preview/`. Modifier la maquette ne modifie donc ni les templates ni le CSS actif de meo.fr. Une intégration future nécessitera un travail séparé dans WordPress et des tests sur préproduction.

## Prévisualisation

Ouvrir `index.html` avec un serveur statique local, ou déployer ce dossier seul sur Vercel. L’URL de démonstration est [meo-cro-preview.vercel.app](https://meo-cro-preview.vercel.app/). La configuration `vercel.json` ajoute un en-tête `X-Robots-Tag: noindex, nofollow`.

Le dossier est actuellement une modification locale non commitée : GitHub Desktop l’affiche, mais GitHub.com ne la recevra qu’après un commit et un push.
