<?php declare(strict_types=1);
$tag = static fn(string $class, $children = ''): CDiv => (new CDiv($children))->addClass($class);
$rows = [];
foreach ($data['rows'] as $row) {
    $disks = [];
    $healthy = count(array_filter($row['disks'], static fn(array $disk): bool => $disk['state'] === 'ok'));
    $total = count($row['disks']);
    foreach ($row['disks'] as $disk) {
        $temperature = isset($disk['temperature'])
            ? preg_replace('/\s*\([^)]*F\)/', '', (string) $disk['temperature'])
            : '';
        $temperature = preg_replace('/\s*C$/i', ' °C', trim((string) $temperature));
        $meta = array_filter([
            $disk['interface'] ?? '', $disk['media'] ?? '',
            isset($disk['life']) && $disk['life'] !== null ? 'Vie '.$disk['life'].' %' : ''
        ]);
        $health = array_filter([
            isset($disk['smart']) && $disk['smart'] !== '' ? 'SMART '.strtoupper($disk['smart'] === 'No' ? 'OK' : 'ALERTE') : '',
            isset($disk['errors']) ? $disk['errors'].' erreur'.((int) $disk['errors'] === 1 ? '' : 's') : '',
            isset($disk['predictive']) && (int) $disk['predictive'] > 0 ? 'Pré-défaillance '.$disk['predictive'] : '',
            isset($disk['life_days']) && $disk['life_days'] !== null ? $disk['life_days'].' j restants' : '',
            !empty($disk['firmware']) ? 'FW '.$disk['firmware'] : ''
        ]);
        $disks[] = $tag('hiolle-raid-disk hiolle-raid-disk-'.$disk['state'], [
            $tag('hiolle-raid-disk-slot', $disk['slot']),
            $tag('hiolle-raid-disk-model', $disk['model']),
            $tag('hiolle-raid-disk-temperature', $temperature),
            $tag('hiolle-raid-disk-meta', implode(' · ', $meta)),
            $tag('hiolle-raid-disk-health', implode(' · ', $health)),
            $tag('hiolle-raid-disk-serial', $disk['serial'] === '' ? '' : 'S/N '.$disk['serial'])
        ])->setAttribute('title', $disk['label']);
    }
    $cardState = $total > 0 && $healthy === $total ? 'ok' : ($healthy > 0 ? 'warning' : 'critical');
    $rows[] = $tag('hiolle-raid-card hiolle-raid-card-'.$cardState, [
        $tag('hiolle-raid-server', [
            $tag('hiolle-raid-brand hiolle-raid-brand-'.$row['brand'], ['hp'=>'HPE','dell'=>'DELL','unknown'=>'?'][$row['brand']]),
            $tag('hiolle-raid-server-name', $row['server']),
            $tag('hiolle-raid-server-caption', [
                $tag('hiolle-raid-server-dot', ''),
                ($row['source'] ?? 'SNMP').' · '.$healthy.'/'.$total.' DISQUES OK'
            ])
        ]),
        $tag('hiolle-raid-disk-list', $disks ?: 'Collecte indisponible : configurez SNMP ou l’élément texte hiolle.raid.storcli sur cet hôte.')
    ]);
}
(new CWidgetView($data))->addItem($tag('hiolle-raid', $rows ?: [
    $tag('hiolle-raid-empty', 'Sélectionnez vos hôtes iDRAC / iLO. Aucun disque compatible découvert pour le moment.')
]))->show();
