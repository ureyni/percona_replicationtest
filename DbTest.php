<?php

/**
 * Created by .
 * User: hucak
 * Date: 1/27/2018
 * Time: 5:54 PM
 */

/*
 * if connection close of real percona node , get reconnect on proxysql
 */
function getMysqlConn() {
    print PHP_EOL."Mysql Proxy Reconnection";
    $mysqli = new mysqli("10.145.172.60", "testuser", "testPass#", "test", 3306);
    return $mysqli;
}

$mysqlidb1 = new mysqli("10.145.172.61", "testuser", "testPass#", "test", 3306);
if (mysqli_connect_errno()) {
    $mysqlidb1 = new mysqli("10.145.172.60", "testuser", "testPass#", "test", 3306);
}
$mysqlidb2 = new mysqli("10.145.172.62", "testuser", "testPass#", "test", 3306);
if (mysqli_connect_errno()) {
    $mysqlidb2 = new mysqli("10.145.172.60", "testuser", "testPass#", "test", 3306);
}
$mysqlidb3 = new mysqli("10.145.172.63", "testuser", "testPass#", "test", 3306);
if (mysqli_connect_errno()) {
    $mysqlidb3 = new mysqli("10.145.172.60", "testuser", "testPass#", "test", 3306);
}

//$mysqlidb1->autocommit(false);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    // exit();
}


$insertSQL = "truncate table test.ondisk";
$stmt = $mysqlidb1->prepare($insertSQL);
if (!$stmt->execute())
    print PHP_EOL . "Truncate : " . $mysqlidb1->error;
sleep(1);
/* Prepare an insert statement */
$insertSQL = "INSERT INTO test.ondisk (c1,c2) VALUES (?,?)";
$stmt = $mysqlidb1->prepare($insertSQL);
$start = microtime(true);
for ($index = 1; $index < 10000; $index++) {
    if (!$mysqlidb1->ping()) {
        $mysqlidb1 = getMysqlConn();
        $stmt = $mysqlidb1->prepare($insertSQL);
    }
    $mysqlidb1->begin_transaction();
    $stmt->bind_param("ss", $index, $str);
    $str = substr(sha1($index), 0, 32);
    /* Execute the statement */
    if (!$stmt->execute())
        print PHP_EOL . "INSERT HATASI : " . $mysqlidb1->error;
    $mysqlidb1->commit();
    $query = "select * from test.ondisk where c1=$index";
   // usleep(5000);
    if (!$mysqlidb2->ping())
        $mysqlidb2 = getMysqlConn();

    if ($result = $mysqlidb2->query($query)) {
        $data = $result->fetch_object();
        if (empty($data->c1)) {
            print PHP_EOL . " ERROR DATA NOT Found $index Query Exec on 3th Node. ";
            try {
                if (!$mysqlidb3->ping())
                    $mysqlidb3 = getMysqlConn();
                $result = $mysqlidb3->query($query);
                if ($data = $result->fetch_object()) {
                    print PHP_EOL . " $index on 3th Node DATA->c2 : " . $data->c2 ?? '';
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        $data = null;
        /* free result set */
        $result->close();
    } else
        print PHP_EOL . "RESULT ERROR $index ->" . $mysqlidb2->error;
    if ($index % 100 == 0) {
        print PHP_EOL . "$index Duraction : " . round(microtime(true) - $start, 2);
    }
}
$mysqlidb1->close();
$mysqlidb2->close();
$mysqlidb3->close();
$end = microtime(true);
print PHP_EOL . " Total Duraction : " . round($end - $start, 2);
