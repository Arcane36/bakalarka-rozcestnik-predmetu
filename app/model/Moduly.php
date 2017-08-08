<?php
/**
 * Created by PhpStorm.
 * User: Arcan
 * Date: 21. 3. 2016
 * Time: 22:32
 */

namespace App\Model;
use Nette\Database\Context;

class Moduly {

    /** @var Context */
    public $database;


    public function __construct(Context $database) {
        $this->database = $database;
    }


    public function getModulInfo() {
        return $this->database->query("SELECT token, domena FROM Modul ORDER BY id_modulu")->fetchAll();
    }

    public function getDomains() {
        return $this->database->query("SELECT domena FROM Modul")->fetchAll();
    }
}