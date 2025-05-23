<?php

namespace App\Services;

use App\Repositories\LabRepositoryInterface;

class LabService
{
    protected $labRepository;

    public function __construct(LabRepositoryInterface $labRepository)
    {
        $this->labRepository = $labRepository;
    }

    public function getAllUsers()
    {
        return $this->labRepository->all();
    }

    public function getUserById($id)
    {
        return $this->labRepository->find($id);
    }

    public function createUser(array $data)
    {
        return $this->labRepository->create($data);
    }

    public function updateUser($id, array $data)
    {
        return $this->labRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->labRepository->delete($id);
    }
}
