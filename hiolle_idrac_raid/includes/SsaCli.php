<?php declare(strict_types=1);
namespace Modules\HiolleIdracRaid\Includes;
final class SsaCli {
    public static function parse(string $json): ?array {
        $payload = json_decode($json, true);
        if (!is_array($payload) || !isset($payload['output']) || !is_string($payload['output']) || isset($payload['error'])) { return null; }
        $disks = []; $controller = ''; $slot = ''; $fields = [];
        $flush = static function () use (&$disks, &$slot, &$fields, &$controller): void {
            if ($slot === '') { return; }
            $status = $fields['Status'] ?? '';
            $state = ['OK'=>'ok', 'Failed'=>'failed', 'Predictive Failure'=>'predictive', 'Rebuilding'=>'predictive'][$status] ?? 'unknown';
            $disks[] = ['slot'=>$controller.' / '.$slot, 'state'=>$state,
                'model'=>trim(preg_replace('/\s+/', ' ', $fields['Model'] ?? '').' '.($fields['Size'] ?? '')),
                'serial'=>$fields['Serial Number'] ?? '', 'interface'=>$fields['Interface Type'] ?? '',
                'media'=>isset($fields['Rotational Speed']) ? 'HDD' : '',
                'temperature'=>isset($fields['Current Temperature (C)']) ? $fields['Current Temperature (C)'].'C' : '',
                'firmware'=>$fields['Firmware Revision'] ?? '',
                'label'=>'SSACLI : '.($status ?: 'inconnu')];
            $slot = ''; $fields = [];
        };
        foreach (preg_split('/\R/', $payload['output']) as $line) {
            if (preg_match('/^\S.* in Slot (\S+)/', $line, $m)) { $flush(); $controller = 'Slot '.$m[1]; }
            elseif (preg_match('/^\s+physicaldrive (\S+)\s*$/i', $line, $m)) { $flush(); $slot = $m[1]; }
            elseif ($slot !== '' && preg_match('/^\s+([^:]+):\s*(.*)$/', $line, $m)) { $fields[trim($m[1])] = trim($m[2]); }
        }
        $flush();
        return $disks ?: null;
    }
}
