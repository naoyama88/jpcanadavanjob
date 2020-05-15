<?php

// e.g. php sql_buy.php 20200514 20200515

$startDate = date("Y-m-d", strtotime($argv[1]));
$formatedStartDate = date("n/d", strtotime($startDate));
$endDate = date("Y-m-d", strtotime($argv[2]));
$tableName = $argv[3];

$sql = "SELECT ";
while (strtotime($startDate) <= strtotime($endDate)) {
    $sql .= "count(post_datetime between '$startDate 00:00:00' and '$startDate 23:59:59' or null) as \"$formatedStartDate\",";
    $preDate = $startDate;
    $startDate         = date("Y-m-d", strtotime($preDate . "+1 day"));
    $formatedStartDate = date("n/d", strtotime($preDate . "+1day"));
}
$sql = substr($sql, 0, -1);
$sql .= " ";
$sql .= "FROM $tableName;";

echo $sql;
