<?php

use Carbon\Carbon;

if (!function_exists('generate_lab_code')) {
    function generate_lab_code($labName)
    {
        return strtoupper(substr($labName, 0, 3));
    }
}

if (!function_exists('generate_lab_path')) {
    function generate_lab_path($labName)
    {
        $firstWord = strtok($labName, ' ');
        return ucfirst(strtolower($firstWord));
    }
}

if (!function_exists('get_email_abbrv')) {
    function get_email_abbrv($email)
    {
        $localPart = strstr($email, '@', true);
        return strtoupper(substr($localPart, 0, 3));
    }
}

if (!function_exists('calculate_age')) {
    function calculate_age($icno)
    {
        $year = substr($icno, 0, 2);
        $currentYear = Carbon::now()->year;
        $currentShortYear = Carbon::now()->format('y');

        $prefix = ($year > $currentShortYear) ? '19' : '20';
        $fullYear = $prefix . $year;

        $age = $currentYear - $fullYear;

        $lastDigit = substr($icno, -1);
        $gender = ($lastDigit % 2 == 0) ? 'Female' : 'Male';

        return [
            'age' => $age,
            'gender' => $gender
        ];
    }
}
