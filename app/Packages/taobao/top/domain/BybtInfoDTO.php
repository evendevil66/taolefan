<?php

/**
 * 百亿补贴信息
 * @author auto create
 */
class BybtInfoDTO
{
	
	/** 
	 * 百亿补贴品牌logo
	 **/
	public $bybt_brand_logo;
	
	/** 
	 * 百亿补贴专属券面额，仅限百亿补贴场景透出
	 **/
	public $bybt_coupon_amount;
	
	/** 
	 * 商品的百亿补贴开始时间
	 **/
	public $bybt_end_time;
	
	/** 
	 * 百亿补贴商品特征标签，eg.今日发货、晚发补偿、限购一件等
	 **/
	public $bybt_item_tags;
	
	/** 
	 * 全网对比参考价格
	 **/
	public $bybt_lowest_price;
	
	/** 
	 * 百亿补贴白底图
	 **/
	public $bybt_pic_url;
	
	/** 
	 * 百亿补贴页面实时价
	 **/
	public $bybt_show_price;
	
	/** 
	 * 商品的百亿补贴结束时间
	 **/
	public $bybt_start_time;	
}
?>