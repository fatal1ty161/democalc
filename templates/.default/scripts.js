$(document).ready(function () {

    var calculator = $('#calculator'),
        loader = calculator.find('.loader-wrap'),
        url = '#',
        timeAnimate = 300;

    function calc()
    {
        let koeff1=0;
        let koeff2=0;
        if (parseFloat($('#length').attr('data-value'))>=15) {
             koeff1=2;
        } else {
            koeff1=1;
        }
        if (parseFloat($('#width').attr('data-value'))>=15) {
            koeff2=2;
        } else {
            koeff2=1;
        }
		if (parseFloat($('#depth').attr('data-value'))>=2.5) {
			koeff2=2;
			koeff1=2;
		} else {
			koeff2=1;
			koeff1=1;
		}
        var length = parseFloat($('#length').attr('data-value')),
            width = parseFloat($('#width').attr('data-value')),
            depth = parseFloat($('#depth').attr('data-value')),            
            squarePlenka = Math.ceil(((length + (2 * depth) + koeff1) * (width + ( 2 * depth) + koeff2))),            
            squareDecode = (2 * (length + width)).toFixed(0),
            filmLength = length + (2 * depth) + koeff1,
            filmWidth = width + (2 * depth) + koeff2,
            film = $('#film .enum.active'),
            armor = $('#armor .enum.active'),
            decode = $('#decode .enum.active'),
            filmVal = +film.attr('data-price'),
            armorVal = +armor.attr('data-price'),
            decodeVal = +decode.attr('data-price'),
            productsWrap = $('.products-wrap'),
            el = $('#result'),
            sum = 0,
            error = 0;
        el.innerHTML = '';        
        calculator.find("input, textarea").each(function() {
            var val = $(this).val();

            if ($(this).hasClass('required'))
            {
                if(val.length == 0 || val <= 0)
                {
                    error++;
                    $(this).addClass('error');
                }
            }
        });

        if (error == 0)
        {
            loader.fadeIn(timeAnimate);
            el.addClass('normal');            
            
            //sum = filmSquare * filmVal + armorSquare * armorVal + decodeSquare * decodeVal;

            // количество защитного материала на 15% чем пленки, с округлением до целого в большую сторону
            var squareArmor = Math.ceil(+squarePlenka + (squarePlenka * 10 / 100));
            var sum=0;
            var sum1=0;
            sum1=setElemProps(film, productsWrap.find('.film'), squarePlenka, filmLength, filmWidth);            
            if(!isNaN(sum1)) {
                sum+=sum1;
            }            
            sum1=setElemProps(armor, productsWrap.find('.armor'), squareArmor);
            if(!isNaN(sum1)) {
                sum+=sum1;
            }            
            sum1=setElemProps(decode, productsWrap.find('.decode'), squareDecode);
            if(!isNaN(sum1)) {
                sum+=sum1;
            }
            if($(document).width()>1000) {
            $('.calculator-wrap .info').animate({ 'margin-left': '-590px' }, 500);
            }
            $('.sumPrice').text(number_format(sum, 0, ' ', ' '));
            calculator.css('background', 'url(' + $('.loadImages').attr('data-background') + ') center no-repeat');
            loader.fadeOut(timeAnimate);
        }
    }

    function setElemProps(elem, parent, square, filmLength, filmWidth)
    {
        var selector = elem.selector,
            width = elem.attr('data-width') ? +elem.attr('data-width').replace(',', '.') : '',
            length = +elem.attr('data-length');

        // если это декор или защитный материал то длина = 1 ( 1 погонный метр ) ( если она не задана )
        if(selector.indexOf('decode') >= 0 || selector.indexOf('armor') >= 0)
        {
            if(!length || length == 0) length = 1;
        }
        if ((width * length)>0) {
            if (elem.attr('data-koef')==1) {
                var quantity = Math.ceil((square / (width * length))/0.5)*0.5;                
            } else {
                var quantity = Math.ceil(square / (width * length));
            }

            if(typeof(filmLength)!='undefined' && typeof(filmWidth)!='undefined') {
                if(filmLength>quantity && filmWidth<width) {
                    quantity=filmLength;
                } else if(filmWidth>quantity && filmWidth>width) {
                    quantity=filmWidth;
                }
            }

        } else {
            var quantity = 0;            
        }
        if(selector.indexOf('decode') >= 0) {
            quantity=square;
        }
        quantity=Math.ceil(quantity);
        
        var sum=elem.attr('data-priceid')*quantity;
        
        var price=elem.attr('data-priceid');
        if (typeof elem.attr('data-measure')=='undefined') {
            meas='';
            meas1='';
        } else {
            meas=elem.attr('data-measure');
            meas1='/ '+meas;
        }
        if(elem.attr('data-picture')) parent.find('.image').attr('src', elem.attr('data-picture')).fadeIn(timeAnimate);
        else parent.find('.image').fadeOut(timeAnimate);
        parent.find('.name').text(elem.text());
        if(elem.attr('data-id'))
        {
            parent.find('.toBasket').attr('data-id', elem.attr('data-id')).attr('data-offer', elem.attr('data-offer'));
        }
        else
        {
            parent.find('.toBasket').attr('data-id', null).attr('data-offer', null);
        }        
        parent.find('.toBasket').attr('data-quantity', quantity);
        if (meas=='') {
            parent.find('.price-block-wrap').hide();
            parent.find('.summ').hide();
            parent.find('.toBasket').hide();
        } else {
            parent.find('.price-block-wrap').show();
            parent.find('.summ').show();
            parent.find('.toBasket').show();
        }
        
        parent.find('.square span').text(square);
        parent.find('.price').text(number_format(elem.attr('data-price'), 0, ' ', ' '));
        parent.find('.meas1').text(meas);
        parent.find('.meas').text(meas1);
        parent.find('.quant').text(quantity);
        parent.find('.pricefull').text(elem.attr('data-priceid'));
        parent.find('.summo').text(sum);
        
        $('.calculator-wrap .info, .calculator-wrap #calculator, .calculator-wrap .products-wrap').css('height', '750px');
        return sum;
    }

    // формат числа ( цены )
    function number_format( number, decimals, dec_point, thousands_sep ) {	// Format a number with grouped thousands
        var i, j, kw, kd, km;

        if( isNaN(decimals = Math.abs(decimals)) )
            decimals = 2;
        if( dec_point == undefined )
            dec_point = ",";
        if( thousands_sep == undefined )
            thousands_sep = ".";

        i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

        if( (j = i.length) > 3 )
            j = j % 3;
        else
            j = 0;

        km = (j ? i.substr(0, j) + thousands_sep : "");
        kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
        kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

        return km + kw + kd;
    }

    function checkInput(input, flag)
    {
        var value = input.val(),
            rep = '';

        if(flag == 'number') rep = /[^0-9,.]/;

        if(value.indexOf(',') >=0)
        {
            input.val(value.replace(',', '.'));
        }

        if (rep.test(value))
        {
            value = value.replace(rep, '');
            input.val(value);
        }

        input.attr('data-value', input.val());
    }

    // проверяет объект на пустоту
    function isEmpty(object) {
        for (var key in object)
            if (object.hasOwnProperty(key)) return true;

        return false;
    }

    // добавляем в корзину
    $('.products-wrap .toBasket').bind('click', function(){
        var element_id = $(this).attr('data-id'),
            offerId = $(this).attr('data-offer') ? $(this).attr('data-offer') : 0,
            quantity = $(this).attr('data-quantity');

        if(!element_id) return false;

        $('.add_item_frame, .jqmOverlay').removeClass('all');

        addToCart(this, 'detail', 'В корзину', 'cart', '/basket/', element_id, offerId, quantity);
        $('.add_item_frame.popup').html('');
        return false;
    });

    // купить все материалы
    $('.products-wrap .buy-all').bind('click', function(){
        var elemsArr = {},
            url = '/ajax/2016/add_item.php',
            productsWrap = $('.products-wrap'),
            element = productsWrap.find('.block'),
            loader = productsWrap.find('.loader-wrap');

        element.each(function(){
            var obj = {},
                toBasket = $(this).find('.toBasket');

            obj['id'] = toBasket.attr('data-offer') ? toBasket.attr('data-offer') : toBasket.attr('data-id');
            obj['quantity'] = toBasket.attr('data-quantity') ? toBasket.attr('data-quantity') : 1;

            if(!obj['id']) return true;

            elemsArr[$(this).attr('data-code')] = obj;
        });

        if(!isEmpty(elemsArr)) return false;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                elemsArr: elemsArr
            },
            beforeSend: function(){
                loader.fadeIn(timeAnimate);
            },
            success: function(response){
                if(response) {
                    loader.fadeOut(timeAnimate);

                    var itemFrame = $('.add_item_frame');

                    itemFrame.html($(response).html()).addClass('all').fadeIn(timeAnimate);
                    $('.jqmOverlay').addClass('all').fadeIn(timeAnimate);
                    bindClickOnClose();
                }
            }
        });
    });

    function bindClickOnClose()
    {
        $('.jqmClose.close, .jqmOverlay, .proceed').bind('click', function(){
            $('.add_item_frame, .jqmOverlay').fadeOut(timeAnimate);
        });
    }
    bindClickOnClose();

    // запускаем подсчет при изменении параметров
    calculator.find('.calculate').on('click', function(){
        calc();
    });

    // сбрасываем значения параметров
    calculator.find('.clear').on('click', function(){
        var parent = $(this).parents('#calculator');
        
        $('.calculator-wrap .info').animate({ 'margin-left': '0' }, 500);
        $('.calculator-wrap .info').removeAttr('style');
        $('.products-wrap').removeAttr('style');
        $('#calculator').removeAttr('style');
        calculator.css('background', 'url(/local/components/UW/calc/templates/.default/images/Rectangle.png) center no-repeat');

        parent.find('input').val('').attr('data-value', '').removeClass('error').css('background', 'white');
        parent.find('.select-wrap#film span').text("Без пленки");
        parent.find('.select-wrap#armor span').text("Без защитного материала");
        parent.find('.select-wrap#decode span').text("Без декорирования");
        parent.find('.select-wrap .enum').removeClass('active');
        parent.find('.input-block .icon').hide();
        parent.find('.input-block').removeClass('active');
        //parent.find('.select-wrap').find('.enums .enum:first').click();

        /*parent.find('.select-wrap#film').find('.enums .enum').each(function(){
           if($(this).attr('data-type') != 'without') $(this).remove();
        });/**/

        $('.products-wrap').find('.block').each(function(){
            $(this).find('.image').fadeOut(timeAnimate).attr('src', '');
            $(this).find('.name').text($(this).find('.name').attr('data-title'));
            $(this).find('.toBasket').attr('data-id', null).attr('data-offer', null);
            $(this).find('.price').text(0);
            $('.sumPrice').text(0);
        });
    });

    // валидация input
    calculator.find('input.prop')
        .bind('input', function(){
            checkInput($(this), 'number');
        })
        .bind('focusout', function(){
            setValueInput($(this));
        })
        .bind('focusin', function(){
            var val = $(this).attr('data-value');

            $(this).val(val);
        });

    function setValueInput(input)
    {
        var val = input.val(),
            iconUrl = '/local/components/UW/calc/templates/.default/images/input-active-icon.png',
            placeholder = input.attr('placeholder').replace(',', ' - '+val);

        if(val.length > 0)
        {
            input.css('background', 'url('+iconUrl+') 95% 56% no-repeat white');
            input.val(placeholder).attr('data-value', val);
        }
        else
        {
            input.css('background', 'white');
        }
    }

    $(document).on("focus", "input.prop.error", function() {
        $(this).removeClass('error');
        return false;
    });

    // свойсво список
    $('.select-wrap').bind('click', function () {
        var list = $(this).find('.enums');

        if (list.is(':visible')) {
            list.fadeOut(300);
            $(this).removeClass('active');
        }
        else {
            list.fadeIn(300);
            $(this).addClass('active');
        }
    });
    $(document).click(function (e) {
        var block = $('.select-wrap.active .enums');

        if (block.height() > 20) {
            if ($(e.target).closest(".select-wrap").length) return;
            block.stop().fadeOut(300);
            e.stopPropagation();
        }
    });
    $(document).on('click', '.select-wrap .enum', function () {
        var parent = $(this).parents('.select-wrap'),
            icon = parent.find('.icon'),
            dataType = $(this).attr('data-type');

        parent.find('.enum').removeClass('active');
        parent.find('.input-block').removeClass('error').find('span').text($(this).text());
        $(this).addClass('active');
        if(dataType == 'without')
        {
            parent.find('.input-block').removeClass('active');
            icon.hide();
        }
        else
        {
            parent.find('.input-block').addClass('active');
            icon.show();
        }
    });

    // подгружаем типы пленки при выборе глубины
    calculator.find('input.prop').bind('keyup', function(){
        var depth = $('#depth'),
            width = $('#width'),
            length = $('#length'),
            depthVal = depth.attr('data-value') ? depth.attr('data-value') : depth.val(),
            widthVal = width.attr('data-value') ? width.attr('data-value') : width.val(),
            lengthVal = length.attr('data-value') ? length.attr('data-value') : length.val(),
            $this = $(this),
            delay = 500;

        if(!depthVal || !widthVal || !lengthVal) return false;

        clearTimeout($this.data('timer'));

        $this.data('timer', setTimeout(function(){
            $this.removeData('timer');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    depth: depthVal,
                    width: widthVal,
                    length: lengthVal,
                    typePlenkaFlag: true
                },
                beforeSend: function(){
                    loader.fadeIn(timeAnimate);
                },
                success: function(response){
                    if(response) {
                        loader.fadeOut(timeAnimate);

                        var enums = $(response).find('#film .enums'),
                            film = $('#film .enums');

                        film.html(enums.html());
                        if(enums.find('.enum').length > 6)
                        {
                            film.addClass('scroll');
                        }
                        else
                        {
                            film.removeClass('scroll');
                        }

                        // выбираем тип пленки - "без пленки"
                        $('#film .enums .enum:first').click();
                    }
                }
            });

        }, delay));
    });

});