<?php declare(strict_types=1);
(new CWidgetFormView($data))
    ->addField(new CWidgetFieldMultiSelectHostView($data['fields']['hostids']))
    ->show();
