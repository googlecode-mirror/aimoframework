<?php
//高亮关键字
$keywords  = '/(try\s+|catch\s+|throw|public\s+|function\s+|\->|private\s+|protected\s+|extends\s+|class\s+|echo\s+';
$keywords .= ')/isU';
//注释
$comment   = array('/\/\*(.*)\*\//isU','/\/\/([^\r\n]+)/i','/#([^\r\n]+)/i');
//引号
$quote     = array('/=?\'([^\']+)\'/i','/=?\"([^\"]+)\"/i');
$contents  = file_get_contents('manaul.html');
preg_match_all('/<pre>(.*)<\/pre>/isU',$contents,$matches);
foreach($matches[1] as $code){
    $tmpCode = preg_replace($keywords,"<span class=\"k\">\\1</span>",$code);
    $tmpCode = preg_replace($comment,"<span class=\"c\">\\0</span>",$tmpCode);
    $tmpCode = preg_replace_callback($quote,"prequote",$tmpCode);   
    
    $contents = str_replace($code,$tmpCode,$contents);
}
function prequote($matches){
    
    if (strpos($matches[0],'=') ===false) {
        return "<span class=\"q\">".$matches[0]."</span>";
    }else {
        return $matches[0];
    }
}
echo $contents;
?>