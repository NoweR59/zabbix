<?php declare(strict_types=1);
namespace Modules\HiolleIdracRaid\Includes;
final class StorCli {
    public static function parse(string $json): ?array {
        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['Controllers'])) { return null; }
        $disks = [];
        foreach ($data['Controllers'] as $controller) {
            if (($controller['Command Status']['Status'] ?? '') !== 'Success') { return null; }
            $id = $controller['Command Status']['Controller'] ?? '?';
            $response = $controller['Response Data'] ?? [];
            foreach ($response as $key => $list) {
                if (!preg_match('~^Drive /c\d+/e\d+/s\d+$~', $key) || !is_array($list)) { continue; }
                foreach ($list as $drive) {
                    if (!is_array($drive) || !isset($drive['EID:Slt'])) { continue; }
                    $state = ['Onln'=>'ok','UGood'=>'ok','GHS'=>'ok','DHS'=>'ok','JBOD'=>'ok',
                        'Rbld'=>'predictive','Offln'=>'failed','UBad'=>'failed','Failed'=>'failed',
                        'Msng'=>'failed','Dgrd'=>'predictive'][$drive['State'] ?? ''] ?? 'unknown';
                    $details = $response[$key.' - Detailed Information'] ?? [];
                    $serial = '';
                    foreach ($details as $section) {
                        if (!is_array($section)) { continue; }
                        if (isset($section['SN'])) { $serial = trim((string) $section['SN']); }
                        if ($state !== 'failed' && ((int) ($section['Predictive Failure Count'] ?? 0) > 0
                            || ($section['S.M.A.R.T alert flagged by drive'] ?? 'No') === 'Yes')) { $state = 'predictive'; }
                    }
                    $disks[] = ['slot'=>'C'.$id.' / '.$drive['EID:Slt'], 'state'=>$state,
                        'model'=>trim((string) ($drive['Model'] ?? '')).' '.trim((string) ($drive['Size'] ?? '')),
                        'serial'=>$serial, 'label'=>'StorCLI : '.($drive['State'] ?? 'inconnu')];
                }
            }
        }
        return $disks ?: null;
    }
}
