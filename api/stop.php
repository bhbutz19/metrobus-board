<?php
// Stop identity lookup; WMATA lists stops via Bus.svc/jStops.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
$stop=(string)($_GET['stop']??'1001453');
if(!preg_match('/^[0-9]{7}$/',$stop)){http_response_code(400);echo json_encode(['error'=>'Invalid stop ID']);exit;}
$key=trim((string)(getenv('WMATA_API_KEY')?:''));
if(!$key){$path=dirname(__DIR__,3).'/.wmata_bus_key';if(is_readable($path))$key=trim((string)file_get_contents($path));}
if(!$key||!function_exists('curl_init')){http_response_code(503);echo json_encode(['error'=>'Stop lookup unavailable']);exit;}
$curl=curl_init('https://api.wmata.com/Bus.svc/json/jStops');
curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>18,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['api_key: '.$key,'Accept: application/json']]);
$body=curl_exec($curl);$code=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);
if($body===false||$code!==200){http_response_code(502);echo json_encode(['error'=>'Stop lookup failed']);exit;}
$data=json_decode($body,true);
foreach(($data['Stops']??[]) as $item){
 if((string)($item['StopID']??'')===$stop){
  echo json_encode(['StopID'=>$stop,'Name'=>(string)($item['Name']??''),'Routes'=>$item['Routes']??[]],JSON_UNESCAPED_SLASHES);exit;
 }
}
http_response_code(404);echo json_encode(['error'=>'Stop not found']);
