<?php

/**
 * Created by PhpStorm.
 * User: hucak
 * Date: 1/27/2018
 * Time: 5:54 PM
 
 * CREATE TABLE test.`ondisk` (`c1` int(11) NOT NULL,`c2` varchar(32) DEFAULT NULL,PRIMARY KEY (`c1`)) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
 *
 * test of percona cluster.
 *  Node Configuration : 
 *      OS : CentOS Linux release 7.3.1611 (Core)
 *      Hardware  : virtual machine with 2 core , 4GB ram  , 10G/s eth speed
 *      Percona : Percona XtraDB Cluster (GPL), Release rel18, Revision 4a4da7e, WSREP version 29.24, wsrep_29.24
 *                 version 5.7.20-18-57-log ,  innodb_version 5.7.20-18 , protocol_version 10
 *      Nodes : 10.145.172.61,62,63
 *      proxysql : 10.145.172.60
 *      wsrep.conf : 
 *  wsrep_sync_wait=1
    wsrep_causal_reads=ON

    sql_mode=ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION

    max_connections=10000
    open_files_limit=32768
    wsrep_provider_options="pc.ignore_quorum=true;pc.ignore_sb=true;gcache.size=2G; gcache.page_size=1G;gcs.fc_limit = 256; gcs.fc_factor = 0.99;"
    wsrep_slave_threads=32

    [sst]
    inno-apply-opts="--use-memory=8G"
    compressor="pigz"
    decompressor="pigz -d"

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
