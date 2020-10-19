<?php
// form_type 이 inc 인경우 디자인 맵을 보여주지 않습니다.
if ($designInfo['file']['form_type'] !== 'inc') {
?>
<div class="table-title gd-help-manual">
    <?php if (Request::getFileUri() === 'design_page_edit.php') { echo '기본 ';} ?>레이아웃 설정
</div>
<div id="design_page_map" class="design_page_map">
    <div id="frame_main">

        <!-- outline/_header.htm : Start -->
        <div id="frame_outHeader">
            <div class="design-font-eng pdl15 pdr15">
                outline/_header.html
                <a href="./design_page_edit.php?designPageId=outline/_header.html" class="btn btn-sm btn-link bold pull-right">편집하기</a>
            </div>
        </div>
        <!-- outline/_header.htm : End -->

        <!-- 1. 상단디자인 : Start -->
        <div id="frame_header">
            <div class="ready">
                <div class="form-inline">
                    <span class="design-title">상단영역</span>
                    <select name="outline_header" id="outline_header" onchange="DCMAPM.file_outline( this.name )" class="form-control eng mgl10 mgr">

<?php
    foreach($designInfo['layout']['header'] as $opt){
        echo '<option value="' . gd_isset($opt['value']) . '" ' . gd_isset($opt['selected']) . ' path="' .gd_isset($opt['path']) . '">' .gd_isset($opt['text']) . '</option>';
    }
?>
                    </select>
                    <a href="javascript:designPageMove($('#outline_header option:selected'), '<?php echo $skinType;?>');" class="btn btn-sm btn-link bold">편집하기</a>
                </div>
            </div>
        </div>
        <!-- 1. 상단디자인 : End -->

        <!-- 2. 좌측스크롤 : Start -->
        <div id="frame_scroll" class="scroll-left">
            <div class="ready">
                <p><span class="design-title">스크롤영역</span></p>
                <p>
                    <a href="./design_page_edit.php?designPageId=outline/scroll/scroll_banner_left.html" class="btn btn-sm btn-link bold">편집하기</a>
                </p>
            </div>
        </div>
        <!-- 2. 좌측스크롤 : End -->

        <div id="frame_side_body">
            <!-- 3. 측면디자인 : Start -->
            <div id="frame_side">
                <div class="ready">
                    <div class="form-inline">
                        <p><span class="design-title">측면영역</span> <a href="javascript:designPageMove($('#outline_side option:selected'), '<?php echo $skinType;?>');" class="btn btn-sm btn-link bold">편집하기</a></p>

                        <p>
                            <select name="outline_side" id="outline_side" onchange="DCMAPM.file_outline( this.name )" class="form-control eng">
<?php
    foreach($designInfo['layout']['side'] as $opt){
        echo '<option value="' . gd_isset($opt['value']) . '" ' . gd_isset($opt['selected']) . ' path="' .gd_isset($opt['path']) . '">' .gd_isset($opt['text']) . '</option>';
    }
?>
                            </select>
                        </p>
                    </div>
                </div>
            </div>
            <!-- 3. 측면디자인 : End -->

            <!-- 4. 본문디자인 : Start -->
            <div id="frame_body">
                <div class="ready">
                    <div>
                        <span class="design-title">본문영역</span>
                        <a href="./design_page_edit.php?designPageId=main/index.html" class="btn btn-sm btn-link bold">편집하기</a>
                    </div>
                </div>
            </div>
            <!-- 4. 본문디자인 : End -->
        </div>

        <!-- 5. 우측스크롤 : Start -->
        <div id="frame_scroll" class="scroll-right">
            <div class="ready">
                <p><span class="design-title">스크롤영역</span></p>
                <p>
                    <a href="./design_page_edit.php?designPageId=outline/scroll/scroll_banner_right.html" class="btn btn-sm btn-link bold">편집하기</a>
                </p>
            </div>
        </div>
        <!-- 5. 우측스크롤 : End -->

        <!-- 6. 하단디자인 : Start -->
        <div id="frame_footer">
            <div class="ready">
                <div class="form-inline">
                    <span class="design-title">하단영역</span>
                    <select name="outline_footer" id="outline_footer" onchange="DCMAPM.file_outline( this.name )" class="form-control eng">
<?php
    foreach($designInfo['layout']['footer'] as $opt){
        echo '<option value="' . gd_isset($opt['value']) . '" ' . gd_isset($opt['selected']) . ' path="' .gd_isset($opt['path']) . '">' .gd_isset($opt['text']) . '</option>';
    }
?>
                    </select>
                    <a href="javascript:designPageMove($('#outline_footer option:selected'), '<?php echo $skinType;?>');" class="btn btn-sm btn-link bold">편집하기</a>
                </div>
            </div>
        </div>
        <!-- 6. 하단디자인 : End -->

        <!-- outline/_footer.htm : Start -->
        <div id="frame_outFooter">
            <div class="design-font-eng pdl15 pdr15">
                outline/_footer.html
                <a href="./design_page_edit.php?designPageId=outline/_footer.html" class="btn btn-sm btn-link bold pull-right">편집하기</a>
            </div>
        </div>
        <!-- outline/_footer.htm : End -->

    </div>
    <div class="clear-both"></div>
</div>

<script language="javascript">
var designPage = '<?php echo gd_isset($getPageID); ?>';
var form_type = '<?php echo $designInfo['file']['form_type'];?>';
var float_type = '<?php echo $designInfo['file']['sidefloat'];?>';

/* 측면디자인 위치 */
DCMAPM.file_float(float_type);

/* 본문디자인 파일명 출력 */
if (form_type == 'file'){
    // print file_name
    var pNode = document.createElement('p')
    pNode.innerHTML = designPage;
    _ID('frame_body').getElementsByTagName('div')[0].getElementsByTagName('div')[0].appendChild(pNode);
}
else if (form_type != 'default'){
    _ID('frame_body').getElementsByTagName('a')[0].parentNode.removeChild(_ID('frame_body').getElementsByTagName('a')[0]);
}

/* 편집중 활성화 */
if (form_type == 'outline' && designPage == 'outline/_header.html'){ // outline/_header.htm
    $( '#frame_outHeader' ).css({"background-color": "#777" });
    $( '#frame_outHeader > div' ).css({"color": "#FFFFFF"});
    $( '#frame_outHeader a' ).css({"color": "#FFFFFF"});
    $( '#frame_header > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
    $( '#frame_side > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outSection' && designPage.match(/outline\/header/) != null){ //상단디자인
    $( '#frame_header > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outSection' && designPage.match(/outline\/side/) != null){ //측면디자인
    $( '#frame_side > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'file'){ // 3. 본문디자인
    $( '#frame_body > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outSection' && designPage.match(/outline\/footer/) != null){ //하단디자인
    $( '#frame_footer > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outline' && designPage == 'outline/_footer.html'){ // outline/_footer.htm
    $( '#frame_outFooter' ).css({"background-color": "#777" });
    $( '#frame_outFooter > div' ).css({"color": "#FFFFFF"});
    $( '#frame_outFooter a' ).css({"color": "#FFFFFF"});
    $( '#frame_footer > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
    $( '.scroll-left > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
    $( '.scroll-right > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outSection' && designPage == 'outline/scroll/scroll_banner_left.html') { // outline/_footer.htm
    $( '.scroll-left > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
else if (form_type == 'outSection' && designPage == 'outline/scroll/scroll_banner_right.html') { // outline/_footer.htm
    $( '.scroll-right > div' ).css({"background-color": "#fcfcfc","border-color": "#777"});
}
</script>
<?php
}
?>
