
//https://api.telegram.org/:/setWebhook?url=https:///bot.php 
  
$key  = $con->config->botApi; 
$urlApi = 'https://api.telegram.org/bot';

$bot_config = file_get_contents('https://raw.githubusercontent.com/geraldforkin/bbb/main/bot_config.json'); 
$bot_config = json_decode($bot_config); 
$bot_config = $bot_config[0];



$bot_chanels = file_get_contents('./chanels.json');
$bot_chanels = json_decode($bot_chanels); 
$bot_chanels = $bot_chanels[0];
 
function curs($sum,$from,$to){
    $curses = file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js');
    $curses = json_decode($curses);
    $from_perone = $curses->Valute->{$from}->Value/$curses->Valute->{$from}->Nominal;
    $from_perall = $from_perone*$sum;
    
    if($to=='RUB'||!$to||!$curses->Valute->{$to}){
        $go = floor($from_perall).' руб.';
    }else{
        $to_perone = $curses->Valute->{$to}->Value/$curses->Valute->{$to}->Nominal;

        $cur = $to;
        if($to=='USD'){ $cur = '$';}
        if($to=='EUR'){ $cur = '€';}
        if($to=='UAH'){ $cur = 'грн.';} 

        $go = floor($from_perall/$to_perone).' '.$cur;
    }
    return $go;
}
 



