<?php
// WMATA published timetable for C91 westbound at stop 1001453.
// Scheduled times are NOT live predictions. Keep the API key outside webroot.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=120');
header('X-Content-Type-Options: nosniff');
date_default_timezone_set('America/New_York');
function replyError($code,$msg){http_response_code($code);echo json_encode(['error'=>$msg]);exit;}
$key=trim((string)(getenv('WMATA_API_KEY')?:''));
if($key===''){ $path=dirname(__DIR__,3).'/.wmata_bus_key';if(is_readable($path))$key=trim((string)file_get_contents($path));}
if($key==='')replyError(503,'WMATA key not configured');
if(!function_exists('curl_init'))replyError(503,'PHP cURL not available');
$today=new DateTimeImmutable('now',new DateTimeZone('America/New_York'));
$date=$today->format('Y-m-d');
$url='https://api.wmata.com/Bus.svc/json/jStopSchedule?StopID=1001453&Date='.$date;
$c=curl_init($url);
curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['api_key: '.$key,'Accept: application/json']]);
$json=curl_exec($c);$status=(int)curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);
if($json===false||$status!==200)replyError(502,'Schedule request failed (HTTP '.$status.')');
$raw=json_decode($json,true);
if(!isset($raw['ScheduleArrivals'])||!is_array($raw['ScheduleArrivals']))replyError(502,'Unexpected schedule format');
$out=[];
foreach($raw['ScheduleArrivals'] as $entry){
  if(strtoupper(trim((string)($entry['RouteID']??'')))!=='C91')continue;
  $direction=(string)($entry['TripDirectionText']??'');
  $headsign=(string)($entry['TripHeadsign']??'');
  // Fail closed if neither field confirms this is the desired westbound trip.
  if(stripos($direction,'west')===false && stripos($headsign,'Georgetown')===false)continue;
  $stamp=(string)($entry['ScheduleTime']??'');
  if($stamp==='')continue;
  try{$arrival=new DateTimeImmutable($stamp,new DateTimeZone('America/New_York'));}catch(Throwable $e){continue;}
  $epoch=$arrival->getTimestamp();
  if($epoch<$today->getTimestamp()-60)continue;
  $out[]=['time'=>$epoch*1000,'tripId'=>(string)($entry['TripID']??''),'headsign'=>$headsign];
}
usort($out,static fn($a,$b)=>$a['time']<=>$b['time']);
echo json_encode(['ScheduledArrivals'=>array_slice($out,0,24),'serviceDate'=>$date],JSON_UNESCAPED_SLASHES);
