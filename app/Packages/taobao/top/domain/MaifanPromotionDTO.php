<?php

/**
 * 猫超买返卡信息
 * @author auto create
 */
class MaifanPromotionDTO
{
	
	/** 
	 * 猫超买返卡总数，-1代表不限量，其他大于等于0的值为总数
	 **/
	public $maifan_promotion_condition;
	
	/** 
	 * 猫超买返卡面额
	 **/
	public $maifan_promotion_discount;
	
	/** 
	 * 猫超买返卡活动结束时间
	 **/
	public $maifan_promotion_end_time;
	
	/** 
	 * 猫超买返卡活动开始时间
	 **/
	public $maifan_promotion_start_time;	
}
?>