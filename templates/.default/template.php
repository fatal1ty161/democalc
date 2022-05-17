<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

include_once("include/includeFiles.php");
//if($USER->IsAdmin()) unset($_SESSION['CONFIGURATOR']);
?>
<div class="calculator-wrap">
    <div class="info <?=$arParams['THEME']?>">
        <div class="icon"></div>
        <div class="title"><?= GetMessage("CALCULATE_TITLE") ?></div>
        <div class="text"><?= GetMessage("CALCULATE_TEXT") ?></div>
        <div class="arrow"></div>
    </div>
    <div id="calculator">
        <div class="title"><?=GetMessage("PRUD_SIZE")?></div>

        <div class="props-wrap">
            <input id="length" class="prop required" type="text" data-value="" placeholder="<?=GetMessage("LENGTH")?>" autocomplete="off">
            <input id="width" class="prop required" type="text" data-value="" placeholder="<?=GetMessage("WIDTH")?>" autocomplete="off">
            <input id="depth" class="prop required" type="text" data-value="" placeholder="<?=GetMessage("DEPTH")?>" autocomplete="off">
        </div>

        <div class="title"><?=GetMessage("PLENKA_TYPE")?></div>
        <div class="select-wrap" id="film">
            <div data-price="0" class="input-block">
                <span><?=GetMessage("WITHOUT_PLENKA")?></span>
                <div class="icon"></div>
            </div>
            <div name="firmtype" class="enums">
                <?
                file_put_contents(__DIR__."/logo.txt", print_r($arResult['PLENKA_ELEMS'], true));
                ?>
                <?foreach($arResult['PLENKA_ELEMS'] as $item):                
                    if(!empty($item["OFFERS"])) {
                        foreach($item["OFFERS"] as $offer) {
                            if(empty($offer["PROPERTY_WIDTH_VALUE"]) && empty($offer["PROPERTY_SHIRINA_VALUE"]))
                            continue;
                            $width = $offer['PROPERTY_WIDTH_VALUE'] ? $offer['PROPERTY_WIDTH_VALUE'] : $offer['PROPERTY_SHIRINA_VALUE'];
                            $length = $offer['PROPERTY_LENGTH_VALUE'] ? $offer['PROPERTY_LENGTH_VALUE'] : $offer['PROPERTY_DLINA_VALUE'];
                            if($width<=0) {
                                $width=1;
                            }
                            if($length<=0) {
                                $length=1;
                            }
                            if($item["IBLOCK_SECTION_ID"]==2185) {
                                if($i==1) continue;
                            else $i++;
                            $l++;
                            } else if($item["IBLOCK_SECTION_ID"]==2186) {
                                if($k==1) continue;
                                else $k++;
                                $l++;
                            }
                            ?>
                            <div data-price="<?=$offer['CATALOG_PRICE_4']/($width*$length);?>" data-priceid="<?=$offer['CATALOG_PRICE_4'];?>" class="enum" data-offer="<?=$offer['ID']?>"
                                 data-picture="<?=CFile::GetPath($item['PREVIEW_PICTURE'])?>" data-id="<?=$offer['PROPERTY_CML2_LINK_VALUE']?>"
                                 data-width="<?=$width?>" data-length="<?=$length?>"
                                 data-measure="пог. м"
                                 data-pricefull="<?=$offer['CATALOG_PRICE_4']?>"
                                 <?if(width==1) {?>
                                 data-koef="1"
                                 <?} else {?>
                                 data-koef="2"
                                 <?}?>
                                 >
                                <?=$offer['NAME']?>
                            </div>
                        <?}
                        
                    } else {
                    $width = $item['PROPERTY_WIDTH_VALUE'] ? $item['PROPERTY_WIDTH_VALUE'] : $item['PROPERTY_SHIRINA_VALUE'];
                    $length = $item['PROPERTY_LENGTHRULON_VALUE'] ? $item['PROPERTY_LENGTHRULON_VALUE'] : $item['PROPERTY_DLINA_VALUE'];
                    if($width<=0) {
                        $width=1;
                    }
                    if($length<=0) {
                        $length=1;
                    }
                    ?>
                    <div data-price="<?=$item['CATALOG_PRICE_4']/($width*$length);?>" class="enum" data-priceid="<?=$item['CATALOG_PRICE_4'];?>"  data-offer="<?=$item['ID']?>"
                    data-pricefull="<?=$item['CATALOG_PRICE_4']?>"
                         data-picture="<?=CFile::GetPath($item['PREVIEW_PICTURE'])?>" data-id="<?=$item['ID']?>"
                         data-width="<?=$width?>" data-length="<?=$length?>"
                         data-measure="пог. м"
                         >
                         
                         <?if($width==1) {?>
                                 data-koef="1"
                                 <?} else {?>
                                 data-koef="2"
                                 <?}?>
                        <?=$item['NAME']?>
                    </div>
                    <?}?>
                <?endforeach;?>
            </div>
        </div>

        <div class="title"><?=GetMessage("MATERIAL_TYPE")?></div>
        <div class="select-wrap" id="armor">
            <div data-price="0" class="input-block">
                <span><?=GetMessage("WITHOUT_MATERIAL")?></span>
                <div class="icon"></div>
            </div>
            <div name="firmtype" class="enums <?if(count($arResult['ARMOR_MATERIAL']) > 6) echo 'scroll'?>">
                <div data-price="0" class="enum active" data-type="without" data-priceid="0">
                    <?=GetMessage("WITHOUT_MATERIAL")?>
                </div>
                <?foreach($arResult['ARMOR_MATERIAL'] as $item):
                    $width = $item['PROPERTY_WIDTH_VALUE'] ? $item['PROPERTY_WIDTH_VALUE'] : $item['PROPERTY_SHIRINA_VALUE'];
                    $arWidth = explode(';', $width);
                    $width = $arWidth[0];
                    $length = $item['PROPERTY_LENGTHRULON_VALUE'] ? $item['PROPERTY_LENGTHRULON_VALUE'] : $item['PROPERTY_DLINA_VALUE'];
                    ?>
                    <div data-price="<?=$item['CATALOG_PRICE_4']?>" class="enum" data-priceid="<?=$item['CATALOG_PRICE_4'];?>"
                         data-picture="<?=CFile::GetPath($item['PREVIEW_PICTURE'])?>" data-id="<?=$item['ID']?>"
                         data-width="1" data-length="<?=$length?>"
                         data-measure="<?=$arResult["MEASURES"][$item["CATALOG_MEASURE"]]?>"
                         data-pricefull="<?=$item['CATALOG_PRICE_4']?>"
                         <?if(width==1) {?>
                                 data-koef="1"
                                 <?} else {?>
                                 data-koef="2"
                                 <?}?>                                 
                         >
                        <?=$item['NAME']?>
                    </div>
                <?endforeach;?>
            </div>
        </div>

        <div class="title"><?=GetMessage("MATERIAL_DECODE")?></div>
        <div class="select-wrap" id="decode">
            <div data-price="0" class="input-block">
                <span><?=GetMessage("WITHOUT_DECODE")?></span>
                <div class="icon"></div>
            </div>
            <div name="armortype" class="enums <?if(count($arResult['DECODE_MATERIAL']) > 6) echo 'scroll'?>">
                <div data-price="0" class="enum active" data-type="without" data-priceid="0">
                    <?=GetMessage("WITHOUT_DECODE")?>
                </div>
                <?foreach($arResult['DECODE_MATERIAL'] as $item):
                    $width = $item['PROPERTY_WIDTH_VALUE'] ? $item['PROPERTY_WIDTH_VALUE'] : $item['PROPERTY_SHIRINA_VALUE'];
                    $arWidth = explode(';', $width);
                    $width = $arWidth[0];
                    $length = $item['PROPERTY_LENGTHRULON_VALUE'] ? $item['PROPERTY_LENGTHRULON_VALUE'] : $item['PROPERTY_DLINA_VALUE'];
                    ?>
                    <div data-price="<?=$item['CATALOG_PRICE_4']?>" class="enum" data-priceid="<?=$item['CATALOG_PRICE_4'];?>"
                         data-picture="<?=CFile::GetPath($item['PREVIEW_PICTURE'])?>" data-id="<?=$item['ID']?>"
                         data-width="<?=$width?>" data-length="<?=$length?>"
                         data-measure="<?=$arResult["MEASURES"][$item["CATALOG_MEASURE"]]?>"
                         data-pricefull="<?=$item['CATALOG_PRICE_4']?>"
                         <?if(width==1) {?>
                                 data-koef="1"
                                 <?} else {?>
                                 data-koef="2"
                                 <?}?>
                                 >                         
                        <?=$item['NAME']?>
                    </div>
                <?endforeach;?>
            </div>
        </div>

        <div class="buttons-wrap">
            <div class="clear"><?=GetMessage('CLEAR');?></div>
            <div class="calculate"><?=GetMessage('CALCULATE');?></div>
        </div>

        <?=echoLoaderTemplate()?>
    </div>
    <div class="products-wrap">
        <?foreach($arResult['RESULT_ELEMS'] as $key => $item):?>
            <div class="<?=$key?> block" data-code="<?=$key?>">
                <div class="title-wrap">
                    <div class="title">
                        <div class="table-cell"><?=$item['TITLE']?></div>
                    </div>
                </div>
                <img src="" alt="image" class="image">
                <div class="name" data-title="<?=$item['DATA_TITLE']?>"></div>
                <?/*<div class="price-block-wrap" style="display:none;">
                    <div class="table-block">
                        <div class="table-cell">
                            <div class="square"><span></span> <?=GetMessage('KV_M');?></div>
                            <div class="price-block">
                                <div class="price"></div>
                                <div class="rub">a</div>
                                <div class="meas1"></div>
                            </div>
                        </div>
                    </div>
                </div>/**/?>
            </div>
            <?endforeach;?>
                <h3>Нужно купить:</h3>
            <?foreach($arResult['RESULT_ELEMS'] as $key => $item):?>
            <div class="<?=$key?> block" data-code="<?=$key?>">
                <div class="price-block-wrap">
                    <div class="table-block">
                        <div class="table-cell">
                            <div class="quantity"><span class="quant"></span> <span class="meas1"></span></div>
                            <div class="price-block">
                                <div class="pricefull"></div>
                                <div class="rub">a</div>
                                <div class="meas"></div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?endforeach;?>
                <h3>Сумма:</h3>
            <?foreach($arResult['RESULT_ELEMS'] as $key => $item):?>
            <div class="<?=$key?> block" data-code="<?=$key?>">
                <div class="summ"><div class="summo"></div><div class="rub">a</div></div>
                <div class="toBasket">
                    <?=GetMessage('TO_CART');?>
                </div>
            </div>
            <?endforeach;?>

        <div class="price-wrap">
            <?=GetMessage("PRICE_MESSAGE")?> <span class="sumPrice">0</span> <span class="rub">a</span>
        </div>

        <div class="buy-all"><?=GetMessage('ALL_TO_CART');?></div>
        <?=echoLoaderTemplate()?>
    </div>
    <div class="loadImages" data-background="<?=$templatePath?>/Rectangle-1.png"></div>
    <div class="jqmOverlay"></div>
</div>
