<?php
            $ch = curl_init();
            $post['key']='ZX13fHxpeDA6fTwtYiotIHQjImEzKjgtM3p_LXMgKDVjITosNSA,';
            $post['userfile'] = "@/var/www/test.png";
            $post['resourcetype'] = 1;
            $post['archive'] = 0;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/r2000/plugins/api_upload/index.php?');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "API Client"); 
            $responseBody = curl_exec($ch);
            $responseInfo	= curl_getinfo($ch);
            curl_close($ch);

echo $responseBody;
?>
