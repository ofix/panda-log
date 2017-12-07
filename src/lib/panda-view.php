<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <title>熊猫日志</title>
    <link href="/company/panda-log/highlight/styles/monokai.css" rel="stylesheet" type="text/css"/>
    <link href="/company/panda-log/panda-log.css" rel="stylesheet">
    <script src="/company/panda-log/jquery-3.2.1.min.js"></script>
    <script src="/company/panda-log/jquery.class.js"></script>
    <script src="/company/panda-log/highlight/highlight.pack.js"></script>
    <script src="/company/panda-log/clipboard.min.js"></script>
</head>
<body>
    <div class="container">
    </div>
</body>

<script>
    var clipboard = new Clipboard('.copy');
    clipboard.on('success', function(e) {
        console.info('Text:', e.text);
        e.clearSelection();
    });
    var iCode =0;
    $(document).ready(function(){
        $.post('/panda/index',{},function(response){
            if(response.data.length===0) return;
            var records = response.data.records;
            for(var i=0,request_count = records.length; i<request_count;i++){
                var request = new Request(records[i][0].debug.time);
                for(var j=0,log_count = records[i].length; j<log_count;j++){
                    var language = new Language(records[i][j].type);
                    var codePiece = new CodePiece(language.parse(),
                       records[i][j].log,records[i][j].debug);
                    request.push(codePiece.render());
                }
                request.render();
            }
            hljs.configure({useBR: true});
            $('div code').each(function(i, block) {
                hljs.highlightBlock(block);
            });
        },'json');
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
        render:function(){
            var s='<div class="request"><div class="request-time">'+this.time.substr(5,14)+'</div>'
                +'<div class="request-items">';
            for(var i=0,len=this.code.length;i<len;i++){
                s+=this.code[i];
            }
            s+='</div></div>';
            $('.container').append(s);
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
            if(path.length>70){
                path = '...'+path.substr(path.length-70,70);
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
                return '<span class="php-num" id="code-i-'+this.iCode+'">'+JSON.stringify(this.log)+'</span>';
            }else{
                return '<code class="'+this.language+'" id="code-i-'+this.iCode+'">'+JSON.stringify(this.log)+'</code>';
            }
        }
    });
</script>
</html>