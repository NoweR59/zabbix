# Pack cartes HIOLLE — prêt à importer

Ce dossier contient uniquement les éléments graphiques des deux cartes :

- `fonds/HIOLLE_sites_reseau_fond.png` : carte de France sans les petites îles ;
- `fonds/HIOLLE_nord_59_62_fond.png` : carte Nord / Pas-de-Calais ;
- `icones/HIOLLE_point_vert.png` : état normal ;
- `icones/HIOLLE_point_rouge.png` : état problème.

## Import dans Zabbix

1. Aller dans **Administration → Général → Images**.
2. Importer les deux fonds avec le type **Arrière-plan**.
3. Importer les deux points avec le type **Icône**.
4. Créer les deux cartes et sélectionner le fond correspondant.
5. Ajouter les sites manuellement avec **Élément de carte → Type : Déclencheur**.
6. Sélectionner le déclencheur ICMP existant de chaque site.
7. Choisir l’icône verte pour **Défaut** et l’icône rouge pour **Problème**.

Les positions, les hôtes et les déclencheurs ne sont volontairement pas inclus : ils seront choisis directement sur le Zabbix entreprise.
