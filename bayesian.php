<?php
/**
  * @author maas(maasdruck@gmail.com)
  * @date 2019/03/28
  * @version v1.04
  * @brief 朴素贝叶斯自动过滤擦边词
  */

# 具体词库涉及到大量国家安全，政治敏感，色情影视，在线赌博，枪支毒品，高官八卦，企业形象，个人隐私等，无法开源，见谅

ignore_user_abort();
ini_set('memory_limit', '2000M');
ini_set('max_execution_time', 0);
set_time_limit(0);

$dir  = './/';              # 路径
$dict = $dir.'d607330.txt'; # 词典
$spam = $dir.'spam.txt';    # 负面词典
$norm = $dir.'normal.txt';  # 正面词典
$freq = $dir.'bayesian';    # 词频序列数组

$priv0 = array(''); # 提权 严重负面
$priv1 = array(''); # 提权 非常正面

$z = null;
isset($_GET['s']) == True ? $s = 1 && $z = $_GET['s'] : $s = null;
isset($_GET['n']) == True ? $n = 1 && $z = $_GET['n'] : $n = null;
isset($_GET['x']) == True ? $x = 1 && $z = $_GET['x'] : $x = null;
isset($_GET['d']) == True ? $d = $_GET['d'] : $d = null;
isset($_GET['o']) == True ? $o = $_GET['o'] : $o = null;
isset($_GET['f']) == True ? $f = $_GET['f'] : $f = null;

# 过滤字符串
$p = array(' ', '#', '&', 'https://', 'http://', 'http:/');
$y = array('+', '%23', '%26', '', '', '');
$be = 0;

if ($o == 1) {
    $spams = str_replace("\n", '', file($spam));
    $norms = str_replace("\n", '', file($norm));
    $isn = number_format(2 / (count(file($norm)) + count(file($spam))), 5);
    echo '平均词频 '.$isn;
    $dicts = str_replace("\n", '', file($dict));
    # 负面词典简易分词
    foreach ($spams as $j => $v) {
        $spm[$j] = htmlspecialchars(strtolower(str_replace($p, $y, $spams[$j])));
        # query 中有 2 个连续的点或是一个加号就拆分
        $segs[$j] = array_filter(preg_split('/\.{2,}|\+/', strtolower($spm[$j])));
        # 负面词典匹配词典每一个词，匹配则生成数组，耗时取决于负面词总数
        foreach ($dicts as $di) {
            if (strpos($spm[$j], $di) > -1) {
                $segc0[$j][] = $di;
            }
        }
        # 如果存在 query 有 2 个连续的点或是一个加号，与词典分词的数组合并成新数组，然后去重
        if (isset($segs[$j][1])) {
            if (isset($segc0[$j])) {
                $segf0[$j] = array_merge($segc0[$j], $segs[$j]);
            }
            else {
                $segf0[$j] = $segs[$j];
            }
            $seg0[$j] = array_values(array_unique($segf0[$j]));
        }
        # 如果存在词典分词，去重
        elseif (isset($segc0[$j])) {
            $segf0[$j] = $segc0[$j];
            $seg0[$j] = array_values(array_unique($segf0[$j]));
        }
        # 词典不存在而且没有 2 个连续的点或是一个加号，新数组等于负面文本
        else {
            $seg0[$j] = array($spm[$j]);
        }
        # 升为三维数组
        $sf0[$j] =  array($seg0[$j]);
    }
    # 正面词典简易分词
    foreach ($norms as $j => $v) {
        $nom[$j] = htmlspecialchars(strtolower(str_replace($p, $y, $norms[$j])));
        foreach ($dicts as $di) {
            if (strpos($nom[$j], $di) > -1) {
                $segc1[$j][] = $di;
            }
        }
        $segn[$j] = array_filter(preg_split('/\.{2,}|\+/', strtolower($nom[$j])));
        if (isset($segn[$j][1])) {
            if (isset($segc1[$j])) {
                $segf1[$j] = array_merge($segc1[$j], $segn[$j]);
            }
            else {
                $segf1[$j] = $segn[$j];
            }
            $seg1[$j] = array_values(array_unique($segf1[$j]));
        }
        elseif (isset($segc1[$j])) {
            $segf1[$j] = $segc1[$j];
            $seg1[$j] = array_values(array_unique($segf1[$j]));
        }
        else {
            $seg1[$j] = array($nom[$j]);
        }
        $nf0[$j] = array($seg1[$j]);
    }
    unset($dicts);
    # 负面词三维数组转为一维数组
    $w = 0;
    foreach ($sf0 as $j => $v) {
        foreach ($sf0[$j] as $k => $v) {
            foreach ($sf0[$j][$k] as $sfl) {
                $sff[$w + 0] = $sfl;
                $w += 1;
            }
        }
    }
    # 正面词三维数组转为一维数组
    $w = 0;
    foreach ($nf0 as $j => $v) {
        foreach ($nf0[$j] as $k => $v) {
            foreach ($nf0[$j][$k] as $nfl) {
                $nff[$w + 0] = $nfl;
                $w += 1;
            }
        }
    }
    # 按数组所有值出现的次数精简
    $cnt = array_count_values($sff);
    $coun = array_count_values($nff);
    # 把负面词出现次数转换为比例
    foreach ($cnt as $k => $v) {
        $c[$k] = number_format($cnt[$k] / count($sf0), 5);
    }
    # 负面词数组中的关键词与比例拆分为 2 个数组
    $c1 = array_keys($c);
    $c2 = array_values($c);
    # 关键词和比例重组为二维新数组
    foreach ($c1 as $k => $v) {
        $cc[$k] = array((string)$c1[$k], $c2[$k]);
    }
    # 把正面词出现次数转换为比例
    foreach ($coun as $k => $v) {
        $d_1[$k] = number_format($coun[$k] / count($nf0), 5);
    }
    # 正面词数组中的关键词与比例拆分为 2 个数组
    $d1 = array_keys($d_1);
    $d2 = array_values($d_1);
    # 关键词和比例重组为新的二维数组
    foreach ($d1 as $k => $v) {
        $dd[$k] = array((string)$d1[$k], $isn, number_format($d2[$k], 5));
    }

    # 负面词数组中每个词的负面和正面概率
    foreach ($cc as $k => $v) {
        foreach ($dd as $i => $v) {
            if ($cc[$k][0] == $dd[$i][0]) {
                $ccc[$k] = array($cc[$k][0], $cc[$k][1], $dd[$i][2]);
                break;
            }
            else {
                $ccc[$k] = array($cc[$k][0], number_format($cc[$k][1], 5), $isn);
            }
        }
    }
    # 正面词数组中每个词的负面和正面概率
    foreach ($dd as $k => $v) {
        foreach ($cc as $i => $v) {
            if (isset($dd[$k][0])) {
                if ($dd[$k][0] == $cc[$i][0]) {
                    unset($dd[$k]);
                }
            }
        }
    }
    $sp0 = array();
    if (isset($priv0[0]) && $priv0[0] != null) {
        foreach ($priv0 as $pv0) {
            $sp0[] = array($pv0, number_format(0.5, 1), $isn);
        }
    }
    $sp1 = array();
    if (isset($priv1[0]) && $priv1[0] != null) {
        foreach ($priv1 as $pv1) {
            $sp1[] = array($pv1, $isn, number_format(0.5, 1));
        }
    }
    # 合并负面词的负面和正面概率与正面词的负面和正面概率和特殊提权的负面和正面概率然后写入文件
    echo "\n生成新贝叶斯词频库\n";
    file_put_contents($freq, serialize(array_merge($ccc, $dd, $sp0, $sp1)), LOCK_EX);
}
elseif ($s != null) {
    $spam_x = str_replace("\n", '', file($spam));
    foreach ($spam_x as $k_sx) {
        if ($z == $k_sx) {
            echo "负面词典已存在\n";
            $be = 1;
            break;
        }
    }
    if ($be == 0) {
        echo $z."\n已添加到负面词典\n";
        file_put_contents($spam, $z."\n", FILE_APPEND | LOCK_EX);
    }
}
elseif ($n != null) {
    $norm_x = str_replace("\n", '', file($norm));
    foreach ($norm_x as $k_nx) {
        if ($z == $k_nx) {
            echo "正面词典已存在\n";
            $be = 1;
            break;
        }
    }
    if ($be == 0) {
        echo $z."\n已添加到正面词典\n";
        file_put_contents($norm, $z."\n", FILE_APPEND | LOCK_EX);
    }
}
elseif ($d != null) {
    $dict_x = str_replace("\n", '', file($dict));
    foreach ($dict_x as $k_dx) {
        if ($d == $k_dx) {
            echo "词典已存在\n";
            $be = 1;
            break;
        }
    }
    if ($be == 0) {
        echo $d."\n已添加到词典\n";
        file_put_contents($dict, $d."\n", FILE_APPEND | LOCK_EX);
    }
}
elseif ($f == 's') {
    $spam_d = str_replace("\n", '', file($spam));
    echo '负面词典里删除 '.end($spam_d)."\n";
    unlink($spam);
    for ($i = 0; $i + 1 < count($spam_d); $i++) {
        file_put_contents($spam, $spam_d[$i]."\n", FILE_APPEND | LOCK_EX);
    }
}
elseif ($f == 'n') {
    $norm_d = str_replace("\n", '', file($norm));
    echo '正面词典里删除 '.end($norm_d)."\n";
    unlink($norm);
    for ($i = 0; $i + 1 < count($norm_d); $i++) {
        file_put_contents($norm, $norm_d[$i]."\n", FILE_APPEND | LOCK_EX);
    }
}
# 删除速度取决于词典大小
elseif ($f == 'd') {
    $dict_d = str_replace("\n", '', file($dict));
    echo '词典里删除 '.end($dict_d)."\n";
    unlink($dict);
    for ($i = 0; $i + 1 < count($dict_d); $i++) {
        file_put_contents($dict, $dict_d[$i]."\n", FILE_APPEND | LOCK_EX);
    }
}
if ($z != null) {
    $q = substr(htmlspecialchars(strtolower(str_replace($p, $y, $z))), 0, 128);
    $lib = unserialize(file_get_contents($freq));
    $bys1 = array_filter(preg_split('/\.{2,}|\+/', $q));
    foreach ($lib as $k1 => $v) {
        if (strlen(strpos($q, $lib[$k1][0])) > 0) {
            $bys2[$k1] = $lib[$k1][0];
        }
    }
    if (isset($bys1[1])) {
        if (isset($bys2)) {
            $bys3 = array_merge($bys1, $bys2);
        }
        else {
            $bys3 = $bys1;
        }
        $seg = array_values(array_unique($bys3));
    }
    elseif (isset($bys2)) {
        $seg = array_values(array_unique($bys2));
    }
    else {
        $seg = $q;
    }
    if (is_array($seg)) {
        foreach ($seg as $k2 => $v) {
            foreach ($lib as $k3 => $v) {
                if ($seg[$k2] == $lib[$k3][0]) {
                    $tf[$k2] = $lib[$k3][1];
                    $nf[$k2] = $lib[$k3][2];
                }
            }
            $seg1[$k2] = array($seg[$k2], $tf[$k2], $nf[$k2]);
        }
        foreach ($seg1 as $k4 => $v) {
            if ($seg1[$k4][1] == 0 && $seg1[$k4][2] == 0) {
                unset($seg1[$k4]);
            }
        }
        array_merge($seg1);
        foreach ($seg1 as $k4 => $v) {
            if ($seg1[$k4][1] == 0) {
                $seg1[$k4][1] = $isn;
            }
            if ($seg1[$k4][2] == 0) {
                $seg1[$k4][2] = $isn;
            }
        }
        foreach ($seg as $k5 => $v) {
            foreach ($seg1 as $k6 => $v) {
                if ($seg[$k5] == $seg1[$k6][0]) {
                    $spp[$k5] = $seg1[$k6][1] / ($seg1[$k6][1] + $seg1[$k6][2]);
                    $spo[$k5] = 1 - $spp[$k5];
                }
            }
        }
    }
    if (isset($spp)) {
        $spp1 = array_values($spp);
        if (isset($spp1[0])) {
            $semantic = number_format(((array_product($spp) / (array_product($spp) + array_product($spo))) * 100), 1);
            echo $semantic."% 概率是负面词\n";
        }
    }
    else {
        echo $q."\n没有收录在词频数组\n";
    }
}
if ($z == null && $d == null && $o == null && $f == null) {
    echo "x 查询词负面概率
s 添加负面词
n 添加正面词
d 添加词汇
o 1 生成贝叶斯词库
f s 删除负面词
f n 删除正面词
f d 删除词汇\n";
}
