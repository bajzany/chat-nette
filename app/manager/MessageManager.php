<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 14:35
 */

namespace App\Manager;


class MessageManager
{
    private $db;

    const TABLE_NAME = 'messages';

    public function __construct(\Dibi\Connection $db)
    {
        $this->db = $db;
    }

    public function getAllMessages()
    {
        return  $this->db->select('messages.id, messages.text, messages.date, users.name')
            ->from(self::TABLE_NAME)
            ->innerJoin(UserManager::TABLE_NAME)->on('messages.user_id = users.id')
            ->fetchAll();
    }

    public function getMessagesAmount($amount)
    {
        return  $this->db->select('messages.id, messages.text, messages.date, users.name')
            ->from(self::TABLE_NAME)
            ->innerJoin(UserManager::TABLE_NAME)->on('messages.user_id = users.id')
            ->limit($amount)
            ->orderBy('id','DESC')
            ->fetchAll();
    }



    /**
     * @param $data
     * @throws \Dibi\Exception
     */
    public function createMessage($data)
    {
        $this->db->insert(self::TABLE_NAME,$data)
            ->execute();
    }

    /**
     * @param $id
     * @throws \Dibi\Exception
     */
    public function deleteMessage($id)
    {
        $this->db->delete(self::TABLE_NAME)
            ->where('id = %s', $id)
            ->execute();
    }

    public function messageExist($id)
    {
        $result = $this->db->select('*')
            ->from(self::TABLE_NAME)
            ->where('id = %s', $id)
            ->fetch();

        if (!$result)
        {
            return false;
        }
        return true;
    }

}