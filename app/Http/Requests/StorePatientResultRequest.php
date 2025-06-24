<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $patient_icno
 * @property string $ic_type
 * @property string $reference_id
 * @property string $lab_no
 * @property string $bill_code
 * @property string $doctor_code
 * @property string $received_date
 * @property string $reported_date
 * @property string $collected_date
 * @property array $results
 */
class StorePatientResultRequest extends FormRequest
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
            'patient_icno' => 'required|string',
            'ic_type' => 'in:NRIC,PP',
            'patient_age' => 'nullable|integer',
            'patient_gender' => 'nullable|in:F,M',
            'reference_id' => 'nullable|string',
            'lab_no' => 'required|string',
            'bill_code' => 'nullable|string',
            'doctor_code' => 'required|string',
            'received_date' => 'required|date_format:Y-m-d H:i:s',
            'reported_date' => 'required|date_format:Y-m-d H:i:s',
            'collected_date' => 'required|date_format:Y-m-d H:i:s',

            'results' => 'required|array',
            'results.*' => 'required|array',
            'results.*.panel_code' => 'nullable|string',
            'results.*.panel_sequence' => 'nullable|integer|min:1',
            'results.*.overall_notes' => 'nullable|string',
            'results.*.tests' => 'required|array|min:1',

            'results.*.tests.*.test_name' => 'required|string',
            'results.*.tests.*.result_value' => 'nullable|string',
            'results.*.tests.*.decimal_point' => 'nullable|string',
            'results.*.tests.*.result_flag' => 'nullable|string',
            'results.*.tests.*.unit' => 'nullable|string',
            'results.*.tests.*.ref_range' => 'nullable|string',
            'results.*.tests.*.test_note' => 'nullable|string',
            'results.*.tests.*.item_sequence' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method cleans up the input data before validation:
     * - Removes non-numeric characters from patient_icno
     * - Casts panel_sequence and item_sequence to integers
     * - Trims whitespace from string fields in test items
     * - Skips panels or tests if improperly structured
     */
    protected function prepareForValidation(): void
    {
        $cleanedResults = [];

        $icInfo = $this->checkIcno($this->patient_icno);

        if (is_array($this->results)) {
            foreach ($this->results as $panel => $data) {
                $panelCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $panel), 0, 3));
                if (!isset($data['tests']) || !is_array($data['tests'])) {
                    continue;
                }

                $data['tests'] = array_map(function ($test) {
                    return [
                        'test_name' => trim($test['test_name'] ?? null),
                        'result_value' => trim((string) ($test['result_value'] ?? null)),
                        'decimal_point' => trim((string) ($test['decimal_point'] ?? null)),
                        'result_flag' => $test['result_flag'] ?? null,
                        'unit' => trim($test['unit'] ?? null),
                        'ref_range' => trim($test['ref_range'] ?? null),
                        'test_note' => $test['test_note'] ?? null,
                        'item_sequence' => (int) ($test['item_sequence'] ?? null),
                    ];
                }, $data['tests']);

                $cleanedResults[$panel] = [
                    'panel_code' => $panelCode,
                    'panel_sequence' => (int) ($data['panel_sequence'] ?? null),
                    'overall_notes' => ($data['overall_notes'] ?? null),
                    'tests' => $data['tests'],
                ];
            }
        }

        /** @var \Illuminate\Http\Request $this */
        $this->merge([
            'patient_icno' => $icInfo['icno'],
            'ic_type' => $icInfo['type'],
            'patient_gender' => $icInfo['gender'],
            'patient_age' => $icInfo['age'],
            'received_date' => $this->convertToDateTimeString($this->received_date),
            'reported_date' => $this->convertToDateTimeString($this->reported_date),
            'collected_date' => $this->convertToDateTimeString($this->collected_date),
            'results' => $cleanedResults,
        ]);
    }

    private function checkIcno($icno): array
    {
        $type = Patient::passport;
        $gender = null;
        $age = null;

        if (strlen($icno) === 12) {
            $year = (int) substr($icno, 0, 2);
            $month = (int) substr($icno, 2, 2);
            $day = (int) substr($icno, 4, 2);
            $lastDigit = (int) substr($icno, -1);

            $currentYear = (int) date('Y');
            $fullYear = $year > ($currentYear % 100) ? 1900 + $year : 2000 + $year;

            if (checkdate($month, $day, $fullYear)) {
                $type = Patient::nric;

                $gender = $lastDigit % 2 === 0 ? Patient::female : Patient::male;

                $age = $currentYear - $fullYear;
            }
        }

        return [
            'icno' => $icno,
            'type' => $type,
            'gender' => $gender,
            'age' => $age,
        ];
    }


    private function convertToDateTimeString($date)
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '0000-00-00 00:00:00';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
