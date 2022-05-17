<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
function cmp($a, $b)
{
    return strcmp($a["PROPERTY_WIDTH_VALUE"], $b["PROPERTY_WIDTH_VALUE"]);
}
$res_measure = CCatalogMeasure::getList();
        while($measure = $res_measure->Fetch()) {
            $measures[$measure["ID"]]=$measure["SYMBOL_RUS"];
        }
       $arResult["MEASURES"]=$measures; 
foreach($arResult['RESULT_ELEMS'] as $key => $item)
{
    switch($item['TITLE'])
    {
        case GetMessage('PLENKA'):
            $title = GetMessage('WITHOUT_PLENKA');
            break;
        case GetMessage('MATERIAL'):
            $title = GetMessage('WITHOUT_MATERIAL');
            break;
        case GetMessage('DECODE'):
            $title = GetMessage('WITHOUT_DECODE');
            break;
    }

    $arResult['RESULT_ELEMS'][$key]['DATA_TITLE'] = $title;
}


if($_REQUEST["length"]<5) $prip1=1;
else $prip1=2;

if($_REQUEST["width"]<5) $prip2=1;
else $prip2=2;



$length=$_REQUEST["length"]+2*$_REQUEST["depth"]+$prip1;
$width=$_REQUEST["width"]+2*$_REQUEST["depth"]+$prip2;
if($_REQUEST["depth"]<=1) {
    $pvkh=array("0,5");
    $epdm=array("0,8");
} else if ($_REQUEST["depth"]<=2) {
    $pvkh=array("1");
    $epdm=array("0,8", "0,9");
} else if($_REQUEST["depth"]<=3) {
    $epdm=array(1, "0,9");
} else {
    $epdm=array(1);
}
$i=0;$k=0;
foreach($arResult["PLENKA_ELEMS"] as $id=>$arElem) {
    if($arElem["IBLOCK_SECTION_ID"]==Configurator::PVHPlenka && !empty($pvkh)) {
        $plenka=implode("|", $pvkh);
            if(!empty($pvkh)) {
            $regexp="/(.*)толщина\s(".$plenka.")\sмм/";
            if(!preg_match($regexp, $arElem["NAME"]) || $i==1) {
                unset($arResult["PLENKA_ELEMS"][$id]);
            }
            
        }
        $offer[$arElem["ID"]]=$arElem["ID"];
    } else if($arElem["IBLOCK_SECTION_ID"]==Configurator::Membrana && !empty($epdm)) {
        $plenka=implode("|", $epdm);
        if(!empty($epdm)) {
            $regexp="/(.*)толщина\s(".$plenka.")\sмм/";
            if(!preg_match($regexp, $arElem["NAME"])) {
                unset($arResult["PLENKA_ELEMS"][$id]);
            }
        }
        
        $offer[$arElem["ID"]]=$arElem["ID"];
    } else {
        unset($arResult["PLENKA_ELEMS"][$id]);
    }/**/
    
}
$offersExist = CCatalogSKU::getExistOffers($offer);

$offerslist=CCatalogSKU::getOffersList(
 $offer,
 0,
 array(">CATALOG_QUANTITY"=>0, "ACTIVE"=>"Y", "NAME"=>"%ширина%"),
 array("CATALOG_GROUP_4", "ID", "NAME", "PROPERTY_CML2_LINK", "PROPERTY_SHIRINA", "PROPERTY_DLINA", "PROPERTY_WIDTH", "PROPERTY_LENGTH") 
);

function grade_sort($x, $y) {
    if ($x['PROPERTY_WIDTH_VALUE'] > $y['PROPERTY_WIDTH_VALUE']) {
        return true;
    } else if ($x['PROPERTY_WIDTH_VALUE'] < $y['PROPERTY_WIDTH_VALUE']) {
        return false;
    } else {
        return 0;
    } 
}

foreach($offerslist as $id=>$offer) {
    
    foreach($offer as $i=>$off)  {
     $off["PROPERTY_SHIRINA_VALUE"]=str_ireplace(",", ".", $off["PROPERTY_SHIRINA_VALUE"]);
     $off["PROPERTY_WIDTH_VALUE"]=str_ireplace(",", ".", $off["PROPERTY_WIDTH_VALUE"]);
     if(!empty($off["PROPERTY_SHIRINA_VALUE"]&& empty($off["PROPERTY_WIDTH_VALUE"]))) {
      $offer[$i]["PROPERTY_WIDTH_VALUE"]=$off["PROPERTY_SHIRINA_VALUE"];
     } else {
      $offer[$i]["PROPERTY_WIDTH_VALUE"]=$off["PROPERTY_WIDTH_VALUE"];
     }
    }
    usort($offer, 'grade_sort');
    foreach($offer as $i=>$off)  {
     if($off["PROPERTY_WIDTH_VALUE"]<$width && $off["PROPERTY_WIDTH_VALUE"]<$length) {
      
      unset($offer[$i]);
     }
    }
    $offersnew[$id]=$offer;    
}

foreach($arResult["PLENKA_ELEMS"] as $id=>$arElem) {
    if(array_key_exists($arElem["ID"], $offersExist) && empty($offersnew[$arElem["ID"]])) {
        unset($arResult["PLENKA_ELEMS"][$id]);
    }     
    else if (array_key_exists($arElem["ID"], $offersExist)){
        $arResult["PLENKA_ELEMS"][$id]["OFFERS"]=$offersnew[$arElem["ID"]];
    }    
} 
  /*foreach($arResult["PLENKA_ELEMS"] as $id=>$arElem) {
    if($arElem["IBLOCK_SECTION_ID"]==Configurator::PVHPlenka) {
        if($i==1) {
            unset ($arResult["PLENKA_ELEMS"][$id]);
            continue;
        }
        $i++;
    }
    else if ($arElem["IBLOCK_SECTION_ID"]==Configurator::Membrana) {
        if($k==1) {
            unset ($arResult["PLENKA_ELEMS"][$id]);
            continue;
        }
        $k++;
    }
  }/**/