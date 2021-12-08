<?php
/**
 * TOP API: taobao.tbk.sc.publisher.info.save request
 * 
 * @author auto create
 * @since 1.0, 2021.11.23
 */
class TbkScPublisherInfoSaveRequest
{
	/** 
	 * 类型，必选 默认为1:
	 **/
	private $infoType;
	
	/** 
	 * 淘宝客邀请渠道或会员的邀请码
	 **/
	private $inviterCode;
	
	/** 
	 * 媒体侧渠道备注
	 **/
	private $note;
	
	/** 
	 * 渠道备案 - 线下场景信息，1 - 门店，2- 学校，3 - 工厂，4 - 其他
	 **/
	private $offlineScene;
	
	/** 
	 * 渠道备案 - 线上场景信息，1 - 微信群，2- QQ群，3 - 其他
	 **/
	private $onlineScene;
	
	/** 
	 * 线下备案注册信息,字段包含: 电话号码(phoneNumber，必填),省(province,必填),市(city,必填),区县街道(location,必填),详细地址(detailAddress,必填),经营类型(career,线下个人必填),店铺类型(shopType,线下店铺必填),店铺名称(shopName,线下店铺必填),店铺证书类型(shopCertifyType,线下店铺选填),店铺证书编号(certifyNumber,线下店铺选填)
	 **/
	private $registerInfo;
	
	/** 
	 * 渠道备案 - 来源，取链接的来源
	 **/
	private $relationFrom;
	
	private $apiParas = array();
	
	public function setInfoType($infoType)
	{
		$this->infoType = $infoType;
		$this->apiParas["info_type"] = $infoType;
	}

	public function getInfoType()
	{
		return $this->infoType;
	}

	public function setInviterCode($inviterCode)
	{
		$this->inviterCode = $inviterCode;
		$this->apiParas["inviter_code"] = $inviterCode;
	}

	public function getInviterCode()
	{
		return $this->inviterCode;
	}

	public function setNote($note)
	{
		$this->note = $note;
		$this->apiParas["note"] = $note;
	}

	public function getNote()
	{
		return $this->note;
	}

	public function setOfflineScene($offlineScene)
	{
		$this->offlineScene = $offlineScene;
		$this->apiParas["offline_scene"] = $offlineScene;
	}

	public function getOfflineScene()
	{
		return $this->offlineScene;
	}

	public function setOnlineScene($onlineScene)
	{
		$this->onlineScene = $onlineScene;
		$this->apiParas["online_scene"] = $onlineScene;
	}

	public function getOnlineScene()
	{
		return $this->onlineScene;
	}

	public function setRegisterInfo($registerInfo)
	{
		$this->registerInfo = $registerInfo;
		$this->apiParas["register_info"] = $registerInfo;
	}

	public function getRegisterInfo()
	{
		return $this->registerInfo;
	}

	public function setRelationFrom($relationFrom)
	{
		$this->relationFrom = $relationFrom;
		$this->apiParas["relation_from"] = $relationFrom;
	}

	public function getRelationFrom()
	{
		return $this->relationFrom;
	}

	public function getApiMethodName()
	{
		return "taobao.tbk.sc.publisher.info.save";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->infoType,"infoType");
		RequestCheckUtil::checkNotNull($this->inviterCode,"inviterCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
