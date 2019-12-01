<?php

namespace App\Repository\Contracts;

interface UserRepositoryInterface
{
    /**
     * Find User by ID
     * @param $id
     * @return mixed
     */
    public function findById($id);

    /**
     * FInd user by login
     * @param $login
     * @return mixed
     */
    public function findByLogin($login);
}