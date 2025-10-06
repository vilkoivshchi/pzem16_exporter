<?php
require_once 'ElectricityPolling.php';
$voltagePoller = new ElectricityPolling("/opt/myterm/myterm");
$deviceAddresses = ["01", "02", "03"];
$pollRequest = "040000000a";
$requests;
foreach($deviceAddresses as $address=>$device)
{
    $requests[$address] = $device . $pollRequest;
}

$voltages;
foreach($requests as $key=>$req)
{
    $a = $voltagePoller->Poll($req);
    sleep(1);
    $voltages[$key] = $voltagePoller->ParsePZEMReply($a);
}
//var_dump($voltages);
foreach ($voltages as $deviceAddress=>$voltage)
{
    header("Content-Type: text/plain");
    header("Connection: close");
    $help = "#HELP";
    $typeGauge = "#TYPE gauge";
    foreach($voltage as $key=>$element)
    {
        echo $help . " {$key}"  . PHP_EOL;
        echo $typeGauge . PHP_EOL;
        echo PrepareTemperaure($deviceAddress, $key, $element);
    }
}
function PrepareTemperaure($deviceAddress, $key, $value)
{
    
    return $key . "{rs485_address=\"{$deviceAddress}\"} " . $value . PHP_EOL;
}
?>
