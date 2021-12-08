<?php
/**
 * TOP API: taobao.tbk.dg.vegas.tlj.stop request
 * 
 * @author auto create
 * @since 1.0, 2021.09.27
 */
class TbkDgVegasTljStopRequest
{
	/** 
	 * adzoneId
	 **/
	private $adzoneId;
	
	/** 
	 * 创建淘礼金时返回的rightsId
	 **/
	private $rightsId;
	
	private $apiParas = array();
	
	public function setAdzoneId($adzoneId)
	{
		$this->adzoneId = $adzoneId;
		$this->apiParas["adzone_id"] = $adzoneId;
	}

	public function getAdzoneId()
	{
		return $this->adzoneId;
	}

	public function setRightsId($rightsId)
	{
		$this->rightsId = $rightsId;
		$this->apiParas["rights_id"] = $rightsId;
	}

	public function getRightsId()
	{
		return $this->rightsId;
	}

	public function getApiMethodName()
	{
		return "taobao.tbk.dg.vegas.tlj.stop";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->adzoneId,"adzoneId");
		RequestCheckUtil::checkNotNull($this->rightsId,"rightsId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
