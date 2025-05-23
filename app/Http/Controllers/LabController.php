<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabRequest;
use App\Services\LabService;

class LabController extends Controller
{
    protected $labService;

    public function __construct(LabService $labService)
    {
        $this->labService = $labService;
    }

    public function index()
    {
        $users = $this->labService->getAllUsers();
        return $users;
    }

    public function show($id)
    {
        $user = $this->labService->getUserById($id);
        return $user;
    }

    public function store(LabRequest $request)
    {
        $data = $request->validated();
        $this->labService->createUser($data);

        return response()->json(['message' => 'Lab successfully created.'], 200);
    }

    public function update(LabRequest $request, $id)
    {
        $data = $request->all();
        $this->labService->updateUser($id, $data);
        return 200;
    }

    public function destroy($id)
    {
        $this->labService->deleteUser($id);
        return 200;
    }
}
