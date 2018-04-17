panda = (function ($,Clipboard,hljs) {
    var clipboard = new Clipboard('.copy');
    clipboard.on('success', function(e) {
        e.clearSelection();
    });
    var iCode =0,page_offset=0,page_size=100,total=0,loaded=0,select_day =today(false);
    var initialized = false;
    $(document).ready(function(){
        $(document).on('keyup',function(e){
            e = window.event || e || e.which;
            if(e.keyCode === 38){ //page up
                (new ScrollBar()).toTop();
            }else if(e.keyCode === 40){ //page down
                (new ScrollBar()).toBottom();
            }
        });
        var date = new Schedule({
            el: '#date', //指定包裹元素（可选）
            date: today(true), //生成指定日期日历（可选）
            clickCb: function(y, m, d) {
                select_day = genDate(y,m,d);
                $(".container").empty();
                page_offset=0; page_size=100; total =0;loaded=0;initialized = false;
                requestLogData(select_day,true,page_offset,page_size,0);
            }
        });
        requestLogData(today(false),true,page_offset,page_size,0);
    });
    function loadNewData(){
        requestLogData(select_day,true,total,page_size,0);
    }
    function loadOldData(){
        requestLogData(select_day,false,loaded,page_size,1);
    }
    function showRecord(){
        $('#record').empty().html(loaded + "/" + total).show();
    }
    function requestLogData(date,loadNew,pageOffset,pageSize,isAsc){
        var oldScrollHeight = $(document).height();
        $.post(pandaViewUrl,{"date":date,"page_offset":pageOffset,"page_size":pageSize,"asc":isAsc},function(response){
            if(response.data === undefined){
                total = 0;
                showRecord();
                return;
            }
            total =response.data.total;
            if(total === undefined){
                total = 0;
                showRecord();
                return;
            }
            loaded += response.data.records.length;
            var records = response.data.records;
            var requests = [];
            for(var i=0,request_count = records.length; i<request_count;i++){
                var request = new Request(records[i][0].debug.time);
                for(var j=0,log_count = records[i].length; j<log_count;j++){
                    var language = new Language(records[i][j].type);
                    var codePiece = new CodePiece(language.parse(),
                        records[i][j].log,records[i][j].debug);
                    request.push(codePiece.render());
                }
                if(loadNew){
                    requests.push(request);
                }else{
                    requests.unshift(request);
                }
            }
            for(var x=0; x<requests.length;x++){
                requests[x].render(loadNew);
            }
            hljs.configure({useBR: true});
            $('div code').each(function(i, block) {
                hljs.highlightBlock(block);
            });
            if(loadNew&&initialized){
                window.scrollTo(0,oldScrollHeight);
            }else if(!loadNew && initialized){
                var newScrollHeight = $(document).height();
                var height = newScrollHeight-oldScrollHeight;
                if(height>0){
                    window.scrollTo(0,height);
                }
            }else if(loadNew&&!initialized){
                (new ScrollBar()).toBottom();
            }
            if(!initialized){
                initialized = true;
                lazyLoad(loadOldData,loadNewData);
            }
            showRecord();
        },'json');
    }
    var ScrollBar = Class.extend({
        init:function(){
            this.winH = document.documentElement.scrollHeight || document.body.scrollHeight;
        },
        toTop:function(){
            window.scrollTo(0,0);
        },
        toBottom:function(){
            window.scrollTo(0,this.winH);
        }
    });
    var Language = Class.extend({
        init:function(i){
            this.i = i;
        },
        parse:function(){
            switch(this.i){
                case 2:// const RECORD_TYPE_STRING = 2;
                    return 'php';
                case 3://  const RECORD_TYPE_SQL    = 3;
                    return 'sql';
                case 4:// const RECORD_TYPE_ARRAY  = 4;
                case 5:// const RECORD_TYPE_OBJECT = 5;
                case 6:// const RECORD_TYPE_REQUEST= 6;
                case 7:// const RECORD_TYPE_LOGIN  = 7;
                    return 'php';
                case 8:
                    return 'num';
                case 9:
                    return 'bool';
                case 10:
                    return 'null';
                default:
                    return 'json';
            }
        }
    });
    var Request = Class.extend({
        init:function(request_time){
            this.code = [];
            this.time = request_time;
        },
        push:function(code_piece){
            this.code.push(code_piece);
        },
        render:function(loadNew){
            var s='<div class="request"><div class="request-time">'+this.time.substr(5,14)+'</div>'
                +'<div class="request-items">';
            for(var i=0,len=this.code.length;i<len;i++){
                s+=this.code[i];
            }
            s+='</div></div>';
            if(loadNew) {
                $('.container').append(s);
            }else{
                $('.container').prepend(s);
            }
        }

    });
    var CodePiece = Class.extend({
        init:function(language,log,debug){
            this.language = language;
            this.log = log;
            this.debug = debug;
            this.iCode = ++iCode;
        },
        render:function(){
            dbg = this.debug;
            path = dbg.cls+dbg.type+dbg.func;
            if(path.length>60){
                path = '...'+path.substr(path.length-60,60);
            }
            $s  = '<div class="php-path"><div class="flt php-bug"></div><div class="flt">'
                +path
                +'</div><div class="flt line"> '+dbg.line+' </div><div class="flt line-no"></div>'
                +'<div class="flt copy" data-clipboard-target="#code-i-'+this.iCode+'"></div></div>';
            $s += '<div class="code-i"><span class="php-var">'+dbg.args
                +'</span><span class="php-equal">&nbsp;=&nbsp;</span>'+this.code()+'</div>';
            return $s;
        },
        code:function(){
            if(this.language === 'num'){
                return '<span class="php-num" id="code-i-'+this.iCode+'">'+JSON.stringify(this.log)+'</span><br/>';
            }else if(this.language === 'bool'){
                return '<span class="php-bool" id="code-i-'+this.iCode+'">'+(1===this.log?'true':'false')+'</span><br/>';
            }else if(this.language === 'null'){
                return '<span class="php-null" id="code-i-'+this.iCode+'">null</span><br/>';
            }else{
                return '<code class="'+this.language+'" id="code-i-'+this.iCode+'">'+JSON.stringify(this.log)+'</code>';
            }
        }
    });
    function today($withSep) {
        var date = new Date();
        var sep = "";
        if($withSep){
            sep = "-";
        }
        var month = date.getMonth() + 1;
        var strDate = date.getDate();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        return date.getFullYear()+sep  + month+sep  + strDate;
    }
    function genDate(year,month,day){
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (day >= 0 && day <= 9) {
            day = "0" + day;
        }
        return ""+year +month+day;
    }

    function lazyLoad(loadPrev,loadNext) {
        $(window).scroll(function(){
            var scrollTop = $(this).scrollTop();
            var scrollHeight = $(document).height();
            var clientHeight = $(this).height();
            if(scrollTop + clientHeight >= scrollHeight){
                if(loadNext){
                    loadNext();
                }
            }else if(scrollTop<=2){
                if(loadPrev){
                    loadPrev();
                }
            }
        });
    }
})(jQuery,Clipboard,hljs);
