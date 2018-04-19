<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 21:52
 */

namespace App\Manager;


class TokenManager
{
    private $db;

    const TABLE_NAME = 'security';

    public function __construct(\Dibi\Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $data
     * @return \Dibi\Result|int
     * @throws \Dibi\Exception
     */
    public function createApiKey($data)
    {
        return $this->db->insert(self::TABLE_NAME,$data)
            ->execute();
    }

    public function tokenExist($data)
    {
        $result = $this->db->select('*')
            ->from(self::TABLE_NAME)
            ->where('api_key = %s', $data)
            ->fetch();

        if (!$result)
        {
            return false;
        }
        return true;
    }
}