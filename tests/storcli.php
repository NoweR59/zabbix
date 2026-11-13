<?php
require '/tmp/StorCli.php';
use Modules\HiolleIdracRaid\Includes\StorCli;
function check($actual, $expected) { if ($actual !== $expected) { throw new Exception('Test failed'); } }
$fixture = ['Controllers'=>[['Command Status'=>['Status'=>'Success','Controller'=>0],
    'Response Data'=>['Drive /c0/e252/s1'=>[['EID:Slt'=>'252:1','State'=>'Onln','Model'=>'SSD','Size'=>'960 GB']],
    'Drive /c0/e252/s1 - Detailed Information'=>['Device attributes'=>['SN'=>'ABC']]]]]];
check(StorCli::parse(json_encode($fixture))[0]['state'], 'ok');
check(StorCli::parse(json_encode($fixture))[0]['serial'], 'ABC');
$fixture['Controllers'][0]['Response Data']['Drive /c0/e252/s1'][0]['State']='Offln';
check(StorCli::parse(json_encode($fixture))[0]['state'], 'failed');
$fixture['Controllers'][0]['Command Status']['Status']='Failure';
check(StorCli::parse(json_encode($fixture)), null);
check(StorCli::parse('{}'), null);
check(StorCli::parse('invalid'), null);
echo "6 StorCLI checks passed\n";
