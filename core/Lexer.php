<?php
/*
 * This file is part of panda-log.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    code lighter
 * @copyright code lighter
 * @qq        981326632
 * @wechat    981326632
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @Date: 2017/12/4
 * @Time: 21:37
 */
namespace ofix\panda\core;

class Lexer
{
    private $str;   // 传入的原始utf8字符串
    private $pos;   // 当前解析位置
    private $chars; // 预处理的时候分割出来的单个字符数组
    private $ch;    // 当前字符
    private $ch_cnt;// 字符个数
    private $args;  // 解析出来的参数数组
    private $line_num; //行数
    private $word;  // 当前字符串
    const EOL = -1;
    public function __construct($utf8_str)
    {
        $this->str = $utf8_str;
        $this->pos = 0;
        $this->chars = [];
        $this->ch = '';
        $this->ch_cnt = 0;
        $this->args =[];
        $this->line_num = 0;
        $this->word = '';
    }

    public function parseArgumentList(){
        $this->chars = self::splitUtf8Str($this->str);
        $this->ch_cnt = count($this->chars);
        if($this->ch_cnt == 0){
            return null;
        }
        $this->ch = $this->chars[$this->pos++];
        while($this->ch != self::EOL){
            $this->getToken();
        }
        return $this->args;
    }
    public function getToken(){
        while($this->ch != '('){
            $this->next();
        }
        $this->skipCommentAndWhiteSpace();
        while($this->ch != self::EOL&&$this->ch !=','){
            $this->word .=$this->ch;
            $this->next();
            $this->skipCommentAndWhiteSpace();
        }
        $this->args[] = $this->word;
        $this->word = '';
        $this->next();
    }
    /*
     * 忽略掉空格和注释
     */
    public function skipCommentAndWhiteSpace(){
        while(1) {
            if ($this->ch == ' ' || $this->ch == '\t' || $this->ch == '\r') {
                $this->skipWhitespace();
            }else if($this->ch == '/'){
                $this->next();
                if($this->ch == '*'){
                    $this->skipMultiLineComment();
                }else{
                    $this->prev();
                    $this->ch = '/';
                    break;
                }
            }else{
                break;
            }
        }
    }
    /*
     * @func 过滤函数参数列表中的行内注释
     */
    protected function skipMultiLineComment(){
            $this->next();
            do{
                do{
                    if($this->ch=='\n'||$this->ch =='*'||$this->ch==self::EOL){
                        break;
                    }else{
                        $this->next();
                    }
                }while(1);
                if($this->ch =='\n'){
                    $this->line_num++;
                }else if($this->ch =='*'){
                    $this->next();
                    if($this->ch =='/'){
                        $this->next();
                        return;
                    }
                }else{
                    return;
                }
            }while(1);
    }
    /*
     * @func 获取下一个字符
     */
    public function next(){
        if($this->pos < $this->ch_cnt){
            $this->ch =$this->chars[$this->pos++];
            return;
        }
        $this->ch = self::EOL;
    }
    /*
     * @func 获取上一个字符
     */
    public function prev(){
        $this->ch=$this->chars[$this->pos--];
    }

    /*
     * @func 跳过
     */
    protected function skipWhitespace(){
        while($this->ch==' '||$this->ch=='\t'||$this->ch=='\r'){
            if($this->ch=='\r'){
                $this->next();
                if($this->ch !='\n')
                    return;
                $this->line_num++;
            }
            $this->next();
        }
    }

    /*
     * @func 分割字符串（包含中文）
     * @str utf8字符串
     */
    public static function splitUtf8Str($utf8_str){
        return (preg_split('//u', $utf8_str, null, PREG_SPLIT_NO_EMPTY));
    }
}