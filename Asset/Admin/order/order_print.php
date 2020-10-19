<style type="text/css">
    .panel {margin-top:15px;margin-bottom:30px;}
    td, th { font-family:돋움; font-size:9pt; color:333333; }
    .red:hover{color:#FF4633;}
    .blue:hover{color:#049BE1;}
    .printall:hover{color:black;}
    .printall{border:1px solid black; color:black; background-color: white; }
    .red {border:1px solid #FF4633; color:#FF4633; background-color: white; }
    .blue {border:1px solid #049BE1; color:#049BE1; background-color: white; }
</style>
<div class="page-header js-affix">
    <h3><?= $popupTitle;?></h3>

    <div class="btn-group" style="padding:2px;">
        <input type="button" value="전체 인쇄" class="btn hidden-print printall" onclick="printAll()">
    </div>
    <?php if ($orderPrintMode == 'reception' || $orderPrintMode == 'particular' || $orderPrintMode == 'taxInvoice') { ?>
    <div class="btn-group" style="padding:2px;">
        <input type="button" value="공급자용 인쇄" class="btn hidden-print red" onclick="red()">
    </div>
    <div class="btn-group" style="padding:2px;">
        <input type="button" value="공급받는자용 인쇄" class="btn hidden-print blue" onclick="blue()">
    </div>
    <?php } ?>
</div>

<div class="hidden-print">
    <div class="panel panel-default">
        <div class="panel-heading">
            인쇄가이드
        </div>
        <div class="panel-body">
            <p>※ 인쇄시 직인(도장이미지)도 인쇄되려면 아래와 같이 설정되어 있어야 가능합니다.</p>
            <ol>
                <li>인터넷 익스플로러 : 브라우저의 [도구 메뉴]-[인터넷옵션]-[고급]-[인쇄] 에서 [배경색 및 이미지 인쇄] 체크</li>
                <li>파이어폭스 : 브라우저의 [파일]-[인쇄화면설정]-[용지 및 설정]-[옵션]에서 [배경 인쇄(색상 및 그림)] 체크</li>
            </ol>
            <?php if ($orderPrintMode == 'report') { ?>
                <p>※ 인쇄시 상품이 많아 내용이 짤린다면 아래와 같이 설정해 주세요.</p>
                <ol>
                    <li>인터넷 익스플로러 : 브라우저의 [파일]-[페이지설정] 에서 [여백]부분을 전부 0으로 설정</li>
                </ol>

            <?php } ?>
            <?php if($orderPrintMode == 'taxInvoice') { ?>
                <p class="notice-danger">발행금액이 0원인 세금계산서는 발행되지 않습니다.</p>
            <?php } ?>
            <div class="center">

            </div>
        </div>
    </div>
</div>

<!-- 영수증 출력 -->
<?php include $layoutForm; ?>
<!-- // 영수증 출력 -->

<script type="text/javascript">

    var btnAll=1;
    var btnBlue = 0;
    var btnRed = 0;

    function red(){
        var blue = document.getElementsByClassName('cssblue');
        var red =  document.getElementsByClassName('cssred');

        for(var i = 0, length = red.length; i < length; i++) {
            if(blue.length!=0) {
                blue[i].style.display = 'none';
            }
            red[i].style.display='block';
        }
        if(btnRed == 0){
            btnRed++;
            btnAll = btnBlue = 0;
        } else {
            window.print();
        }
    }

    function blue(){
        var blue = document.getElementsByClassName('cssblue');
        var red =  document.getElementsByClassName('cssred');

        for(var i = 0, length = blue.length; i < length; i++) {
            blue[i].style.display='block';
            if(red.length!=0) {
                red[i].style.display = 'none';
            }
        }
        if(btnBlue == 0){
            btnBlue++;
            btnAll = btnRed = 0;
        } else {
            window.print();
        }
    }

    function printAll(){
        var blue = document.getElementsByClassName('cssblue');
        var red =  document.getElementsByClassName('cssred');

        if((blue.length!=0) && (red.length!=0)) {
        for(var i = 0, length = blue.length; i < length; i++) {
                blue[i].style.display = 'block';
                red[i].style.display = 'block';
            }
        }
        if(btnAll == 0){
            btnAll++;
            btnBlue = btnRed = 0;
        } else {
            window.print();
        }
    }
</script>
