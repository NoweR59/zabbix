<?php declare(strict_types=1);
(new CWidgetFormView($data))
    ->addField(new CWidgetFieldMultiSelectHostView($data['fields']['hostids']))
    ->addItem(new CDiv('Sans sélection : tous les hôtes accessibles possédant des disques Dell iDRAC / HP iLO découverts. Configurez la collecte SNMP sur les hôtes.'))
    ->show();
