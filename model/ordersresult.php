<?php
namespace model;

class ordersresult{
	
	/*
	 *  <?xml version="1.0" encoding="utf-8"?>
		 <root>
		 <status>0</status>
		 <timestamp>2016/12/03 23:29:36</timestamp>
		
		 <barcode guid="0">80398223895</barcode>
		 <barcode guid="1">80398223918</barcode>
		 <barcode guid="2">80398223921</barcode>
		 <mark>0</mark>
		 <PDF_A4_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=false&amp;mark=0</PDF_A4_EN_URL>
		 <PDF_10_EN_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=false&amp;mark=0</PDF_10_EN_URL>
		 <PDF_A4_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=A4&amp;use_local=true&amp;mark=0</PDF_A4_LCL_URL>
		 <PDF_10_LCL_URL>https://wishpost.wish.com/api/v2/download_label?access_token=50b16225937b4cdda4e5bdd317cc7f44&amp;format=10&amp;use_local=true&amp;mark=0</PDF_10_LCL_URL>
		
		 </root>
		 */
	public $status,$timestamp,$mark,$barcodes,$PDF_A4_EN_URL,$PDF_10_EN_URL,$PDF_A4_LCL_URL,$PDF_10_LCL_URL,$error_message;
	
	/*
	 * --------------- (以下字段只有DLP返回) --------------------
PDF_15_EN_URL => PDF标签10*5下载地址
recipient_country => 到达国家(如：法国) 
recipient_country_short => 国家简码(如：FR) 
c_code => 产品代码(如：C02 ) 
q_code => 渠道代码(如：Q03) 
y_code => 验证码(如：9709)
user_desc => 用户自定义信息
	 * */
	
	public $PDF_15_EN_URL,$recipient_country,$recipient_country_short,$c_code,$q_code,$y_code,$user_desc;
	
}
