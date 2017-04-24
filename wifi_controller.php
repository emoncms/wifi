<?php
/*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
*/

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function wifi_controller()
{
    global $session, $route;
    if (!$session['write']) {
        return ['content' => false];
    }
    
    $route->format = "json";

    require "wifi.php";
    $wifi = new Wifi();

    switch ($route->action) {
        case 'scan':
            $result = $wifi->scan();
            break;
        case 'info':
            $result = $wifi->info();
            break;
        case 'start':
            $result = $wifi->start();
            break;
        case 'stop':
            $result = $wifi->stop();
            break;
        case 'restart':
            $result = $wifi->restart();
            break;
        case 'log':
            $route->format = "text";
            $result = $wifi->wifilog();
            break;
        case 'getconfig':
            $result = $wifi->getconfig();
            break;
        case 'setconfig':
            $result = $wifi->setconfig(json_decode($_POST['networks']));
            break;
        default:
            $result = view("Modules/wifi/view.html", []);
            $route->format = "html";
            break;
    }

    return ['content' => $result];
}
