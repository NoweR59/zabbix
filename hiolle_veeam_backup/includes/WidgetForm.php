<?php declare(strict_types=1);
namespace Modules\HiolleVeeamBackup\Includes;
use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CWidgetFieldMultiSelectHost;
class WidgetForm extends CWidgetForm {
    public function addFields(): self {
        return $this->addField(new CWidgetFieldMultiSelectHost('hostids', _('Hôtes (vide = tous les hôtes accessibles)')));
    }
}
