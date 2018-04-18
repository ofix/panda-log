### Panda Log Document

> panda-log is a debugger tool for flushing program runtime data to binary log file.
 It depends on Yii2 framework, it's lightweight, easy-to-use and user-friendly. Just enjoy it.

**Panda-log Screen Shot**

![PHP code example](http://github.com/ofix/panda-log/raw/master/assets/panda_log_1.png)

![View debug information in browser](http://github.com/ofix/panda-log/raw/master/assets/panda_log_2.png) 

**Install panda-log via composer**

```php
composer require "ofix/panda-log:1.*"
```
 
**Config panda-log as a module in `Yii2`  framework**
```php 
  'bootstrap' => ['panda-log'],
  $config['modules']['panda-log'] = [
      'class' => ofix\PandaLog\Module::class,
      'log_dir'=> '@backend/runtime/panda-log/', // log_dir is the directory panda-log files located on
  ];
```

**How to use panda-log in PHP**
```php
 // log string
 $str = "test for string";
 Panda::log("str",$str);
 // log sql
 $sql = (new Query())->select("some_table")->where(["id"=>4032]);
 Panda::log("sql",$sql);
 // log number
 $num = 323;
 Panda::log("num",$num);
 // log object
 $student = new \stdClass();
 $student->name = "tom";
 $student->age = 28;
 Panda::log("student",$student);
 // log array
 $arr = ["id"=>3223,"mobile"=>13993434];
 Panda::log("arr",$arr);
 
 //flush all above records to log file please call following code
 Panda::flush();
```

**View panda-log data in browser**
>With the above configuration, you will be able to access panda-log in your browser using
 the URL `http://localhost/path/to/index.php?r=panda-log`

>If your application enables [[\yii\web\UrlManager::enablePrettyUrl|pretty URLs]],
you can then access panda-log via URL: `http://localhost/path/to/index.php/panda-log`


**Support or Contact**

Having trouble with panda-log usage? 
contact me with QQ|WeChat `981326632` or send email to 981326632@qq.com
