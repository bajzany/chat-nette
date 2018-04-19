<?php

namespace App\Model;

use Dibi\Connection;

class RoomManager
{
    const USER_SALT = 'ash324vDa';

    const
        TABLE_NAME = 'rooms',
        COLUMN_ID = 'id',
        COLUMN_NAME = 'name',
        COLUMN_DESC = 'description',
        MODERATOR_GROUP = 3;

    /**
     * @var Connection $db
     */
    private $db;


    public function __construct(\Dibi\Connection $db)
    {
        $this->db = $db;
    }

    public function insertRoom($data, $retId = false)
    {
        if (isset($data['moderators'])) {
            $moderators = $data['moderators'];
            unset($data['moderators']);
        }

        $this->db->insert(self::TABLE_NAME, $data)
            ->execute();

        $affectedRows = $this->db->getAffectedRows();
        $id = $this->db->getInsertId();

        if (isset($moderators)) {
            $this->insertRoomModerators($id, $moderators);
        }

        if (!$retId) {
            return $affectedRows;
        } else {
            return $id;
        }
    }

    public function updateRoom($id, $data)
    {
        if (isset($data['moderators'])) {
            $moderators = $data['moderators'];
            unset($data['moderators']);
        }

        $this->db->update(self::TABLE_NAME, $data)
            ->where(self::COLUMN_ID . ' = %i', $id)
            ->execute();

        $affectedRows = $this->db->getAffectedRows();

        if (isset($moderators)) {
            $this->deleteRoomModerators($id);
            $this->insertRoomModerators($id, $moderators);

            if ($affectedRows == 0) {
                $affectedRows = $this->db->getAffectedRows();
            }
        }

        return $affectedRows;
    }

    public function deleteRoomModerators($roomId)
    {
        $this->db->delete("rooms_moderators")
            ->where( "room_id = %i", $roomId)
            ->execute();
    }

    public function insertRoomModerators($roomId, $moderators)
    {
        foreach ($moderators as $moderatorId) {
            $roomModerators = [
                                'user_id' => $moderatorId,
                                'room_id' => $roomId,
                              ];

            $this->db->insert("rooms_moderators", $roomModerators)
                ->execute();
        }
    }

    public function deleteRoom($id)
    {
        $this->db->delete(self::TABLE_NAME)
            ->where(self::COLUMN_ID . ' = %i', $id)
            ->execute();

        return $this->db->getAffectedRows();
    }

    public function getRooms($id = null)
    {
        $query = $this->db->select('r.*, GROUP_CONCAT(u.name) AS moderators')
            ->from(self::TABLE_NAME)
            ->as("r")
            ->leftJoin("rooms_moderators")
            ->as("mr")
            ->on("r.id = mr.room_id")

            ->leftJoin("users")
            ->as("u")
            ->on("u.id = mr.user_id")
            ->groupBy("r.id");

        if (!is_null($id)) {
            $res = $query->where("r." . self::COLUMN_ID . ' = %i', $id)
                ->fetch();
        } else {
            $res = $query->fetchAll();
        }

        return $res;
    }

    public function getModerators()
    {
        return
            $this->db->select("u.id, u.name")
            ->from("users")
            ->as("u")
            ->leftJoin("users_groups")
            ->as("ug")
            ->on("u.id = ug.user_id")
            ->where("ug.group_id = %i", self::MODERATOR_GROUP)
            ->fetchAll();
    }

    public function getModeratorsIdByNames($aNames = null)
    {
            return $this->db->select("id")
                ->from("users")
                ->where("name IN %l", $aNames)
                ->fetchPairs();
    }

    /**
     * Detail mistnosti
     *
     * @param $id
     * @return \Dibi\Row|false
     */
    public function getRoom($id)
    {
        return $this->db->select('r.*, GROUP_CONCAT(u.name) AS moderators')
            ->from(self::TABLE_NAME)
            ->as("r")
            ->leftJoin("rooms_moderators")
            ->as("mr")
            ->on("r.id = mr.room_id")

            ->leftJoin("users")
            ->as("u")
            ->on("u.id = mr.user_id")

            ->where("r." . self::COLUMN_ID . ' = %i', $id)
            ->groupBy("r.id")
            ->fetch();
    }

    /**
     * Detail mistnosti na hlavni strane
     * @return array
     */
    public function getRoomsHomepage()
    {
        return $this->db->select('r.*, COUNT(ru.user_id) AS countMembers')
            ->from(self::TABLE_NAME)
            ->as("r")

            ->leftJoin("rooms_users")
            ->as("ru")
            ->on("r.id = ru.room_id")

            ->leftJoin("users")
            ->as("u")
            ->on("ru.user_id = u.id")
//            ->where("u.status = %i", 1)
            ->groupBy("r." . self::COLUMN_ID)
            ->fetchAll();
    }

    public function getUsersByRoom($roomId = null)
    {
        $query = $this->db->select('u.name')
            ->from("users")
            ->as("u")
            ->where("u.status = %i", 1)
            ->leftJoin("rooms_users")
            ->as("ru")
            ->on("u.id = ru.user_id");

        if (!is_null($roomId)) {
            $query->where("ru.room_id = %i", $roomId);
        }

        return $query->fetchAll();
    }


    public function deleteUserRooms($userId)
    {
        $this->db->delete("rooms_users")
            ->where("user_id = %i", $userId)
            ->execute();
    }

    public function insertUserRoom($data)
    {
        $this->db->insert("rooms_users", $data)
            ->execute();
    }


    public function getMessagesAmount($roomId, $amount)
    {
        return  $this->db->select('messages.id, messages.text, messages.date, users.name')
            ->from("messages")
            ->innerJoin("users")->on('messages.user_id = users.id')
            ->where("room_id = %i", $roomId)
            ->limit($amount)
            ->orderBy('id','DESC')
            ->fetchAll();
    }
}