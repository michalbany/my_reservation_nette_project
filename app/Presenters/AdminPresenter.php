<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\Database\Context;
use Nextras\Migrations\Printers\Console;

class AdminPresenter extends Nette\Application\UI\Presenter
{
    /** @var Context */
    private $database;


    public function __construct(Context $database)
    {
        $this->database = $database;
    }

	public function renderDefault()
	{
        // načtení všech pokojů a rezervací z databáze
        $rooms = $this->database->table('rooms')->fetchAll();
        $reservations = $this->database->table('reservations')->fetchAll();
        
        $this->template->rooms = $rooms;
        $this->template->reservations = $reservations;
	}

    protected function createComponentAddRoomForm(): Form
    {
        $form = new Form;

        // Vytvoření formuláře pro přidání pokoje
        $form->addText('name', 'Název pokoje:')
            ->setRequired('Zadejte prosím název pokoje.')
            ->setAttribute('class', 'form-control');


        $form->addSelect('type', 'Typ pokoje:', [
                '1lůžkový' => '1 lůžkový',
                '2lůžkový' => '2 lůžkový',
                'apartmán' => 'Apartmán',
            ])->setPrompt('Vyberte typ pokoje')
            ->setRequired('Zadejte prosím typ pokoje.')
            ->setAttribute('class', 'form-control');


        $form->addText('room_number', 'Číslo pokoje:')
            ->setRequired('Zadejte prosím číslo pokoje.')
            ->setType('number')
            ->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Přidat pokoj')
            ->setAttribute('class', 'btn btn-primary btn-block');

        $form->onSuccess[] = [$this, 'addRoomFormSucceeded'];
        
        return $form;
    }

    public function addRoomFormSucceeded(Form $form, \stdClass $values)
    {
        // přidání pokoje do databáze
        $this->database->table('rooms')->insert([
            'name' => $values->name,
            'type' => $values->type,
            'room_number' => $values->room_number
        ]);

        $this->flashMessage('Pokoj byl úspěšně přidán!', 'success');
        $this->redirect('this');
    }

    public function actionDeleteRoom($id)
    {
        // kontrola, zda pokoj nemá rezervaci - pokud ano, nelze ho smazat
        if ($this->database->table('reservations')->where('room_id', $id)->fetch())
        {
            $this->flashMessage('Pokoj nelze smazat, protože má rezervaci.', 'danger');
            $this->redirect('default');
        }
        // nalezení a smazání pokoje
        $this->database->table('rooms')->where('id', $id)->delete();
        
        $this->flashMessage('Pokoj byl úspěšně smazán.', 'success');
        $this->redirect('default');
    }

    public function actionDeleteReservation($id)
    {
        // nalezení a smazání rezervace
        $this->database->table('reservations')->where('id', $id)->delete();

        $this->flashMessage('Rezervace byla úspěšně smazána.', 'success');
        $this->redirect('default');
    }

}
