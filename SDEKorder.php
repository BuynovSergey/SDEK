<?php
if($AUTH_USER_ID==1){
    $account = '***';
    $key = '***';
    $secure = md5('2019-10-08&'.$key);
    if($del){
    	$datesdek = doQuery("select DateCreateCDEK from Info_98 where Info_ID=$del","\$data[DateCreateCDEK]");
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <deleterequest number="order'.$del.'" ordercount="1" account="'.$account.'" date="'.$datesdek.'" secure="'.$key.'">
            <order number="order'.$del.'"/>
        </deleterequest>';
        $url = 'https://integration.edu.cdek.ru/delete_orders.php';
        $url = 'https://integration.cdek.ru/delete_orders.php';
    }
    if($_POST[Info_ID]){
        if(!$_POST[chosenPost] && $_POST[CDate] && $_POST[Ctimebeg] && $_POST[Ctimeend]){
            doQuery("update Info_98 set CCom='$_POST[CCom]', CDate='$_POST[CDate]', Ctimebeg='$_POST[Ctimebeg]', Ctimeend='$_POST[Ctimeend]' where Info_ID=$_POST[Info_ID]","");
        }
        $phone = preg_replace('![^0-9]+!', '', $_POST[Phone]);
        $tov='';
        $weight=0;
        $order_arr = explode(";", substr($_POST[Item],1));
        $orderK_arr = explode(";", substr($_POST[ItemK],1));
        $co = count($order_arr);
        for($i = 0; $i < $co; $i++){
            $order_info = explode(":", $order_arr[$i]);
            $order_id = $order_info[0];
            $order_count = $orderK_arr[$i];
            $order_price = $order_info[2];
            $gab = explode("::",doQuery("select Artikul, Name, Width, Height, Length, Mass from Info_10 where Info_ID=$order_id","\$data[Width]::\$data[Height]::\$data[Length]::\$data[Mass]::\$data[Name]::\$data[Artikul]"));
            $tov.='<package barcode="order'.$_POST[Info_ID].'-'.($i+1).'" number="'.($i+1).'" sizea="'.$gab[2].'"
                    sizeb="'.$gab[0].'" sizec="'.$gab[1].'" weight="'.(($gab[3]*1000)*$order_count).'">
                    <item amount="'.$order_count.'" comment="'.$gab[4].'" cost="'.$order_price.'"
                        payment="'.$order_price.'" paymentvatrate="VATX" paymentvatsum="0.0"
                        warekey="'.$gab[5].'" weight="'.($gab[3]*1000).'"></item>
                </package>';
            $weight+=$gab[3]*1000*$order_count;             
        }
        $weight = $weight;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <deliveryrequest account="'.$account.'"
            date="'.date('Y-m-d H:i:s').'" number="order'.$_POST[Info_ID].'" ordercount="1" secure="'.$key.'">            
            <order number="order'.$_POST[Info_ID].'" phone="+'.$phone.'" reccitycode="'.$_POST[chosenCityID].'"
                recipientemail="'.$_POST[Email].'" recipientname="'.$_POST[Name].'"
                sendcitycode="44" tarifftypecode="'.$_POST[tarif].'">
                '.(!$_POST[chosenPost] ? '<schedule>
                    <attempt date="'.$_POST[CDate].'" id="1"
                        timebeg="'.$_POST[Ctimebeg].'" timeend="'.$_POST[Ctimeend].'" comment="'.$_POST[CCom].'">
                        <address flat="'.$_POST[kvartira].'" house="'.$_POST[Home].'" street="'.$_POST[Street].'"></address>
                    </attempt>
                </schedule>' : '').'
                <address '.($_POST[chosenPost] ? 'PvzCode="'.$_POST[chosenPost].'"' : 'flat="'.$_POST[kvartira].'" house="'.$_POST[Home].'" street="'.$_POST[Street].'"').'></address>
                '.$tov.'
            </order>
        </deliveryrequest>';
        $url = 'https://integration.edu.cdek.ru/new_orders.php';
        $url = 'https://integration.cdek.ru/new_orders.php';
    }
    if($his){        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <StatusReport Account="'.$account.'" Date="'.date('Y-m-d H:i:s').'" Secure="'.$key.'" ShowHistory="1">
            <Order DispatchNumber="1105080696"/>
        </StatusReport>';
        $url = 'https://integration.edu.cdek.ru/status_report_h.php';
    }
    if($url && $xml){
    	$ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'xml_request' => $xml
    ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $order=curl_exec($ch);
            curl_close($ch);
            echo($order);
        $result = new SimpleXMLElement($order);
            
        if($result->Order[ErrorCode]){        
            echo "<p style='color:#ff0000'>№ заказа ".$result->Order[Number]."</p><p style='color:#ff0000'>".$result->Order[Msg]."</p>";
            if($del){
                doQuery("update Info_98 set ".($result->Order[ErrorCode]=='ERR_ORDER_NOTFIND' ? "StatusSDEK='3', " : "")."OrderCode='".$result->Order[ErrorCode]."', Msg='".$result->Order[1][Msg]."' where Info_ID=$del","");
            } else {
                doQuery("update Info_98 set OrderCode='".$result->Order[ErrorCode]."', Msg='".$result->Order[Msg]."' where Info_ID=$_POST[Info_ID]","");
            }
        } else {        
            echo "<p style='color:#090'>№ заказа ".$result->Order[0][DispatchNumber]."</p><p style='color:#090'>".$result->Order[1][Msg]."</p>";
            if($del){
                doQuery("update Info_98 set StatusSDEK='3', DateCreateCDEK='".date("Y-m-d H:i:s")."', OrderCode='', Msg='".$result->Order[1][Msg]."', CCom='', CDate='', Ctimebeg='', Ctimeend='' where Info_ID=$del","");
            } else {
                doQuery("update Info_98 set StatusSDEK='1', DateCreateCDEK='".date("Y-m-d H:i:s")."', OrderCode='', DispatchNumber='".$result->Order[0][DispatchNumber]."', Msg='".$result->Order[1][Msg]."' where Info_ID=$_POST[Info_ID]","");
            }
        }
    }
}
echo ($xml);
?>