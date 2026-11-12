<?php declare(strict_types=1);
namespace Modules\HiolleIdracRaid\Actions;
use API;
use CControllerDashboardWidgetView;
use CControllerResponseData;
use Modules\HiolleIdracRaid\Includes\DiskData;
class WidgetView extends CControllerDashboardWidgetView {
    protected function doAction(): void {
        $selected = $this->fields_values['hostids'] ?? [];
        $options = [
            'output' => ['itemid','hostid','key_','name','lastvalue','lastclock','state','status'],
            'search' => ['key_' => ['dell.server.hw.physicaldisk.', 'system.hw.physicaldisk.']],
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
            $rows[] = DiskData::host($host['name'], $byHost[$host['hostid']] ?? [], time(), $unavailable);
        }
        $this->setResponse(new CControllerResponseData([
            'name' => $this->getInput('name', $this->widget->getName()), 'rows' => $rows,
            'user' => ['debug_mode' => $this->getDebugMode()]
        ]));
    }
}
