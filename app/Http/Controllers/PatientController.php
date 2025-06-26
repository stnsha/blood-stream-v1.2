<?php

namespace App\Http\Controllers;

use App\Http\Requests\InnoquestResultRequest;
use App\Http\Requests\StorePatientResultRequest;
use App\Models\DeliveryFile;
use App\Models\DeliveryFileHistory;
use App\Models\DoctorCode;
use App\Models\Panel;
use App\Models\PanelComment;
use App\Models\PanelItem;
use App\Models\PanelMetadata;
use App\Models\Patient;
use App\Models\ReferenceRange;
use App\Models\TestResult;
use App\Models\TestResultItem;
use App\Models\TestResultReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function labResults(StorePatientResultRequest $request)
    {
        $validated = $request->validated();
        try {
            if ($validated) {
                DB::beginTransaction();
                $user = Auth::guard('lab')->user();
                $lab_id = $user->lab_id;

                if (filled($validated['sending_facility']) && $validated['sending_facility'] === 'INN') {
                    $sending_facility = $validated['sending_facility'];
                    $batch_id = $validated['batch_id'] ?? null;
                    $is_completed = true;
                } else {
                    $sending_facility = $user->lab->code . 'API';
                    $batch_id = now()->format('YmdHis') . $user->lab->code;
                    $is_completed = false;
                }

                $patient_icno = $validated['patient_icno'];
                $ic_type = $validated['ic_type'];
                $patient_age = $validated['patient_age'];
                $patient_gender = $validated['patient_gender'];
                $reference_id = $validated['reference_id'];
                $bill_code = $validated['bill_code'];
                $lab_no = $validated['lab_no'];
                $doctor_code = $validated['doctor_code'];
                $collected_date = $validated['collected_date'];
                $received_date = $validated['received_date'];
                $reported_date = $validated['reported_date'];
                $results = $validated['results'];

                $doctor_code = DoctorCode::firstOrCreate(
                    [

                        'lab_id' => $lab_id,
                        'name' => $doctor_code,
                    ],
                    [
                        'code' => $doctor_code
                    ]
                );

                $doctor_code_id = $doctor_code->id;

                $patient = Patient::firstOrCreate(
                    [
                        'icno' => $patient_icno
                    ],
                    [
                        'ic_type' => $ic_type,
                        'name' => null,
                        'dob' => null,
                        'age' => $patient_age,
                        'gender' => $patient_gender
                    ]
                );

                $patient_id = $patient->id;

                $test_result = TestResult::firstOrCreate([
                    'doctor_code_id' => $doctor_code_id,
                    'patient_id' => $patient_id,
                    'ref_id' => $reference_id,
                    'bill_code' => $bill_code,
                    'lab_no' => $lab_no,
                    'collected_date' => $collected_date,
                    'received_date' => $received_date,
                    'reported_date' => $reported_date,
                    'is_completed' => $is_completed
                ]);

                $test_result_id = $test_result->id;

                foreach ($results as $key => $item) {
                    $panel_name = $key;
                    $panel_code = $item['panel_code'];
                    $panel_sequence = $item['panel_sequence'];
                    $overall_notes = $item['overall_notes'];

                    $panel = Panel::firstOrCreate(
                        [
                            'lab_id' => $lab_id,
                            'name' => $panel_name,
                        ],
                        [
                            'code' => $panel_code,
                            'sequence' => $panel_sequence,
                            'overall_notes' => $overall_notes
                        ]
                    );
                    $panel_id = $panel->id;

                    if (filled($item['tests'])) {
                        foreach ($item['tests'] as $index => $test) {
                            $panel_item = PanelItem::firstOrCreate(
                                [
                                    'panel_id' => $panel_id,
                                    'name' => $test['test_name'],
                                ],
                                [
                                    'decimal_point' => $test['decimal_point'],
                                    'unit' => $test['unit'],
                                    'item_sequence' => $test['item_sequence']
                                ]
                            );
                            $panel_item_id = $panel_item->id;

                            if (filled($test['ref_range'])) {
                                $ref_range = ReferenceRange::firstOrCreate(
                                    [
                                        'value' => $test['ref_range'],
                                        'panel_item_id' => $panel_item_id,
                                    ]
                                );

                                $ref_range_id = $ref_range->id;
                            }

                            TestResultItem::firstOrCreate(
                                [

                                    'test_result_id' => $test_result_id,
                                    'reference_range_id' => $ref_range_id,
                                    'value' => $test['result_value']
                                ],
                                [
                                    'flag' => $test['result_flag'],
                                    'test_notes' => $test['test_note'],
                                    'status' => 'C',
                                    'is_completed' => true
                                ]
                            );
                        }
                    }
                }

                $deliveryFile = DeliveryFile::firstOrCreate(
                    [

                        'test_result_id' => $test_result_id,
                    ],
                    [
                        'lab_id' => $lab_id,
                        'sending_facility' => $sending_facility,
                        'batch_id' => $batch_id,
                        'status' => DeliveryFile::compl,
                    ]
                );

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Blood test results successfully submitted.',
                    'result_id' => $test_result_id
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            /** @var \Illuminate\Http\Request $request */
            Log::error('Failed to save data', [
                'exception' => $e->getMessage(),
                'data' => json_encode($request->all()),
            ]);

            if (isset($deliveryFile)) {
                DeliveryFileHistory::create([
                    'delivery_file_id' => $deliveryFile->id,
                    'message' => $e->getMessage(),
                    'err_code' => '500',
                ]);

                $deliveryFile->update(['status' => DeliveryFile::fld]);
            }

            return response()->json([
                'error' => 'Failed to save data',
            ], 500);
        }
    }

    /**
     * Innoquest custom API
     */
    public function panelResults(InnoquestResultRequest $request)
    {
        $validated = $request->validated();
        try {
            if ($validated) {
                DB::beginTransaction();
                $user = Auth::guard('lab')->user();
                $lab_id = $user->lab_id;

                if (filled($validated['SendingFacility'])) {
                    $sending_facility = $validated['SendingFacility'];
                    $batch_id = $validated['MessageControlID'] ?? null;
                }

                //refid
                $reference_id = $validated['patient']['PatientExternalID'] ?? null;

                $icInfo = checkIcno($validated['patient']['AlternatePatientID']);
                $icno = $icInfo['icno'];
                $ic_type = $icInfo['type'];
                $patient_gender = $icInfo['gender'];
                $age = $icInfo['age'];
                $patient_name = filled($validated['patient']['PatientLastName']) ? $validated['patient']['PatientLastName'] : null;
                $patient_dob = filled($validated['patient']['PatientDOB']) ? $validated['patient']['PatientDOB'] : null;
                $gender = filled($validated['patient']['PatientGender']) ? $validated['patient']['PatientGender'] : $patient_gender;

                $patient = Patient::firstOrCreate(
                    [

                        'icno' => $icno,
                    ],
                    [
                        'ic_type' => $ic_type,
                        'name' => $patient_name,
                        'dob' => $patient_dob,
                        'age' => $age,
                        'gender' => $gender
                    ]
                );

                $patient_id = $patient->id;

                foreach ($validated['Orders'] as $key => $od) {
                    if (filled($od['Observations'])) {
                        foreach ($od['Observations'] as $key => $obv) {

                            $doctor_name = $obv['OrderingProvider']['Name'];
                            $doctor_code = $obv['OrderingProvider']['Code'];

                            $doctor_code = DoctorCode::firstOrCreate(
                                [

                                    'lab_id' => $lab_id,
                                    'code' => $doctor_code
                                ],
                                [
                                    'name' => $doctor_name,
                                ]
                            );

                            $doctor_code_id = $doctor_code->id;

                            $lab_no = $obv['FillerOrderNumber'];
                            if (is_null($reference_id) && filled($obv['PlacerOrderNumber'])) $reference_id = $obv['PlacerOrderNumber'];
                            $bill_code = $obv['PackageCode'];
                            $collected_date = $obv['StartDateTime'];
                            $received_date = $obv['EndDateTime'];
                            $reported_date = $obv['RequestedDateTime'];

                            $test_result = TestResult::firstOrCreate([
                                'doctor_code_id' => $doctor_code_id,
                                'patient_id' => $patient_id,
                                'ref_id' => $reference_id,
                                'bill_code' => $bill_code,
                                'lab_no' => $lab_no,
                                // 'collected_date' => $collected_date,
                                // 'received_date' => $received_date,
                                // 'reported_date' => $reported_date,
                                'collected_date' => null,
                                'received_date' => null,
                                'reported_date' => null,
                                'is_completed' => false
                            ]);

                            $test_result_id = $test_result->id;

                            $panel_code = $obv['ProcedureCode'];
                            $panel_name = $obv['ProcedureDescription'];
                            $panel_notes = filled($obv['ClinicalInformation']) ? $obv['ClinicalInformation'] : null; //overall notes

                            $panel = Panel::firstOrCreate(
                                [
                                    'lab_id' => $lab_id,
                                    'name' => $panel_name,
                                ],
                                [
                                    'code' => $panel_code,
                                    'sequence' => null,
                                    'overall_notes' => $panel_notes
                                ]
                            );
                            $panel_id = $panel->id;

                            $is_completed_result = (filled($obv['ResultStatus']) && $obv['ResultStatus'] == 'F')  ? true : false;

                            $results = $obv['Results'];
                            if (filled($results)) {
                                foreach ($results as $key => $res) {
                                    $ordinal_id = $res['ID'];
                                    $type = $res['Type'];
                                    $identifier = $res['Identifier'];

                                    $result_value = filled($res['Value']) ? $res['Value'] : null;
                                    $unit = filled($res['Units']) ? $res['Units'] : null;
                                    $result_flag = filled($res['Flags']) ? $res['Flags'] : null;
                                    $result_status = filled($res['Status']) ? $res['Status'] : null;

                                    if (filled($res['Text']) && $res['Text'] != 'COMMENT') {
                                        $panel_item = PanelItem::firstOrCreate(
                                            [
                                                'panel_id' => $panel_id,
                                                'name' => $res['Text'],
                                            ],
                                            [
                                                'decimal_point' => null,
                                                'unit' => $unit,
                                                'item_sequence' => null
                                            ]
                                        );
                                        $panel_item_id = $panel_item->id;

                                        if (filled($res['ReferenceRange'])) {
                                            $ref_range = ReferenceRange::firstOrCreate(
                                                [
                                                    'value' => $res['ReferenceRange'],
                                                    'panel_item_id' => $panel_item_id,
                                                ]
                                            );

                                            $ref_range_id = $ref_range->id;
                                        }

                                        PanelMetadata::firstOrCreate(
                                            [

                                                'panel_item_id' => $panel_item_id,
                                                'ordinal_id' => $ordinal_id,
                                            ],
                                            [
                                                'type' => $type,
                                                'identifier' => $identifier
                                            ]
                                        );

                                        TestResultItem::firstOrCreate(
                                            [

                                                'test_result_id' => $test_result_id,
                                                'reference_range_id' => $ref_range_id,
                                                'value' => $result_value
                                            ],
                                            [
                                                'flag' => $result_flag,
                                                'test_notes' => null,
                                                'status' => $result_status,
                                                'is_completed' => $is_completed_result
                                            ]
                                        );
                                    }

                                    if ($res['Text'] == 'COMMENT') {
                                        PanelComment::firstOrCreate(
                                            [
                                                'panel_item_id' => $panel_item_id,
                                                'identifier' => $identifier
                                            ],
                                            [
                                                'comment' => $result_value,
                                                'sequence' => null
                                            ]
                                        );
                                    }

                                    if ($res['Identifier'] == 'REPORT') {
                                        TestResultReport::firstOrCreate([
                                            'test_result_id' => $test_result_id,
                                            'panel_id' => $panel_id,
                                            'text' => $result_value,
                                            'is_completed' => $is_completed_result,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }


                $deliveryFile = DeliveryFile::firstOrCreate(
                    [

                        'test_result_id' => $test_result_id,
                    ],
                    [
                        'lab_id' => $lab_id,
                        'sending_facility' => $sending_facility,
                        'batch_id' => $batch_id,
                        'json_content' => json_encode($validated),
                        'status' => DeliveryFile::compl,
                    ]
                );

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Blood test results successfully submitted.',
                    'result_id' => $test_result_id
                ]);
            }
        } catch (\Throwable $e) {

            DB::rollBack();
            /** @var \Illuminate\Http\Request $request */
            Log::error('Failed to save data', [
                'exception' => $e->getMessage(),
                'data' => json_encode($request->all()),
            ]);

            if (isset($deliveryFile)) {
                DeliveryFileHistory::create([
                    'delivery_file_id' => $deliveryFile->id,
                    'message' => $e->getMessage(),
                    'err_code' => '500',
                ]);

                $deliveryFile->update(['status' => DeliveryFile::fld]);
            }
            return response()->json([
                'error' => 'Failed to save data',
            ], 500);
        }
    }
}
