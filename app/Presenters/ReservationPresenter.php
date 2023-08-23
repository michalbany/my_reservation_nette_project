<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class ReservationPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function renderDefault($roomId, $arrivalDate, $departureDate)
    {
        // inicializace dat
        $this->template->room = $this->database->table('rooms')->get($roomId);
        $this->template->arrivalDate = $arrivalDate;
        $this->template->departureDate = $departureDate;
    }

    protected function createComponentReservationForm(): Form
    {
        $form = new Form;

        // Vytvoření formuláře pro rezervaci

        $form->getElementPrototype()->setAttribute('class','form mt-3 mb-3');

        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte prosím jméno.')
            ->setAttribute('class', 'form-control');

        $form->addText('phone', 'Telefon:')
            ->setRequired('Zadejte prosím telefonní číslo.')
            ->setType('tel')
            ->setAttribute('class', 'form-control');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte prosím e-mail.')
            ->setType('email')
            ->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Rezervovat')
            ->setAttribute('class', 'btn btn-primary btn-block');

        $form->onSuccess[] = [$this, 'reservationFormSucceeded'];

        return $form;
    }

    public function reservationFormSucceeded(Form $form, \stdClass $values)
    {
        $roomId = $this->getParameter('roomId');
        $arrivalDate = $this->getParameter('arrivalDate');
        $departureDate = $this->getParameter('departureDate');

        // znovu kontrola, zda je pokoj volný kdyby někdo jiný chtěl vytvořit rezervaci současně
        $alreadyReservated = $this->database->table('reservations')
                            ->where('room_id', $roomId)
                            ->where('arrival_date <= ?', $departureDate)
                            ->where('departure_date >= ?', $arrivalDate)
                            ->fetch();
        
        if ($alreadyReservated) {
            $this->flashMessage('Tento pokoj je již v tomto termínu obsazen.', 'danger');
            $this->redirect('Home:default');
        }

        // vytvoření rezervace zapsání do databáze
        $this->database->table('reservations')->insert([
            'room_id' => $roomId,
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'name' => $values->name,
            'phone' => $values->phone,
            'email' => $values->email,
        ]);

        $this->flashMessage('Rezervace byla úspěšně vytvořena.', 'success');
        $this->redirect('Home:default');
    }

}
