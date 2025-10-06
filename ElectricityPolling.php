<?php

class ElectricityPolling
{
    private string $_electricPollExec;

    public function __construct(string $execPath)
    {
        $this->_electricPollExec = $execPath;
    }

    public function Poll(string $argument): string
    {
        exec($this->_electricPollExec . " " . $argument, $electricPollOutput, $exitcode);

        if ($exitcode == 0 && count($electricPollOutput) > 0) {
            return $electricPollOutput[0];
        } else {
            return "";
        }
    }

    public function ParsePZEMReply(string $reply)
    {
        $data = [];

        $voltage = hexdec(substr($reply, 0, 4));
        $data["voltage"] = (int)round($voltage / 10, 0);
        //echo "voltage: " . $voltage / 10 . "V\n";
        $current = hexdec(substr($reply, 8, 4) . substr($reply, 4, 4));
        $data["current"] = (float)round($current / 1000, 1);
        //echo "current: " . $current / 1000 . "A\n";
        $power = hexdec(substr($reply, 16, 4) . substr($reply, 12, 4));
        //echo "power: " . $power / 10 . "W\n";
        $data["power"] = (float)round($power / 10, 1);
        $energyValue = hexdec(substr($reply, 24, 4) . substr($reply, 20, 4));
        $data["energy_wh"] = $energyValue;
        $data["energy_value"] = (float)round($energyValue / 1000, 3);

        //echo "energy value: " . $energyValue / 1000 . "kWh\n";
        $frequency = hexdec(substr($reply, 28, 4));
        //echo "frequency: " . $frequency / 10 . "Hz\n";
        $data["frequency"] = (float)round($frequency / 10, 1);
        $powerFactor = hexdec(substr($reply, 32, 4));
        //echo "power factor: " . $powerFactor / 100 . "\n";
        $data["power_factor"] = (float)round($powerFactor / 100, 1);
        if (substr($reply, 36, 4) == "FFFF") {
            //echo "alarm\n";
            $data["alarm"] = 1;
        } else {
            $data["alarm"] = 0;
            //echo "no alarm\n";
        }
        return $data;
    }

    public function workshopPolling(string $reply) : array
    {
        if ($reply != null) {
            $electricMeasuredParameters = $this->ParsePZEMReply($reply);
        } else {
            $electricMeasuredParameters["voltage"] = null;
            $electricMeasuredParameters["current"] = null;
            $electricMeasuredParameters["power"] = null;
            $electricMeasuredParameters["energy_value"] = null;
            $electricMeasuredParameters["frequency"] = null;
            $electricMeasuredParameters["power_factor"] = null;
            $electricMeasuredParameters["alarm"] = null;
        }
        return $electricMeasuredParameters;
    }

    public function ParseTRMValues(string $reply) : array
    {
        $data = [];
        if ($reply != null) {
            $data["controller_power"] = 1;
            $data["alarm_status"] = (bool)hexdec(substr($reply, 0, 4));
            $data["program_num"] = hexdec(substr($reply, 4, 4));
            $data["program_step"] = hexdec(substr($reply, 8, 4));
            $data["controller_state"] = hexdec(substr($reply, 12, 4));
        }
        else
        {
            $data["controller_power"] = 0;
            $data["alarm_status"] = null;
            $data["program_num"] = null;
            $data["program_step"] = null;
            $data["controller_state"] = null;
        }
        return $data;
    }

    public function hex2float(string $strHex)
    {
        if ($strHex != null) {
            $hex = sscanf($strHex, "%02x%02x%02x%02x");
            $bin = implode('', array_map('chr', $hex));
            $array = unpack("Gnum", $bin);
            return round($array['num'], 1);
        } else {
            return null;
        }
    }
}
?>
