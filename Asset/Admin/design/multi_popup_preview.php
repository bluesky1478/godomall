<style>
    .modal-wide-lg{
        width:<?=($getData['popupSlideViewW']+100)?>px;
    }
</style>
<div style="width:100%">
<table style="width:<?=$getData['popupSlideViewW']?>px;margin:0 auto;">
    <tr>
        <td class="bxslider-wrap" colspan="<?= $getData['widthCount']?>" style="width:<?=$getData['popupSlideViewW']?>px;height:<?=$getData['popupSlideViewH']?>px;position:relative;overflow:hidden;">
            <ul class="multi-popup-bxslider<?=$getData["sno"]?>" style="width:<?=$getData['popupSlideViewW']?>px;height:<?=$getData['popupSlideViewH']?>px;padding:0px;margin:0px;">
                <?php foreach($getData['image'] as $k => $v) {
                    if($v['imageUploadFl'] =='n') $imagePath = "";
                    else $imagePath  = $getData['imagePath'];
                    ?>
                <li><a href="<?=$v['imageLinkUrl']?>" target="<?=$v['imageLinkTarget']?>"><img src="<?=$imagePath.$v['mainImage']?>"  style="width:<?=$getData['popupSlideViewW']?>px;height:<?=$getData['popupSlideViewH']?>px;overflow:hidden" /></a></li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    <tbody  id="multi-popup-pager<?=$getData["sno"]?>" >
    <?php for($i = 0 ; $i < $getData['heightCount']; $i++) { ?>
    <tr >
        <?php for($j = 0 ; $j < $getData['widthCount']; $j++) {
            $sno = $j+($i*$getData['widthCount']);


            if( $getData['image'][$sno]['imageUploadFl']=='n') $imagePath = "";
            else $imagePath  = $getData['imagePath'];


            ?>
        <td class="mp-item slider-pager-<?=$sno?>" style="width:<?=$getData['popupSlideThumbW']?>px;height:<?=$getData['popupSlideThumbH']?>px;overflow:hidden">
            <a  class="js-multi-popup-out js-multi-popup-out<?=$getData["sno"]?>-<?=$sno?>" data-slide-index="<?=$sno ?>" href=""><img src="<?=$imagePath?><?=$getData['image'][$sno]['thumbImage1']?>"  style="width:<?=$getData["popupSlideThumbW"]?>px;height:<?=$getData["popupSlideThumbH"]?>px;overflow:hidden;"/></a>
            <a class="js-multi-popup-over js-multi-popup-over<?=$getData["sno"]?>-<?=$sno ?>" data-slide-index="<?php echo$sno ?>" href="" style="display:none"><img src="<?=$imagePath?><?=$getData['image'][$sno]['thumbImage2']?>" style="width:<?=$getData["popupSlideThumbW"]?>px;height:<?=$getData["popupSlideThumbH"]?>px;overflow:hidden;"/></a>

        </td>
        <?php } ?>
    </tr>
    <?php } ?>
    </tbody>
    <?php if($getData['todayUnSeeFl'] =='y') { ?>
        <tfoot>
            <tr>
                <td  colspan="<?= $getData['widthCount']?>" style="height:30px;line-height:30px;text-align:<?=$getData['todayUnSeeAlign']?>;color:<?=$getData['todayUnSeeFontColor']?>;background:<?=$getData['todayUnSeeBgColor']?>;">오늘 하루 보이지 않음 <input type="checkbox"> </td>
            </tr>
        </tfoot>
    <?php } ?>
</table>
</div>

<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.js"></script>
<link type="text/css" href="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.css" rel="stylesheet"/>

<script>
    $(document).ready(function () {


        $('.multi-popup-bxslider<?=$getData["sno"]?>').slick({
                draggable : false,
                autoplay: true,
                arrows: false,
                <?php if($getData["popupSlideDirection"]=='none'){?>fade: true,<?php }?>
                <?php if($getData['popupSlideDirection'] =='up' || $getData['popupSlideDirection'] =='down') { ?> vertical : true,<?php } ?>
                infinite: true,
                speed: <?=$getData['popupSlideSpeed']*100?>,
                slidesToShow: 1
    });


        $("#multi-popup-pager<?=$getData["sno"]?> a").click(function(e){
            e.preventDefault();
            var slideIndex = $(this).data('slide-index');
            $('.multi-popup-bxslider<?=$getData["sno"]?>').slick('slickGoTo', slideIndex, true);
        });


        $('.multi-popup-bxslider<?=$getData["sno"]?>').on('beforeChange', function(event, slick, currentSlide, nextSlide){
            $(".js-multi-popup-over").hide();
            $(".js-multi-popup-out").show();
            $(".js-multi-popup-out<?=$getData["sno"]?>-"+nextSlide).hide();
            $(".js-multi-popup-over<?=$getData["sno"]?>-"+nextSlide).show();
        });


        $(document).on('mouseover', '#multi-popup-pager<?=$getData["sno"]?> a.js-multi-popup-out', function (e) {
            var index = $(this).data('slide-index');
            $(".js-multi-popup-out<?=$getData["sno"]?>-"+index).hide();
            $(".js-multi-popup-over<?=$getData["sno"]?>-"+index).show();
        });

        $(document).on('mouseleave', '#multi-popup-pager<?=$getData["sno"]?> a.js-multi-popup-over', function (e) {
            var index = $(this).data('slide-index');
            $(".js-multi-popup-out<?=$getData["sno"]?>-"+index).show();
            $(".js-multi-popup-over<?=$getData["sno"]?>-"+index).hide();
        });

        $(".js-multi-popup-out<?=$getData["sno"]?>-0").hide();
        $(".js-multi-popup-over<?=$getData["sno"]?>-0").show();

    });

</script>
