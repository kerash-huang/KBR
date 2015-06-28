<?php
namespace ezapi\Controller;

use \ezapi as ez;

class Home extends \ezapi\Controller {

    public static function WhoAmI() {
        echo "You are ".ez\Common::GetParam("name");
    }    
}