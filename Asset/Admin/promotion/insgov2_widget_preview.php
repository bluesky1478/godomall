<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.js"></script>
<link type="text/css" href="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.css" rel="stylesheet"/>
<script type="text/javascript">
    var insgoData = $.parseJSON('<?php echo $insgoData; ?>');
    var widthCountSize = 0;
    var borderSize = 0;
    var speed = 0;
    var size = 0;

    $(function(){
        if (typeof insgoData.thumbnails == 'undefined') return false;
        if (insgoData.data.widgetThumbnailSize == 'auto') {
            if (insgoData.displayType == 'grid') {
                widthCountSize = (insgoData.data.widgetWidthCount * insgoData.data.widgetImageMargin) + Number(insgoData.data.widgetImageMargin);
                borderSize = (insgoData.data.widgetThumbnailBorder == 'y' ? insgoData.data.widgetWidthCount * 2 : 0);
                size = Math.floor((790 - widthCountSize - borderSize) / insgoData.data.widgetWidthCount);
            } else {
                size = 150;
            }
        } else {
            size = Number(insgoData.data.widgetThumbnailSizePx);
        }

        var imageType = getImageSize(size);
        //이미지 사이즈
        var imageStyle = 'width:' + size + 'px; height:' + size + 'px;';
        //이미지 테두리
        imageStyle += (insgoData.data.widgetThumbnailBorder == 'y' ? 'border:1px solid #ACACAC;' : 'border:none;');
        var marginLeft = (insgoData.data.widgetImageMargin > 0 && insgoData.data.displayType == 'grid' ? 'margin-left:' + insgoData.data.widgetImageMargin + 'px;' : 'margin-left:0;');
        var marginTop = (insgoData.data.widgetImageMargin > 0 && insgoData.data.displayType == 'grid' ? 'margin-top:' + insgoData.data.widgetImageMargin + 'px;' : 'margin-top:0;');

        var insgoWidgetHtml = '';
        $.each(insgoData.thumbnails, function(index, thumb){
            var imageTag = '<a href="' + thumb.viewUrl + '" target="_blank"><img src="' + thumb.image[imageType]['url'] + '" style="cursor:pointer; ' + imageStyle + marginLeft + marginTop + '" /></a>';
            if (insgoData.displayType == 'grid' && index % insgoData.data.widgetWidthCount == (insgoData.data.widgetWidthCount - 1)) imageTag += '<br />';
            insgoWidgetHtml += imageTag;
        });
        $('.inso-widget-data').append(insgoWidgetHtml);

        if (insgoData.data.widgetOverEffect != 'n') {
            $('.inso-widget-data img').hover(
                function(){
                    var idx = $('.inso-widget-data img').index(this);
                    if (insgoData.data.widgetOverEffect == 'blurPoint') {
                        $('.inso-widget-data img').eq(idx).stop().fadeTo(50, 0.3);
                    } else if (insgoData.data.widgetOverEffect == 'blurException') {
                        $('.inso-widget-data img').not(':eq(' + idx + ')').stop().fadeTo(50, 0.3);
                    }
                },
                function(){
                    var idx = $('.inso-widget-data img').index(this);
                    if (insgoData.data.widgetOverEffect == 'blurPoint') {
                        $('.inso-widget-data img').eq(idx).stop().fadeTo(50, 1);
                    } else if (insgoData.data.widgetOverEffect == 'blurException') {
                        $('.inso-widget-data img').not(':eq(' + idx + ')').stop().fadeTo(50, 1);
                    }
                }
            );
        }

        switch (insgoData.displayType) {
            case 'grid':
                if (insgoData.data.widgetImageMargin > 0) {
                    $('.inso-widget-area, .inso-widget-data').css('padding-bottom', insgoData.data.widgetImageMargin + 'px');
                }
                break;
            case 'scroll':
            case 'slide':
                switch (insgoData.data.widgetScrollSpeed) {
                    case 'fast':
                        speed = 1000;
                        break;
                    case 'normal':
                        speed = 2000;
                        break;
                    case 'slow':
                        speed = 3000;
                        break;
                }
                var setting = {
                    draggable : false,
                    infinite: true,
                    autoplaySpeed : speed,
                    speed: (insgoData.displayType == 'scroll') ? speed : (insgoData.data.widgetScrollTime * 1000),
                    slidesToShow: (insgoData.displayType == 'scroll') ? Math.floor((insgoData.data.widgetWidth > size ? insgoData.data.widgetWidth : size) / (size + borderSize)) : 1,
                    slidesToScroll: 1
                };

                if (insgoData.displayType == 'scroll') {
                    $('.inso-widget-area, .inso-widget-data').css({'width': (insgoData.data.widgetWidth > size) ? insgoData.data.widgetWidth : size + 'px', 'margin': 'auto'});

                    if (insgoData.data.widgetAutoScroll == 'auto') {
                        setting['autoplay'] = true;
                        setting['arrows'] = false;
                        setting['prevArrow'] = '';
                        setting['nextArrow'] = '';
                    } else {
                        setting['false'] = true;
                        setting['arrows'] = true;
                        var insgoMove = '';
                        $('.inso-widget-data').on('mouseenter', '.slick-prev, .slick-next', function(){
                            var arrow = $(this).hasClass('slick-prev') ? 'slickPrev' : 'slickNext';
                            var insgoMoveFunc = function(){
                                $('.inso-widget-data').slick(arrow);
                            };
                            insgoMove = setInterval(insgoMoveFunc, speed);
                            insgoMoveFunc();
                        });
                        $('.inso-widget-data').on('mouseleave', '.slick-prev, .slick-next', function(){
                            clearInterval(insgoMove);
                        });
                    }
                } else {
                    $('.inso-widget-area').css({'width': size + 'px'});
                    $('.inso-widget-data').css({'width': size + 'px'});

                    setting['autoplay'] = true;
                    setting['arrow'] = false;
                    setting['prevArrow'] = '';
                    setting['nextArrow'] = '';
                    if (insgoData.data.widgetEffect == 'fade') {
                        setting['fade'] = true;
                    }
                }

                $('.inso-widget-data').slick(setting);
                break;
        }

        if(insgoData.data.widgetBackgroundColor) {
            $(".slider-wrap").attr("style", "background-color:" + insgoData.data.widgetBackgroundColor + ";");
        }
    });

    function getImageSize(size) {
        var type = '';
        if (size <= 150) {
            type = 'thumbnail';
        } else if (size <= 320) {
            type = 'low_resolution';
        } else {
            type = 'standard_resolution';
        }

        return type;
    }
</script>
<style>
    .modal {
        display: block;
    }
    .modal-body {
        overflow-y:auto;
    }
    .inso-widget-area {
        width:790px;
    }
    .inso-widget-data  {
        margin: 0 auto;
        overflow:hidden;
    }

    .inso-widget-data .slick-slide img {
        position:static !important;
        top:0px  !important;
    }
    .inso-widget-data .slick-prev , .inso-widget-data .slick-next {
        font-size: 0;
        line-height: 0;
        position: absolute;
        top: 45%;
        display: block;
        width: 27px;
        height: 44px;
        padding: 0;
        -webkit-transform: translate(0, -45%);
        -ms-transform: translate(0, -45%);
        transform: translate(0, -45%);
        cursor: pointer;
        z-index:10;
        background:none;
        border:none;
    }

    .inso-widget-data .slick-prev {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M0%2C22L22%2C0l2.1%2C2.1L4.2%2C22l19.9%2C19.9L22%2C44L0%2C22L0%2C22L0%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $widgetSideButtonColor); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        left: 10px;
        right: auto;
    }
    .inso-widget-data .slick-next {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M27%2C22L27%2C22L5%2C44l-2.1-2.1L22.8%2C22L2.9%2C2.1L5%2C0L27%2C22L27%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $widgetSideButtonColor); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        right: 10px;
        left: auto;
    }
</style>
<div class="notice-info">인스타그램 정책에 따라 일 통신횟수가 제한될 수 있으며, 어드민 미리보기 갱신 역시 일정시간(최소 1시간) 후 가능합니다.</div>
<div class="inso-widget-area">
    <div class="inso-widget-data slider-wrap"></div>
</div>
