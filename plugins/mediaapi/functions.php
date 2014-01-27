<?php
/**
 * Retrieve the resource derivative data
 * @param int $ref
 * @return array
 */
function mediaapi_get_derivative_resources($ref)
{
    return sql_query("SELECT * FROM mediaapi_derivatives WHERE alt_file_id='" . $ref . "'");
}

function mediaapi_upsert_derivative_resources($ref, array $data)
{
    $update = "alt_file_id='{$ref}', ";
    foreach ($data as $key => $val) {
        $update .= "{$key}='{$val}', ";
    }
    sql_query("INSERT INTO mediaapi_derivatives SET " . rtrim($update, ", ") . "
               ON DUPLICATE KEY UPDATE " . rtrim($update, ", "));
}
