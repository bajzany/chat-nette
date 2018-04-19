<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 14.03.2018
 * Time: 22:58
 */

namespace App\Manager;



class UserManager
{

    private $db;

    const TABLE_NAME = 'users';

    const USER_SALT = 'ash324vDa';

    const ROLE_ADMIN = 1;
    const ROLE_MEMBERS = 2;
    const ROLE_MODERATOR = 3;

    public function __construct(\Dibi\Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param $data
     * @return \Dibi\Result|int
     * @throws \Dibi\Exception
     */
    public function register($data)
    {
        unset($data["password2"]);
        // defaultni role
        $data['token'] = $token = \Nette\Utils\Random::generate(32);
        $data["password"] = sha1($data["password"] . self::USER_SALT);
        $data["status"] = 0;

        $this->db->insert(self::TABLE_NAME, $data)
            ->execute();

        $id = $this->db->getInsertId();

        $affectedRows = $this->db->getAffectedRows();

        $data = ['user_id' => $id, 'group_id' => self::ROLE_MEMBERS ];
        $this->db->insert("users_groups", $data)
            ->execute();

        if ($affectedRows == 0) {
            $affectedRows = $this->db->getAffectedRows();
        }

        return $affectedRows;
    }

    public function getUserById($id)
    {
        return $this->db->select('u.id, u.email, u.name, ug.group_id as role')
            ->from(UserManager::TABLE_NAME)
            ->as("u")
            ->leftJoin("users_groups")
            ->as("ug")
            ->on("u.id = ug.user_id")
            ->where('u.id = %i', $id)
            ->fetch();
    }

    public function getUserByEmail($email)
    {
        return $this->db->select('u.*, g.name as role')
            ->from(UserManager::TABLE_NAME)
            ->as("u")
            ->leftJoin("users_groups")
            ->as("ug")
            ->on("u.id = ug.user_id")
            ->leftJoin("groups")
            ->as("g")
            ->on("ug.group_id = g.id")

            ->where('u.email = %s', $email)
            ->fetch();
    }

    public function getAllUsers()
    {
        return $this->db->select('u.id, u.email, u.name, g.name as role')
            ->from(UserManager::TABLE_NAME)
            ->as("u")
            ->leftJoin("users_groups")
            ->as("ug")
            ->on("u.id = ug.user_id")
            ->leftJoin("groups")
            ->as("g")
            ->on("ug.group_id = g.id")
            ->fetchAll();
    }


    public function checkStatusAllUsers($interval)
    {
        $this->db->update("users", ['status' => 0])
            ->where("last_activity IS NULL or ((ROUND( TIME_TO_SEC(TIMEDIFF(now(), last_activity))/60)) >= {$interval})")
            ->execute();
    }

    public function getPublicUsers()
    {
        return $this->db->select("u.name AS user, g.name AS role, u.last_activity, u.status, r.name AS room_name, r.id AS room_id")
            ->from(UserManager::TABLE_NAME)
            ->as("u")

            ->leftJoin("users_groups")
            ->as("ug")
            ->on("u.id = ug.user_id")

            ->leftJoin("groups")
            ->as("g")
            ->on("ug.group_id = g.id")

            ->leftJoin("rooms_users")
            ->as("ru")
            ->on("u.id = ru.user_id")

            ->leftJoin("rooms")
            ->as("r")
            ->on("r.id = ru.room_id")


            ->fetchAll();
    }

    /**
     * Zobrazeni online uzivatelu v sidebaru
     *
     * @param null $roomId
     * @return array
     */
    public function getUsersOnline($roomId = null)
    {
        $query = $this->db->select('u.name')
            ->from("users")
            ->as("u")
            ->where("u.status = %i", 1);

        if (!is_null($roomId)) {
            $query->innerJoin("rooms_users")
                ->as("ru")
                ->on("u.id = ru.user_id")
                ->where("ru.room_id = %i", $roomId);
        }

        return $query->fetchAll();
    }

    public function getGroups()
    {
        return $this->db->select('id, name')
            ->from("groups")
            ->fetchAll();
    }

    /**
     * @param $id
     * @param $values
     * @throws \Dibi\Exception
     */
    public function updateUser($id, $data)
    {
        $groupId = $data['role'];
        unset($data['role']);

        $this->db->update(UserManager::TABLE_NAME, $data)
            ->where('id = %i', $id)
            ->execute();

        $data = ['group_id' => $groupId];
        $this->db->update("users_groups", $data)
            ->where('user_id = %i', $id)
            ->execute();
    }

    /**
     * Online/offline uzivatele
     * @param $userId
     * @param $data
     * @throws \Dibi\Exception
     */
    public function updateUserStatus($userId, $data)
    {
        $this->db->update(UserManager::TABLE_NAME, $data)
            ->where('id = %i', $userId)
            ->execute();
    }

    /**
     * Posledni aktivita uzivatele
     * @param $userId
     * @param $data
     * @throws \Dibi\Exception
     */
    public function updateUserActivity($userId, $data)
    {
        $this->db->update(UserManager::TABLE_NAME, $data)
            ->where('id = %i', $userId)
            ->execute();
    }

    /**
     * @param $id
     * @throws \Dibi\Exception
     */
    public function promoteUser($id)
    {
        $this->db->update("user_groups",['group_id' => 1])
            ->where('user_id = %i', $id)
            ->execute();
    }

    /**
     * @param $id
     * @throws \Dibi\Exception
     */
    public function demoteUser($id)
    {
        $this->db->update("user_groups",['group_id' => 2])
            ->where('user_id = %i', $id)
            ->execute();
    }

    /**
     * @param $id
     * @throws \Dibi\Exception
     */
    public function deleteUser($id)
    {
        $this->db->delete(UserManager::TABLE_NAME)
            ->where('id = %s', $id)
            ->execute();
    }

}