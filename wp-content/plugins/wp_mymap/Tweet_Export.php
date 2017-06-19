<?php

function tweets_to_xls()
{
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}tweets";
    $result = $wpdb->get_results($sql);

    $columnHeader = '';
    $columnHeader = "ID" . "\t" . "Name" . "\t" . "Tweet" . "\t" . "Date" . "\t" . "Tag" . "\t";

    $setData = '';

    while ($rec = mysqli_fetch_row($result)) {
        $rowData = '';
        foreach ($rec as $value) {
            $value = '"' . $value . '"' . "\t";
            $rowData .= $value;
        }
        $setData .= trim($rowData) . "\n";
    }

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=User_Detail_Reoprt.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo ucwords($columnHeader) . "\n" . $setData . "\n";
}



