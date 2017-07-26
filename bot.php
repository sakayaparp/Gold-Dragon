<?php 
require 'vendor/autoload.php';

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (!is_null($events['events'])) { // Validate parsed JSON data
    // LINE config
    $access_token = 'YOUR_ACCESS_ACCESS_TOKEN';
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'YOUR_CHANNEL_SECRET']);

    foreach ($events['events'] as $event) { // Loop through each event
        // Reply only when message sent is in 'text' format
        if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
            // get detail from user
            $textUser = $event['message']['text'];
            $replyToken = $event['replyToken'];
            $menuName = explode('สั่งซื้อ ', $textUser);

            // select reply message
            if(strpos($textUser, 'สั่งซื้อ') === false && in_array($textUser, getKeyword())) {
                $columnTemplateBuilders = multiColumn(getMenu($textUser));
                $templates = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columnTemplateBuilders);
            } else if(strpos($event['message']['text'], 'ยืนยันการสั่งซื้อ ') !== false) {
                $payLink = getPayLink();
                $textMessage = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ลิ้งค์สำหรับจ่ายเงิน {$payLink[$menuName[1]]}", "กรุณาระบุที่อยู่เพื่อจัดส่ง โดยการ Share Location");
                $response = $bot->replyMessage($replyToken, $textMessage);
                echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
                exit;
            } else if(in_array($menuName[1], getKeyword())) {
                $templates = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder(
                    "คุณแน่ใจที่จะสั่งซื้อ {$menuName[1]}?",
                    array(
                        new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('ตกลง', "ยืนยันการสั่งซื้อ {$menuName[1]}"),
                        new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('ยกเลิก', "เมนู")
                    )
                );
            } else {
                $textMessage = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ไม่พบเมนู {$textUser}", 'ขออภัยค่ะ');
                $response = $bot->replyMessage($replyToken, $textMessage);
                echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
                exit;
            }

            // send message
            $altText = 'this is a buttons template to avaiable on mobile only.';
            $templateMessageBuilder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
                $altText, 
                $templates
            );
            $response = $bot->replyMessage($replyToken, $templateMessageBuilder);
            echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
        } else if($event['type'] == 'message' && $event['message']['type'] == 'location') {
            $replyToken = $event['replyToken'];
            $textMessage = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ทางเราจะจัดส่งสินค้าไปที่ address: {$event['message']['address']}", "แนะนำร้านที่ใกล้ที่สุด: https://campaign.eggdigital.com/line_api/google_map/index.php?lat={$event['message']['latitude']}&lng={$event['message']['longitude']}");
            $response = $bot->replyMessage($replyToken, $textMessage);
            echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
        }
    }
}

/**
 */
function getKeyword()
{
    return array(
        'เมนู',
        'ข้าวขาหมูไทย',
        'ข้าวขาหมูยูนาน',
        'ข้าวขาหมูนครปฐม',
        'ก๋วยเตี๋ยวขาหมู',
        'ขาหมูล้วน',
        'ขาหมูคากิ',
    );
}

/**
 */
function getPayLink()
{
    return array(
        'ข้าวขาหมูไทย' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
        'ข้าวขาหมูยูนาน' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
        'ข้าวขาหมูนครปฐม' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
        'ก๋วยเตี๋ยวขาหมู' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
        'ขาหมูล้วน' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
        'ขาหมูคากิ' => 'https://campaign.eggdigital.com/line_api/web/pay/payment.html',
    );
}

/**
 */
function getMenu($textUser)
{
    $menus = array(
        array(
            'text' => 'ข้าวขาหมูยูนาน', 
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/yunan.jpg'
        ),
        array(
            'text' => 'ข้าวขาหมูนครปฐม', 
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/nakhonprathom.jpg'
        ),
        array(
            'text' => 'ก๋วยเตี๋ยวขาหมู',
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/pig_leg_noodle.jpg'
        ),
        array(
            'text' => 'ขาหมูล้วน',
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/allpig.jpg'
        ),
        array(
            'text' => 'ขาหมูคากิ',
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/kaki.jpg'
        ),
        array(
            'text' => 'ข้าวขาหมูไทย',
            'url' => 'https://campaign.eggdigital.com/line_api/pictures/skinpig.jpg',
        ),
    );
    shuffle($menus);

    if($textUser == 'เมนู') {
        unset($menus[5]);
        return $menus;
    } else {
        $haveInMenu = array_search($textUser, array_column($menus, 'text'));
        return array($menus[$haveInMenu]);
    }
}

/**
 */
function multiColumn($menus)
{
    foreach ($menus as $key => $menu) {
        $datas[] = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder(
            $menu['text'],
            "เมนู {$menu['text']} สุดอร่อย", 
            $menu['url'], 
            array(
                new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('Open Browser', "https://campaign.eggdigital.com/line_api/web/index.html"),
                new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('Order', "สั่งซื้อ {$menu['text']}"),
                new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('Show only this menu', $menu['text']),
            )
        );
    }

    return $datas;
}

echo "OK";
