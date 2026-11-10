<?php declare(strict_types=1);
namespace Modules\HiolleVeeamBackup\Actions;
use API;
use CControllerDashboardWidgetView;
use CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView {
    protected function doAction(): void {
        $options = [
            'output' => ['itemid', 'hostid', 'value_type', 'state'],
            'filter' => ['key_' => 'veeam.jobs', 'status' => 0, 'value_type' => 4],
            'monitored' => true
        ];
        if (!empty($this->fields_values['hostids'])) {
            $options['hostids'] = $this->fields_values['hostids'];
        }
        $items = API::Item()->get($options);
        $hosts = $items ? API::Host()->get([
            'output' => ['hostid', 'name'], 'hostids' => array_column($items, 'hostid'),
            'preservekeys' => true
        ]) : [];
        $jobs = [];
        $messages = [];
        foreach ($items as $item) {
            $host = $hosts[$item['hostid']]['name'] ?? 'Hôte inconnu';
            $history = API::History()->get([
                'output' => ['value', 'clock'], 'history' => 4,
                'itemids' => [$item['itemid']], 'sortfield' => 'clock',
                'sortorder' => 'DESC', 'limit' => 1
            ]);
            if ($item['state'] != 0 || !$history || (int) $history[0]['clock'] < time() - 900) {
                $messages[] = $host.' : collecte absente, non supportée ou ancienne (>15 min).';
                continue;
            }
            $payload = json_decode($history[0]['value'], true);
            if (!is_array($payload) || isset($payload['Error'])) {
                $messages[] = $host.' : erreur de collecte ou JSON invalide.';
                continue;
            }
            $list = $payload['jobs'] ?? (isset($payload['Name']) ? [$payload] : $payload);
            if (!is_array($list)) {
                $messages[] = $host.' : liste de jobs invalide.';
                continue;
            }
            foreach ($list as $job) {
                if (!is_array($job) || !is_string($job['Name'] ?? null)) {
                    $messages[] = $host.' : job invalide (Name requis).';
                    continue;
                }
                $running = in_array($job['IsRunning'] ?? false, [true, 1, 'true'], true);
                $result = is_string($job['Status'] ?? null) ? strtolower($job['Status']) : '';
                $state = $running ? 'running' : ([
                    'success' => 'ok', 'warning' => 'warning', 'failed' => 'critical'
                ][$result] ?? 'unknown');
                $progress = is_numeric($job['Progress'] ?? null)
                    ? max(0, min(100, (float) $job['Progress'])) : ($state === 'ok' ? 100 : null);
                $string = static fn($value): string => is_scalar($value) ? (string) $value : '—';
                $jobs[] = [
                    'hypervisor' => $host, 'name' => $job['Name'],
                    'status' => $state, 'status_label' => [
                        'ok' => 'OK', 'running' => 'En cours', 'warning' => 'Attention',
                        'critical' => 'Échec', 'unknown' => 'Inconnu'
                    ][$state],
                    'repo' => $string($job['Repository'] ?? null),
                    'last' => $string($job['LastRun'] ?? null),
                    'duration' => $string($job['Duration'] ?? null),
                    'size' => $string($job['Size'] ?? null), 'progress' => $progress
                ];
            }
        }
        if (!$jobs && !$messages) {
            $messages[] = 'Aucun job disponible. Configurez un item texte veeam.jobs sur vos hôtes.';
        }
        $this->setResponse(new CControllerResponseData([
            'name' => $this->getInput('name', $this->widget->getName()),
            'jobs' => $jobs, 'messages' => array_unique($messages),
            'user' => ['debug_mode' => $this->getDebugMode()]
        ]));
    }
}
