# Widget Veeam — Zabbix 7.4

Cette version lit les données réelles de Zabbix. Aucun script, mot de passe ou jeu de données simulées inclus.

Extraire hiolle_veeam_backup dans le dossier modules du frontend, scanner puis activer le module dans Administration / Général / Modules. Ajouter HIOLLE - Sauvegardes Veeam au tableau de bord.

Sur chaque hôte Hyper-V, créer un item Zabbix Agent (ou actif), clé veeam.jobs, type d'information Texte, intervalle conseillé 60 secondes. Le script agent est à déployer séparément. Le widget utilise les droits de lecture de l'utilisateur Zabbix connecté.

Le widget détecte ces items automatiquement. Une sélection manuelle d'hôtes est aussi disponible. Les données de plus de 15 minutes sont signalées comme anciennes.

## Format JSON attendu

Retourner une liste (ou un objet avec la propriété jobs contenant cette liste) :

```json
[{"Name":"Backup production","Status":"Success","IsRunning":false,"LastRun":"2026-11-10 02:15:00","Progress":100,"Repository":"REPO-01","Duration":"18 min","Size":"284 Go"}]
```

Status : Success, Warning ou Failed. IsRunning : booléen JSON ; true donne l'état bleu En cours. Progress : nombre de 0 à 100. Les champs Repository, Duration, Size, LastRun sont des textes optionnels. Un job seul au format objet est accepté.

Le script initial Name/Type/Status/LastRun/IsRunning est accepté. Il ne fournit pas de progression, dépôt, durée ou volume : ces informations restent absentes jusqu'à leur ajout au script. Une progression inconnue n'est jamais inventée. Un succès sans Progress affiche 100 %. Un échec affiche le bloc rouge sans barre.

L'hyperviseur affiché est le nom de l'hôte Zabbix. Les jobs sont regroupés par hôte. Agrandir la hauteur du widget si nécessaire pour afficher toutes les lignes (barre de défilement masquée selon le design).
