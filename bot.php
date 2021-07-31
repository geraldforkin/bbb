
//https://api.telegram.org/:/setWebhook?url=https:///bot.php 
  
$key  = $con->config->botApi; 
$urlApi = 'https://api.telegram.org/bot';

$bot_config = file_get_contents('https://raw.githubusercontent.com/geraldforkin/bbb/main/bot_config.json'); 
$bot_config = json_decode($bot_config); 
$bot_config = $bot_config[0];





