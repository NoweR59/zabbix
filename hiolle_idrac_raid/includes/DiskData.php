<?php declare(strict_types=1);
namespace Modules\HiolleIdracRaid\Includes;
final class DiskData {
    // Official templates use a six-hour heartbeat for unchanged values.
    private const MAX_AGE = 25200;
    public static function host(string $name, array $items, int $now, bool $unavailable = false): array {
        $disks = [];
        $brand = 'unknown';
        foreach ($items as $item) {
            if (!preg_match('/^(dell\.server|system)\.hw\.physicaldisk\.(status|smart_status|model|serialnumber|size)\[[A-Za-z]+\.([0-9.]+)\]$/D', $item['key_'], $m)) { continue; }
            $brand = $m[1] === 'system' ? 'hp' : 'dell';
            $id = $brand.':'.$m[3];
            $slot = $brand === 'dell'
                ? preg_replace('/^Physical disk \[(.*)\]:.*$/', '$1', $item['name'])
                : preg_replace('/: (?:Physical disk|Disk size).*$/i', '', $item['name']);
            if (str_contains($slot, '{#')) { continue; }
            $disks[$id]['slot'] = $slot;
            $disks[$id]['brand'] = $brand;
            $valid = !$unavailable && $item['status'] == 0 && $item['state'] == 0
                && (int) $item['lastclock'] > 0 && (int) $item['lastclock'] >= $now - self::MAX_AGE;
            $disks[$id][$m[2]] = $valid ? $item['lastvalue'] : null;
        }
        $result = [];
        foreach ($disks as $disk) {
            $value = $disk['status'] ?? null;
            $state = $value === null ? 'unknown' : (($disk['brand'] === 'dell'
                ? [3 => 'ok', 4 => 'predictive', 5 => 'failed', 6 => 'failed']
                : [2 => 'ok', 3 => 'failed', 4 => 'predictive'])[(int) $value] ?? 'unknown');
            $smart = $disk['smart_status'] ?? null;
            $smartFailure = $smart !== null && ($disk['brand'] === 'dell'
                ? (int) $smart === 1 : in_array((int) $smart, [3,4], true));
            if ($state !== 'failed' && $smartFailure) { $state = 'predictive'; }
            $size = isset($disk['size']) && is_numeric($disk['size']) && (float) $disk['size'] > 0
                ? number_format((float) $disk['size'] / 1073741824, 0, ',', ' ').' Gio' : '';
            $result[] = [
                'slot' => $disk['slot'], 'state' => $state,
                'model' => trim(($disk['model'] ?? '').' '.$size), 'serial' => $disk['serialnumber'] ?? '',
                'label' => ['ok'=>'Normal', 'predictive'=>'Alerte / pré-défaillance', 'failed'=>'Critique',
                    'unknown'=>'État inconnu : collecte absente, ancienne ou indisponible'][$state]
            ];
        }
        usort($result, static fn(array $a, array $b): int => strnatcasecmp($a['slot'], $b['slot']));
        return ['server'=>$name, 'brand'=>$brand, 'disks'=>$result];
    }
}
