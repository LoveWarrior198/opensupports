<?php
use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

class UnAssignStaffController extends Controller {
    const PATH = '/un-assign-ticket';

    public function validations() {
        return [
            'permission' => 'staff_1',
            'requestData' => [
                'ticketNumber' => [
                    'validation' => DataValidator::validTicketNumber(),
                    'error' => ERRORS::INVALID_TICKET
                ]
            ]
        ];
    }

    public function handler() {
        $ticketNumber = Controller::request('ticketNumber');
        $user = Controller::getLoggedUser();
        $ticket = Ticket::getByTicketNumber($ticketNumber);

        if($ticket->owner && $ticket->owner->id == $user->id) {
            $user->sharedTicketList->remove($ticket);
            $user->store();
            $ticket->owner = null;
            $ticket->uread = true;
            $ticket->store();
            Response::respondSuccess();
        } else {
            Response::respondError(ERRORS::NO_PERMISSION);
            return;
        }
    }
}