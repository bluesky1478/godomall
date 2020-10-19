<form id="frmBase" action="base_ps.php" method="post" enctype="multipart/form-data" target="ifrmProcess">
    <input type="hidden" name="mode" value="base_seo"/>
    <input type="hidden" name="mallFaviconTmp" value="<?php echo $data['mallFavicon']; ?>"/>
    <input type="hidden" name="stampImageTmp" value="<?php echo $data['stampImage'];?>" />
    <input type="hidden" name="mallSno" value="<?php echo $mallSno;?>" />

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="design-notice-box" style="margin-bottom:20px;">
        <strong>검색엔진 최적화란?</strong><br/>
        검색엔진에서 특정 키워드 등으로 검색을 했을 때, 쇼핑몰을 보다 효과적으로 노출시킬 수 있도록 최적화하는 웹사이트 구성방식입니다.<br/>
        검색엔진 최적화를 통해 검색결과 노출 순위를 높여 내 쇼핑몰과 관련된 키워드로 쇼핑몰을 홍보할 수 있습니다.
    </div>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs mgt10" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./base_seo.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php if ($mallInputDisp === false) { ?>
    <div class="table-title gd-help-manual">
        검색로봇 정보수집 설정
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
            <tr>
                <th>검색로봇 정보수집<br/>허용설정</th>
                <td colspan="3" class="form-inline">
                    <div>
                        <ul class="nav nav-tabs mgt20">
                            <li role="presentation" class="active">
                                <a href="#front" aria-controls="robot-content-front" role="tab" data-toggle="tab">PC 쇼핑몰</a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#mobile" aria-controls="robot-content-mobile" role="tab" data-toggle="tab">모바일 쇼핑몰</a>
                            </li>
                        </ul>
                        <table class="table table-cols" style="border-top-color: #e6e6e6; margin-top: -10px;">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th>검색로봇 접근제어<br/>상세설정<br/>(robots.txt)</th>
                                <td colspan="3" class="form-inline">

                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane fade in active" id="front">
                                            <textarea name="robotsTxt[front]" rows="13" class="form-control width-3xl"><?php echo gd_isset($data['robotsTxt']['front']); ?></textarea>
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade" id="mobile">
                                            <textarea name="robotsTxt[mobile]" rows="13" class="form-control width-3xl"><?php echo gd_isset($data['robotsTxt']['mobile']); ?></textarea>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
    </table>
    <?php } ?>


    <div class="table-title gd-help-manual">
        주요 페이지 SEO 태그 설정
    </div>
    <div >
        <ul class="nav nav-tabs">
            <?php foreach($seoConfig['title'] as $k => $v) { ?>
        <li role="presentation" <?php if($k =='common') { ?>class="active"<?php } ?>>
            <a href="#<?=$k?>Tag" aria-controls="seo-content-tag" role="tab" data-toggle="tab"><?=$v?></a>
        </li>
            <?php } ?>
    </ul>
    </div>
    <div class="tab-content mgb30">
        <?php foreach($seoConfig['title'] as $k => $v) {
            $seoTagData = $seoTagCommonList[$seoConfig['pageGroup'][$k]];
            ?>
            <input type="hidden" name="seo[<?=$k?>][sno]" value="<?php echo $seoTagData['sno']?>"  class="form-control width100p"/>
            <div role="tabpanel" class="tab-pane in <?php if($k =='common') { ?>active<?php } ?>" id="<?=$k?>Tag">
                <div class="pull-right"><button type="button" class="btn btn-sm btn-gray js-code-view" data-target="<?=$k?>">치환코드 보기</button></div>
                <?php if($k =='common') { ?>
                <div class="notice-info">메인 페이지 및 기타페이지에 공통으로 적용됩니다.<br/>“상품, 카테고리, 브랜드, 기획전, 게시판”의 주요 페이지별 SEO 태그 설정을 하지 않았을 경우 공통 설정이 자동으로 적용됩니다.</div>
                <?php } else { ?>
                    <div class="notice-info mgb10">입력하지 않을 경우 공통 항목에 등록된 SEO 태그 설정 정보가 동일하게 적용됩니다.</div>
                <?php } ?>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <?php foreach($seoConfig['tag'] as $k1 =>$v1) { ?>
                        <tr>
                            <th><?=$v1?></th>
                            <td class="form-inline">
                                <input type="text" name="seo[<?=$k?>][<?=$k1?>]" value="<?php echo $seoTagData[$k1]?>"  class="form-control width100p"/>
                                <?php if($k1 =='title' && $k =='common') { ?>
                                    <div class="notice-info">
                                        입력하지 않을 경우 <a class="btn-link" href="./base_info.php" target="_blank">기본설정>기본정책>기본정보설정</a>의 쇼핑몰 기본정보 중 상단타이틀에 등록된 정보가 적용됩니다.
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="js-replace-code-<?=$k?> display-none">
            <table class="table table-rows">
                <thead>
                <tr>
                    <th>번호</th>
                    <th>치환코드</th>
                    <th>설명</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $num = 1;
                foreach($seoConfig['replaceCode'][$k] as $k1 =>$v1) { ?>
                <tr class="text-center">
                    <td><?=$num++?></td>
                    <td><?=$k1?></td>
                    <td><?=$v1?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } ?>
    </div>


    <?php if ($mallInputDisp === false) { ?>
    <h4 class="table-title gd-help-manual">오픈그래프/트위터 메타태그 기본설정</h4>
    <table class="table table-cols mgb10">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>대표이미지</th>
            <td class="form-inline">
                <input type="hidden" name="social[snsRepresentImageTmp]" value="<?php echo $socialData['snsRepresentImage']; ?>"/>
                <input type="file" name="snsRepresentImage" class="form-control"/>
                <span class="small-image">
<?php
if (empty($socialData['snsRepresentImage']) === false) {
    echo gd_html_image(UserFilePath::data('common', $socialData['snsRepresentImage'])->www(), '대표이미지');
    echo '<label class="checkbox-inline" style="padding-left:10px"><input type="checkbox" name="social[snsRepresentImageDel]" value="y" />삭제</label>';
}
?>
				</span>
                <ul class="notice-info">
                    <li>대표 이미지 사이즈는 최소 600pixel(픽셀) 이상, 파일형식은 jpg, gif, png만 등록해 주세요.</li>
                    <li>페이스북에서 권장하는 미리보기 이미지 사이즈는 1200x627px이며 최소 권장 사이즈는 PC에서 400x209px, 모바일에서 560x292px 입니다.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>대표제목<br>(og:title, twitter:title)</th>
            <td>
                <input type="text" name="social[snsRepresentTitle]" value="<?php echo $socialData['snsRepresentTitle']?>"  class="form-control width100p"/>
            </td>
        </tr>
        <tr>
            <th>대표설명<br/>(og:description, twitter:description)</th>
            <td>
                <textarea name="social[snsRepresentDescription]" class="form-control"><?=$socialData['snsRepresentDescription']?></textarea>
                <p class="notice-info">
                    오픈그래프/트위터의 메타태그 설명으로 사용되며, 기본설정의 메타태그 설명과는 별개로 동작합니다.
                </p>
            </td>
        </tr>
    </table>
    <div class="notice-info">
        쇼핑몰 URL을 SNS로 전송시 대표이미지와 쇼핑몰 소개 내용을 설정할 수 있습니다.<br>
        쇼핑몰 상품상세페이지에서 상품정보 SNS공유 시 노출되는 문구는 <a href="../promotion/sns_share_config.php" class="btn-link" target="_blank">“프로모션>SNS서비스 관리>SNS공유하기 설정”</a>에서 설정하실 수 있습니다.<br>
        대표이미지와 대표설명을 설정하지 않는 경우 소셜 정책에 따라 임의의 정보가 노출됩니다.
    </div>
    <div class="notice-info">
        썸네일 이미지 or 텍스트가 변경되지 않으신다면 기존이미지가 캐시로 적용되어 있을 수 있으니<br>
        <a href="https://developers.facebook.com/tools/debug/" class="btn-link" target="_blank">오픈그래프 개체 디버거</a>에 가셔서 "새로운 스크랩 정보 가져오기" 버튼을 눌러 해당 URL을 새롭게 갱신해주세요!
    </div>
    <div class="notice-info mgb30">
        IP 접속제한 설정 (<a href="../policy/manage_security.php" class="btn-link" target="_blank">기본설정 > 관리정책 > 운영 보안 설정</a>)을 통해 SNS의 서버가 위치한 국가 IP를 차단한 경우 설정한 대표이미지, 쇼핑몰 소개 내용이 정상적으로 노출되지 않을 수 있습니다.
    </div>
    <?php } ?>

    <?php if ($mallInputDisp === false) { ?>
    <div class="table-title gd-help-manual">
        사이트맵 설정
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
            <tr>
                <th>사이트맵 경로</th>
                <td colspan="3" class="form-inline">
                    <input type="file" name="sitemap[front]" class="form-control"/>
                    <span>
<?php
if (empty($data['sitemap']['front']) === false) {
    echo '<span>sitemap.xml</span>';
    echo '<label class="checkbox-inline" style="padding-left:10px"><input type="checkbox" name="sitemapDel[front]" value="y" />삭제</label>';
}
?>
				</span>
                    <div class="notice-info">확장자가 .xml 인 파일만 등록 가능하며, 업로드 가능한 파일 크기는 최대 <?php echo ini_get('upload_max_filesize');?>입니다.</div>
                    <div class="notice-info">등록한 파일 경로는 <?php if (empty($data['mallDomain']) === false) { echo 'http://' . $data['mallDomain']; } else { echo '(쇼핑몰주소)';}?>/sitemap.xml 입니다.</div>
                </td>
            </tr>

    </table>
    <?php } ?>


    <?php if ($mallInputDisp === false) { ?>
        <div class="table-title gd-help-manual">
            RSS 설정
        </div>
        <table class="table table-cols mgb30">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr class="js-rss-url" <?php if($data['rss']['useFl'] =='n') { ?>display-none<?php } ?>">
                <th>RSS 경로</th>
                <td class="form-inline">
                    <input type="file" name="rss[front]" class="form-control"/>
                    <span>
        <?php
        if (empty($data['rss']['front']) === false) {
            echo '<span>rss.xml</span>';
            echo '<label class="checkbox-inline" style="padding-left:10px"><input type="checkbox" name="rssDel[front]" value="y" />삭제</label>';
        }
        ?>
				</span>
                    <div class="notice-info">확장자가 .xml 인 파일만 등록 가능하며, 업로드 가능한 파일 크기는 최대 <?php echo ini_get('upload_max_filesize');?>입니다.</div>
                    <div class="notice-info">등록한 파일 경로는 <?php if (empty($data['mallDomain']) === false) { echo 'http://' . $data['mallDomain']; } else { echo '(쇼핑몰주소)';}?>/rss.xml 입니다.</div>
                </td>
            </tr>
        </table>
       <?php } ?>



        <div class="table-title gd-help-manual">
           페이지 경로 설정
        </div>
        <table class="table table-cols mgb30">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>페이지 없음<br/>경로설정</th>
                <td colspan="3">
                    <p class="form-inline">
                        <label class="radio-inline">
                        <input type="radio" name="errPage[useFl]" value="n" onclick="display_toggle('errPage', 'y');" <?php echo gd_isset($checked['errPage']['useFl']['n']); ?> />오류 페이지로 연결
                        </label>
                        <a href="http://<?=$data['mallDomain']?>/goods/go" target="_blank" class="btn btn-gray btn-sm">미리보기</a>
                    </p>
                    <p>
                        <label class="radio-inline">
                        <input type="radio" name="errPage[useFl]" value="y" onclick="display_toggle('errPage', 'n');"  <?php echo gd_isset($checked['errPage']['useFl']['y']); ?> />설정한 경로로 연결
                        </label>
                    </p>

                    <div id="errPage_n" <?php if($data['errPage']['useFl'] =='n') { ?>class="display-none"<?php } ?>>
                        <table class="table table-cols mgt20">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th>PC 쇼핑몰</th>
                                <td class="form-inline">
                                    http://<?=$data['mallDomain']?>/<input type="text" name="errPage[front]" value="<?php echo $data['errPage']['front']; ?>" class="form-control width-lg"/>
                                </td>
                            </tr>
                            <tr>
                                <th>모바일 쇼핑몰</th>
                                <td class="form-inline">
                                    http://m.<?=$data['mallDomain']?>/<input type="text" name="errPage[mobile]" value="<?php echo $data['errPage']['mobile']; ?>" class="form-control width-lg"/>
                                </td>
                            </tr>
                        </table>
                        <div class="notice-info">입력한 경로가 사용중인 스킨 파일 내에 존재하지 않거나 해당 페이지가 없는 경우, 오류 페이지로 자동 연결됩니다.</div>
                    </div>
                </td>
            </tr>
        </table>

    <?php if ($mallInputDisp === false) { ?>
        <div class="table-title gd-help-manual">
            대표 URL(Canonical URL) 설정
        </div>
        <table class="table table-cols mgb30">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="canonicalUrl[useFl]" value="y" <?php echo gd_isset($checked['canonicalUrl']['useFl']['y']); ?> />사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="canonicalUrl[useFl]" value="n" <?php echo gd_isset($checked['canonicalUrl']['useFl']['n']); ?> />사용안함
                    </label>
                </td>
            </tr>
        </table>
    <?php } ?>


    <?php if ($mallInputDisp === false) { ?>
        <div class="table-title gd-help-manual">
            연관채널 설정
        </div>

        <table class="table table-cols " id="tblRelationChannel">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
            </colgroup>
            <tdody>
                <?php if(gd_isset($data['relationChannel']) && count($data['relationChannel'])) { ?>
                    <?php foreach($data['relationChannel'] as $infoKey => $infoValue) { ?>
                        <tr id="tblRelationChannel<?=$infoKey?>">
                            <th>연관채널 <?=$infoKey+1?></th>
                            <td class="center"><input value="<?=$infoValue?>" name="relationChannel[]"  class="form-control" ></td>
                            <td>
                                <?php if($infoKey) { ?>
                                    <button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="remove_relation_channel('tblRelationChannel<?=$infoKey?>');"  >삭제</button>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_relation_channel();"  >추가</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else {  ?>
                    <tr>
                        <th>연관채널 1</th>
                        <td class="center"><input name="relationChannel[]"  class="form-control" placeholder="ex) https://www.facebook.com/nhngodo"></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_relation_channel();">추가</button>
                        </td>
                    </tr>
                <?php } ?>
            </tdody>

        </table>
        <div class="notice-info mgb30">쇼핑몰과 관련된 SNS채널주소를 URL로 입력하시면 네이버 검색결과의 연관채널 부문에 해당 채널이 노출될 수 있습니다.<br/>
            네이버 정책에 따라 네이버 블로그/카페, 스토어팜, 포스트, 폴라, 페이스북, 인스타그램, 아이튠즈, 구글 플레이 스토어만 지원하며 최대 9개 채널만 연동 가능합니다.
        </div>
    <?php } ?>
</form>
<?php if ($mallInputDisp === false) { ?>
    <div class="table-title gd-help-manual">
        기타 페이지 SEO 태그 설정
    </div>

    <div class="js-seo-tag-list">
        <ul class="nav nav-tabs mgt20">
            <li role="presentation" class="active">
                <a href="#seoEtcPagePc" aria-controls="robot-content-front" role="tab" data-toggle="tab">PC 쇼핑몰</a>
            </li>
            <li role="presentation" class="">
                <a href="#seoEtcPageMobile" aria-controls="robot-content-mobile" role="tab" data-toggle="tab">모바일 쇼핑몰</a>
            </li>
        </ul>
        <table class="table table-rows" style="margin-bottom:0px">
            <colgroup>
                <col class="width5p"/><col class="width5p"/><col class="width20p"/><col class="width20p"/><col /><col class="width5p"/>
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" class="js-checkall" data-target-name="sno[]"></th>
                <th>번호</th>
                <th>페이지 경로</th>
                <th>타이틀</th>
                <th>메타태그 설명</th>
                <th class="width5p">수정</th>
            </tr>
            </thead>
        </table>
        <form id="frmBaseSeoTag" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value=""/>
        <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="seoEtcPagePc">
            <table class="table table-rows">
                <colgroup>
                    <col class="width5p"/><col class="width5p"/><col class="width20p"/><col class="width20p"/><col /><col class="width5p"/>
                </colgroup>
                <tbody>
                </tbody>
            </table>
        </div>
        <div role="tabpanel" class="tab-pane fade " id="seoEtcPageMobile">
            <table class="table table-rows">
                <colgroup>
                    <col class="width5p"/><col class="width5p"/><col class="width20p"/><col class="width20p"/><col /><col class="width5p"/>
                </colgroup>
                <tbody>
                </tbody>
            </table>
        </div>
        </div>
        </form>
        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white js-seo-tag-delete">선택 삭제</button>
            </div>
            <div class="pull-right">
                <div class="form-inline">
                    <button type="button" class="btn btn-white  js-seo-tag-regist">페이지 추가</button>
                </div>
            </div>
        </div>
        <div class="center js-seo-tag-page"></div>

    </div>
<?php } ?>


<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $(document).on('click', '.js-seo-tag-regist', function(){
            if($('#seoEtcPagePc').hasClass('active')) {
                var deviceFl = "p";
                var targetDiv = "#seoEtcPagePc";
            }  else {
                var deviceFl = "m";
                var targetDiv = "#seoEtcPageMobile"
            }

            var sno = $(this).data('sno') != undefined ? $(this).data('sno') : '';
            var page = $(this).data('page') != undefined ? $(this).data('page') : '1';


            var params = {
                mallSno : '<?=$mallSno?>',
                sno: sno,
                page: page,
                targetDiv: targetDiv,
                deviceFl: deviceFl
            };
            $.get('layer_seo_tag_register.php', params, function (data) {
                BootstrapDialog.show({
                    title: '기타 페이지 SEO 태그 설정',
                    message: $(data),
                    size: BootstrapDialog.SIZE_WIDE,
                    closable: true
                });
            });
        });

        //기타페이지 탭 관련
        $('.js-seo-tag-list ul.nav a').click(function (e) {
            get_seo_tag_list(1,$(this).attr('href'));
        });

        // 기타펭피지 삭제
        $('.js-seo-tag-delete').click(function () {

            var chkCnt = $('.js-seo-tag-list input[name*="sno"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 페이지가 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 페이지를  정말로 삭제하시겠습니까?', function (result) {
                if (result) {
                    if($('#seoEtcPagePc').hasClass('active')) {
                        var targetDiv = "#seoEtcPagePc";
                    }  else {
                        var targetDiv = "#seoEtcPageMobile"
                    }

                    var params = $("#frmBaseSeoTag").serializeArray();
                    params.push({name: "mode", value: "seo_tag_delete"});
                    $.post('./base_ps.php', params).done(function (data) {
                        alert("삭제 되었습니다.");
                        get_seo_tag_list(1,targetDiv);
                    });
                }
            });

        });


        get_seo_tag_list(1,'#seoEtcPagePc');

        $('.js-code-view').click(function(e){
            BootstrapDialog.show({
                nl2br: false,
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '치환코드 보기',
                message: $(".js-replace-code-"+$(this).data('target')).html()
            });
        });

    });

    function get_seo_tag_list(page,targetDiv) {
        if(targetDiv =='#seoEtcPagePc') {
            var deviceFl = "p";
        }  else {
            var deviceFl = "m";
        }
        $( targetDiv +' table tbody').html("");

        $.post('./base_ps.php', {'mode': 'seo_tag_layer_list', 'deviceFl': deviceFl, 'page': page}, function (data) {
            if(data) {
                var tagList = $.parseJSON(data);
                var num = tagList.index;
                if(num > 0) {
                    $.each(tagList.data, function (key, val) {
                        var addHtml = '';
                        var complied = _.template($('#seoTagTemplate').html());
                        addHtml += complied({
                            path: val.path,
                            title: val.title,
                            description: val.description,
                            num : num,
                            page : page,
                            sno: val.sno
                        });
                        num--;
                        $( targetDiv +' table tbody').append(addHtml);
                    });
                    $('.js-seo-tag-page').html(tagList.pageHtml);
                } else {
                    $( targetDiv +' table tbody').append("<tr><td colspan='6' class='center'>등록된 페이지가 없습니다.</td></tr>");
                }
            }
        });
    }

    function set_seo_tag_list_page(pageLink) {
        var tmpPage = pageLink.split('=');
        if($('#seoEtcPagePc').hasClass('active')) {
            var targetDiv = "#seoEtcPagePc";
        }  else {
            var targetDiv = "#seoEtcPageMobile"
        }
        get_seo_tag_list(tmpPage[1],targetDiv);
    }

    /*
     * 연관채널추가
     */
    function add_relation_channel() {

        var fieldID		= 'tblRelationChannel';
        var fieldNoChk	= $('#'+fieldID+' tr').length;
        if(fieldNoChk < 9) {

            if (fieldNoChk == '') {
                var fieldNoChk	= 0;
            }
            var fieldNo		= parseInt(fieldNoChk);

            var addHtml		= '';
            addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
            addHtml	+= '<th>연관채널 '+(fieldNo+1)+'</th>';
            addHtml	+= '<td class="center"><input type="text" name="relationChannel[]" value="" class="form-control" /></td>';
            addHtml	+= '<td><button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="remove_relation_channel(\''+fieldID+fieldNo+'\');"  >삭제</button></td>';
            addHtml	+= '</tr>';
            $('#'+fieldID).append(addHtml);
        }  else {
            alert("연관 채널은 최대 9개까지 추가할 수 있습니다.");
            return false;
        }
    }


    /*
     * 연관채널삭제
     */
    function remove_relation_channel(fieldID) {
        field_remove(fieldID);
        $('#tblRelationChannel tr').each(function (index) {
            $(this).find('th').text("연관채널 "+(index+1));
        });
    }

    /**
     * 출력 여부
     *
     * @param string thisIdKey 해당 ID Key
     */
    function display_toggle(thisId, thisIdKey) {
        $('div[id*=\''+thisId+'_\']').attr('class','display-none');
        $('#'+thisId+'_'+thisIdKey).attr('class','display-block');
    }
    //-->
</script>


<script type="text/html" id="seoTagTemplate">
    <tr>
        <td class="center"><input type="checkbox" name="sno[]" value="<%=sno%>"/></td>
        <td class="center number"><%=num%></td>
        <td><%=path%></td>
        <td><%=title%></td>
        <td><%=description%></td>
        <td class="center">
            <button type="button" class="btn btn-white btn-xs js-seo-tag-regist" data-sno="<%=sno%>" data-page="<%=page%>" >수정</button>
        </td>
    </tr>
</script>
