<?php

/**
 * resultList
 * @author auto create
 */
class MapData
{
	
	/** 
	 * 确认收货时间，仅天猫拉新适用
	 **/
	public $accept_time;
	
	/** 
	 * 活动id
	 **/
	public $activity_id;
	
	/** 
	 * 活动类型，taobao-淘宝 alipay-支付宝 tmall-天猫
	 **/
	public $activity_type;
	
	/** 
	 * 来源广告位ID(pid中mm_1_2_3)中第3位
	 **/
	public $adzone_id;
	
	/** 
	 * 来源广告位名称
	 **/
	public $adzone_name;
	
	/** 
	 * 绑卡日期，仅适用于手淘拉新
	 **/
	public $bind_card_time;
	
	/** 
	 * 当前活动为淘宝拉新活动时，bind_time为新激活时间； 当前活动为支付宝拉新活动时，bind_time为绑定时间。
	 **/
	public $bind_time;
	
	/** 
	 * 日期，格式为"20180202"
	 **/
	public $biz_date;
	
	/** 
	 * 首购时间，仅淘宝，天猫拉新适用
	 **/
	public $buy_time;
	
	/** 
	 * 领取权益时间
	 **/
	public $get_rights_time;
	
	/** 
	 * 银行卡是否是绑定状态：1-绑定，0-未绑定
	 **/
	public $is_card_save;
	
	/** 
	 * loginTime
	 **/
	public $login_time;
	
	/** 
	 * 来源媒体ID(pid中mm_1_2_3)中第1位
	 **/
	public $member_id;
	
	/** 
	 * 来源媒体名称
	 **/
	public $member_nick;
	
	/** 
	 * 新人手机号
	 **/
	public $mobile;
	
	/** 
	 * 订单淘客类型:1.淘客订单；2.非淘客订单，仅淘宝，天猫拉新适用
	 **/
	public $order_tk_type;
	
	/** 
	 * 复购订单，仅适用于手淘拉新
	 **/
	public $orders;
	
	/** 
	 * 领取红包时间，仅天猫拉新适用
	 **/
	public $receive_time;
	
	/** 
	 * 新注册时间，仅淘宝拉新适用
	 **/
	public $register_time;
	
	/** 
	 * 渠道关系id
	 **/
	public $relation_id;
	
	/** 
	 * 来源站点ID(pid中mm_1_2_3)中第2位
	 **/
	public $site_id;
	
	/** 
	 * 来源站点名称
	 **/
	public $site_name;
	
	/** 
	 * 新人状态， 当前活动为淘宝拉新活动时，1: 新注册，2:激活，3:首购，4:确认收货； 当前活动为支付宝实名活动时，1：已绑定，2：拉新成功，3：无效用户；当前活动为支付宝新登活动时，3：手淘首购，4：手淘确认收货；当前活动为天猫拉新活动时，2:已领取，3:已首购，4:已收货
	 **/
	public $status;
	
	/** 
	 * 拉新成功时间，仅支付宝拉新适用
	 **/
	public $success_time;
	
	/** 
	 * 淘宝订单id，仅淘宝，天猫拉新适用
	 **/
	public $tb_trade_parent_id;
	
	/** 
	 * 分享用户(unionid)，仅淘宝，天猫拉新适用
	 **/
	public $union_id;
	
	/** 
	 * 使用权益时间
	 **/
	public $use_rights_time;	
}
?>