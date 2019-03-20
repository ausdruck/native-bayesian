<?php
/**
  * @author maas(maasdruck@gmail.com)
  * @date 2019/03/20
  * @version v1.0
  * @brief 朴素贝叶斯自动过滤擦边词
  */

# 具体词库涉及到大量国家安全，政治敏感，色情影视，在线赌博，枪支毒品，高官八卦，企业形象，个人隐私等，无法开源，见谅

ignore_user_abort();
ini_set('memory_limit', '800M');
ini_set('max_execution_time', 0);
set_time_limit(0);
$t = microtime(1);
if (isset($_GET['s'])) {
    $s = $_GET['s'];
}
else {
    $s = null;
}
if (isset($_GET['n'])) {
    $n = $_GET['n'];
}
else {
    $n = null;
}
if (isset($_GET['d'])) {
    $d = $_GET['d'];
}
else {
    $d = null;
}
if (isset($_GET['o'])) {
    $o = $_GET['o'];
}
else {
    $o = null;
}
if (isset($_GET['x'])) {
    $x = $_GET['x'];
}
else {
    $x = null;
}
if (isset($_GET['h'])) {
    $h = $_GET['h'];
}
else {
    $h = null;
}
if (isset($_GET['f'])) {
    $f = $_GET['f'];
}
else {
    $f = null;
}

// 过滤字符串
$p = array (' ', '#', '&', 'https://', 'http://', 'http:/');
$y = array ('+', '%23', '%26', '', '', '');

if (strlen($s) > 0) {
    $spam_x = str_replace("\n", '', file('spam-text.txt')); // 垃圾文本
    $s_sx = 0;
    foreach ($spam_x as $k_sx) {
        if ($s == $k_sx) {
            echo "垃圾词库已存在\n";
            $s_sx = 1;
            break;
        }
    }
    if ($s_sx == 0) {
        file_put_contents('spam-text.txt', $s."\n", FILE_APPEND | LOCK_EX);
        echo $s."\n已添加到垃圾词库\n";
    }
    $q = substr(htmlspecialchars(strtolower(str_replace($p, $y, $s))), 0, 128);
}
elseif (strlen($n) > 0) {
    $normal_x = str_replace("\n", '', file('normal-text.txt')); // 正常文本
    $n_nx = 0;
    foreach ($normal_x as $k_nx) {
        if ($n == $k_nx) {
            echo "正常词库已存在\n";
            $n_nx = 1;
            break;
        }
    }
    if ($n_nx == 0) {
        file_put_contents('normal-text.txt', $n."\n", FILE_APPEND | LOCK_EX);
        echo $n."\n已添加到正常词库\n";
    }
    $q = substr(htmlspecialchars(strtolower(str_replace($p, $y, $n))), 0, 128);
}
elseif (strlen($d) > 0) {
    $dict_x = str_replace("\n", '', file('dict.txt')); // 词典
    $d_dx = 0;
    foreach ($dict_x as $k_dx) {
        if ($d == $k_dx) {
            echo "词典已存在\n";
            $d_dx = 1;
            break;
        }
    }
    if ($d_dx == 0) {
        file_put_contents('dict.txt', $d."\n", FILE_APPEND | LOCK_EX);
        echo $d."\n已添加到词典\n";
    }
}
elseif ($f == 1) {
    $spam_d = str_replace("\n", '', file('spam-text.txt')); // 垃圾文本
    echo '垃圾词库里删除 '.end($spam_d)."\n";
    array_pop($spam_d);
    array_unique($spam_d);
    foreach ($spam_d as $k_s_d) {
        file_put_contents('spam-back.txt', $k_s_d."\n", FILE_APPEND | LOCK_EX);
    }
    unlink('spam-text.txt');
    rename('spam-back.txt', 'spam-text.txt');
}
elseif ($f == 2) {
    $normal_d = str_replace("\n", '', file('normal-text.txt')); // 正常文本
    echo '正常词库里删除 '.end($normal_d)."\n";
    array_pop($normal_d);
    array_unique($normal_d);
    foreach ($normal_d as $k_n_d) {
        file_put_contents('normal-back.txt', $k_n_d."\n", FILE_APPEND | LOCK_EX);
    }
    unlink('normal-text.txt');
    rename('normal-back.txt', 'normal-text.txt');
}
elseif ($f == 3) {
    $dict_d = str_replace("\n", '', file('dict.txt')); // 词典
    echo '词典里删除 '.end($dict_d)."\n";
    array_pop($dict_d);
    array_unique($dict_d);
    foreach ($dict_d as $k_d_d) {
        file_put_contents('d1.txt', $k_d_d."\n", FILE_APPEND | LOCK_EX);
    }
    unlink('dict.txt');
    rename('dict-back.txt', 'dict.txt');
}

if (strlen($x) > 0) {
    $q = substr(htmlspecialchars(strtolower(str_replace($p, $y, $x))), 0, 128);
}

if ($o == 1) {
    $spam = str_replace("\n", '', file('spam-text.txt')); // 垃圾文本
    $norm = str_replace("\n", '', file('normal-text.txt')); // 正常文本
    $dict = str_replace("\n", '', file('dict.txt')); // 词典
    $isn = number_format(2 / (count(file('normal-text.txt')) + count(file('spam-text.txt'))), 5);
    echo '逆词频 '.$isn;

    // 垃圾文本简易分词
    foreach ($spam as $j => $v) {
        $spm[$j] = htmlspecialchars(strtolower(str_replace($p, $y, $spam[$j])));
        // query 中有 2 个连续的点或是一个加号就拆分
        $segs[$j] = array_filter(preg_split('/\.{2,}|\+/', strtolower($spm[$j])));
        // 垃圾文本匹配词典每一个词，匹配则生成数组 因为循环spam次词典，耗时 150秒
        foreach ($dict as $k => $v) {
            if (strlen(strpos($spm[$j], $dict[$k])) > 0) {
                $segc0[$j][$k] = $dict[$k];
            }
        }
        // 如果存在 query 有 2 个连续的点或是一个加号，与词典分词的数组合并成新数组，然后去重
        if (isset($segs[$j][1])) {
            if (isset($segc0[$j])) {
                $segf0[$j] = array_merge($segc0[$j], $segs[$j]);
            }
            else {
                $segf0[$j] = $segs[$j];
            }
            $seg0[$j] = array_values(array_unique($segf0[$j]));
        }
        // 如果存在词典分词，去重
        elseif (isset($segc0[$j])) {
            $segf0[$j] = $segc0[$j];
            $seg0[$j] = array_values(array_unique($segf0[$j]));
        }
        // 词典不存在的词而且没有 2 个连续的点或是一个加号，新数组等于垃圾文本
        else {
            $seg0[$j] = array($spm[$j]);
        }
        // 升为三维数组
        $sf0[$j] =  array($seg0[$j]);
    }
    // 正常文本简易分词
    foreach ($norm as $j => $v) {
        $nom[$j] = htmlspecialchars(strtolower(str_replace($p, $y, $norm[$j])));
        $segn[$j] =  array_filter(preg_split('/\.{2,}|\+/', strtolower($nom[$j])));
        foreach ($dict as $k => $v) {
            if (strlen(strpos($nom[$j], $dict[$k])) > 0) {
                $segc1[$j][$k] = $dict[$k];
            }
        }
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
    // 垃圾词三维数组转为一维数组
    foreach ($sf0 as $j => $v) {
        foreach ($sf0[$j] as $k => $v) {
            foreach ($sf0[$j][$k] as $i => $v) {
                $sff[@$u+ 0] = $sf0[$j][$k][$i];
                @$u += 1;
            }
        }
    }
    // 正常词三维数组转为一维数组
    foreach ($nf0 as $j => $v) {
        foreach ($nf0[$j] as $k => $v) {
            foreach ($nf0[$j][$k] as $i => $v) {
                $nff[@$w + 0] = $nf0[$j][$k][$i];
                @$w += 1;
            }
        }
    }
    // 按数组所有值出现的次数精简
    $cnt = array_count_values($sff);
    $coun = array_count_values($nff);

    // 把垃圾词出现次数转换为比例
    foreach ($cnt as $k => $v) {
        $c[$k] = number_format($cnt[$k] / count($sf0), 5);
    }
    // 垃圾词数组中的关键词与比例拆分为 2 个数组
    $c1 = array_keys($c);
    $c2 = array_values($c);
    // 关键词和比例重组为新的二维数组
    foreach ($c1 as $k => $v) {
        $cc[$k] = array((string)$c1[$k], $c2[$k]);
    }

    // 把正常词出现次数转换为比例
    foreach ($coun as $k => $v) {
        $d_1[$k] = number_format($coun[$k] / count($nf0), 5);
    }
    // 正常词数组中的关键词与比例拆分为 2 个数组
    $d1 = array_keys($d_1);
    $d2 = array_values($d_1);
    // 关键词和比例重组为新的二维数组
    foreach ($d1 as $k => $v) {
        $dd[$k] = array((string)$d1[$k], $isn, number_format($d2[$k], 5));
    }

    // 垃圾词数组中每个词的垃圾和正常概率
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
    // 正常词数组中每个词的垃圾和正常概率
    foreach ($dd as $k => $v) {
        foreach ($cc as $i => $v) {
            if (@$dd[$k][0] == $cc[$i][0]) {
                unset($dd[$k]);
            }
        }
    }

    // 特殊提权 严重垃圾
    $sp1w = array();
    // 特殊提权 轻微垃圾
    $sp2w = array();

    // 特殊提权 正常
    $sp4w = array();
    // 特殊提权 很正常
    $sp3w = array();

    foreach ($sp1w as $k => $v) {
        $sp1[$k] = array($sp1w[$k], number_format(0.5, 1), $isn);
    }
    foreach ($sp2w as $k => $v) {
        $sp2[$k] = array($sp2w[$k], number_format(0.05, 2), $isn);
    }
    foreach ($sp3w as $k => $v) {
        $sp3[$k] = array($sp3w[$k], $isn, number_format(0.5, 1));
    }
    foreach ($sp4w as $k => $v) {
        $sp4[$k] = array($sp4w[$k], $isn, number_format(0.05, 2));
    }

    // 合并垃圾词的垃圾和正常概率与正常词的垃圾和正常概率和特殊提权的垃圾和正常概率
    $new = array_merge($ccc, $dd, $sp1, $sp2, $sp3, $sp4);

    // 写入文件
    file_put_contents('bayesian.txt', serialize($new), LOCK_EX);

    echo "\n生成新贝叶斯词库\n";
    echo '计算耗时 '.number_format((microtime(1) - $t), 6)." 秒\n";
}
if (strlen($s) > 0 || strlen($n) > 0 || strlen($x) > 0) {
    $lib = unserialize(file_get_contents('bayesian.txt'));
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
                    $spo[$k5] = 1- $seg1[$k6][1] / ($seg1[$k6][1] + $seg1[$k6][2]);
                }
            }
        }
    }
    if (isset($spp)) {
        $spp1 = array_values($spp);
        if (isset($spp1[0])) {
            $semantic = number_format(((array_product($spp) / (array_product($spp) + array_product($spo))) * 100), 2);
            echo $semantic."% 概率是擦边词\n";
        }
    }
    else {
        echo $q."\n没有收录\n";
    }
}
if ($h == 1) {
    echo "s 添加垃圾词
n 添加正常词
d 添加词汇
o 1 生成贝叶斯词库
x 计算查询词垃圾概率
f 1 删除垃圾词
f 2 删除正常词
f 3 删除词汇\n";
}
