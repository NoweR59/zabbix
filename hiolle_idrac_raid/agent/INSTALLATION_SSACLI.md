# Collecte SSACLI — Windows Server 2016 / Zabbix Agent 1

Copier raid-ssacli.ps1 dans C:\Zabbix\scripts sur le serveur Hyper-V.
Le script utilise SSACLI en lecture seule et le chemin standard HPE.

Ajouter dans la configuration de l'agent :

```ini
UserParameter=hiolle.raid.ssacli,powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "C:\Zabbix\scripts\raid-ssacli.ps1"
```

Redémarrer le service Zabbix Agent. Sur l'hôte Zabbix correspondant, créer un élément :

- Nom : RAID SSACLI
- Type : Agent Zabbix (ou actif selon la configuration existante)
- Clé : hiolle.raid.ssacli
- Type d'information : Texte
- Intervalle : 1m

Sélectionner cet hôte dans le widget RAID. Les données âgées de plus de 15 minutes sont refusées.
Les informations absentes (SMART, usure SSD) ne sont pas inventées.
Le script StorCLI des autres serveurs reste utilisable.
