<!-- //gnbAnchor_wrap -->
<div class="ly_setting <?=($indexFl == 'y') ? '':'sub_type'?>" <?=($legalRequirementsData['config']['displayFl'] == 'true') ? '' : 'style="display:none;"'?>>
    <div class="setting_header">
        <h4>쇼핑몰 필수 설정</h4>
        <p>
        <?php if(Session::get('manager.isSuper') == 'y') { ?>
            설정이 완료된 항목은 체크해주세요
        <?php } else { ?>
            설정항목체크는 최고운영자만 할 수 있습니다.
        <?php } ?>
        </p>
        <label for="check1" class="check_label">
            <div class="input_check_box">
                <input type="checkbox" name="" value="" id="check1" class="check-origin" <?=($legalRequirementsData['config']['checkedFl'] == 'true') ?'checked=checked' : ''?>>
                <span class="check-clone"></span>
            </div>
            <span class="label_txt">체크된 항목  <?=($legalRequirementsData['config']['checkedFl'] == 'true') ?'노출' : '숨김'?></span>
        </label>
        <ul class="tab_list">
            <li class="active"><a href="#necessary">필수 설정</a></li>
            <li><a href="#operation">쇼핑몰 운영</a></li>
            <li><a href="#recommend">추천 서비스</a></li>
        </ul>
        <span class="btn_close"><img src="../admin/gd_share/img/icon_ly_close.png" alt="닫기 버튼"/></span>
    </div>
    <!-- //setting_header -->
    <div class="inner">
        <div class="cont_wrap">
            <div id="necessary" class="setting_cont">
                <h5>필수 설정</h5>
                <div class="setting_box">
                    <!--N:체크 항목 리스트 노출-->
                    <ul class="setting_list">
                        <?php foreach($legalRequirementsData['list']['necessary'] as $key => $val) { ?>
                        <li>
                            <div class="input_area">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="necessary" value="<?=$key?>" <?=($legalRequirementsData['data']['necessary'][$key] == 'true') ?'checked=checked' : ''?> >
                                    <span></span>
                                </label>
                            </div>
                            <div class="text_area">
                                <a href="<?=$val['url']?>" target="_blank">
                                        <span class="txt">
                                            <strong><?=$val['title']?></strong>
                                            <span class="s_txt"><?=$val['desc']?></span>
                                        </span>
                                </a>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                    <!--//N-->

                    <!--N:모든 항목 숨김 처리시 노출-->
                    <p class="empty_notice" style="display:none;">모든 항목의 설정이 완료 되었습니다.</p>
                    <!--//N-->
                </div>
                <!-- //setting_box -->
            </div>
            <!-- //setting_cont -->

            <div id="operation" class="setting_cont">
                <h5>쇼핑몰 운영</h5>
                <div class="setting_box">
                    <!--N:체크 항목 리스트 노출-->
                    <ul class="setting_list">
                        <?php foreach($legalRequirementsData['list']['operation'] as $key => $val) { ?>
                            <li>
                                <div class="input_area">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="operation" value="<?=$key?>" <?=($legalRequirementsData['data']['operation'][$key] == 'true') ?'checked=checked' : ''?> >
                                        <span></span>
                                    </label>
                                </div>
                                <div class="text_area">
                                    <a href="<?=$val['url']?>" target="_blank">
                                        <span class="txt">
                                            <strong><?=$val['title']?></strong>
                                            <span class="s_txt"><?=$val['desc']?></span>
                                        </span>
                                    </a>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                    <!--//N-->

                    <!--N:모든 항목 숨김 처리시 노출-->
                    <p class="empty_notice" style="display:none;">모든 항목의 설정이 완료 되었습니다.</p>
                    <!--//N-->
                </div>
                <!-- //setting_box -->
            </div>
            <!-- //setting_cont -->

            <div id="recommend" class="setting_cont">
                <h5>추천 서비스</h5>
                <div class="setting_box">
                    <!--N:체크 항목 리스트 노출-->
                    <ul class="setting_list">
                        <?php foreach($legalRequirementsData['list']['recommend'] as $key => $val) { ?>
                            <li>
                                <div class="input_area">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="recommend" value="<?=$key?>" <?=($legalRequirementsData['data']['recommend'][$key] == 'true') ?'checked=checked' : ''?> >
                                        <span></span>
                                    </label>
                                </div>
                                <div class="text_area">
                                    <a href="<?=$val['url']?>" target="_blank">
                                        <span class="txt">
                                            <strong><?=$val['title']?></strong>
                                            <span class="s_txt"><?=$val['desc']?></span>
                                        </span>
                                    </a>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                    <!--//N-->

                    <!--N:모든 항목 숨김 처리시 노출-->
                    <p class="empty_notice" style="display:none;">모든 항목의 설정이 완료 되었습니다.</p>
                    <!--//N-->
                </div>
                <!-- //setting_box -->
            </div>
            <!-- //setting_cont -->

        </div>
    </div>

</div>
<script>

    $(function(){
        var header_h = $('#header').outerHeight();
        var location_h = $('#header .breadcrumb').outerHeight();
        var pageheader_h = $('.page-header').outerHeight();
        var sub_h = header_h + location_h + 76;

        $(window).resize(resizeContents);
        resizeContents();


        function resizeContents() {
            if($('.ly_setting').hasClass('sub_type')){
                if($('.ly_setting').hasClass('on')){
                    var ly_sub_h3 = ($(window).height())-pageheader_h;
                    $(".ly_setting").css({'height':ly_sub_h3});
                }else{
                    var ly_sub_h2 = ($(window).height())-sub_h;
                    $(".ly_setting").css({'height':ly_sub_h2});
                }
            }else{
                if($('.ly_setting').hasClass('on')){
                    $(".ly_setting").css('height','100%');
                }else{
                    var ly_h = ($(window).height())-header_h;
                    $(".ly_setting").css('height', ly_h);
                }
            }
        }

        if($('.ly_setting').hasClass('sub_type')){
            $(".sub_type").css('top', sub_h);
        }else{
            $(".ly_setting").css('top', header_h);
        }

        $(window).scroll(function() {
            var position = $(window).scrollTop();
            if($('.ly_setting').hasClass('sub_type')){
                var ly_sub_h = ($(window).height())-59;
                if(position >= 130) {
                    $(".ly_setting").addClass('on');
                    $(".ly_setting").addClass('active');
                    $(".ly_setting").css({'height':ly_sub_h,'top':59});
                }else{
                    $(".ly_setting").removeClass('on');
                    $(".ly_setting").removeClass('active');
                    resizeContents();
                    $(".sub_type").css('top', sub_h);

                    if(1 <= position <= 129){
                        $(".ly_setting").css({'height':ly_sub_h});
                        $(".sub_type").css('top', sub_h-position);
                    }else{
                        var ly_sub_h2 = ($(window).height())-sub_h;
                        $(".ly_setting").css({'height':ly_sub_h2});
                    }
                }
            }else{
                if(position >= 1) {
                    $(".ly_setting").addClass('on');
                    $(".ly_setting").css({'height':'100%','top':'0px'});
                } else {
                    $(".ly_setting").removeClass('on');
                    resizeContents();
                    $(".ly_setting").css('top', header_h);
                }
            }


        });

        //setting_button
        $('.btn_setting').on('click',function(){
            $('.ly_setting').show();
            listSort();
            var data = {
                'mode': 'saveLegalRequirementsConfig',
                'key': 'displayFl',
                'val': 'true'
            }
            $.ajax('/base/layer_legal_requirements_ps.php', {type: "post", data: data});
        });
        function listSort() {
            var checked = $( ".setting_header .input_check_box input[type=checkbox]" ).is(':checked');
            $('.cont_wrap li').each(function(){
                var parentsId = $(this).parents('div.setting_cont').attr('id');
                if($(this).find('input[type=checkbox]').is(':checked') == true) {
                    var li = $(this).clone();
                    $('#'+parentsId+' ul').append(li);
                    if(checked) $(li).attr('style', "display:none;");
                    $(this).remove();
                }
            }).promise().done(function(){
                if(checked) displayChecked();
                if ($("#necessary li").length == $("#necessary li input[type=checkbox]:checked").length) {
                    if ($("#operation li").length == $("#operation li input[type=checkbox]:checked").length) {
                        if ($("#recommend li").length != $("#recommend li input[type=checkbox]:checked").length) {
                            $('a[href=#recommend]').get(0).click();
                        }
                    } else {
                        $('a[href=#operation]').get(0).click();
                    }
                }
            });
        }

        //close_button
        $('.btn_close').on('click',function(){
            $('.ly_setting').hide();
            var data = {
                'mode': 'saveLegalRequirementsConfig',
                'key': 'displayFl',
                'val': 'false'
            }
            $.ajax('/base/layer_legal_requirements_ps.php', {type: "post", data: data});
        });

        // checkbox checked
        $(document).on('click', '.ly_setting .cont_wrap input[type="checkbox"]', function(event){
            <?php if(Session::get('manager.isSuper') == 'y') { ?>
            var name = $(this).attr('name');
            var key = $(this).val();
            var checked = $(this).is(':checked');
            var data = {
                'mode': 'saveLegalRequirements',
                'name': name,
                'key': key,
                'val': checked
            }
            $.ajax('/base/layer_legal_requirements_ps.php', {type: "post", data: data});
            <?php } else { ?>
            event.preventDefault();
            <?php } ?>
        })

        //check_hidden
        function displayChecked() {
            var checked = $( ".setting_header .input_check_box input[type=checkbox]" ).is(':checked');
            if(checked == true) {
                $('.ly_setting .cont_wrap input[type="checkbox"]:checked').parents('li').hide();
                if($("input[name=necessary]").length == $("input[name=necessary]:checked").length) $('#necessary .empty_notice').show();
                if($("input[name=operation]").length == $("input[name=operation]:checked").length) $('#operation .empty_notice').show();
                if($("input[name=recommend]").length == $("input[name=recommend]:checked").length) $('#recommend .empty_notice').show();
                $(".setting_header .label_txt").text(" 체크된 항목 노출");
            } else {
                $('ul.setting_list li').show();
                $('.ly_setting .empty_notice').hide();
                $(".setting_header .label_txt").text(" 체크된 항목 숨김");
            }
        };

        $(".setting_header .input_check_box input[type=checkbox]").on("click", function() {
            displayChecked();
            var checked = $( ".setting_header .input_check_box input[type=checkbox]" ).is(':checked');
            var data = {
                'mode': 'saveLegalRequirementsConfig',
                'key': 'checkedFl',
                'val': checked
            }
            $.ajax('/base/layer_legal_requirements_ps.php', {type: "post", data: data});
        });
        listSort();
    });
</script>
<!-- //pub_190802 -->

<?php if($indexFl == 'y') { ?>
<div class="gnbAnchor_wrap">
    <span class="btn_setting"><img src="../admin/gd_share/img/button_setting.png" alt="필수설정 버튼"/></span>
</div>
<?php } ?>