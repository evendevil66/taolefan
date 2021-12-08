<?php

/**
 * 入参的对象
 * @author auto create
 */
class TopApiAfOrderOption
{
	
	/** 
	 * pid中的第三段，adzoneId
	 **/
	public $adzone_id;
	
	/** 
	 * pageNo
	 **/
	public $page_no;
	
	/** 
	 * pagesize
	 **/
	public $page_size;
	
	/** 
	 * 此参数不再使用，请勿入参
	 **/
	public $punish_status;
	
	/** 
	 * 渠道关系id
	 **/
	public $relation_id;
	
	/** 
	 * pid中的第二段，siteId
	 **/
	public $site_id;
	
	/** 
	 * 查询时间跨度，不超过30天，单位是天
	 **/
	public $span;
	
	/** 
	 * 此参数不再使用，请勿入参
	 **/
	public $special_id;
	
	/** 
	 * 查询开始时间，以taoke订单创建时间开始
	 **/
	public $start_time;
	
	/** 
	 * 子订单号
	 **/
	public $tb_trade_id;
	
	/** 
	 * 此参数不再使用，请勿入参
	 **/
	public $tb_trade_parent_id;
	
	/** 
	 * 此参数不再使用，请勿入参
	 **/
	public $violation_type;	
}
?>