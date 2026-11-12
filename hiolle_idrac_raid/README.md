# HIOLLE - RAID iDRAC / iLO — Zabbix 7.4

Le ZIP contient le dossier hiolle_idrac_raid à copier dans le répertoire modules du frontend Zabbix utilisé par votre installation. Ne pas renommer le dossier. Sauvegardez l'ancien module avant remplacement.

1. Copier le dossier complet (pas seulement PHP/CSS).
2. Dans Zabbix : Administration > Général > Modules > Scanner le répertoire, puis activer HIOLLE - RAID iDRAC.
3. Ajouter le widget au tableau de bord et sélectionner les hôtes iDRAC/iLO. Sans sélection, il prend les hôtes accessibles avec des disques compatibles.
4. Configurer plus tard SNMP et le modèle officiel Dell iDRAC by SNMP ou HP iLO by SNMP sur chaque hôte. Attendre la découverte des disques et les premières valeurs.

Aucun compte iDRAC/iLO ni mot de passe n'est demandé dans le widget. Les paramètres SNMP restent sur les hôtes Zabbix. Le widget utilise les permissions de l'utilisateur connecté.

Affichage : emplacement issu du nom découvert, modèle, capacité physique et numéro de série si disponibles. Fond discret vert (normal), orange (alerte), rouge (critique), gris (inconnu). Sans clignotement, pastille ou texte OK/HS dans les cases. Le détail d'état est disponible au survol.

Les valeurs absentes, non supportées ou âgées de plus de 7 heures sont inconnues. Ce délai tient compte du heartbeat de 6 heures des modèles officiels. Une interface SNMP déclarée indisponible ou un hôte désactivé rend les disques gris. Ce widget montre le dernier état collecté, pas une interrogation directe du contrôleur.

Portée : disques physiques uniquement. Pas de pourcentage d'occupation du système de fichiers ni de relation inventée disque/volume RAID. Modèles tiers, Redfish et anciennes clés personnalisées non pris en charge sans adaptation. Aucun disque simulé dans cette version.

Références :
- https://github.com/zabbix/zabbix/blob/release/7.4/templates/server/dell_idrac_snmp/template_server_dell_idrac_snmp.yaml
- https://github.com/zabbix/zabbix/blob/release/7.4/templates/server/hp_ilo_snmp/template_server_hp_ilo_snmp.yaml

Validation : syntaxe PHP et scénarios automatisés sur jeux de données. La validation sur vos contrôleurs réels reste à effectuer après configuration SNMP.
