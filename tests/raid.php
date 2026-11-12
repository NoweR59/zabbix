<?php
require $argv[1].'/includes/DiskData.php';
use Modules\HiolleIdracRaid\Includes\DiskData;
$now = 100000;
function item($hp, $value, $field='status', $clock=100000, $index='1.2') {
    $objects = $hp ? ['status'=>'cpqDaPhyDrvStatus','model'=>'cpqDaPhyDrvModel','smart_status'=>'cpqDaPhyDrvSmartStatus']
        : ['status'=>'physicalDiskComponentStatus','model'=>'physicalDiskProductID','smart_status'=>'physicalDiskSmartAlertIndication'];
    return ['key_'=>($hp ? 'system' : 'dell.server').'.hw.physicaldisk.'.$field.'['.$objects[$field].'.'.$index.']',
        'name'=>$hp ? 'Port 1I: Box 1: Bay 2: Physical disk '.$field : 'Physical disk [Disk.Bay.2:Enclosure.1]: '.$field,
        'lastvalue'=>(string)$value, 'lastclock'=>$clock, 'state'=>0, 'status'=>0];
}
$count=0;
function check($actual,$expected) {
    global $count;
    if ($actual !== $expected) { throw new Exception(json_encode([$actual,$expected])); }
    $count++;
}
foreach ([false=>[1=>'unknown',2=>'unknown',3=>'ok',4=>'predictive',5=>'failed',6=>'failed'],true=>[1=>'unknown',2=>'ok',3=>'failed',4=>'predictive']] as $hp=>$states) {
    foreach ($states as $value=>$expected) {
        $row=DiskData::host('host',[item((bool)$hp,$value)],$now);
        check($row['disks'][0]['state'],$expected);
    }
}
check(DiskData::host('host',[],$now)['disks'],[]);
check(DiskData::host('host',[item(false,3, 'status',0)],$now)['disks'][0]['state'],'unknown');
check(DiskData::host('host',[item(false,3, 'status',70000)],$now)['disks'][0]['state'],'unknown');
check(DiskData::host('host',[item(false,3, 'status',79000)],$now)['disks'][0]['state'],'ok');
check(DiskData::host('host',[item(false,3)],$now,true)['disks'][0]['state'],'unknown');
$bad=item(false,3); $bad['state']=1;
check(DiskData::host('host',[$bad],$now)['disks'][0]['state'],'unknown');
foreach ([[false,3,1],[true,2,3]] as [$hp,$state,$smart]) {
    $row=DiskData::host('host',[item($hp,$state),item($hp,$smart,'smart_status'),item($hp,'Model X','model')],$now);
    check(count($row['disks']),1);
    check($row['disks'][0]['state'],'predictive');
    check($row['disks'][0]['model'],'Model X');
    check($row['disks'][0]['slot'],$hp ? 'Port 1I: Box 1: Bay 2' : 'Disk.Bay.2:Enclosure.1');
}
check(DiskData::host('host',[item(false,5),item(false,1,'smart_status')],$now)['disks'][0]['state'],'failed');
echo "$count checks passed\n";
