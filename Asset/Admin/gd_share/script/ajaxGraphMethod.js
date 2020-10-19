/**
 * Ajax Graph Method
 *
 * Created by qnibus on 2015-10-29.
 */
AGM = {
    bMsg: new Array(),
    iobj: null,
    articles: new Array(),
    running: new Array(),
    interverID: '',

    /**
     * 실행
     *
     * @param object c 콜백
     */
    act: function (c) {
        if (c && typeof (c.onStart) == 'function') {
            this.func = c;
            this.func.onStart(this);
            this.start();
        }
        else
            return;
    },

    /**
     * 시작(프로세스 처리창 구성)
     */
    start: function () {
        this.running = new Array();
        this.clearinterverid();

/* 고도몰5에서는 기존 팝업레이어 사용불가해서 주석처리 cjb3333

        this.layout = '\
    <div class="process">\
    <div class="report">\
      <h1>{title}</h1>\
      <table><tr><th>전송상태</th><td><div class="briefing"><ul><li>브리핑 메시지 샘플.</li></ul></div></td></tr></table>\
      <h2 class="report_step">준비중..</h2>\
      <div class="report_line"><div class="report_white"><div class="report_graph"></div></div></div>\
      <p><!--점선--></p>\
      <div class="report_btn"><span class="button black"><a href="javascript:;">닫기</a></span></div>\
    </div>\
    </div>\
    ';
        this.layout = this.layout.replace(/{title}/, this.layoutTitle);

        layer_ui(this.layout);
*/

        $('.report_graph').css('width', '0%');

        if (this.articles.length < 1) {
            this.briefing(this.bMsg['chkEmpty'], true);
            this.closeBtn();
            return;
        }

        this.briefing(this.bMsg['chkCount'].replace(/__count__/, this.articles.length), true);
        this.briefing(this.bMsg['start']);
        this.request();
    },

    /**
     * Step by step 처리요청
     */
    request: function () {
        if (this.running.length < this.articles.length) { // 전송중
            var idx = this.articles[this.running.length];
            var tmp = new Array();
            tmp.push(idx);
            this.running.push(tmp);
            $('.report_step').text('[' + this.iobj.eq(idx).attr('subject') + '] 내역 처리중');
            this.setIntervalId('AGM.graph()', 500);
            this.func.onRequest(this, idx);
        }
        else if (this.running.length == this.articles.length) { // 전송완료
            this.clearinterverid();
            this.done();
        }
    },

    /**
     * Step by step 처리완료
     */
    complete: function (req) {
        this.running[(this.running.length - 1)].push(true);
        var idx = this.running[(this.running.length - 1)][0];
        var response = req.replace(/{subject}/, this.iobj.eq(idx).attr('subject'));
        this.briefing(response);
        this.setIntervalId("AGM.graph('continue')", 1);
    },

    /**
     * 에러출력
     *
     * @param object XMLHttpRequest
     */
    error: function (XMLHttpRequest) {
        this.running[(this.running.length - 1)].push(false);
        var idx = this.running[(this.running.length - 1)][0];

        if (XMLHttpRequest.status == '404' && XMLHttpRequest.statusText == 'Not Found') {
            this.briefing('[Not Found]\nThe requested URL was not found.');
            this.done();
        }
        else {
            var msg = XMLHttpRequest.responseText.replace(/{subject}/, this.iobj.eq(idx).attr('subject'));
            var remsg = '';
            var tmp = msg.split('^');
            for (i = 0; i < tmp.length; i++) {
                if (i == 1) {
                    remsg += '<ol type="1" class="sub">';
                }
                if (i == 0) {
                    remsg += tmp[i];
                }
                else {
                    remsg += '<li>' + tmp[i] + '</li>';
                }
                if (i > 0 && (i + 1) == tmp.length) {
                    remsg += '</ol>';
                }
            }
            this.briefing(remsg, false, 'red');
            this.setIntervalId("AGM.graph('continue')", 1);
        }
    },

    /**
     * 종료
     */
    done: function () {
        this.briefing(this.bMsg['end']);
        $('.report_step').text('완료');
        this.closeBtn();
        this.clearinterverid();
    },

    /**
     * 닫기버튼정의
     */
    closeBtn: function () {

        $('.process .report .report_btn').show();
    },

    /**
     * Interval 아이디 정의
     */
    setIntervalId: function (func, interval) {
        this.clearinterverid();
        this.interverID = setInterval(func.toString(), interval);
    },

    /**
     * Interval 아이디 제거
     */
    clearinterverid: function () {
        clearInterval(this.interverID);
        this.interverID = '';
    },

    /**
     * 브리핑 출력
     *
     * @param string str 브리핑내용
     * @param bool emtpy ?
     * @param string color 색상
     */
    briefing: function (str, emtpy, color) {
        if (emtpy == true) {
            $('.process .report .briefing ul').empty();
        }
        var li = $('<li>' + str + '</li>').appendTo($('.process .report .briefing ul'));
        if (color != '') {
            li.css({
                'color': color
            });
        }
    },

    /**
     * 그래프
     *
     * @param string code
     */
    graph: function (code) {
        var limitPercent = eval(this.running.length) / eval(this.articles.length) * 100;
        /*
         * 그래프 오류로 실행 중지되어 아래와 같이 수정함 - 2011-09-02 by artherot var nowPercent = eval($('.report_graph').css('width').toString().replace( /%/, '')); if (limitPercent > nowPercent) $('.report_graph').css('width', ++nowPercent + '%'); else if (code == 'continue') this.request();
         */
        var nowPercent = eval($('.report_graph').css('width').toString().replace(/%/, '').replace(/px/, ''));
        $('.report_graph').css('width', +limitPercent + '%');
        if (code == 'continue') this.request();
    }
}
