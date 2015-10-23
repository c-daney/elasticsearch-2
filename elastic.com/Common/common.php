<?php
/*
*简化二维数组，取出_source里面的数组，重新压入一个新的数组
*@param $arr 要简化的数组
*return 返回简化的数组
*/
function simplifyArr($arr){
        $tmp = array();
        if(empty($arr)){
                return $tmp;
        }else{
                foreach($arr as $k=>$v){
                        $tmp[$k] = $arr[$k]['_source'];
                }
        }
        return $tmp;
}
/*
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $suffix=true, $charset="utf-8") {
		if(function_exists("mb_substr"))
			$slice = mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
			if(false === $slice) {
				$slice = '';
			}
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		switch (strtolower($charset)) {
			case 'utf-8' :
				if (strlen($str) > $length*3) {
					return $suffix ? $slice.'...' : $slice;
				} else {
					return $slice;
				}
				break;
			default :
				if (strlen($str) > $length) {
					return $suffix ? $slice.'...' : $slice;
				} else {
					return $slice;
				}
		}
}
/*
*对二维数组的某个值进行排序
*@param $arr 要排序的数组
*return $arr
*/
function multi_array_sort($arr){
        if(empty($arr)) return $arr;
        $tmp = array();
        foreach($arr as $key=>$value){
                $tmp[$key] = $value['hotnum'];
        }
        array_multisort($tmp,SORT_DESC,$arr);
        return $arr;
}
/*
*拆分排序
*param $arr数组
*param $column
*param $keywords
*param $sort bool是否根据hotnum排序
*return array
*/
function splitSort($arr,$column,$keywords,$sort=true){
        $equal = array();
        $left = array();
        $other = array();
        foreach($arr as $key=>$value){
                //如果全等
                if($arr[$key][$column] == $keywords){
                        $equal[] = $value;
                }elseif(stripos($arr[$key][$column],$keywords)===0 && strlen($keywords) != strlen($arr[$key][$column])){
                        $left[] = $value;
                }else{
                        $other[] = $value;
                }
        }
        if($sort){
                return array_merge(multi_array_sort($equal),multi_array_sort($left),multi_array_sort($other));
        }else{
                return array_merge($equal,$left,$other);
        }

}
