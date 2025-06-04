<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'SendingFacility' => 'required|string',
            'MessageControlID' => 'required|string',

            'patient' => 'required|array',
            'patient.PatientID' => 'nullable|string',
            'patient.PatientExternalID' => 'nullable|string',
            'patient.AlternatePatientID' => 'required|string',
            'patient.PatientLastName' => 'nullable|string',
            'patient.PatientFirstName' => 'nullable|string',
            'patient.PatientMiddleName' => 'nullable|string',
            'patient.PatientDOB' => 'nullable|date_format:Ymd',
            'patient.PatientGender' => 'nullable|string|in:M,F',
            'patient.PatientAddress' => 'nullable|string',
            'patient.PatientNationality' => 'nullable|string',

            'Orders' => 'required|array|min:1',
            'Orders.*.PlacerOrderNumber' => 'nullable|string',
            'Orders.*.FillerOrderNumber' => 'required|string',
            'Orders.*.PlacerGroupNumber' => 'nullable|string',
            'Orders.*.Status' => 'nullable|string',
            'Orders.*.Quantity' => 'nullable|string',
            'Orders.*.TransactionDateTime' => 'nullable|string',
            'Orders.*.Organization' => 'nullable|string',

            'Orders.*.OrderingProvider' => 'required|array',
            'Orders.*.OrderingProvider.Code' => 'required|string',
            'Orders.*.OrderingProvider.Name' => 'required|string',

            'Orders.*.Observations' => 'required|array|min:1',
            'Orders.*.Observations.*.PlacerOrderNumber' => 'nullable|string',
            'Orders.*.Observations.*.FillerOrderNumber' => 'required|string',
            'Orders.*.Observations.*.ProcedureCode' => 'required|string',
            'Orders.*.Observations.*.ProcedureDescription' => 'required|string',
            'Orders.*.Observations.*.PackageCode' => 'nullable|string',
            'Orders.*.Observations.*.Priority' => 'nullable|string',
            'Orders.*.Observations.*.RequestedDateTime' => 'required|date_format:Ymd',
            'Orders.*.Observations.*.StartDateTime' => 'nullable|date_format:YmdHi',
            'Orders.*.Observations.*.EndDateTime' => 'nullable|date_format:YmdHi',
            'Orders.*.Observations.*.ClinicalInformation' => 'nullable|string',
            'Orders.*.Observations.*.SpecimenDateTime' => 'required|date_format:YmdHi',
            'Orders.*.Observations.*.ResultStatus' => 'required|string',
            'Orders.*.Observations.*.ServiceDateTime' => 'required|date_format:Ymd',
            'Orders.*.Observations.*.ResultPriority' => 'required|string',

            'Orders.*.Observations.*.OrderingProvider' => 'required|array',
            'Orders.*.Observations.*.OrderingProvider.Code' => 'required|string',
            'Orders.*.Observations.*.OrderingProvider.Name' => 'required|string',

            'Orders.*.Observations.*.Results' => 'required|array|min:1',
            'Orders.*.Observations.*.Results.*.ID' => 'required|string',
            'Orders.*.Observations.*.Results.*.Type' => 'required|string',
            'Orders.*.Observations.*.Results.*.Identifier' => 'required|string',
            'Orders.*.Observations.*.Results.*.Text' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.CodingSystem' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.Value' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.Units' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.ReferenceRange' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.Flags' => 'nullable|string',
            'Orders.*.Observations.*.Results.*.Status' => 'required|string',
            'Orders.*.Observations.*.Results.*.ObservationDateTime' => 'required|date_format:YmdHi',

            'Attachment' => 'nullable|array',
            'Attachment.*.FileName' => 'required|string',
            'Attachment.*.FileType' => 'required|string|in:pdf,image,PDF,IMAGE',
            'Attachment.*.EncodedBase64' => 'required|string',
        ];
    }
}
