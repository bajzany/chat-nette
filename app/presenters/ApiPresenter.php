<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 13:58
 */

namespace App\Presenters;

use App\Manager\MessageManager;
use App\Manager\TokenManager;
use App\Manager\UserManager;
use App\Model\RoomManager;
use Dibi\Exception;
use Drahak\Restful\Security\SecurityException;
use Nette;


class ApiPresenter extends Nette\Application\UI\Presenter
{

    CONST STATUS_OK = "OK";
    CONST STATUS_ERROR = "ERROR";

    /** @var MessageManager @inject */
    public $messageManager;

   /** @var UserManager @inject */
    public $userManager;

    /** @var RoomManager @inject */
    public $roomManager;

    /** @var TokenManager @inject */
    public $tokenManager;

    private $status = [];
    private $message = [];

    protected function startup()
    {
        parent::startup();

        if (empty($this->getHttpRequest()->getHeader('Authorization')))
        {
            $this->setStatus(0);
            $this->setMessage("Prázdný token");
            $this->sendResponse(new \Nette\Application\Responses\JsonResponse( $this->getResponse()));
        }

        $result = $this->tokenManager->tokenExist($this->getHttpRequest()->getHeader('Authorization'));

        if (!$result)
        {
            $this->setStatus(0);
            $this->setMessage("Neplatný token");
            $this->sendResponse(new \Nette\Application\Responses\JsonResponse( $this->getResponse()));
        }

    }

    private function isAllowedMessagesMethod()
    {
        if ($this->getHttpRequest()->isMethod('GET')
            || $this->getHttpRequest()->isMethod('PUT')
            || $this->getHttpRequest()->isMethod('POST')
            ||  $this->getHttpRequest()->isMethod('DELETE'))
        {
            return true;
        } else {
            $this->setStatus(0);
            $this->setMessage("Nepovolená metoda");
            $this->sendResponse(new \Nette\Application\Responses\JsonResponse( $this->getResponse()));
        }
    }

    public function actionMessages()
    {
        if ($this->isAllowedMessagesMethod()) {

            if ($this->getHttpRequest()->isMethod('GET')) {
                $result = $this->messageManager->getAllMessages();
                $this->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
            }

            if ($this->getHttpRequest()->isMethod('POST')) {
                if (empty($this->getHttpRequest()->getHeader('user_id'))) {
                    $this->setStatus(0);
                    $this->setMessage("Musi byt zadano ID uzivatele");
                }
                $data['user_id%i'] = $this->getHttpRequest()->getHeader('user_id');
                $data['text%s'] = $this->getHttpRequest()->getHeader('text');
                $data['date%d'] = new \DateTime();

                $roomId = $this->getHttpRequest()->getHeader('room_id');
                if (!is_null($roomId) && !empty($roomId)) {
                    $data['room_id%i'] = $roomId;
                }

                try {
                    $this->messageManager->createMessage($data);
                    $this->setStatus(1);
                    $this->setMessage("Zprava byla uspesne pridana");
                } catch (Exception $e) {
                    $this->setStatus(0);
                    $this->setMessage("Zpravu se nepodarilo pridat");
                }
            }

            if ($this->getHttpRequest()->isMethod('DELETE')) {
                $id = $this->getParameter('id');

                if (!$this->messageManager->messageExist($id)) {
                    $this->setStatus(0);
                    $this->setMessage("Zprava neexistuje");
                    $this->sendResponse(new \Nette\Application\Responses\JsonResponse('CHYBA: Zprava neexistuje'));
                }

                try {
                    $this->messageManager->deleteMessage($id);
                    $this->setStatus(1);
                    $this->setMessage("Zprava byla uspesne smazana");
                } catch (Exception $e) {

                }
            }

            $this->sendResponse(new \Nette\Application\Responses\JsonResponse( $this->getResponse()));
        }
    }

    public function actionRooms()
    {
        if ($this->isAllowedMessagesMethod()) {

            if ($this->getHttpRequest()->isMethod('GET')) {
                $id = $this->getHttpRequest()->getHeader('id');
                $moderator_id = $this->getHttpRequest()->getHeader('moderator_id');
                $result = $this->roomManager->getRooms($id, $moderator_id);
                $this->sendResponse(new \Nette\Application\Responses\JsonResponse($result));
            }

            if ($this->getHttpRequest()->isMethod('POST')) {
                $data['name%s'] = $this->getHttpRequest()->getHeader('name');
                $data['description%s'] = $this->getHttpRequest()->getHeader('description');

                $roomId = null;
                try {
                    $roomId = $this->roomManager->insertRoom($data, true);
                    $this->setStatus(1);
                    $this->setMessage("Mistnost byla uspesne vytvorena");
                } catch (Exception $e) {
                    $this->setStatus(0);
                    $this->setMessage("Mistnost se nepodarilo vytvorit");
                }

                $moderators = $this->getHttpRequest()->getHeader('moderator');
                if (!is_null($moderators) && !empty($moderators)) {
                    $moderators = explode(",", $moderators);
                    try {
                        $this->roomManager->insertRoomModerators($roomId, $moderators);
                        $this->setStatus(1);
                        $this->setMessage(count($moderators) == 1 ? "Moderator byl uspesne pridan": "Vice moderatoru bylo pridano");
                    } catch (Exception $e) {
                        $this->setStatus(0);
                        $this->setMessage(count($moderators) == 1 ? "Moderatora se nepodarilo pridat": "Vice moderatoru se nepodarilo pridat");
                    }
                }
            }

            if ($this->getHttpRequest()->isMethod('PUT')) {
                $roomId = $this->getParameter('id');

                $data['id%i'] = $roomId;
                $data['name%s'] = $this->getHttpRequest()->getHeader('name');
                $data['description%s'] = $this->getHttpRequest()->getHeader('description');

                try {
                    $affectedRows = $this->roomManager->updateRoom($roomId, $data);
                    if ($affectedRows != 0) {
                        $this->setStatus(1);
                        $this->setMessage("Mistnost byla uspesne upravena");
                    }
                } catch (Exception $e) {
                    $this->setStatus(0);
                    $this->setMessage("Mistnost se nepodarilo upravit");
                }

                $moderators = $this->getHttpRequest()->getHeader('moderator');
                if (!is_null($moderators) && !empty($moderators)) {
                    $moderators = explode(",", $moderators);
                    try {
                        $this->roomManager->deleteRoomModerators($roomId);
                        $this->roomManager->insertRoomModerators($roomId, $moderators);
                        $this->setStatus(1);
                        $this->setMessage(count($moderators) == 1 ? "Moderator byl uspesne upraven": "Vice moderatoru bylo upraveno");
                    } catch (Exception $e) {
                        $this->setStatus(0);
                        $this->setMessage(count($moderators) == 1 ? "Moderatora se nepodarilo upravit": "Vice moderatoru se nepodarilo upravit");
                    }
                }
            }

            if ($this->getHttpRequest()->isMethod('DELETE')) {
                $id = $this->getParameter('id');

                try {
                    $affectedRow = $this->roomManager->deleteRoom($id);

                    if ($affectedRow != 0) {
                        $this->setStatus(1);
                        $this->setMessage("Mistnost byla uspesne smazana");
                    } else {
                        $this->setStatus(0);
                        $this->setMessage("Mistnost se nepodarilo smazat");
                    }
                } catch (Exception $e) {
                    $this->setStatus(0);
                    $this->setMessage("Mistnost se nepodarilo smazat");
                }
            }

            $this->sendResponse(new \Nette\Application\Responses\JsonResponse( $this->getResponse()));
        }
    }

    private function setStatus($status)
    {
        $this->status[] = ($status) ? self::STATUS_OK : self::STATUS_ERROR;
    }

    private function getStatus()
    {
        return $this->status;
    }

    private function setMessage($message)
    {
        $this->message[] = $message;
    }

    private function getMessages()
    {
        return $this->message;
    }

    private function getResponse() {
        $aStatus = $this->getStatus();
        $aMessages = $this->getMessages();

        $ret = [];
        foreach($aStatus as $index => $status)
        {
            $ret[] = $status . ":" . $aMessages[$index];
        }

        return $ret;
    }
}