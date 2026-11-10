<?php declare(strict_types=1);

/** @var array $data */

$tag = static function(string $name, string $class, $children = ''): CTag {
    return (new CTag($name, true, $children))->addClass($class);
};

$ok = count(array_filter($data['jobs'], static fn(array $job): bool => $job['status'] === 'ok'));
$attention = count(array_filter($data['jobs'], static fn(array $job): bool => $job['status'] === 'warning'));
$failed = count(array_filter($data['jobs'], static fn(array $job): bool => $job['status'] === 'critical'));

$rows = [];
$jobs_by_hypervisor = [];
foreach ($data['jobs'] as $job) {
    $jobs_by_hypervisor[$job['hypervisor']][] = $job;
}
ksort($jobs_by_hypervisor);
foreach ($jobs_by_hypervisor as $hypervisor => $hypervisor_jobs) {
    $rows[] = $tag('div', 'hiolle-veeam-hypervisor', [
        $tag('span', 'hiolle-veeam-hypervisor-label', 'HYPERVISEUR'),
        $tag('span', 'hiolle-veeam-hypervisor-name', $hypervisor)
    ]);
    foreach ($hypervisor_jobs as $job) {
    $progress = $job['status'] === 'critical' || $job['progress'] === null ? [] : [
        $tag('div', 'hiolle-veeam-progress-track', [
            $tag('div', 'hiolle-veeam-progress', '')->setAttribute('style', '--hiolle-veeam-progress:'.$job['progress'].'%'),
            $tag('span', 'hiolle-veeam-progress-label', $job['progress'].' %')
        ])
    ];
    $rows[] = $tag('div', 'hiolle-veeam-job hiolle-veeam-job-'.$job['status'], [
        $tag('div', 'hiolle-veeam-job-main', [
            $tag('div', 'hiolle-veeam-job-name', $job['name']),
            $tag('div', 'hiolle-veeam-job-repo', $job['repo'])
        ]),
        $tag('div', 'hiolle-veeam-job-last', [
            $tag('span', 'hiolle-veeam-label', 'Dernière sauvegarde'),
            $tag('span', 'hiolle-veeam-value', $job['last'])
        ]),
        $tag('div', 'hiolle-veeam-job-stats', [
            $tag('span', 'hiolle-veeam-stat', $job['duration']),
            $tag('span', 'hiolle-veeam-stat', $job['size'])
        ]),
        $tag('div', 'hiolle-veeam-job-state', [
            $tag('span', 'hiolle-veeam-state-dot'),
            $tag('span', 'hiolle-veeam-state-label', $job['status_label'])
        ]),
        ...$progress
    ]);
    }
}

$content = $tag('div', 'hiolle-veeam', [
    $tag('div', 'hiolle-veeam-summary', [
        $tag('span', 'hiolle-veeam-title', 'SAUVEGARDES VEEAM')
    ]),
    $tag('div', 'hiolle-veeam-head', [
        $tag('span', '', 'JOB'),
        $tag('span', '', 'DERNIÈRE EXÉCUTION'),
        $tag('span', '', 'DURÉE / VOLUME'),
        $tag('span', '', 'ÉTAT')
    ]),
    ...$rows,
    ...array_map(static fn(string $message): CTag => $tag('div', 'hiolle-veeam-message', $message), $data['messages'] ?? [])
]);

(new CWidgetView($data))
    ->addItem($content)
    ->show();
