<?php

/**
 * PublisherOrderDto
 * @author auto create
 */
class OrderPage
{
	
	/** 
	 * 是否还有下一页
	 **/
	public $has_next;
	
	/** 
	 * 是否还有上一页
	 **/
	public $has_pre;
	
	/** 
	 * 页码
	 **/
	public $page_no;
	
	/** 
	 * 页大小
	 **/
	public $page_size;
	
	/** 
	 * 位点字段，由调用方原样传递
	 **/
	public $position_index;
	
	/** 
	 * PublisherOrderDto
	 **/
	public $results;	
}
?>