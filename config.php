<?php

$conn = mysqli_connect('localhost', 'root', '', 'hospital');

/**
 * Global guard: block negative numeric input for money/quantity/id/date-count fields.
 * This runs for both GET and POST requests on pages that include config.php.
 */
function hms_has_negative_numeric_value($data, $key = '')
{
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (hms_has_negative_numeric_value($v, (string)$k)) {
                return true;
            }
        }
        return false;
    }

    if (!is_scalar($data)) {
        return false;
    }

    $value = trim((string)$data);
    $field = strtolower($key);

    // Validate only likely numeric business fields, not all inputs.
    $is_numeric_field = (bool)preg_match('/(price|amount|cash|qty|quantity|fee|fees|total|count|id|age|stock)/', $field);
    $is_numeric_value = preg_match('/^-?\d+(\.\d+)?$/', $value);

    if ($is_numeric_field && $is_numeric_value && (float)$value < 0) {
        return true;
    }

    return false;
}

function hms_has_invalid_phone_value($data, $key = '')
{
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (hms_has_invalid_phone_value($v, (string)$k)) {
                return true;
            }
        }
        return false;
    }

    if (!is_scalar($data)) {
        return false;
    }

    $field = strtolower($key);
    $value = trim((string)$data);

    // Treat these as phone fields.
    $is_phone_field = (bool)preg_match('/(phone|mobile|number)/', $field);
    if (!$is_phone_field) {
        return false;
    }

    // Ignore empty values here; required checks are handled by form/business rules.
    if ($value === '') {
        return false;
    }

    // Must be exactly 10 digits.
    return !preg_match('/^\d{10}$/', $value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $has_negative_post = hms_has_negative_numeric_value($_POST);
    $has_negative_get = hms_has_negative_numeric_value($_GET);
    $has_invalid_phone_post = hms_has_invalid_phone_value($_POST);
    $has_invalid_phone_get = hms_has_invalid_phone_value($_GET);

    if ($has_negative_post || $has_negative_get) {
        echo "<script>alert('Negative values are not allowed. Please enter valid non-negative data.'); history.back();</script>";
        exit();
    }

    if ($has_invalid_phone_post || $has_invalid_phone_get) {
        echo "<script>alert('Phone number must be exactly 10 digits.'); history.back();</script>";
        exit();
    }
}
?>
