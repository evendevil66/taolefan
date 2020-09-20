<?php
include "TopSdk.php";
date_default_timezone_set('Asia/Shanghai');

$c = new DingTalkClient(DingTalkConstant::$CALL_TYPE_OAPI, DingTalkConstant::$METHOD_POST , DingTalkConstant::$FORMAT_JSON);
$req = new OapiMediaUploadRequest;
$req->setType("image");
$req->setMedia(array('type'=>'application/octet-stream','filename'=>'image.png', 'content' => file_get_contents('/Users/test/image.png')));
$resp=$c->execute($req, "******","https://oapi.dingtalk.com/media/upload");
var_dump($resp)

?>