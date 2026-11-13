param([string]$StorCliPath = 'C:\Program Files\StorCLI\storcli64.exe')
$ErrorActionPreference = 'Stop'
try {
    if (!(Test-Path -LiteralPath $StorCliPath -PathType Leaf)) { throw 'Chemin StorCLI introuvable' }
    $output = & $StorCliPath /call/eall/sall show all J 2>&1
    if ($LASTEXITCODE -ne 0) { throw 'StorCLI a retourne une erreur' }
    $raw = $output -join "`n"
    $parsed = $raw | ConvertFrom-Json
    if (!$parsed.Controllers) { throw 'Aucun controleur dans le JSON' }
    foreach ($controller in $parsed.Controllers) {
        if ($controller.'Command Status'.Status -ne 'Success') { throw 'Collecte StorCLI en echec' }
    }
    $parsed | ConvertTo-Json -Depth 100 -Compress
}
catch {
    @{error=$_.Exception.Message} | ConvertTo-Json -Compress
    exit 1
}
