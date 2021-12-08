<?php

/**
 * 服务费信息
 * @author auto create
 */
class ServiceFeeDto
{
	
	/** 
	 * 结算专项服务费
	 **/
	public $share_fee;
	
	/** 
	 * 预估专项服务费
	 **/
	public $share_pre_fee;
	
	/** 
	 * 专项服务费率
	 **/
	public $share_relative_rate;
	
	/** 
	 * 专项服务费来源，122-渠道
	 **/
	public $tk_share_role_type;	
}
?>