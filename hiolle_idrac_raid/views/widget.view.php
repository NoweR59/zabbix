<?php declare(strict_types=1);
$tag = static fn(string $class, $children = ''): CDiv => (new CDiv($children))->addClass($class);
$rows = [];
foreach ($data['rows'] as $row) {
    $disks = [];
    foreach ($row['disks'] as $disk) {
        $disks[] = $tag('hiolle-raid-disk hiolle-raid-disk-'.$disk['state'], [
            $tag('hiolle-raid-disk-slot', $disk['slot']),
            $tag('hiolle-raid-disk-model', $disk['model']),
            $tag('hiolle-raid-disk-model', $disk['serial'] === '' ? '' : 'S/N '.$disk['serial'])
        ])->setAttribute('title', $disk['label']);
    }
    $rows[] = $tag('hiolle-raid-card', [
        $tag('hiolle-raid-server', [
            $tag('hiolle-raid-brand hiolle-raid-brand-'.$row['brand'], ['hp'=>'HPE','dell'=>'DELL','unknown'=>'?'][$row['brand']]),
            $tag('hiolle-raid-server-name', $row['server'])
        ]),
        $tag('hiolle-raid-disk-list', $disks ?: 'Aucun disque découvert : configurez le modèle SNMP Dell iDRAC ou HP iLO sur cet hôte.')
    ]);
}
(new CWidgetView($data))->addItem($tag('hiolle-raid', $rows ?: [
    $tag('hiolle-raid-empty', 'Sélectionnez vos hôtes iDRAC / iLO. Aucun disque compatible découvert pour le moment.')
]))->show();
