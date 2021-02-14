<?php
global $session;
if ($session["write"]) {
    $menu["setup"]["l2"]['wifi'] = array(
        "name"=>_("WiFi"),
        "href"=>"wifi", 
        "order"=>11, 
        "icon"=>"wifi"
    );
}
