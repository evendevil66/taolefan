<?php

/**
 * model
 * @author auto create
 */
class TljInstanceReportDto
{
	
	/** 
	 * 引导成交金额
	 **/
	public $alipay_amount;
	
	/** 
	 * 退款红包金额
	 **/
	public $fp_refund_amount;
	
	/** 
	 * 退款红包个数
	 **/
	public $fp_refund_num;
	
	/** 
	 * 引导预估佣金金额
	 **/
	public $pre_commission_amount;
	
	/** 
	 * 失效回退金额
	 **/
	public $refund_amount;
	
	/** 
	 * 失效回退红包个数
	 **/
	public $refund_num;
	
	/** 
	 * 解冻金额
	 **/
	public $unfreeze_amount;
	
	/** 
	 * 解冻红包个数
	 **/
	public $unfreeze_num;
	
	/** 
	 * 红包核销金额
	 **/
	public $use_amount;
	
	/** 
	 * 红包核销个数
	 **/
	public $use_num;
	
	/** 
	 * 红包领取金额
	 **/
	public $win_amount;
	
	/** 
	 * 红包领取个数
	 **/
	public $win_num;	
}
?>