<?php /* Template_ 2.2.7 2016/05/02 11:34:31 D:\wamp\godo5\user\data\skin\front\food_story\proc/_slider_banner.html 000001783 */ ?>
<style>
    .modal {display:block !important;}
    .swiper-container {
        margin: 0 auto;
        position: relative;
        overflow:hidden;
        /* Fix of Webkit flickering */
        z-index: 1;
        width: <?php echo $setData['bannerSize']['width'] . $setData['bannerSize']['sizeType']; ?>;
        <?php if ($setData['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $setData['bannerSize']['height']; ?>px;
        <?php } ?>
    }

    .swiper-wrapper  {
        width: <?php echo $setData['bannerSize']['width'] . $setData['bannerSize']['sizeType']; ?>;
        <?php if ($setData['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $setData['bannerSize']['height']; ?>px;
        <?php } ?>
        overflow:hidden;
    }

    .swiper-wrapper .slick-slide img {
        width: <?php echo $setData['bannerSize']['width'] . $setData['bannerSize']['sizeType']; ?>;
        <?php if ($setData['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $setData['bannerSize']['height']; ?>px;
        <?php } ?>
    }
    .swiper-wrapper .slick-prev , .swiper-wrapper .slick-next {
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
        border:0px;
        background:none;
    }

    .swiper-wrapper   .slick-prev {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M0%2C22L22%2C0l2.1%2C2.1L4.2%2C22l19.9%2C19.9L22%2C44L0%2C22L0%2C22L0%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $setData['bannerSideButton']['activeColor']); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        left: 10px;
        right: auto;
    }
    .swiper-wrapper    .slick-next {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M27%2C22L27%2C22L5%2C44l-2.1-2.1L22.8%2C22L2.9%2C2.1L5%2C0L27%2C22L27%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $setData['bannerSideButton']['activeColor']); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        right: 10px;
        left: auto;
    }

    .swiper-wrapper  .slick-dots {
        position: absolute;
        bottom: 10px;
        display: block;
        width: 100%;
        padding: 0;
        margin: 0;
        text-align: center;
    }
    .swiper-wrapper  .slick-dots li {
        position: relative;
        display: inline-block;
        margin: 0 5px;
        padding: 0;
        cursor: pointer;
    }
    <?php if ($setData['bannerPageButton']['useFl'] == 'c') { ?>
    .swiper-wrapper  .slick-dots li {
        width:<?php echo $setData['bannerDeviceType'] == 'front' ? '12.5' : '25'; ?>%;
        margin:0 !important;
    }
    .swiper-wrapper  .slick-dots li button {
        font-size:12px;
        width:100%;
        height:30px;
        text-align:center;
        background:#000000;
        border-radius:0;
        color:#fff;
        opacity:1;
    }

    .swiper-wrapper .slick-dots li.slick-active button {
         background:#cfcfcf;
    }
    <?php foreach ($setData['bannerImgInfo'] as $key => $value) { ?>
    <?php if ($value['act']) { ?>
    .swiper-wrapper .slick-dots li.slick-active#slick-slide<?php echo sprintf('%02d', $key); ?> {
        width: <?php echo $value['act']['width']?>px !important;
        height: <?php echo $value['act']['height']?>px !important;
    }
    .swiper-wrapper .slick-dots li.slick-active#slick-slide<?php echo sprintf('%02d', $key); ?> button {
        background: url(<?php echo $value['act']['img']?>) no-repeat !important;
        width: <?php echo $value['act']['width']?>px !important;
        height: <?php echo $value['act']['height']?>px !important;
        font-size:0 !important;
    }
    <?php } ?>
    <?php if ($value['inact']) { ?>
    .swiper-wrapper .slick-dots li#slick-slide<?php echo sprintf('%02d', $key); ?>:not(.slick-active) {
        width: <?php echo $value['inact']['width']?>px !important;
        height: <?php echo $value['inact']['height']?>px !important;
    }
    .swiper-wrapper .slick-dots li#slick-slide<?php echo sprintf('%02d', $key); ?>:not(.slick-active) button {
        background: url(<?php echo $value['inact']['img']?>) no-repeat !important;
        width: <?php echo $value['inact']['width']?>px !important;
        height: <?php echo $value['inact']['height']?>px !important;
        font-size:0 !important;
    }
    <?php } ?>
    <?php } ?>
    <?php } else { ?>
    .swiper-wrapper  .slick-dots li button {
        font-size: 0;
        line-height: 0;
        display: block;
        width: <?php echo $setData['bannerPageButton']['size']; ?>px;
        height: <?php echo $setData['bannerPageButton']['size']; ?>px;
        padding: 5px;
        cursor: pointer;
        border: 0;
        outline: none;
        border-radius:<?php echo $setData['bannerPageButton']['radius']; ?>%;
        background: <?php echo $setData['bannerPageButton']['inactiveColor']; ?>;
        opacity:0.75;
    }

    .swiper-wrapper .slick-dots li.slick-active button {
        background: <?php echo $setData['bannerPageButton']['activeColor']; ?>;
        opacity:1;
    }
    <?php } ?>
</style>

<div class="swiper-container">
    <div class="swiper-wrapper">
        <?php foreach($setData['bannerInfo'] as $value) {?>
        <div class="swiper-slide"><?php echo $value; ?></div>
        <?php } ?>
    </div>

    <?php if ($setData['bannerSideButton']['useFl'] === 'y') {?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <?php } ?>
</div>


<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.js"></script>
<link type="text/css" href="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.css" rel="stylesheet"/>
<script>
    $(document).ready(function () {


        $('.swiper-wrapper').slick({
            draggable : false,
            <?php if ($setData['bannerTime'] == 'manual') { ?>
            autoplay: false,
            <?php } else { ?>
            autoplay: true,
            <?php } ?>
            <?php if ($setData['bannerPageButton']['useFl'] !== 'n') {?>
            dots: true,
            <?php }?>
            <?php if ($setData['bannerSideButton']['useFl'] === 'n') {?>
            arrows: false,
            <?php } ?>
            infinite: true,
            autoplaySpeed : <?php echo $setData['bannerSliderTime']; ?>,
            speed: <?php echo $setData['bannerSpeed']; ?>,
            slidesToShow: 1,
            <?php if ($setData['bannerEffect'] === 'fade') {?>
            fade: true,
            <?php }?>
            <?php if ($setData['bannerSize']['sizeType'] === '%') {?>
            adaptiveHeight: true
            <?php }?>
        });

    });

</script>

