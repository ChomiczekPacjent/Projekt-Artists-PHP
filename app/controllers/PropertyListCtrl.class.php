<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use app\forms\PropertyListForm;

class PropertyListCtrl {

    private $form;

    public function __construct() {
        $this->form = new PropertyListForm();
    }

    public function action_mainpage() {
        $properties = App::getDB()->query("SELECT * FROM properties ORDER BY RAND() LIMIT 3")->fetchAll();
        
        foreach ($properties as &$property) {
            $property_images = App::getDB()->select("property_images", "imagePath", ["property_id" => $property['idProperty']]);
            if (!empty($property_images)) {
                $property['main_image'] = $property_images[0];
            } else {
                $property['main_image'] = "/assets/images/kruk1.jpg";
                $owner = App::getDB()->get("users", ["firstName", "lastName"], ["idUser" => $property['ownerId']]);
                $property['owner_name'] = $owner['firstName'] . ' ' . $owner['lastName'];
            }
           
        }
        $loggedUser = SessionUtils::load("logged_user", true);
        App::getSmarty()->assign("logged_user", $loggedUser);
        App::getSmarty()->assign("properties", $properties);
        App::getSmarty()->assign('page_title', 'Main Page');
        App::getSmarty()->display("main.tpl");
    }

    public function action_property_list() {
        $this->form->page = ParamUtils::getFromCleanURL(1);
        $property_count = App::getDB()->count("properties", "*");
        $max_page = ceil($property_count / 2);
        $properties = App::getDB()->select("properties", "*", ["LIMIT" => [2 * ($this->form->page - 1), 2]]);

        $loggedUser = SessionUtils::load("logged_user", true);
        

        foreach ($properties as &$property) {
            $property_images = App::getDB()->select("property_images", "imagePath", ["property_id" => $property['idProperty']]);
            if (!empty($property_images)) {
                $property['main_image'] = $property_images[0];
            } else {
                $property['main_image'] = "/assets/images/default_image.jpg";
                
            }$owner = App::getDB()->get("users", ["firstName", "lastName"], ["idUser" => $property['ownerId']]);
            $property['owner_name'] = $owner['firstName'] . ' ' . $owner['lastName'];
           
            
        }

        App::getSmarty()->assign("logged_user", $loggedUser);
        App::getSmarty()->assign("properties", $properties);
        App::getSmarty()->assign("page", $this->form->page);
        App::getSmarty()->assign("max_page", $max_page);
        App::getSmarty()->assign("msgs", App::getMessages()->getMessages());
        App::getSmarty()->assign('page_title', 'Gallery');
        App::getSmarty()->display("properties.tpl");
    }

    // PropertyListCtrl.class.php

    

    public function action_find_properties() {
        $this->form->page = ParamUtils::getFromPost("page");
        $this->form->address = ParamUtils::getFromPost("address");
        $loggedUser = SessionUtils::load("logged_user", true);
        $offset = ($this->form->page - 1) * 5;

        $property_count = App::getDB()->count("properties", "*", ["address[~]" => $this->form->address]);
        $max_page = ceil($property_count / 5);
        $properties = App::getDB()->select("properties", "*", [
            "LIMIT" => [$offset, 5],
            "address[~]" => $this->form->address
        ]);

        

        foreach ($properties as &$property) {
            $property_images = App::getDB()->select("property_images", "imagePath", ["property_id" => $property['idProperty']]);
            if (!empty($property_images)) {
                $property['main_image'] = $property_images[0];
            } else {
                $property['main_image'] = "/assets/images/kruk1.jpg";
            }
            
        }

        App::getSmarty()->assign("properties", $properties);
        App::getSmarty()->assign("page", $this->form->page);
        App::getSmarty()->assign("max_page", $max_page);
        App::getSmarty()->assign("address", $this->form->address);
        App::getSmarty()->assign('page_title', 'Gallery');
        App::getSmarty()->assign('logged_user', $loggedUser);
        App::getSmarty()->display("property_list_partial.tpl");
    }
}

?>