<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
$id_channel = 'UC1nxTJHKHTh_Wc6cbMle0qg';
$api_key = 'AIzaSyDKHPLBYUxePuG2yKgiRzYKIVX4_0qKdzw';

$logfile = $_SERVER["DOCUMENT_ROOT"]."/dev/youtube-pars/log.txt";

$arrVideoID = [];

if (CModule::IncludeModule("iblock")){
    $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>11));
    while($ob = $res->GetNext()){
        //print_r($ob["ID"]); получвем id всех элементов раздела
        $res2 = CIBlockElement::GetProperty(11, $ob["ID"], array("sort" => "asc"), Array("CODE"=>"KEY"));
        while ($ob2 = $res2->GetNext()) {
            $arrVideoID[] = $ob2['VALUE'];
        }
    }
}

$json_result = file_get_contents ("https://youtube.googleapis.com/youtube/v3/activities?part=snippet%2CcontentDetails&channelId=$id_channel&maxResults=5&key=$api_key");
$obj = json_decode($json_result, true);

//echo $obj['items'][0]['contentDetails']['upload']['videoId'];

if (!in_array($obj['items'][0]['contentDetails']['upload']['videoId'], $arrVideoID)) {
    //echo "есть новые видео";

    $el = new CIBlockElement;

    $PROP = array();
    $PROP[96] = $obj['items'][0]['contentDetails']['upload']['videoId'];
    $PROP[106] = "youtube";
    $PROP[107] = $obj['items'][0]['contentDetails']['upload']['videoId'];
    $PROP[109] = "http://img.youtube.com/vi/".$obj['items'][0]['contentDetails']['upload']['videoId']."/0.jpg";
    $PROP[110] = "http://img.youtube.com/vi/".$obj['items'][0]['contentDetails']['upload']['videoId']."/1.jpg";
    $PROP[111] = $obj['items'][0]['snippet']['title'];
    $PROP[112] = $obj['items'][0]['snippet']['description'];

    $arLoadProductArray = Array(
        "MODIFIED_BY"    => 29574, // элемент изменен пользователем Бехметьев Никита
        "IBLOCK_SECTION_ID" => false,
        "DATE_ACTIVE_FROM" => date("j.n.Y"),
        "IBLOCK_ID"      => 11,
        "PROPERTY_VALUES"=> $PROP,
        "NAME"           => $obj['items'][0]['snippet']['title'],
        "ACTIVE"         => "Y",            // активен
        "PREVIEW_TEXT"   => $obj['items'][0]['snippet']['description'],
        "DETAIL_TEXT"    => "",
        "DETAIL_PICTURE" => CFile::MakeFileArray($obj['items'][0]['snippet']['thumbnails']['standard']['url'])
    );

    if($PRODUCT_ID = $el->Add($arLoadProductArray)){
        $fw = fopen($logfile, "a");
        fwrite($fw, date("j.n.Y") . " ID: " . $PRODUCT_ID . " - добавлено новое видео \n");
        fwrite($fw, "\n-------------\n");
        fclose($fw);
    }else{
        $fw = fopen($logfile, "a");
        fwrite($fw, date("j.n.Y") . "Error: ".$el->LAST_ERROR ."\n");
        fwrite($fw, "\n-------------\n");
        fclose($fw);
    }
} else {

    $fw = fopen($logfile, "a");
    fwrite($fw, date("j.n.Y") . " - нет новыйх видео \n");
    fwrite($fw, "\n-------------\n");
    fclose($fw);
}
/*
foreach ($obj['items'] as &$value) {
    //print_r($value);
    echo $value['snippet']['title'];
    echo "<br>";
    echo $value['snippet']['description'];
    echo "<br>";
    echo $value['snippet']['thumbnails']['standard']['url'];
    echo "<br>";
    echo $value['contentDetails']['upload']['videoId'];
    echo "<br>---<br>";
}*/
?>