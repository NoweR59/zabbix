param([string]$SsaCliPath = 'C:\Program Files\Smart Storage Administrator\ssacli\bin\ssacli.exe')
$ErrorActionPreference = 'Stop'
try {
    if (!(Test-Path -LiteralPath $SsaCliPath -PathType Leaf)) { throw 'SSACLI introuvable' }
    $output = & $SsaCliPath ctrl all show config detail 2>&1
    if ($LASTEXITCODE -ne 0) { throw 'Collecte SSACLI en echec' }
    $raw = $output -join "`n"
    if ($raw -notmatch '(?m)^\s+physicaldrive \S+\s*$') { throw 'Aucun disque physique detaille' }
    @{source='ssacli'; output=$raw} | ConvertTo-Json -Compress
}
catch {
    @{error=$_.Exception.Message} | ConvertTo-Json -Compress
    exit 1
}
