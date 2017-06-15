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
    
    $route->format = "json";

    require "wifi.php";
    $wifi = new Wifi();
    
    $result = false;
    
    // Special setup access to WIFI function scan and setconfig
    $setup_access = false;
    if (isset($_SESSION['setup_access']) && $_SESSION['setup_access']) $setup_access = true;

    // ------------------------------------------------------------
    // Write level access
    // ------------------------------------------------------------
    if ($session["write"]) {
        
        if ($route->action=="start") $result = $wifi->start();
        if ($route->action=="stop") $result = $wifi->stop();
        if ($route->action=="restart") $result = $wifi->restart();
        
        if ($route->action=="") {
            $route->format = "html";
            $result = view("Modules/wifi/view.html",array());
        }
    }
     
    // ------------------------------------------------------------
    // Read level access
    // ------------------------------------------------------------   
    if ($session["read"]) {
    
        if ($route->action=="info") {
            $result = $wifi->info();
        }
        
        if ($route->action=="getconfig") {
            $result = $wifi->getconfig();
        }
        
        if ($route->action=="log") {
            $route->format = "text";
            $result = $wifi->wifilog();  
        } 
    }
    
    if ($session["read"] || $setup_access) {
        if ($route->action=="scan") $result = $wifi->scan();
    }
    
    if ($session["write"] || $setup_access) {
        if ($route->action=="setconfig") {
            $result = $wifi->setconfig(json_decode($_POST['networks']));
        }
    }

    return array('content' => $result);
}
