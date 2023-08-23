<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Nette\Database\Context;
use Nextras\Migrations\Printers\Console;

class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @var Context */
    private $database;


    public function __construct(Context $database)
    {
        $this->database = $database;
    }

	public function renderDefault()
	{
        // nic
	}

	protected function createComponentReservationForm(): Form
    {
        $form = new Form;

        // Vytvoření formuláře pro hledání volných pokojů
        $form->addText('arrivalDate', 'Datum příjezdu:')
            ->setRequired('Zadejte prosím datum příjezdu.')
            ->setType('date')
            ->setAttribute('class', 'form-control');

        $form->addText('departureDate', 'Datum odjezdu:')
            ->setRequired('Zadejte prosím datum odjezdu.')
            ->setType('date')
            ->setAttribute('class', 'form-control');

        $form->addSelect('roomType', 'Typ pokoje:', [
            '1lůžkový' => '1 lůžkový',
            '2lůžkový' => '2 lůžkový',
            'apartmán' => 'Apartmán',
            'all' => 'Všechny typy',
        ])->setPrompt('Vyberte typ pokoje')
        ->setRequired('Vyberte prosím typ pokoje.')
        ->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Pokračovat')
        ->setAttribute('class', 'btn btn-primary btn-block');

        $form->onSuccess[] = [$this, 'reservationFormSucceeded'];

        return $form;
    }

    public function reservationFormSucceeded(Form $form, \stdClass $values)
    {   
        $arrivalDate = $values->arrivalDate;
        $departureDate = $values->departureDate;

        // Kontrola, zda je datum příjezdu dříve než datum odjezdu
        if ($arrivalDate > $departureDate) {
            $this->flashMessage('Datum příjezdu musí být dříve než datum odjezdu.', 'danger');
            $this->redirect('this');
        }

        // Získáme všechny rezervace, které se kříží s daným obdobím
        $reservedRooms = $this->database->table('reservations')
            ->where('arrival_date <= ? AND departure_date >= ?', $departureDate, $arrivalDate)
            ->fetchPairs('room_id', 'room_id');

        if ($values->roomType == 'all') {
            $rooms = $this->database->table('rooms')
                ->where('id NOT', $reservedRooms)  // vyřadíme pokoje, které jsou rezervovány
                ->fetchAll();
        } else {
            $rooms = $this->database->table('rooms')
                ->where('id NOT', $reservedRooms)  // vyřadíme pokoje, které jsou rezervovány
                ->where('type', $values->roomType)
                ->fetchAll();
        }

        $this->template->rooms = $rooms;

        if (count($rooms) == 0) {
            // Najdeme nejbližší volné datum pro pokoj, který máte v úmyslu rezervovat
            $roomIds = $this->database->table('rooms')
                ->where('type', $values->roomType)
                ->fetchPairs('id', 'id');

            // Najít nejbližší datum odjezdu pro pokoje s těmito ID:
            $departureDate = $this->database->table('reservations')
                ->where('room_id', $roomIds)
                ->order('departure_date ASC')
                ->limit(1)
                ->fetch();

            $nextDate = new \DateTime($departureDate->departure_date);
            $nextDate->add(new \DateInterval('P1D')); // přičteme jeden den
            $dataString = $nextDate->format('d. m. Y');
    
            $this->flashMessage('Nejbližší volný termín je od '. $dataString, 'info');
        }
    }

}
