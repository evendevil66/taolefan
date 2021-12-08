<?php
/**
 * TOP API: taobao.tbk.tpwd.create request
 * 
 * @author auto create
 * @since 1.0, 2021.11.24
 */
class TbkTpwdCreateRequest
{
	/** 
	 * 兼容旧版本api参数，无实际作用
	 **/
	private $ext;
	
	/** 
	 * 兼容旧版本api参数，无实际作用
	 **/
	private $logo;
	
	/** 
	 * 兼容旧版本api参数，无实际作用
	 **/
	private $text;
	
	/** 
	 * 联盟官方渠道获取的淘客推广链接，请注意，不要随意篡改官方生成的链接，否则可能无法生成淘口令
	 **/
	private $url;
	
	/** 
	 * 兼容旧版本api参数，无实际作用
	 **/
	private $userId;
	
	private $apiParas = array();
	
	public function setExt($ext)
	{
		$this->ext = $ext;
		$this->apiParas["ext"] = $ext;
	}

	public function getExt()
	{
		return $this->ext;
	}

	public function setLogo($logo)
	{
		$this->logo = $logo;
		$this->apiParas["logo"] = $logo;
	}

	public function getLogo()
	{
		return $this->logo;
	}

	public function setText($text)
	{
		$this->text = $text;
		$this->apiParas["text"] = $text;
	}

	public function getText()
	{
		return $this->text;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		$this->apiParas["url"] = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "taobao.tbk.tpwd.create";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->url,"url");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
