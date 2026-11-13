# Collecte HPE MR avec StorCLI et Zabbix Agent 1

Sur le Windows physique Hyper-V :

1. Copier raid-storcli.ps1 dans C:\Program Files\Zabbix Agent\scripts\.
2. Modifier StorCliPath au debut du script pour mettre le chemin reel de storcli64.exe.
3. Tester dans PowerShell administrateur :

```powershell
& 'C:\Program Files\Zabbix Agent\scripts\raid-storcli.ps1'
```

Le resultat doit etre un JSON avec Controllers. Le compte du service Agent doit pouvoir lire le controleur (tester avec le compte de service reel).

4. Ajouter cette ligne au fichier de configuration de l'agent reellement utilise :

```ini
UserParameter=hiolle.raid.storcli,powershell.exe -NoProfile -ExecutionPolicy Bypass -File "C:\Program Files\Zabbix Agent\scripts\raid-storcli.ps1"
```

Si la commande depasse le timeout actuel, ajuster Timeout dans l'agent (maximum 30 secondes pour Agent 1) et le delai correspondant cote Zabbix. Redemarrer uniquement le service Zabbix Agent.

5. Sur l'hote Windows Hyper-V existant dans Zabbix, creer un element :
- Type : Agent Zabbix (ou actif si votre agent utilise les controles actifs).
- Cle : hiolle.raid.storcli
- Type d'information : Texte
- Intervalle : 1m
- Historique : 1d
- Aucun pretraitement supprimant les valeurs identiques.

6. Verifier le JSON dans Dernieres donnees, puis selectionner cet hote Windows dans le widget RAID.

Le widget utilise SNMP lorsqu'il dispose d'etats exploitables, sinon StorCLI. Les donnees Agent de plus de 15 minutes ne sont pas utilisees. Le chemin Agent est prevu pour les HPE MR. Les anciens hôtes SNMP restent compatibles.

Les commandes sont en lecture seule. Le parseur attend les sections standard Drive /cN/eN/sN de StorCLI show all J. Si votre version utilise une autre structure, fournir un export JSON pour adaptation. Tests sur fixtures uniquement ; validation finale sur le serveur reel necessaire.
