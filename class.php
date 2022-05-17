<?
use Bitrix\Main,
    Bitrix\Sale,
    Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Web\Json,
    Bitrix\Iblock\PropertyTable,
    Bitrix\Iblock\ElementTable,
    Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

Loc::loadMessages(__FILE__);

if (!Loader::includeModule("iblock")) {
    ShowError(Loc::getMessage("IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

CJSCore::Init(array("jquery"));

class Configurator extends CBitrixComponent
{
    const cacheTime = 36000000;
    const fileLogName = "calc_log.txt";
    const fileLogPath = "kalkulyator-plenki";
    const maxFileSize = 1000000;                                  # размер ( байт ) после которого обновляем файл логов
    const propText = 'PROPERTY_';
    const iblockId = 17;
    const iblockOffersId = 27;
    const priceCode = 4;
    //const plenkaSections = [];                                    # id разделов, из которых будем брать пленки
    const armorSection = 2866;
    const PVHPlenka = 2870;
    const Membrana = 2869;
    const decodeSections = [2871, 2868];

    public $arResult, $arParams, $arFilterProd;
    private $width, $length;
    private $excludeSections = [self::PVHPlenka, self::Membrana];

    public function onPrepareComponentParams($arParams)
    {
        if (!isset($arParams["CACHE_TIME"])) {
            $arParams["CACHE_TIME"] = self::cacheTime;
        }

        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);

        if (strlen($arParams["IBLOCK_TYPE"]) <= 0) {
            $arParams["IBLOCK_TYPE"] = self::iblockType;
        }

        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);
        if(empty($arParams["IBLOCK_ID"])) {
            throw new Exception('Не указан ID инфоблока!');
        }
        $this->iblockId = $arParams["IBLOCK_ID"];

        return $arParams;
    }

    private function getRequest()
    {
        $values = false;
        $request = Context::getCurrent()->getRequest();
        $flagPost = $request->isPost();

        if (!empty($flagPost)) {
            $values = $request->getPostList();
            $values = $this->object_to_array($values);
        }

        return $values;
    }

    private function object_to_array($data)
    {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }

    private function saveLogsToFile($flag)
    {
        if ($flag == 'begin') {
            self::writeToImportLog("Начало работы конфигуратора! " . date("Y-m-d H:i:s") . PHP_EOL, true);
        } elseif ($flag == 'end') {
            self::writeToImportLog("Конец работы конфигуратора! " . date("Y-m-d H:i:s") . PHP_EOL);
            self::writeToImportLog("/*--------------------------------------------------------------------*/" . PHP_EOL . PHP_EOL . PHP_EOL);
        }

        return;
    }

    private static function writeToImportLog($text, $clearFile = false)
    {
        if (!empty($text)) {
            file_put_contents(self::getTmpTimestampFile($clearFile), $text . PHP_EOL, FILE_APPEND);
        }
    }

    private static function getTmpTimestampFile($clearFile = false)
    {
        $server = Context::getCurrent()->getServer();
        $file = $server->getDocumentRoot() . "/" . self::fileLogPath . "/" . self::fileLogName;
        $size = (int)filesize($file);
        if ($size > self::maxFileSize && !empty($clearFile)) {
            fopen($file, "w");
        }

        return $file;
    }

    public function executeComponent()
    {

        if ($this->startResultCache()) {

            #$this->saveLogsToFile('begin');

            # устанавливаем начальные параметры $arParams
            $this->onPrepareComponentParams($this->arParams);

            # получаем массив $_POST
            $postArr = $this->getRequest();

            # калькулятор
            $this->calculate($postArr);

            #self::writeToImportLog($stepNumber);

            #$this->saveLogsToFile('end');

            $this->includeComponentTemplate();
        }

        return $this->arResult;
    }

    private function calculate($postArr)
    {
        # получаем тип пленки
        /*if(!empty($postArr['typePlenkaFlag']))
        {
            $this->arResult['PLENKA_ELEMS'] = $this->getPlenkaElems(self::PVHPlenka, self::Membrana);
        }/**/
        $this->arResult['PLENKA_ELEMS'] = $this->getPlenkaElems(self::PVHPlenka, self::Membrana);        
        # получаем тип защитного материала
        $this->arResult['ARMOR_MATERIAL'] = $this->getArmorMaterialElems(self::armorSection);

        # получаем тип защитного материала
        $this->arResult['DECODE_MATERIAL'] = $this->getDecorMaterialElems(self::decodeSections);

        # формируем массив элементов для вывода
        $this->arResult['RESULT_ELEMS'] = $this->setArrResultElems();
    }

    private function setArrResultElems()
    {
        return $resArr = [
            'film' => [
                'TITLE' => GetMessage('PLENKA_TITLE')
            ],
            'armor' => [
                'TITLE' => GetMessage('ARMOR_TITLE')
            ],
            'decode' => [
                'TITLE' => GetMessage('DECODE_TITLE')
            ],
        ];
    }

    private function getDecorMaterialElems($sectionId)
    {
        $elems = $this->getElemsFromFilter(['SECTION_ID' => $sectionId]);

        return $elems;
    }

    private function getArmorMaterialElems($sectionId)
    {
        $elems = $this->getElemsFromFilter(['SECTION_ID' => $sectionId]);

        return $elems;
    }

    private function getPlenkaElems($Plenka, $Membrana)
    {
        
        //$arFilter = $this->setArFilterForDepth($depth);
        //file_put_contents(__DIR__."/log2.txt", print_r($arFilter, true));
        /*if(!empty($arFilter))
        {
            $offers = $this->getOffersOFProducts($arFilter);
        }
        
        /**/
        $elems = $this->getElemsFromFilter([['LOGIC'=>'OR', ['SECTION_ID' => $Plenka], ['SECTION_ID' => $Membrana]]], 'PLENKA');
        
        file_put_contents(__DIR__."/log3.txt", print_r($elems, true));
        return $elems;
    }

    private function setArFilterForDepth($depth, $arFilter = false)
    {
        $minValue = 0;
        $maxValue = 0;

        if($depth <= 1)
        {
            $minValue = 0.5;
            $maxValue = 0.8;
        }
        elseif($depth >= 1 && $depth <= 2)
        {
            $minValue = 0.8;
            $maxValue = 1;
        }
        elseif($depth >= 2 && $depth <= 3)
        {
            $minValue = 0.9;
            $maxValue = 1;
            $this->excludeSections = [self::Membrana];
        }
        elseif($depth > 3)
        {
            $minValue = 1;
            $maxValue = 1;
            $this->excludeSections = [self::Membrana];
        }

        if(!empty($minValue))
        {
            $this->arFilterProd = ['>=PROPERTY_TOLSHCHINA' => $minValue, '<=PROPERTY_TOLSHCHINA' => $maxValue]; // свойства товара
            $arFilter = ['>=PROPERTY_THICKNESS' => $minValue, '<=PROPERTY_THICKNESS' => $maxValue];  // свойства торговых предложений
        }

        return $arFilter;
    }

    private function getElemsFromFilter($arFilter, $type='')
    {
        $elems = [];
        if($type=='PLENKA') {
            $sort=array("IBLOCK_SECTION_ID"=>"ASC", "property_TOLSHCHINA"=>"ASC");
        } else {
            $sort=array('SORT' => 'ASC');
        }
        $iblockArr = ['IBLOCK_ID' => self::iblockId, 'ACTIVE' => 'Y', 'INCLUDE_SUBSECTIONS' => 'Y', 'PROPERTY_AVAILABLE'=>'1'];
        $arFilter = array_merge($iblockArr, $arFilter);
        $arFilter["!NAME"] = array("%отрез %", "%рулон%", "%готовый отрез%");                
        $rsElements = CIBlockElement::GetList(
            $sort,
            $arFilter,
            false,
            false,
            [
                'ID', 'NAME', 'PREVIEW_PICTURE',
                'IBLOCK_SECTION_ID',
                'CATALOG_GROUP_'.self::priceCode,
                'PROPERTY_WIDTH',
                'PROPERTY_LENGTH',
                'PROPERTY_THICKNESS',
                'PROPERTY_LENGTHRULON',
                'PROPERTY_SHIRINA',
                'PROPERTY_TOLSHCHINA'
            ]
        );
        
        while($elemArr = $rsElements->GetNext())
        {
            $elems[$elemArr["ID"]] = $elemArr;            
        }        
        return $elems;
    }

    private function getOffersOFProducts($arFilter, $offersArr = [])
    {
        # ищем предложения с такой шириной, длиной, толщиной
        $resOffers = $this->getOffersForWidthLengthAndThickness($arFilter);

        # если не нашли, то ищем с такой шириной, длиной
        //if(!$resOffers->GetNext()) $resOffers = $this->getOffersForWidthLength();

        # если не нашли, то ищем с такой шириной
        //if(!$resOffers->GetNext()) $resOffers = $this->getOffersForWidthAndThickness($arFilter);

        $elemsIds = [];
        while($offer = $resOffers->GetNext())
        {
            $offersArr[] = $offer;
            $elemsIds[] = $offer['PROPERTY_CML2_LINK_VALUE'] ? $offer['PROPERTY_CML2_LINK_VALUE'] : $offer['ID'];
        }

        # получаем картинки товаров
        $offersArr = $this->getImagesOfProductFromOffer($elemsIds, $offersArr,  $this->excludeSections);

        return $offersArr;
    }

    private function getOffersForWidthLengthAndThickness($arFilter)
    {
        # фильтр для торговых предложений
        $arFilterOffers = array_merge(
            [/*'PROPERTY_LENGTHRULON' => $this->length, /**/'PROPERTY_WIDTH' => $this->width],
            $arFilter
        );

        # фильтр для товаров
        $arFilterProducts = array_merge(
            [/*'PROPERTY_DLINA' => $this->length, /**/'PROPERTY_SHIRINA' => $this->width],
            $this->arFilterProd
        );
        file_put_contents(__DIR__."/elems.txt", print_r($elems, true), FILE_APPEND);
        $iblockArr = [
            'IBLOCK_ID' => [self::iblockId, self::iblockOffersId],
            'ACTIVE' => 'Y',
            [
                "LOGIC" => 'OR',
                $arFilterOffers,
                $arFilterProducts,
            ]
        ];
        file_put_contents(__DIR__."/log1.txt", print_r($elems, true));
        return $this->getOffersObject($iblockArr);
    }

//    private function getOffersForWidthLength()
//    {
//        $iblockArr = [
//            'IBLOCK_ID' => self::iblockOffersId,
//            'ACTIVE' => 'Y',
//            'PROPERTY_WIDTH' => $this->width,
//            'PROPERTY_LENGTH' => $this->length
//        ];
//
//        return $this->getOffersObject($iblockArr);
//    }

    private function getOffersForWidthAndThickness($arFilter)
    {
        $iblockArr = [
            'IBLOCK_ID' => self::iblockOffersId,
            'ACTIVE' => 'Y',
            '=PROPERTY_WIDTH' => $this->width
        ];
        $arFilter = array_merge($iblockArr, $arFilter);

        return $this->getOffersObject($arFilter);
    }

    private function getOffersObject($arFilter)
    {
        return CIblockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'CATALOG_GROUP_'.self::priceCode,
                'PROPERTY_WIDTH',
                'PROPERTY_LENGTHRULON',
                'PROPERTY_THICKNESS',
                'PROPERTY_DLINA',
                'PROPERTY_SHIRINA',
                'PROPERTY_TOLSHCHINA',
                'PROPERTY_CML2_LINK'
            ]
        );
    }

    private function getImagesOfProductFromOffer($elemsIds, $offersArr, $excludeSections)
    {
        $elemsArr = $this->getElemsFromFilter(['ID' => $elemsIds]);

        foreach($elemsArr as $keyElem => $elem)
        {
            foreach ($offersArr as $keyOffer => $offer)
            {
                if($elem['ID'] == $offer['PROPERTY_CML2_LINK_VALUE'])
                {
                    $offersArr[$keyOffer]['PREVIEW_PICTURE'] = $elem['PREVIEW_PICTURE'];
                }
                $offersArr[$keyOffer]['IBLOCK_SECTION_ID'] = $elem['IBLOCK_SECTION_ID'];
            }
        }
        
        # удаляем элементы которые не принадлежат разделам из массива $excludeSections
        $offersArr = $this->removeElementsByExcludeSections($offersArr, $excludeSections);
        foreach($offersArr as $offer) {
            $offersNew[$offer["PROPERTY_CML2_LINK_VALUE"]][]=$offer;
        }

        return $offersNew;
    }

    private function removeElementsByExcludeSections($offersArr, $excludeSections, $resultArr = [])
    {
        foreach ($offersArr as $keyOffer => $offer)
        {
            if (in_array($offer['IBLOCK_SECTION_ID'], $excludeSections))
            {
                $resultArr[] = $offer;
            }
        }

        return $resultArr;
    }
}

