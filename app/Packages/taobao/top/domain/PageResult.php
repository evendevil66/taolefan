<?php

/**
 * 真正的业务数据结构
 * @author auto create
 */
class PageResult
{
	
	/** 
	 * pageNo
	 **/
	public $page_no;
	
	/** 
	 * pageSize
	 **/
	public $page_size;
	
	/** 
	 * 订单列表
	 **/
	public $results;
	
	/** 
	 * 总值
	 **/
	public $total_count;	
}
?>