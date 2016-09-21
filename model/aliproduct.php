<?php
namespace model;


class aliproduct{
	public $url,$title,$keywords,$mainphoto,$storeurl,$storename,$fullprice,$discountprice,$lowprice,$highprice,$colors,$sizes,$galleryphotos,$extraphotos,$description,$descriptionhtml;
	
	function showproduct(){
		echo "<br/>";
		echo "<br/>title:".$this->title;
		echo "<br/>keywords:".$this->keywords;
		echo "<br/>mainphoto:".$this->mainphoto;
		echo "<br/>storeurl:".$this->storeurl;
		echo "<br/>storename:".$this->storename;
		echo "<br/>fullprice:".$this->fullprice;
		echo "<br/>discountprice:".$this->discountprice;
		echo "<br/>lowprice:".$this->lowprice;
		echo "<br/>highprice:".$this->highprice;
		echo "<br/>colors:".$this->colors;
		echo "<br/>sizes:".$this->sizes;
		echo "<br/>galleryphotos:".$this->galleryphotos;
		echo "<br/>extraphotos:".$this->extraphotos;
		echo "<br/>description:".$this->description;
		echo "<br/>descriptionhtml:".$this->descriptionhtml;
		echo "<br/>";
		
	}
}
