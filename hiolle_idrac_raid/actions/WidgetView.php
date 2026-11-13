<?php declare(strict_types=1);
namespace Modules\HiolleIdracRaid\Actions;
use API;
use CControllerDashboardWidgetView;
use CControllerResponseData;
use Modules\HiolleIdracRaid\Includes\DiskData;
use Modules\HiolleIdracRaid\Includes\StorCli;
class WidgetView extends CControllerDashboardWidgetView {
    protected function doAction(): void {
        $selected = $this->fields_values['hostids'] ?? [];
        $options = [
            'output' => ['itemid','hostid','key_','name','lastvalue','lastclock','state','status'],
            'search' => ['key_' => ['dell.server.hw.physicaldisk.', 'system.hw.physicaldisk.', 'hiolle.raid.storcli']],
            'searchByAny' => true
        ];
        if ($selected) { $options['hostids'] = $selected; }
        $items = API::Item()->get($options);
        $ids = $selected ?: array_values(array_unique(array_column($items, 'hostid')));
        $hosts = $ids ? API::Host()->get([
            'output' => ['hostid','name','status'], 'hostids' => $ids,
            'selectInterfaces' => ['type','available'], 'sortfield' => 'name'
        ]) : [];
        $byHost = [];
        foreach ($items as $item) { $byHost[$item['hostid']][] = $item; }
        $rows = [];
        foreach ($hosts as $host) {
            $unavailable = $host['status'] != 0;
            foreach ($host['interfaces'] as $interface) {
                if ($interface['type'] == 2 && $interface['available'] == 2) { $unavailable = true; }
            }
            $hostItems = $byHost[$host['hostid']] ?? [];
            $row = DiskData::host($host['name'], $hostItems, time(), $unavailable);
            $usable = array_filter($row['disks'], static fn(array $d): bool => $d['state'] !== 'unknown');
            if (!$usable && $host['status'] == 0) {
                foreach ($hostItems as $item) {
                    if ($item['key_'] !== 'hiolle.raid.storcli' || $item['status'] != 0 || $item['state'] != 0) { continue; }
                    $history = API::History()->get(['output'=>['value','clock'], 'history'=>4,
                        'itemids'=>[$item['itemid']], 'sortfield'=>'clock', 'sortorder'=>'DESC', 'limit'=>1]);
                    if (!$history || (int) $history[0]['clock'] < time() - 900) { continue; }
                    $disks = StorCli::parse($history[0]['value']);
                    if ($disks !== null) { $row = ['server'=>$host['name'], 'brand'=>'hp', 'disks'=>$disks]; }
                }
            }
            $rows[] = $row;
        }
        $this->setResponse(new CControllerResponseData([
            'name' => $this->getInput('name', $this->widget->getName()), 'rows' => $rows,
            'user' => ['debug_mode' => $this->getDebugMode()]
        ]));
    }
}
