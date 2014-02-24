<?php
function HookMediaapiSearchResultsbottomtoolbar()
{
    global $mediaapi_publish_reports;

    $canbepublished = getval('mediaapi_canbepublished', null);
    if ($canbepublished === 'Y') {
        if (isset($mediaapi_publish_reports['published']) && $mediaapi_publish_reports['published'] > 0) {
            echo "No. of published resource: {$mediaapi_publish_reports['published']}<br />";
        }
        if (isset($mediaapi_publish_reports['not-published']) && $mediaapi_publish_reports['not-published'] > 0) {
            echo "No. of unpublished resource resulting errors: {$mediaapi_publish_reports['not-published']}";
        }

        echo '
        <form action="" method="POST">
            <input type="submit" name="submit" value="Send to mediaapi">
        </form>
        ';
    }
}

function HookMediaapiSearchProcess_search_results($results)
{
    global $mediaapi_publish_reports;

    $mediaapi_publish_reports = array(
        'published'     => 0,
        'not-published' => 0,
    );

    if (getval('submit', null) === "Send to mediaapi") {
        $publish_results = array();
        $remove = array();
        foreach ($results as $key => $resource) {
            $resource_data = mediaapi_get_filtered_resource_for_publish($resource['ref']);
            $publish_results = mediaapi_publish_resource($resource['ref'], $resource_data);

            // remove from the results the successfully synced once
            if (!empty($publish_results['uuid'])) {
                unset($results[$key]);
                $mediaapi_publish_reports['published']++;
            } else {
                $mediaapi_publish_reports['not-published']++;
            }
        }

        // just resort using foreach
        $resort = array();
        foreach ($results as $result) {
            $resort[] = $result;
        }
        $results = $resort;
        unset($resort);
    }

    return $results;
}
