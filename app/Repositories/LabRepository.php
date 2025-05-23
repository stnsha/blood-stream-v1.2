<?php

namespace App\Repositories;

use App\Models\Lab;

class LabRepository
{
    public function all()
    {
        return Lab::all();
    }

    public function find($id)
    {
        return Lab::find($id);
    }

    public function create(array $data)
    {
        return Lab::create($data);
    }

    public function update($id, array $data)
    {
        $Lab = Lab::find($id);
        $Lab->update($data);
        return $Lab;
    }

    public function delete($id)
    {
        return Lab::destroy($id);
    }
}
