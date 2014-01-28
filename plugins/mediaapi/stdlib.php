<?php
function mediaapi_is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}

function mediaapi_session_start()
{
    if (mediaapi_is_session_started() === FALSE) {
        session_start();
    }
}

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
        if ($val != "") {
            $update .= "{$key}='{$val}', ";
        }
    }
    $update = rtrim($update, ", ");
    sql_query("INSERT INTO mediaapi_derivatives SET " . $update . "
               ON DUPLICATE KEY UPDATE " . $update);
}

function mediaapi_generate_derivative_metadata($ref, $dir_res = -1)
{
    global $storagedir;

    $file_data     = ($dir_res !== -1) ? get_alternative_file($ref, $dir_res) : null;
    $res_data      = get_resource_data($ref);
    $dir_file_path = get_resource_path($ref, true, "", false, $res_data['file_extension'], -1, 1, false, "", $dir_res);

    $filename_rootpath = ltrim(str_replace($storagedir, '', $dir_file_path), '/');
    $filename  = substr($filename_rootpath, (strrpos($filename_rootpath, '/') + 1));
    $extension = substr($filename, (strrpos($filename, '.') + 1));

    $return = array();

    if (!empty($file_data['name'])) {
        $return['short_name'] = str_replace(".{$file_data['file_extension']}", '', $file_data['name']);
    }

    $return['prefix']          = $res_data['file_extension'];
    $return['file_path']       = rtrim(str_replace($filename, '', $filename_rootpath), '/');
    $return['file_name']       = str_replace(".{$extension}", '', $filename);
    $return['file_extension']  = $extension;
    $return['use_extension']   = ($extension === 'mp4') ? 'y' : 'n';
    $return['is_downloadable'] = 'y';
    $return['is_streamable']   = in_array($extension, array('mp4', 'mp3')) ? 'y' : 'n';

    return $return;
}

/**
 * Gather derivative data.
 * Checks for php globals first then defaults to $data if it exists
 *
 * @param  array $data
 * @return array
 */
function mediaapi_collect_derivative_data(array $data = null)
{
    $derivative = array();
    $derivative['short_name']      = getvalescaped("short_name", (isset($data['short_name']) ? $data['short_name'] : ""));
    $derivative['prefix']          = getvalescaped("prefix", (isset($data['prefix']) ? $data['prefix'] : ""));
    $derivative['file_path']       = getvalescaped("file_path", (isset($data['file_path']) ? $data['file_path'] : ""));
    $derivative['file_name']       = getvalescaped("file_name", (isset($data['file_name']) ? $data['file_name'] : ""));
    $derivative['file_extension']  = getvalescaped("file_extension", (isset($data['file_extension']) ? $data['file_extension'] : ""));
    $derivative['use_extension']   = getvalescaped("use_extension", (isset($data['use_extension']) ? $data['use_extension'] : ""));
    $derivative['is_downloadable'] = getvalescaped("is_downloadable", (isset($data['is_downloadable']) ? $data['is_downloadable'] : ""));
    $derivative['is_streamable']   = getvalescaped("is_streamable", (isset($data['is_streamable']) ? $data['is_streamable'] : ""));
    $derivative['is_primary']      = getvalescaped("is_primary", (isset($data['is_primary']) ? $data['is_primary'] : ""));

    return $derivative;
}
