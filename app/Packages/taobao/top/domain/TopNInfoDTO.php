<?php

/**
 * 前N件佣金信息-前N件佣金生效或预热时透出以下字段
 * @author auto create
 */
class TopNInfoDTO
{
	
	/** 
	 * 前N件佣金结束时间
	 **/
	public $topn_end_time;
	
	/** 
	 * 前N件剩余库存
	 **/
	public $topn_quantity;
	
	/** 
	 * 前N件佣金率
	 **/
	public $topn_rate;
	
	/** 
	 * 前N件佣金开始时间
	 **/
	public $topn_start_time;
	
	/** 
	 * 前N件初始总库存
	 **/
	public $topn_total_count;	
}
?>