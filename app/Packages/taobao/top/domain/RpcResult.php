<?php

/**
 * 返回结果封装对象
 * @author auto create
 */
class RpcResult
{
	
	/** 
	 * 业务错误码 101, 102,103
	 **/
	public $biz_error_code;
	
	/** 
	 * 业务错误信息
	 **/
	public $biz_error_desc;
	
	/** 
	 * 真正的业务数据结构
	 **/
	public $data;
	
	/** 
	 * 接口返回值信息，跟rpc架构保持一致
	 **/
	public $result_code;
	
	/** 
	 * 返回信息
	 **/
	public $result_msg;	
}
?>