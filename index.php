<?
header('Content-Type: application/xml; charset=utf-8');
include "simple_html_dom.php";
function conv($str, $from="euc-kr", $to="utf-8"){
  return trim(iconv($from, $to, $str));
}
function generateRSS($item){
  $rss = "<item>\n";
  foreach($item as $k=>$v){
    $rss .= "<$k>".htmlspecialchars($v)."</$k>\n";
  }
  $rss .= "</item>\n";
  return $rss;
}
function extractItem($el){
  $d = array();
  $a = $el->find('dt a', 0);
  $d['title'] = conv($a->innertext);
  $d['link'] = "http://news.naver.com".$a->href;
  $d['pubDate'] = conv($el->find('span[class=num]', 0)->innertext);
  $d['author'] = conv($el->find('dt span', 0)->plaintext);
  if(strlen($d['author']) == 0) {
    $d['author'] = conv($el->find('dd span', 0)->plaintext);
  }
  $d['description'] = conv($el->find('dd text', 0)->plaintext);
  return $d;
}
//$url = "naver.html";
$url =
"http://news.naver.com/main/ranking/popularDay.nhn?rankingType=popular_day&".$_SERVER['QUERY_STRING'];
$html = file_get_html($url);
$result = array();
foreach($html->find('div[class=ranking_top3] ol li dl') as $el){
  $result[] = extractItem($el);
}
foreach($html->find('div[class=ranking_section] ol li dl') as $el){
  $result[] = extractItem($el);
}
$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
$rssfeed .= '<rss version="2.0">';
$rssfeed .= '<channel>';
$rssfeed .= '<title>Naver Popular News</title>';
$rssfeed .= '<link>http://www.naver.com</link>';
$rssfeed .= '<description>This is an example RSS feed</description>';
$rssfeed .= '<language>euc-kr</language>';
$rssfeed .= "<copyright>Copyright (C) 2009 mywebsite.com</copyright>\n";

foreach($result as $item){
  $rssfeed .= generateRSS($item);
}

$rssfeed .= "</channel>\n";
$rssfeed .= "</rss>\n";
print $rssfeed;
?>
