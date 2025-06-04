<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Models\DeliveryFile;
use Illuminate\Http\Request;

class ImportController extends Controlle       r
{
    public function import(ImportRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            return response()->json($validated);

            $deliveryFile = DeliveryFile::create([]);
        }
    }
}
