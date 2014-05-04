<?php 

function debug($data) {
    echo "<script>\r\n//<![CDATA[\r\nif(!console){var console={log:function(){}}}";
    $output = explode("\n", print_r($data, true));
    foreach ($output as $line) {
        if (trim($line)) {
            $line = addslashes($line);
            echo "console.log(\"{$line}\");";
        }
    }
    echo "\r\n//]]>\r\n</script>";
}

