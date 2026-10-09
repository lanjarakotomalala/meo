# Prévisualisation CRO Méo

Cette maquette statique est stockée dans le dépôt Git, dans `cro-preview/`, afin que ses changements soient visibles dans GitHub Desktop. Elle utilise HTML, CSS et JavaScript sans intégration WordPress ni paiement réel.

## Portée

- La direction artistique reprend les ressources du thème Méo (logo, polices, icônes, couleurs) et la structure visuelle observée sur la page d’accueil le 8 octobre 2026.
- Les images de la première section sont chargées depuis les URLs publiques de meo.fr. Les photos et les promotions peuvent changer sur le site réel.
- Le catalogue figé du 8 octobre 2026 vient de l’API publique WooCommerce Store de meo.fr : **123 produits publics et 49 catégories**. Les produits et catégories des exports CSV ont servi au contrôle des données ; les commandes ne sont pas utilisées.
- Les routes `/catalogue/`, `/categorie-produit/.../`, `/produit/.../`, `/abonnement/`, `/panier/`, `/commander/` et `/recherche/` affichent les pages de la boutique. La règle Apache dans `.htaccess` les réécrit vers `catalog.html`, qui charge `data/catalog.json`.
- La homepage garde un parcours express en trois actions : choisir le mode, ajouter un café, consulter le paiement simulé. Le panier est conservé localement entre les pages.
- Une proposition d’abonnement apparaît à partir de **2 cafés en achat unique** dans le panier. Ce seuil est une hypothèse de maquette. Les produits non café restent en achat unique.
- Les fréquences proposées et l’éligibilité à l’abonnement sont des hypothèses à valider avec les règles WooCommerce. Aucune remise n’est supposée.
- Le paiement reste simulé : aucune adresse, commande, transaction ou abonnement n’est envoyé à meo.fr.

## Isolation du thème

Le thème WordPress ne référence aucun fichier de ce dossier. Le script de publication `resources/build/release/release.js` utilise la liste explicite `config.json > release.include`, qui n’inclut pas `cro-preview/`. Modifier la maquette ne modifie donc ni les templates ni le CSS actif de meo.fr. Une intégration future nécessitera un travail séparé dans WordPress et des tests sur préproduction.

## Prévisualisation sur Nexylan

L’hébergement séparé `test.meo.fr` est créé dans N-admin sur le serveur Méo `nc3199` (`185.46.230.199`), avec `htdocs` comme racine web. Les fichiers de la maquette (`index.html`, `catalog.html`, CSS/JS, `assets/`, `data/catalog.json`, `.htaccess`) y ont été publiés le 8 octobre 2026. Les routes principales et l’en-tête `X-Robots-Tag: noindex, nofollow` ont été vérifiés directement sur l’IP du serveur. La clé SSH temporaire utilisée pour le transfert a été retirée du compte Nexylan et supprimée localement.

Le DNS public de `test.meo.fr` pointe désormais sur `185.46.230.199`. Au 9 octobre 2026, Nexylan n’a aucun certificat SSL assigné à cet hébergement ; le navigateur refuse donc `https://test.meo.fr/` avec `ERR_CERT_COMMON_NAME_INVALID`. Un certificat valide pour ce sous-domaine doit être créé puis assigné dans Nexylan. Ne pas assigner à cet hébergement le certificat existant de `blog.meo.fr`.

La refonte éditoriale de la homepage se trouve dans `index.html`, `editorial-home.css` et `editorial-home.js`. Ces trois fichiers doivent être copiés ensemble dans `/var/www/test.meo.fr/htdocs/`. Un push Git seul ne met pas cette prévisualisation à jour : aucun déploiement automatique vers Nexylan n’est configuré ici.

Le catalogue peut être reconstruit à partir des réponses des endpoints publics `https://www.meo.fr/wp-json/wc/store/v1/products?per_page=100&page=1`, `...page=2` et `https://www.meo.fr/wp-json/wc/store/v1/products/categories?per_page=100&page=1` avec `scripts/build_catalog.py`. Les réponses brutes ne sont pas conservées dans le dépôt.

Les changements de ce dossier sont visibles dans GitHub Desktop. GitHub.com ne les recevra qu’après un commit et un push.
