<?php
function validateDate($date) {
    $dateFormat = "Y-m-d"; // Format de la date attendu
    $dateObj = DateTime::createFromFormat($dateFormat, $date);

    return $dateObj && $dateObj->format($dateFormat) === $date;
}

function validateSelect($selectedValue, $validOptions) {
    return in_array($selectedValue, $validOptions);
}