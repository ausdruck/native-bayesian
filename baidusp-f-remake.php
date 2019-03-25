<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN" xml:lang="zh-CN">
<head>
<meta content="text/html;charset=UTF-8" http-equiv="Content-Type" />
<meta content="initial-scale=1,maximum-scale=1,user-scalable=0,width=device-width" name="viewport" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="format-detection" content="telephone=no" />

<title>百度搜索结果F系列参数</title>
<meta content="百度搜索结果参数,F,F1,F2,F3" name="keywords" />
<meta content="判断百度搜索结果 F 系列参数。" name="description" />
<link rel="author" href="https://plus.google.com/109624994087248289296/posts" />
<link rel="canonical" href="http://www.weixingon.com/baidusp-f.php" />
<style type="text/css">
body,div,h1{
margin:0;
}
body{
color:#222;
background-color:#f8f7f5;
font-family:"STHeiti STXihei","Lucida Grande","Microsoft JhengHei","Microsoft Yahei",Helvetica,Tohoma,Arial,Verdana,sans-serif;
height:100%;
}
table{
width:61.25em;
border-collapse:collapse;
border:0.0625em solid #390BDE;
}
thead{
color:#0080FF;
background-color:#F2F2F2;
}
th,td{
border:0.0625em solid #390BDE;
padding:0.1875em;
}
a{
color:#607fa6;
font-size:1em;
text-decoration:none;
text-shadow:0 .0625em #fff
}
input{
font:normal 100% "STHeiti STXihei","Lucida Grande","Microsoft JhengHei","Microsoft Yahei",Helvetica,Tohoma,Arial,Verdana,sans-serif;
}
.text{
padding:0.125em 0.3125em 0.25em 0.3125em;
height:1.375em;
width:32.25em;
outline:none;
background:url(http://www.baidu.com/img/i-1.0.0.png) no-repeat -19em 0;
}
.submit{
height:2em;
width:5.9375em;
border:none;
background:url(http://www.baidu.com/img/i-1.0.0.png) no-repeat;
}
.bold{
font-size:1.5em;
font-weight:bold;
word-break:normal;
word-wrap:break-word
}
.detail {
width:61.25em;
margin:0 auto;
padding:1.25em;
padding-top:0;
border-left:.0625em solid #ccc;
border-bottom:.0625em solid #ccc;
border-right:.0625em solid #ccc;
background-color:#fff
}
.header{
padding-top:1.25em;
padding-bottom:.625em;
border-bottom:.0625em dotted #ccc
}
.red{
color:#ff0000;
}
.back-pink{
background-color:#ffb7dd;
}
.back-yellow{
background-color:#ffddaa;
}
.back-green{
background-color:#eeffbb;
}
.back-blue{
background-color:#ccddff;
}
.center{
text-align:center;
}
</style>

</head>

<body>
<div class="detail">

<div class="header">
    <form method="post" action="baidusp-f-remake.php">
        <input class="text" type="text" value="<?php echo htmlspecialchars($_POST['query'],ENT_QUOTES);?>" name="query" title="解释" autocomplete="off" maxlength="76" baiduSug="1" autofocus="autofocus" />
        <input class="submit" type="submit" value="查询 F 参数" />
    </form>
</div>
<?php
$query = $_POST['query'];
$queryfilter = htmlspecialchars(preg_replace("/(\s+)/","%20",$query));

$baidu = "http://www.baidu.com/s?wd=";
$baiduserp = file_get_contents($baidu.$queryfilter);

if
(!is_null($query))
echo "
<h1 class=\"bold\">
    <a href=\"$baidu$queryfilter&amp;ie=utf-8\" target=\"_blank\" rel=\"external nofollow\">查看“<span class=\"red\">$queryfilter</span>”的百度搜索结果</a>
</h1>

<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>数值</th>
            <th>参数</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody class=\"center\">";

if
(preg_match_all("/(	'F':')([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})(?=',)/", $baiduserp, $match))
{
    foreach ($match[1] as $i => $position)
    {
        $fvalue1 = $match[2][$i];
        $fvalue2 = $match[3][$i];
        $fvalue3 = $match[4][$i];
        $fvalue4 = $match[5][$i];
        $fvalue5 = $match[6][$i];
        $fvalue6 = $match[7][$i];
        $fvalue7 = $match[8][$i];
        $fvalue8 = $match[9][$i];
        echo "
        <tr>
            <td>".$fvalue1."</td>";
        {
        if
            ($fvalue2==3)
            echo "
            <td>
                 匹配错别字|拼音正确搜索结果
            </td>";
        else
            echo "
            <td>".$fvalue2."</td>";
        }
            echo"
            <td>".$fvalue3."</td>
            <td>".$fvalue4."</td>
            <td>".$fvalue5."</td>
            <td>".$fvalue6."</td>";
        {
        if
            ($fvalue7=='F')
            echo "
            <td>
                 查询词匹配网址或描述
            </td>";
        else
            echo "
            <td>".$fvalue7."</td>";
        }
            echo"
            <td>".$fvalue8."</td>
            <td>".$fvalue1.$fvalue2.$fvalue3.$fvalue4.$fvalue5.$fvalue6.$fvalue7.$fvalue8."</td>
            <td>F</td>
            <td>".($i+1)."</td>
        </tr>";
    }
}

if
(!is_null($query))
echo"
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>数值</th>
            <th>参数</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody class=\"center\">";

if
(preg_match_all("/(	'F1':')([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})(?=',)/", $baiduserp, $match))
{
    foreach ($match[1] as $i => $position)
    {
        $f1value1 = $match[2][$i];
        $f1value2 = $match[3][$i];
        $f1value3 = $match[4][$i];
        $f1value4 = $match[5][$i];
        $f1value5 = $match[6][$i];
        $f1value6 = $match[7][$i];
        $f1value7 = $match[8][$i];
        $f1value8 = $match[9][$i];
        echo "
        <tr class=\"back-pink\">";
        {
        if
            ($f1value1=='D')
            echo "
            <td>
                 百度地图
            </td>";
        elseif
            ($f1value1=='B')
            echo "
            <td>
                 百度文库
            </td>";
        else
            echo "
            <td>".$f1value1."</td>";
        }
            echo"
            <td>".$f1value2."</td>";
        {
        if
            ($f1value3==6)
            echo "
            <td>
                 0-24小时前的网页<br />
                hh小时前|mm分钟前|ss秒前
            </td>";
        elseif
            ($f1value3==5)
            echo "
            <td>
                 24-48小时前的网页<br />
                yyyy年mm月dd日
            </td>";
        elseif
            ($f1value3==4)
            echo "
            <td>
                 2-7天前的网页<br />
                yyyy年mm月dd日
            </td>";
        else
            echo "
            <td>".$f1value3."</td>";
        }
            echo"
            <td>".$f1value4."</td>";
        {
        if
            ($f1value5=='B')
            echo "
            <td>
                 百度知道
            </td>";
        else
            echo "
            <td>".$f1value5."</td>";
        }
            echo"
            <td>".$f1value6."</td>
            <td>".$f1value7."</td>
            <td>".$f1value8."</td>
            <td>".$f1value1.$f1value2.$f1value3.$f1value4.$f1value5.$f1value6.$f1value7.$f1value8."</td>
            <td>F1</td>
            <td>".($i+1)."</td>
        </tr>";
    }
}

if
(!is_null($query))
echo"
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>标题前缀</th>
            <th>标题后缀</th>
            <th>数值</th>
            <th>参数</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody class=\"center\">";

if
(preg_match_all("/(	'F2':')([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})(?=',)/", $baiduserp, $match))
{
    foreach ($match[1] as $i => $position)
    {
        $f2value1 = $match[2][$i];
        $f2value2 = $match[3][$i];
        $f2value3 = $match[4][$i];
        $f2value4 = $match[5][$i];
        $f2value5 = $match[6][$i];
        $f2value6 = $match[7][$i];
        $f2value7 = $match[8][$i];
        $f2value8 = $match[9][$i];
        echo "
        <tr class=\"back-yellow\">
            <td>".$f2value1."</td>
            <td>".$f2value2."</td>
            <td>".$f2value3."</td>
            <td>".$f2value4."</td>
            <td>".$f2value5."</td>
            <td>".$f2value6."</td>";
        {
        if
            ($f2value7=='E')
            echo "
            <td>
                 链接锚文本<br />
                anchor-text
            </td>";
        else
            echo "
            <td>".$f2value7."</td>";
        }
        {
        if
            ($f2value8=='E')
            echo "
            <td>
                标题标签<br />
                heading
            </td>";
        elseif
            ($f2value8=='B')
            echo "
            <td>
                网页标题<br />
                title
            </td>";
        elseif
            ($f2value8=='A')
            echo "
            <td>
                没有<br />
                no
            </td>";
        else
            echo "
            <td>".$f2value8."</td>";
        }
            echo"
            <td>".$f2value1.$f2value2.$f2value3.$f2value4.$f2value5.$f2value6.$f2value7.$f2value8."</td>
            <td>F2</td>
            <td>".($i+1)."</td>
        </tr>";
    }
}

if
(!is_null($query))
echo"
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>时效性</th>
            <th>域名</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>数值</th>
            <th>参数</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody class=\"center\">";

if
(preg_match_all("/(	'F3':')([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})(?=',)/", $baiduserp, $match))
{
    foreach ($match[1] as $i => $position)
    {
        $f3value1 = $match[2][$i];
        $f3value2 = $match[3][$i];
        $f3value3 = $match[4][$i];
        $f3value4 = $match[5][$i];
        $f3value5 = $match[6][$i];
        $f3value6 = $match[7][$i];
        $f3value7 = $match[8][$i];
        $f3value8 = $match[9][$i];
        echo "
        <tr class=\"back-green\">
            <td>".$f3value1."</td>
            <td>".$f3value2."</td>
            <td>".$f3value3."</td>";
        {
            if
            ($f3value4==7)
                echo "
            <td>
                最低<br />
                8 级
            </td>";
            elseif
            ($f3value4==6)
                echo "
            <td>
                7 级
            </td>";
            elseif
            ($f3value4==5)
                echo "
            <td>
                默认<br />
                6 级
            </td>";
            elseif
            ($f3value4==4)
                echo "
            <td>
                星火计划 [原创]<br />
                5 级
            </td>";
            elseif
            ($f3value4==3)
                echo "
            <td>
                星火计划 [原创]<br />
                4 级
            </td>";
            elseif
            ($f3value4==2)
                echo "
            <td>
                星火计划 [原创]<br />
                3 级
            </td>";
            elseif
            ($f3value4==1)
                echo "
            <td>
                星火计划 [原创]<br />
                2 级
            </td>";
            elseif
            ($f3value4==0)
                echo "
            <td>
                星火计划 [原创]<br />
                最高<br />
                1 级
            </td>";
            else
            echo "
            <td>".$f3value4."</td>";
        }
        {
        if
            ($f3value5=='B')
            echo "
            <td>
                优先级较高<br />
                目录|详情页
            </td>";
        elseif
            ($f3value5=='A')
            echo "
            <td>
                优先级较高<br />
                主、子域名及域名权重相对较高的目录、详情页
            </td>";
        elseif
            ($f3value5==3)
            echo "
            <td>
                优先级较低<br />
                目录|详情页
            </td>";
        elseif
            ($f3value5==2)
            echo "
            <td>
                优先级较低<br />
                主、子域名及域名权重相对较高的目录、详情页
            </td>";
        else
            echo "
            <td>".$f3value5."</td>";
        }
            echo"
            <td>".$f3value6."</td>
            <td>".$f3value7."</td>
            <td>".$f3value8."</td>
            <td>".$f3value1.$f3value2.$f3value3.$f3value4.$f3value5.$f3value6.$f3value7.$f3value8."</td>
            <td>F3</td>
            <td>".($i+1)."</td>
        </tr>";
    }
}

if
(!is_null($query))
echo"
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>数值</th>
            <th>参数</th>
            <th>排序</th>
        </tr>
    </thead>
    <tbody class=\"center\">";

if
(preg_match_all("/(	'y':')([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})([0-9A-F]{1})(?=')/", $baiduserp, $match))
{
    foreach ($match[1] as $i => $position)
    {
        $yvalue1 = $match[2][$i];
        $yvalue2 = $match[3][$i];
        $yvalue3 = $match[4][$i];
        $yvalue4 = $match[5][$i];
        $yvalue5 = $match[6][$i];
        $yvalue6 = $match[7][$i];
        $yvalue7 = $match[8][$i];
        $yvalue8 = $match[9][$i];
        echo "
        <tr class=\"back-blue\">
            <td>".$yvalue1."</td>
            <td>".$yvalue2."</td>
            <td>".$yvalue3."</td>
            <td>".$yvalue4."</td>
            <td>".$yvalue5."</td>
            <td>".$yvalue6."</td>
            <td>".$yvalue7."</td>
            <td>".$yvalue8."</td>
            <td>".$yvalue1.$yvalue2.$yvalue3.$yvalue4.$yvalue5.$yvalue6.$yvalue7.$yvalue8."</td>
            <td>y</td>
            <td>".($i+1)."</td>
        </tr>";
    }
}

if
(!is_null($query))
echo"
    </tbody>
</table>";

date_default_timezone_set('PRC');
$time = date('Y-m-d H:i:s');
clearstatcache();
?>

<p>
    <div class="bdlikebutton"></div>
</p>

<div class="ds-thread"></div>

</div>

<script charset="gbk" src="http://www.baidu.com/js/opensug.js"></script>

<script id="bdlike_shell"></script>

<script>
var bdShare_config = {
"type":"small",
"color":"blue",
"uid":"6452695"
};
document.getElementById("bdlike_shell").src="http://bdimg.share.baidu.com/static/js/like_shell.js?t=" + Math.ceil(new Date()/3600000);
</script>

<script type="text/javascript">
var duoshuoQuery = {short_name:"weixingon"};
(function() {
var ds = document.createElement('script');
ds.type = 'text/javascript';ds.async = true;
ds.src = 'http://static.duoshuo.com/embed.js';
(document.getElementsByTagName('head')[0] 
|| document.getElementsByTagName('body')[0]).appendChild(ds);
})();
</script>

</body>
</html>
