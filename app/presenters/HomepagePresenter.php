<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class HomepagePresenter extends Nette\Application\UI\Presenter {
    /** @var \App\Model\Moduly @inject */
    public $modul;

    public function renderDefault() {
        $domains = $this->modul->getDomains();
        $this->template->domains = $domains;
    }

    public function renderResults($searchInput, $checkbox) {
        $results = $this->modul->getModulInfo();

        if ($checkbox == '1') {
            $mode = 'shortname';
        } else {
            $mode = 'fullname';
        }

        foreach ($results as $res) {
            $url = $res['domena'] . '/webservice/rest/server.php?wstoken='
                . $res['token'] . '&wsfunction=local_tul_ws_get_courses&moodlewsrestformat=json&'
                . $mode . '=' . $searchInput;

            //vrací obsah stránky
            $json = file_get_contents($url);
            $obj = json_decode($json);

            //koukám jestli má sitename. Pokud ne, vyhodilo mi to exception a nezahrnu ji
            if (property_exists($obj, 'sitename')) {
                //vytvořim novou property, abych měl v objektu i název domeny. Doménu ještě ořežu o http
                $obj->{"domain"} = str_replace(array('http://','https://'), '', $res['domena']);
                $final[] = $obj;
            }
        }
        $this->template->final = $final;
    }


    public function createComponentSearchBar() {
        $form = new Form;

        $form->addText("searchInput", "Název předmětu: ")
            ->addRule(Form::FILLED, "Zadejte prosím požadovaný předmět")
            ->addRule(Form::MIN_LENGTH, "Řetězec musí mít alespoň 3 znaky", 3)
            ->setAttribute("placeholder", "např. MA1 nebo Matematika");
        $form->addSubmit("searchButton", "Hledat");

        $form->addCheckbox("checkbox", "Kliknutím si přepněte způsob vyhledávání:")
            ->setAttribute("data-toggle", "toggle")
            ->setAttribute("data-on", "Zkratka")
            ->setAttribute("data-off", "Celý název")
            ->setAttribute("data-onstyle", "default");

        $form->onSuccess[] = $this->processSearchBar;
        return $form;
    }


    public function processSearchBar(Form $form) {
        $value = $form->getValues(TRUE);
        $this->redirect('Homepage:results', $value['searchInput'], $value['checkbox'] );
    }
    

}
