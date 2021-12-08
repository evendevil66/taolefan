<?php

/**
 * 物料评估-商品列表
 * @author auto create
 */
class Ucrowdrankitems
{
	
	/** 
	 * 物料评估-商品佣金率，如：1234表示12.34%，material_id=41377时选填
	 **/
	public $commirate;
	
	/** 
	 * 物料评估-商品ID，material_id=41377时必填
	 **/
	public $item_id;
	
	/** 
	 * 物料评估-商品价格，单位：元，material_id=41377时选填
	 **/
	public $price;	
}
?>