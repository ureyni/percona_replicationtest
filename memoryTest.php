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

$mysqli = new mysqli("10.145.172.60", "testuser", "testPass#", "test",3340);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

/* Prepare an insert statement */
$query = "INSERT INTO ondisk (c1,c2) VALUES (?,?)";
$stmt = $mysqli->prepare($query);
$start = microtime(true);
for($index = 1 ; $index<100001;$index++) {
    $stmt->bind_param("ss", $index, $str);
    $str = substr(sha1($index), 0, 32);
    /* Execute the statement */
    $stmt->execute();
}
print "DISK : ".(round(microtime(true) - $start,2)).PHP_EOL;
$stmt->close();

/* Prepare an insert statement */
$query = "INSERT INTO onmemory (c1,c2) VALUES (?,?)";
$stmt2 = $mysqli->prepare($query);
$start = microtime(true);
for($index = 1 ; $index<100001;$index++) {
    $stmt2->bind_param("ss", $index, $str);
    $str = substr(sha1($index), 0, 32);
    /* Execute the statement */
    $stmt2->execute();
}
print "MEMORY : ".(round(microtime(true) - $start,2)).PHP_EOL;
$stmt2->close();

/* Prepare an insert statement */
$query = "INSERT INTO onmyisam (c1,c2) VALUES (?,?)";
$stmt3 = $mysqli->prepare($query);
$start = microtime(true);
for($index = 1 ; $index<100001;$index++) {
    $stmt3->bind_param("ss", $index, $str);
    $str = substr(sha1($index), 0, 32);
    /* Execute the statement */
    $stmt3->execute();
}
print "MYISAM : ".(round(microtime(true) - $start,2)).PHP_EOL;
$stmt3->close();
$mysqli->close();
