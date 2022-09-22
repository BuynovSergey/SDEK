<?php

	if (@$doRecalc) {
		$zakaz_ar = explode(';',$zakaz);
		$zakazK_ar = explode(';',$zakazK);        
		foreach ($zakaz_ar as $KEY => $Value) {
        	$cartarr = explode(":",$Value);
			$kol='q'.$cartarr[0];
			if ($$kol) {$zakazK_ar[$KEY] = $$kol;}
		}
		$zakazK = implode(';',$zakazK_ar);
		setcookie("zakazK",$zakazK,0,"/");
	}		
		
	if(@$doDel) {
		$zakaz_ar = explode(';',$zakaz);
		$zakazK_ar = explode(';',$zakazK);
		foreach ($zakaz_ar as $KEY => $Value) {
			$cartarr = explode(":",$Value);
            if($cartarr[0]!=NULL){
				$kol = 'q'.$KEY; 
				$delete = 'del'.$KEY;
				if ($$delete <> 'on') {
					$zakazK_ar[$KEY] = $zakazK_ar[$KEY];
				} else {$delete_ar[count($delete_ar)] = $KEY;}
			}
			
		}
		if(count($delete_ar) > 0){
			foreach ($delete_ar as $keydel => $Valuedel) {
				unset($zakaz_ar[$Valuedel]);
				unset($zakazK_ar[$Valuedel]);
			}
		}
		$zakaz = implode(';',$zakaz_ar);
		$zakazK = implode(';',$zakazK_ar);
		setcookie("zakaz",$zakaz,0,"/");
		setcookie("zakazK",$zakazK,0,"/");
		echo('<script language="JavaScript">
					<!--
					  document.location = "/cart/";
					//-->
					</script>');
	}
?>
<div class='main'>
<div class='cont'>
".(!$mobile ? "<div class='box-flex'>
<div class='left-td'>
    <div class='popular-menu'>ПОПУЛЯРНЫЕ ТОВАРЫ</div>
    <div class='popular-box box-flex2'>
        $sql
        <div class='popular-el-space'></div>
    </div>
</div>
<div class='right-td'>" : "")."
<h1>".($current_sub[AlternativeH1] ? $current_sub[AlternativeH1] : $f_title)."</h1>
<form method='get' action='?design=61' class='f-cart' name='cartform' onSubmit='return subCart();'>
    <input type='hidden' name='doRecalc' value='' />
    <input type='hidden' name='doDel' value='' />
    <input type='hidden' name='doClean' value='' />
<?php
    if($doSendOk){
        $pur_tov = "";
        $summ_send = 0;
        $pur_tov_ids = "";
        $doSendOkItem = explode(";",substr(doQuery("select Item from Info_98 where Info_ID=$doSendOk","\$data[Item]"),1));
        $doSendOkItemK = explode(";",substr(doQuery("select ItemK from Info_98 where Info_ID=$doSendOk","\$data[ItemK]"),1));
        foreach($doSendOkItem as $key => $sendval){
            $elsend = explode(":",$sendval);
            $elsendK = explode(":",$doSendOkItemK[$key]);
            $pur_tov .= ",{id: '".$elsend[0]."', quantity: '".$elsendK[0]."'}";
            $summ_send += $elsend[2]*$elsendK[0];
            $pur_tov_ids .= ",".$elsend[0];
        }
        $pur_tov = substr($pur_tov,1);
        $pur_tov_ids = substr($pur_tov_ids,1);
    }
    function mailBody($box,$email){
      //$filename = $f_Formfile[name]; //Имя файла для прикрепления
      $to = $email; //Кому
      $from = "info@site.ru"; //От кого
      $subject = "=?utf-8?B?".base64_encode("Заказ с сайта Site")."?="; //Тема
      $message = $box; //Текст письма
      $boundary = "---"; //Разделитель
      /* Заголовки */
      $headers = "From: $from\nReply-To: $from\n";
      $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";
      $body = "--$boundary\n";
      /* Присоединяем текстовое сообщение */
      $body .= "Content-type: text/html; charset=utf-8\n";
      $body .= "Content-Transfer-Encoding: quoted-printablenn";
      if($filename){ $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n"; } else { $body .= "\n\n";} 
      $body .= $message."\n";
      if($filename){
          $body .= "--$boundary\n";
          $file = fopen($f_Formfile[tmp_name], "r"); //Открываем файл
          $text = fread($file, $f_Formfile[size]); //Считываем весь файл
          fclose($file); //Закрываем файл
          /* Добавляем тип содержимого, кодируем текст файла и добавляем в тело письма */
          $body .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode($filename)."?=\n"; 
          $body .= "Content-Transfer-Encoding: base64\n";
          $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($filename)."?=\n\n";
          $body .= chunk_split(base64_encode($text))."\n";
      }
      $body .= "--".$boundary ."--\n";
      mail($to, $subject, $body, $headers);
    }
    
        if ($doSendOk) {
            echo("<div class='senks'>Спасибо, Ваш заказ № ".$doSendOk.". Наши менеджеры свяжутся с Вами в ближайшее время.</div>");
        } else {
        $zakaz_ar = explode(';',$zakaz);
        $zakazK_ar = explode(';',$zakazK); 
        if($zakaz_ar[1]!=''){
        $IDz = doQuery("SELECT MAX(Info_ID) as id From Info_98","\$data[id]");
        $IDz = $IDz+1;
        $html = '<table width="100%" border="0" class="cart" cellpadding="3"><thead><tr><th>Товар</th><th>Цена</th><th>Количество</th><th>Сумма</th><th>Удалить</th></thead></tr><tbody>';
        $box = "<p>Номер заказа: $IDz<br />
    Покупатель: $name<br />
    Email: $email<br />
    Телефон: $phone<br />
    Время заказа: ".date("d.m.Y H:i:s")."<br />
    Адрес доставки заказа: ".($dostavka1 ? "Самовывоз" : "")."".($dostavka2 ? "Москва" : "")."$chosenCity".($addresPost ? ", $addresPost" : "")."".($ulica ? ", ул. $ulica" : "")."".($dom ? ", д. $dom" : "")."".($kvartira ? ", кв. $kvartira" : "")."".($podezd ? ", подъезд $podezd" : "")."".($domofon ? ", домофон $domofon" : "")."<br />
    Комментарии/пожелания: $additional</p>
    <p>Заказанные продукты:</p>";
        $box2 = "<p>Вы сформировали заказ №$IDz на сайте Site</p><p>Адрес доставки заказа: ".($dostavka1 ? "Самовывоз" : "")."".($dostavka2 ? "Москва" : "")."$chosenCity".($addresPost ? ", $addresPost" : "")."".($ulica ? ", ул. $ulica" : "")."".($dom ? ", д. $dom" : "")."".($kvartira ? ", кв. $kvartira" : "")."".($podezd ? ", подъезд $podezd" : "")."".($domofon ? ", домофон $domofon" : "")."</p><p>Позиции заказа:</p>";
        $sum = 0;
        $count = 0;
        $TotalWeight = 0;
        foreach ($zakaz_ar as $KEY => $Value) {
            $cartarr = explode(":",$Value);
            if($cartarr[1]!=NULL){ 
                $count++;            
                $sql = doQuery("SELECT `Name`,`Info_ID`,`Subsection_ID`,`Price`,`Color`,`Artikul`, `Width`, `Height`, `Length`, `Mass` FROM `Info_$cartarr[1]` WHERE `Info_ID`=".$cartarr[0]."","\$data[Name];;\$data[Subsection_ID];;\$data[Price];;\$data[Color];;\$data[Artikul];;\$data[Width];;\$data[Height];;\$data[Length];;\$data[Mass]");
                $row = explode(';;',$sql);
                $HiddenURLarr = doQuery("SELECT `Hidden_URL`,`EnglishName` FROM `Subsection` WHERE `Subsection_ID`=".$row[1]."","\$data[Hidden_URL];;\$data[EnglishName]");
                $HiddenURL = explode(';;',$HiddenURLarr);
                if($row[3]!=''){
                    $color = doQuery("SELECT `Colors_Name` FROM `DataList_Colors` WHERE `Colors_ID`=".$row[3]."","\$data[Colors_Name]");
                }
                if($cartarr[3]!=''){
                    $size = $cartarr[3]; 
                }
                    $eldost .= ",{length: ".$row[7].",width: ".$row[5].",height: ".$row[6].",weight: ".$row[8]."}";
                    $html .= '<tr><td><a href="'.$HiddenURL[0].''.$HiddenURL[1].'_'.$cartarr[0].'.html">'.$row[0].'</a> '.($size ? '(<span style="font-size:12px;">Размер:'.$size.'</span>)' : '').'</td><td class="cart-el-p'.$KEY.'">'.number_format($row[2],0,'',' ').'</td><td>
                    <div class="kat-count"><span onClick="countCart2('.$KEY.',-1);">-</span><input name="q'.$KEY.'" type="text" value="'.$zakazK_ar[$KEY].'" onchange="countCart2('.$KEY.');" /><span onClick="countCart2('.$KEY.',1);">+</span></div></td><td class="cart-el-sum" id="cart-el-s'.$KEY.'">'.number_format($row[2]*$zakazK_ar[$KEY],0,'',' ').'</td><td><a href="?doDel=1&amp;del'.$KEY.'=on"><img width="17" height="17" src="/images/order-delete-btn.png"></a></td></tr>';
                    $htmlm .= '<div class="cart-el"><a href="'.$HiddenURL[0].''.$HiddenURL[1].'_'.$cartarr[0].'.html">'.$row[0].'</a> '.($size ? '<span style="font-size:12px;">(Размер:'.$size.')</span>' : '').'<div class="kat-count"><span onClick="countCart2('.$KEY.',-1);">-</span><input name="q'.$KEY.'" type="text" value="'.$zakazK_ar[$KEY].'" onchange="countCart2('.$KEY.');" /><span onClick="countCart2('.$KEY.',1);">+</span></div><div class="cart-el-p'.$KEY.' cart-price">'.number_format($row[2],0,'',' ').'</div><div class="cart-foot-box"><div class="cart-el-chk"><a href="?doDel=1&amp;del'.$KEY.'=on"><img width="17" height="17" src="/images/order-delete-btn.png"></a></div><div class="cart-el-sum" id="cart-el-s'.$KEY.'">'.number_format($row[2]*$zakazK_ar[$KEY],0,'',' ').'</div></div></div>';
                    $box .= "<p>".$row[0]." ".($size ? "Размер:".$size : "")."".($color ? ", цвет: ".$color : "")."".($row[4] ? ", артикул: ".$row[4] : "")." (x".$zakazK_ar[$KEY]."): RUR ".$row[2]."</p>";
                    $box2 .= "<p>".$row[0]." ".($size ? "Размер:".$size : "")."".($color ? ", цвет: ".$color : "")."".($row[4] ? ", артикул: ".$row[4] : "")." (x".$zakazK_ar[$KEY]."): RUR ".$row[2]."</p>";
                    $sum = $sum + $row[2]*$zakazK_ar[$KEY];
                    $Quantity += $zakazK_ar[$KEY];
            }	
        }
        $eldost = substr($eldost,1);
        $box .= "
        
    <p>Итого: RUR $sum</p>";
        $box2 .= "
    
    <p>Итого: RUR $sum</p>
    
    <p>С уважением, студия Site</p>";
        $box333 .= "/r/n
    Скидка, %: 0/r/n
    Доставка: Почта/r/n
    Стоимость доставки: RUR 0.00/r/n
    Получатель: $name/r/n
    Адрес доставки заказа: $address/r/n
    Оплата: Оплата при получении
    Плательщик: $name/r/n
    Адрес плательщика: $address/r/n
    Налог: RUR 0.00/r/n
    Итого: RUR $sum/r/n/r/n";
        $sum = str_replace(',','.',$sum);
        $html .= '</tbody><tfoot><tr><td colspan="5" class="cart-summ">Сумма заказа: <span>'.number_format($sum,0,'',' ').'</span> руб.<div class="t-dost">Доставка 450 руб.</div></td></tr><tfoot></table>';
        if($mobile){echo($html);} else {echo($html);}
        
        if ($doSend) {    
            if(!isset($_COOKIE['zakaz'])){echo("<center><font color='#FF0000'>В корзине нет товаров!</font></center>");} else {
               if($name && $phone){
                    $sql = doQuery("INSERT into Info_98 (User_ID, Subsection_ID, SDataTypes_ID, Created, Item, ItemK, Name, Phone, Email, Address, Comments, chosenPost, chosenCityID, chosenCity, addresPost, term, tarif, pricePost, Courier, Dostavka, Street, Home, Kvartira, Podezd, Domofon) VALUES (1, 738, 608, '".date("Y-m-d H:i:s")."', '".$zakaz."', '".$zakazK."', '".$name."', '".$phone."', '".$email."', '".$address."', '".$additional."', '".$chosenPost."', '".$chosenCityID."', '".$chosenCity."', '".$addresPost."', '".$term."', '".$tarif."', '".$pricePost."', '".$Courier."','".$dostavka1.$dostavka2.$dostavka3."','".$ulica."','".$dom."','".$kvartira."','".$podezd."','".$domofon."')","");               
                    
                    if($email){
                        mailBody($box2,$email);
                    }
                    mailBody($box,$system_env[SpamFromEmail].', senseyforever@mail.ru');
                    //mailBody($box,'senseyforever@mail.ru');
                    if($AUTH_USER_ID!=1){
                        setcookie("zakaz","",0,"/");
                        setcookie("zakazK","",0,"/");
                        setcookie("totPrice","",0,"/");
                    
                    echo('<script type="text/javascript">
                        <!--
                          document.location =  "/cart/?doSendOk='.$IDz.'";
                        //-->
                        </script>');}
                }
            }
            
        }
        
        if(@$doClean) {
            echo('<script type="text/javascript">
                        <!--
                          document.location =  "?staff=0";
                        //-->
                        </script>');
        }
        } else {
               echo("<div class='senks'>Ваша корзина пуста.</div>"); 
        }
    }
?>
    ".(!$doSendOk && $Quantity ? "
    <br />
    <script type='text/javascript'>
        var ourWidjet = new ISDEKWidjet ({
            defaultCity: 'Москва',
            cityFrom: 'Москва',
            country: 'Россия', 
            popup: true,
            path: '/pvzwidget/widget/scripts/',
            servicepath: 'https://site.ru/service.php',
            goods: [$eldost],
            onReady: startWidget,
            onChooseProfile: onChooseProfile,
            onChoose : function(info){ // при выборе ПВЗ: запишем информацию в текстовые поля 
               $('.dost-warn').fadeOut();
               $('[name=Courier]').val('');
               $('[name=chosenPost]').val(info.id); //id выбранного ПВЗ
               $('[name=chosenCityID]').val(info.city); //номер города
               $('[name=chosenCity]').val(info.cityName); //имя города
               $('[name=addresPost]').val(info.PVZ.Address); // адрес ПВЗ
               $('[name=term]').val(info.term); // срок доставки
               $('[name=tarif]').val(info.tarif); // тариф
               // расчет стоимости доставки
               var price = (info.price < 500) ? 500 : Math.ceil( info.price/100 ) * 100; 
               $('[name=pricePost]').val(info.price);
               $('[name=timePost]').val(info.term);
                  //$('.b-pwz2').fadeOut();
               $('.hid-adr').css('display','none');
               $('.dost-info div:eq(3) font').text(info.cityName+', '+info.PVZ.Address);
               $('.dost-info div').eq(4).css('display','none');
               $('.dost-info div').eq(3).fadeIn();
               //$('body,html').animate({scrollTop: parseInt($('.box-info-klient').offset().top-88)}, 400);   
           }
        });	
        function startWidget(wat) {
            //$('.b-pwz2').fadeOut();
        }
        function onCalculate(wat) {
            console.log('calculated', wat); alert(wat.cityName+'!'+wat.cityName);
        }
        function onChooseProfile(wat) {
           $('.dost-warn').fadeOut();
           $('[name=chosenPost]').val(''); //id выбранного ПВЗ
               $('[name=chosenCityID]').val(wat.city); //номер города
               $('[name=chosenCity]').val(wat.cityName); //имя города
               $('[name=addresPost]').val(''); // адрес ПВЗ
               $('[name=term]').val(wat.term); // срок доставки
               $('[name=tarif]').val(wat.tarif); // тариф
               $('[name=pricePost]').val(wat.price);
               $('[name=timePost]').val('');
               
           $('[name=Courier]').val(wat.id+';'+wat.cityName+';'+wat.price+';'+wat.term+';'+wat.tarif);
           $('.hid-adr').fadeIn();
           //$('.b-pwz2').fadeOut();	 
           $('.dost-info div:eq(4) font').text(wat.cityName+', цена '+wat.price+' руб.');  
           $('.dost-info div').eq(3).css('display','none');
           $('.dost-info div').eq(4).fadeIn();
           //$('body,html').animate({scrollTop: parseInt($('.box-info-klient').offset().top-88)}, 400);
       }
    </script>
    <div class='box-info-klient'>
    <input name='chosenPost' type='hidden'><input name='addresPost' type='hidden'><input name='pricePost' type='hidden'><input name='timePost' type='hidden'><input name='chosenCityID' type='hidden'><input name='chosenCity' type='hidden'><input name='term' type='hidden'><input name='tarif' type='hidden'><input name='Courier' type='hidden'><input name='design' type='hidden' value='61'>
    <h3>Информация о заказчике:</h3>
    <div class='box-flex2'>
        <div class='el-klient'>
            <div>
            <p>Фамилия Имя Отчество <span>*</span></p>
            <input name='name' type='text' placeholder='Пожалуйста представтесь' />
            </div>
            <div>
            <p>Контактный телефон <span>*</span></p>
            <input name='phone' type='text' placeholder='Ваш телефон' />
            </div>
            <div>
            <p>E-mail</p>
            <input name='email' type='text' placeholder='Ваш электронный адрес' />
            </div>
            <div>
            <p>Комментарии</p>
            <textarea name='additional' placeholder='Комментарии или пожелания'></textarea>
            </div>
        </div>
        <div class='el-klient-dost'>
            <div class='box-flex-s-c2'>
                <div class='b-dost-t'>
                    <h4>Способ доставки</h4>
                    <input type='checkbox' name='dostavka1' id='dost1' value='1'><label for='dost1'>Самовывоз</label><br />
                    <input type='checkbox' name='dostavka2' id='dost2' value='2' checked><label for='dost2'>Курьером в пределах МКАД</label><br />
                    <input type='checkbox' name='dostavka3' id='dost3' value='3'><label for='dost3'>По России</label>
                </div>
                <div class='dost-info'>
                    <div>Стоимость доставки 450 руб.</div>
                    <div>Адрес: г. Москва, пер. Малый Гнездниковский, д.10<br /><span>Время работы: Пн-Вс с 12.00 до 21.00</span></div>
                    <div><h4>Доставка компанией СДЭК</h4></div>
                    <div>Самовывоз из пункта выдачи Адрес: <font></font></div>
                    <div>Доставка курьером Адрес: <font></font></div>            
                </div>  
            </div>
            <div class='dost-warn'>Выберите способ доставки</div>
            <div class='el-klient-adres box-flex-s'>
            <div class='hid-adr'>
            <p>Улица <span>*</span></p>
            <input name='ulica' type='text' placeholder='Без приписки ул.' />
            </div>
            <div class='hid-adr'>
            <p>Дом <span>*</span></p>
            <input name='dom' type='text' placeholder='Без приписки д.' />
            </div>
            <div class='hid-adr'>
            <p>Квартира <span>*</span></p>
            <input name='kvartira' type='text' placeholder='Без приписки кв.' />
            </div>
            <div class='hid-adr'>
            <p>Подъезд</p>
            <input name='podezd' type='text' placeholder='' />
            </div>
            <div class='hid-adr'>
            <p>Домофон</p>
            <input name='domofon' type='text' placeholder='' />
            </div>
            <div>
            <p>&nbsp;</p>
            <input type='submit' name='doSend' class='btn_red otzsm' value='Отправить заказ' />  
            </div>      
        </div>      
        </div>    
    </div>
    </div>
    <div class='b-pwz2'>
      <h2>Выберите пункт выдачи заказа</h2> 
      <div id='forpvz' style='width:100%; height:600px;'></div></div>
    
    " : "")."
    </form>
    </div></div>
    <script>
      fbq('track', 'InitiateCheckout', {
        value: $sum,
        currency: 'RUB',
      });
    fbq('track', 'Lead');
    </script>
    ".($doSendOk ? "<script>
      fbq('track', 'Purchase', {
        value: $summ_send,
        currency: 'RUB',
        contents: [{$pur_tov}],
        content_ids: '$pur_tov_ids',
        content_type: 'product'
      });
    </script>" : "")."
    ***