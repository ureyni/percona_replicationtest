we use percona mysql cluster 5.5, 5.6 , 5.7
 
 test table 
 CREATE TABLE test.`ondisk` (`c1` int(11) NOT NULL,`c2` varchar(32) DEFAULT NULL,PRIMARY KEY (`c1`)) ENGINE=InnoDB DEFAULT CHARSET=latin1; 

Environment :  
   Node Configuration : <br>
       OS : CentOS Linux release 7.3.1611 (Core)
       Hardware  : virtual machine with 2 core , 4GB ram  , 10G/s eth speed
       Percona : Percona XtraDB Cluster (GPL), Release rel18, Revision 4a4da7e, WSREP version 29.24, wsrep_29.24
                  version 5.7.20-18-57-log ,  innodb_version 5.7.20-18 , protocol_version 10
       Nodes : 10.145.172.61,62,63
       proxysql : 10.145.172.60
    
    wsrep.conf : 
    wsrep_sync_wait=1
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