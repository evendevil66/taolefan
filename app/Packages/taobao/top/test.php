<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 
    $c = new TopClient;
    $c->appkey = '***********';
    $c->secretKey = '*****************';

    $req = new HttpdnsGetRequest;

    $req->putOtherTextParam("name","test");
    $req->putOtherTextParam("value",0);

    var_dump($c->execute($req));
?>