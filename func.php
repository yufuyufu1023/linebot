<?php
/*
	引数$val1，$val2を四則演算し戻り値としてその結果を配列で返す
	引数$val2が0だった場合割り算の答えはエラーとなる
*/
function sisokuenzan($val1,$val2){
	$plus=$val1+$val2;
	$minus=$val1-$val2;
	$kakeru=$val1*$val2;
	if($val2==0){
		$waru='エラー';
	}else{
		$waru=$val1/$val2;
	}
	return array ($plus,$minus,$kakeru,$waru);
}
/*
	引数$array1（配列）の最大値を求める
	戻り値は最大値
*/
function umax($array1){
	foreach ($array1 as $ar) {
		if (!is_int($ar)) {
			return false;
		}
	}
	$max=0;
	foreach ($array1 as $ar){
		if($max<=$ar){
			$max=$ar;
		}
	}
	return $max;
}
/*
	引数$array1（配列）の中身を降順に並べ替える
	戻り値は降順に並べ替えた$array1
*/
function narabikae($array1){
	foreach ($array1 as $ar) {
		if (!is_int($ar)) {
			return false;
		}
	}
	for ($i=0;$i<count($array1);$i++) {
		for($j=$i+1;$j<count($array1);$j++)
		if($array1[$i]>=$array1[$j]){
			$temp=$array1[$i];
			$array1[$i]=$array1[$j];
			$array1[$j]=$temp;
		}
	}
	return $array1;
}
/*
	引数$array（二次元配列）の中身の配列を引数$culの部屋番号をもとに昇順で並び替える
	戻り値は昇順に並び替えた$array
*/
function hairetuirekae($array,$cul){
	for ($i=0;$i<count($array);$i++) {
		for($j=$i+1;$j<count($array);$j++)
		if($array[$i][$cul]>=$array[$j][$cul]){
			$temp=$array[$i];
			$array[$i]=$array[$j];
			$array[$j]=$temp;
		}
	}
	return $array;
}
/*
	引数＄str（文字列）を受け取り、htmlで表示しても問題のない文字列に変換する。
	戻り値は返還した文字列
*/
function h($str){
	return htmlspecialchars($str,ENT_QUOTES);
}
/*
	引数＄fp（ファイルオープンした結果）を受け取り、カンマ区切りで配列にファイルを一行ずつ入れていく
	戻り値は一行ずつ入った配列
*/
function file_r($fp){
	if(!$fp){
	  //エラー処理
	  exit;
	}
	$member_list=array();
	if(flock($fp,LOCK_EX)){
		//ファイルの中身を格納する
	  while($member=fgets($fp)){
	    $member_list[]=explode(',',$member);
	  }
	  flock($fp,LOCK_UN);
	}else{
	  fclose($fp);
	  //エラー処理
	  exit;
	}
	fclose($fp);
	return $member_list;
}
?>
