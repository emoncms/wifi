<?php

class Wifi
{
    public function start()
    {
        exec('sudo /sbin/ifconfig wlan0 up',$return);
	return "wlan0 started";
    }

    public function stop()
    {
        exec('sudo /sbin/ifconfig wlan0 down',$return);
	return "wlan0 stopped";
    }

    public function restart()
    {
        exec('sudo /sbin/ifconfig wlan0 down',$return);
        exec('sudo /sbin/ifconfig wlan0 up',$return);
        return "wlan0 restarted";
    }

    public function wifilog()
    {
        global $openenergymonitor_dir;

        if (file_exists("$openenergymonitor_dir/emonpi/wifiAP/networklog.sh")) {
            exec("sudo $openenergymonitor_dir/emonpi/wifiAP/networklog.sh",$out);
            $result = "";
            foreach($out as $line) {
                $result .= $line."\n";
            }
            return $result;
        }
        return "Error: Cannot find $openenergymonitor_dir/emonpi/wifiap/networklog.sh";
    }

    public function scan()
    {
        exec('sudo /sbin/ifconfig wlan0 up',$return);
        exec("sudo wpa_cli -i wlan0 scan",$return);
        sleep(3);

        print "wlan scan: " . json_encode($return) . "\n";

        $scan_results = "";
        exec("sudo wpa_cli -i wlan0 scan_results", $scan_results);

        $networks = array();
        foreach($scan_results as $network) {
            if ($network!="bssid / frequency / signal level / flags / ssid") {
                $arrNetwork = preg_split("/[\t]+/", $network);
                if (isset($arrNetwork[4])) {
                    $ssid = $arrNetwork[4];
                    $networks[$ssid] = array(
                        "BSSID"=>$arrNetwork[0],
                        "CHANNEL"=>$arrNetwork[1],
                        "SIGNAL"=>$arrNetwork[2],
                        "SECURITY"=>substr($arrNetwork[3], 1, -1)
                    );
                }
            }
        }
        print json_encode($networks) . "\n";
        return $networks;
    }

    public function info()
    {
        $return = "";
        exec('/sbin/ifconfig wlan0', $return);
        exec('/sbin/iwconfig wlan0', $return);
        $strWlan0 = implode(" ", $return);
        $strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);

        $wlan = array(
            'RxBytes' => "",
            'TxBytes' => "",
        );

        preg_match('/(?:HWaddr|ether) ([0-9a-f:]+)/i', $strWlan0, $result); // Adding MAC Address for onboard wifi
        if (isset($result[1])) {
            $wlan['MacAddress'] = $result[1];
        }

        preg_match('/inet (?:addr:)? ?([0-9.]+)/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['IPAddress'] = $result[1];
        }

        preg_match('/(?:Mask:|netmask) ?([0-9.]+)/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['SubNetMask'] = $result[1];
        }

        preg_match('/RX packets:?(\d+)/', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['RxPackets'] = $result[1];
        }

        preg_match('/TX packets:?(\d+)/', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['TxPackets'] = $result[1];
        }

        preg_match('/RX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['RxBytes'] = $result[1];
        }

        preg_match('/RX packets \d+ bytes (\d+ \(\d+.\d+ [K|M|G]iB\))/i', $strWlan0, $result); // Adding RX Bytes
        if (isset($result[1])) {
            $wlan['RxBytes'] = $result[1];
        }

        preg_match('/TX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['TxBytes'] = $result[1];
        }

        preg_match('/TX packets \d+ bytes (\d+ \(\d+.\d+ [K|M|G]iB\))/i', $strWlan0, $result); // Adding TX Bytes
        if (isset($result[1])) {
            $wlan['TxBytes'] = $result[1];
        }

        preg_match('/ESSID:\"([a-zA-Z0-9_\-\s]+)\"/i', $strWlan0, $result); //Added some additional charicters here
        if (isset($result[1])) {
            $wlan['SSID'] = str_replace('"', '', $result[1]);
        }

        preg_match('/Access Point: ([0-9a-f:]+)/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['BSSID'] = $result[1];
        }

        preg_match('/Bit Rate[:=]([0-9]+ Mb\/s)/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['Bitrate'] = $result[1];
        }

        preg_match('/Frequency:(\d+\.\d+ GHz)/i', $strWlan0, $result); //escaped the full stop here
        if (isset($result[1])) {
            $wlan['Freq'] = $result[1];
        }

        preg_match('/Link Quality=([0-9]+\/[0-9]+)/i', $strWlan0, $result);
        if (isset($result[1])) {
            $wlan['LinkQuality'] = $result[1];
        }

        // TODO: This first preg_match is a no-op
        preg_match('/Signal Level=([0-9]+\/[0-9]+)/i', $strWlan0, $result);
        preg_match('/Signal Level=(\-[0-9]+ dBm)/i', $strWlan0, $result); //Added alternative Signal Level Measure
        if (isset($result[1])) {
            $wlan['SignalLevel'] = $result[1];
        }

        if ( (strpos($strWlan0, "ESSID") !== false) && (isset($wlan['SSID'])) ) {
            $wlan['status'] = "connected"; 
        } else {
            $wlan['status'] = "disconnected";
        }

        return $wlan; //Removed a few whitespace lines here
    }

    public function getconfig()
    {
        exec('sudo cat /etc/wpa_supplicant/wpa_supplicant.conf',$return);
        $ssid = array();
        $psk = array();
        $country = "";
        foreach($return as $a) {
            if(preg_match('/SSID/i',$a)) {
                $arrssid = explode("=", $a);
                $ssid[] = str_replace('"', '', $arrssid[1]);
            }
            if(preg_match('/\#psk/i',$a)) {
                $arrpsk = explode("=", $a);
                $psk[] = str_replace('"', '', $arrpsk[1]);
            }
            if(preg_match('/country/i',$a)) {
                $arrcountry = explode("=", $a); 
                $country = trim($arrcountry[1]);
            }
        }
        $numSSIDs = count($ssid);
        if (strlen($country) != 2) {
            $country = "GB";
        }

        $registered = array();
        for($i = 0; $i < $numSSIDs; $i++) {
            $registered[$ssid[$i]] = array();
            if (isset($psk[$i])) {
                $registered[$ssid[$i]]["PSK"] = $psk[$i];
            }
            $registered[$ssid[$i]]["SIGNAL"] = 0;
        }
        return array("networks" => $registered, "country" => $country);
    }

    public function setconfig($networks,$country_code)
    {
        global $openenergymonitor_dir;

        $country_code = strtoupper($country_code);
        if (strlen($country_code) != 2) {
            return array("success" => false, "message" => "Country code must be characters long");
        }

        $config = "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\ncountry=$country_code\n\n";
        if(!empty($networks)) {
            foreach ($networks as $ssid=>$network) {
                if (!empty($network->PSK) && (strlen($network->PSK) >= 8 && strlen($network->PSK) < 64)) {
                    $psk = hash_pbkdf2("sha1", $network->PSK, $ssid, 4096, 64);
                    $config .= sprintf("\nnetwork={\n\tssid=\"%s\"\n\t#psk=\"%s\"\n\tpsk=%s\n}\n", $ssid, $network->PSK, $psk);
                } else {
                    $config .= "network={\n  ssid=" . '"' . $ssid . '"' . "\n  key_mgmt=NONE\n}\n";
                }
            }
        }

        exec("echo '$config' > /tmp/wifidata",$return);
        system('sudo cp /tmp/wifidata /etc/wpa_supplicant/wpa_supplicant.conf',$returnval);

        if (file_exists("$openenergymonitor_dir/data/wifiAP-enabled")) {
            exec("sudo $openenergymonitor_dir/emonpi/wifiAP/stopAP.sh");
        }

        $this->restart();

        return $config;
    }
}
