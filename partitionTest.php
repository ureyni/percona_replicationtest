<?php
/**
 * Created by PhpStorm.
 * User: hucak
 * Date: 1/17/2018
 * Time: 6:34 PM
 *
  there is  two tables , first table engine is innodb , second table on memory so memory engine.
 *
CREATE TABLE `ondisk` (
`c1` int(11) NOT NULL,
`c2` varchar(32) DEFAULT NULL,
PRIMARY KEY (`c1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1


 CREATE TABLE `onmemory` (
`c1` int(11) NOT NULL,
`c2` varchar(32) DEFAULT NULL,
PRIMARY KEY (`c1`)
) ENGINE=Memory DEFAULT CHARSET=latin1

CREATE TABLE `onmyisam` (
`c1` int(11) NOT NULL,
`c2` varchar(32) DEFAULT NULL,
PRIMARY KEY (`c1`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1

 */

$mysqli = new mysqli("10.145.172.20", "testuser", "testPass#", "test");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

/* Prepare an insert statement */
$query = "INSERT INTO KEP_PAKET_ILETI (GUID,MESSAGE_ID,KAYIT_TARIHI) VALUES (?,?,?)";
$stmt = $mysqli->prepare($query);
for ($month = 1 ; $month<12;$month++ ) {
    $start = microtime(true);
    for ($index = 1; $index < 10001 ; $index++) {
        $stmt->bind_param("sss", $index, $str, $timestamp);
        $str = substr(sha1($index), 0, 32);
        $int = rand(strtotime("2018-$month-01"), strtotime("2018-" . ($month + 1) . "-01"));
        $timestamp = gmdate("Y-m-d H:i:s", $int);
        /* Execute the statement */
        $stmt->execute();
    }
    print "2018-" . $month . "-01 : " . (round(microtime(true) - $start, 2)) . PHP_EOL;
}
$stmt->close();
$mysqli->close();
