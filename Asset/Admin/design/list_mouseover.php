<form name="registform" id="registform" method="post" action="../design/list_mouseover_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red js-group-register" />
        </div>
    </div>

    <div>
        <ul class="nav nav-tabs mgb20">
            <li role="presentation" class="<?= $mode == "main" ? "active" : "" ?>">
                <a href="./list_mouseover.php?mode=main">메인 페이지</a></li>
            <li role="presentation" class="<?= $mode == "goods" ? "active" : "" ?>">
                <a href="./list_mouseover.php?mode=goods">상품 리스트 페이지</a></li>
            <li role="presentation" class="<?= $mode == "brand" ? "active" : "" ?>">
                <a href="./list_mouseover.php?mode=brand">브랜드 리스트</a></li>
            <li role="presentation" class="<?= $mode == "search" ? "active" : "" ?>">
                <a href="./list_mouseover.php?mode=search">검색 리스트</a></li>
            <li role="presentation" class="<?= $mode == "event" ? "active" : "" ?>">
                <a href="./list_mouseover.php?mode=event">기획전 리스트</a></li>
        </ul>

        <h5 class="table-title">효과설정</h5>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>
                    사용설정
                </th>
                <td colspan="3">
                    <div class="radio-inline">
                        <label><input type="radio" name="useFl" value="y" <?php echo $checked['useFl']['y']; ?>>사용함</label>
                        <label><input type="radio" name="useFl" value="n" <?php echo $checked['useFl']['n']; ?>>사용안함</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="center">
                    <div class="thumbnail_img" data-image="<?php echo PATH_ADMIN_GD_SHARE . 'img/godo5_banner_02.jpg'; ?>"><a><img src="<?php echo PATH_ADMIN_GD_SHARE . 'img/godo5_banner_01.jpg'; ?>" /></a></div>
                </td>
            </tr>
            <tr>
                <th>
                    전환효과
                </th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('effectFl', 'effectFl', $effectFl, null, $data['effectFl'], null); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    전환이미지
                </th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('image', 'image', $image, null, $data['image'], null); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    전환속도
                </th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('speedFl', 'speedFl', $speedFl, null, $data['speedFl'], null); ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</form>

<style>
    .thumbnail_img {position:relative; width:600px; height:384px; overflow:hidden; margin:auto; border:none;}
    .thumbnail_img a {display:inline-block; position:relative;}
    .thumbnail_img img {float:left;}
    </style>
<script type="text/javascript">
    <!--
    var listMouseoverOri = {};
    var listMouseoverEffect = '';
    var listMouseoverSpeed = '';
    var listMouseoverWidth = '';
    var listMouseoverHeight = '';

    $(document).ready(function () {
        $('.thumbnail_img').hover(
            function(e){
                e.preventDefault();
                var idx = $('.thumbnail_img').index(this);

                listMouseoverOri = {
                    idx: idx,
                    html: $(this).html(),
                    src: $(this).find('img:eq(0)').prop('src')
                };
                listMouseoverEffect = $('select[name="effectFl"]').val();
                listMouseoverSpeed = Number($('select[name="speedFl"]').val());
                listMouseoverWidth = $(this).find('img:eq(0)').width();
                listMouseoverHeight = $(this).find('img:eq(0)').height();
                goodsImgUrl = $(this).data('image');

                if (goodsImgUrl) {
                    overEvent(listMouseoverOri.idx, goodsImgUrl, listMouseoverEffect, listMouseoverSpeed, listMouseoverWidth, listMouseoverHeight);
                }
            },
            function(e){
                e.preventDefault();
                if (listMouseoverOri.idx >= 0) {
                    oriChange();
                }
            }
        );
        function overEvent(idx, goodsImgUrl, effect, speed, width, height) {
            var $this = $('.thumbnail_img').eq(idx);
            $this.css({
                width: width + 'px',
                height: height + 'px'
            });

            switch (effect) {
                case 'slide':
                    $this.find('a').css('width', ((width * 2) + 6) + 'px');
                    $this.find('a img:eq(0)').after('<img src="' + goodsImgUrl + '" width="' + width + '">');
                    $this.find('a').stop().animate({'left':'-' + width + 'px'}, speed);
                    if ($this.find('.soldout-img').length > 0) {
                        $this.find('.soldout-img').css('width', width + 'px').stop().animate({'left': width + 'px'}, speed);
                    }
                    if ($this.find('.hover').length > 0) {
                        $this.find('.hover').css('width', width + 'px').stop().animate({'left': width + 'px'}, speed);
                    }
                    break;
                case 'fade':
                    $this.find('a img:eq(0)').after('<img src="' + goodsImgUrl + '" width="' + width + '" style="position:absolute; top:0; left:0; opacity:0.0">');
                    $this.find('a img:eq(0)').stop().animate({'opacity': '0.0'}, speed);
                    $this.find('a img:eq(1)').stop().animate({'opacity': '1.0'}, speed);
                    break;
                case 'zoom':
                    $this.find('a img:eq(0)').after('<img src="' + goodsImgUrl + '" style="position:absolute; top:0; left:0; width:' + width + 'px;">');
                    $this.find('a img:eq(1)').stop().animate({'width': (width + (width * 0.1)) + 'px', 'top': '-5%', 'left': '-5%'}, speed);
                    break;
            }
        }
    });

    function oriChange() {
        $('.thumbnail_img').eq(listMouseoverOri.idx).html(listMouseoverOri.html);
        listMouseoverOri = {};
    }
    //-->
</script>