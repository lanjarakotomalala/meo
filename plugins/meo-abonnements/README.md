# Méo Abonnements pour WordPress

Ce plugin ajoute **WooCommerce > Abonnements Méo** à l'administration. Il reprend le parcours de gestion utile de `E-Commerce-Replicate` tout en utilisant les contrats, commandes, dates et passerelles de **WooCommerce Subscriptions** déjà présents sur le site WordPress.

Le plugin ne charge aucun CSS ni JavaScript sur la boutique. Il ne modifie aucun modèle du thème ni la page « Mon compte ».

## Installation

1. Vérifier que WooCommerce et WooCommerce Subscriptions sont actifs sur l'environnement de test.
2. Copier le dossier `meo-abonnements` dans `wp-content/plugins/` ou importer son archive ZIP via **Extensions > Ajouter > Téléverser**.
3. Activer **Méo Abonnements**.
4. Ouvrir **WooCommerce > Abonnements Méo**.

Ce dossier est conservé dans le dépôt du thème comme livrable autonome. Un déploiement du thème seul ne l'active pas automatiquement.
L'archive ZIP contient le plugin uniquement. Les corrections du panier dans `app/hooks.php` et `app/src/WooCommerce/Subscription.php` font partie du thème et doivent être déployées avec lui.

## Fonctions disponibles

- Liste paginée des abonnements, filtre par état et recherche par numéro ou email de facturation.
- Vue du contrat, des articles, de la prochaine échéance et des commandes de renouvellement.
- Pause, reprise, saut d'une échéance, déplacement de la prochaine date et annulation immédiate, **uniquement lorsque WooCommerce Subscriptions et la passerelle déclarent l'action possible**.
- Liste des produits abonnement et des plans ajoutés aux produits classiques (métadonnée `_wcsatt_schemes` utilisée par le thème actuel).
- Accès direct à l'éditeur WooCommerce pour modifier un produit ou un contrat.
- État indicatif des passerelles de paiement actives.

Les écritures passent par `WC_Subscription::update_status()` et `WC_Subscription::update_dates()`. La lecture des contrats utilise `wc_get_orders()` afin de rester compatible avec le stockage des commandes WooCommerce, y compris HPOS. Chaque action est limitée au rôle `manage_woocommerce`, protégée par un nonce et journalisée dans les notes du contrat ou dans les journaux WooCommerce en cas d'erreur.

## Répartition des responsabilités

| Besoin | Source de vérité WordPress |
| --- | --- |
| Prix, fréquence et durée d'une offre | Produit ou plan WooCommerce Subscriptions |
| Contrat, état, prochaine échéance | WooCommerce Subscriptions |
| Paiement récurrent et essais après échec | Passerelle compatible et WooCommerce Subscriptions |
| Commandes de renouvellement | WooCommerce |
| Interface simplifiée de gestion | Ce plugin |

Le moteur de renouvellement TypeScript de `E-Commerce-Replicate` ne doit pas être lancé en parallèle sur les mêmes contrats : deux moteurs pourraient créer deux commandes ou deux paiements. Aucun identifiant de carte ou jeton PayPlug n'est copié depuis l'autre application.

## Vérifications avant mise en service

Sur une copie du site avec moyens de paiement de test :

1. Créer un produit abonnement et effectuer une commande de test.
2. Vérifier le contrat et l'échéance dans **WooCommerce > Abonnements Méo**.
3. Vérifier pause, reprise et déplacement de date avec une passerelle qui les prend en charge.
4. Vérifier le renouvellement via les outils de WooCommerce Subscriptions et contrôler qu'une seule commande et un seul paiement sont créés.
5. Vérifier aussi un contrat existant et le fonctionnement avec HPOS activé et désactivé selon la configuration du site.
6. Vérifier dans le panier une hausse, une baisse et une suppression de quantité, ainsi que le minimum de 20 € pour un abonnement.

WooCommerce Subscriptions désactive normalement les paiements automatiques lorsqu'il reconnaît une copie de préproduction. Contrôler son indicateur de mode test avant d'interpréter un renouvellement qui ne se déclenche pas. Ne jamais activer les paiements automatiques d'une copie contenant des contrats réels avec une passerelle en mode production.

La compatibilité des paiements automatiques dépend de l'extension de paiement réellement installée sur WordPress. Une passerelle qui affiche « abonnements compatibles » dans la page d'aide doit encore être testée avec un renouvellement réel en mode test. Le plugin ne migre pas les contrats historiques de `E-Commerce-Replicate` vers WordPress ; une telle migration demande un inventaire des comptes, commandes et jetons de paiement.
