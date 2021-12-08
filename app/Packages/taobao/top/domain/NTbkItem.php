<?php

/**
 * 淘宝客商品
 * @author auto create
 */
class NTbkItem
{
	
	/** 
	 * 叶子类目名称
	 **/
	public $cat_leaf_name;
	
	/** 
	 * 一级类目名称
	 **/
	public $cat_name;
	
	/** 
	 * 是否包邮
	 **/
	public $free_shipment;
	
	/** 
	 * 好评率是否高于行业均值
	 **/
	public $h_good_rate;
	
	/** 
	 * 成交转化是否高于行业均值
	 **/
	public $h_pay_rate30;
	
	/** 
	 * 是否是热门商品，0不是，1是
	 **/
	public $hot_flag;
	
	/** 
	 * 退款率是否低于行业均值
	 **/
	public $i_rfd_rate;
	
	/** 
	 * 是否加入消费者保障
	 **/
	public $is_prepay;
	
	/** 
	 * 商品链接
	 **/
	public $item_url;
	
	/** 
	 * 聚划算信息-聚淘结束时间（毫秒）
	 **/
	public $ju_online_end_time;
	
	/** 
	 * 聚划算信息-聚淘开始时间（毫秒）
	 **/
	public $ju_online_start_time;
	
	/** 
	 * 聚划算满减  -结束时间（毫秒）
	 **/
	public $ju_play_end_time;
	
	/** 
	 * 聚划算满减  -开始时间（毫秒）
	 **/
	public $ju_play_start_time;
	
	/** 
	 * 聚划算信息-商品预热结束时间（毫秒）
	 **/
	public $ju_pre_show_end_time;
	
	/** 
	 * 聚划算信息-商品预热开始时间（毫秒）
	 **/
	public $ju_pre_show_start_time;
	
	/** 
	 * 跨店满减信息
	 **/
	public $kuadian_promotion_info;
	
	/** 
	 * 商品库类型，支持多库类型输出，以英文逗号分隔“,”分隔，1:营销商品主推库，如果值为空则不属于1这种商品类型
	 **/
	public $material_lib_type;
	
	/** 
	 * 店铺名称
	 **/
	public $nick;
	
	/** 
	 * 商品ID
	 **/
	public $num_iid;
	
	/** 
	 * 商品主图
	 **/
	public $pict_url;
	
	/** 
	 * 1聚划算满减：满N件减X元，满N件X折，满N件X元）  2天猫限时抢：前N分钟每件X元，前N分钟满N件每件X元，前N件每件X元）
	 **/
	public $play_info;
	
	/** 
	 * 预售商品-定金（元）
	 **/
	public $presale_deposit;
	
	/** 
	 * 预售商品-商品优惠信息
	 **/
	public $presale_discount_fee_text;
	
	/** 
	 * 预售商品-付定金结束时间（毫秒）
	 **/
	public $presale_end_time;
	
	/** 
	 * 预售商品-付定金开始时间（毫秒）
	 **/
	public $presale_start_time;
	
	/** 
	 * 预售商品-付定金结束时间（毫秒）
	 **/
	public $presale_tail_end_time;
	
	/** 
	 * 预售商品-付尾款开始时间（毫秒）
	 **/
	public $presale_tail_start_time;
	
	/** 
	 * 商品所在地
	 **/
	public $provcity;
	
	/** 
	 * 卖家等级
	 **/
	public $ratesum;
	
	/** 
	 * 商品一口价格
	 **/
	public $reserve_price;
	
	/** 
	 * 活动价
	 **/
	public $sale_price;
	
	/** 
	 * 卖家id
	 **/
	public $seller_id;
	
	/** 
	 * 店铺dsr 评分
	 **/
	public $shop_dsr;
	
	/** 
	 * 商品小图列表
	 **/
	public $small_images;
	
	/** 
	 * 是否品牌精选，0不是，1是
	 **/
	public $superior_brand;
	
	/** 
	 * 商品标题
	 **/
	public $title;
	
	/** 
	 * 天猫限时抢可售  -结束时间（毫秒）
	 **/
	public $tmall_play_activity_end_time;
	
	/** 
	 * 天猫限时抢可售  -开始时间（毫秒）
	 **/
	public $tmall_play_activity_start_time;
	
	/** 
	 * 卖家类型，0表示集市，1表示商城，3表示特价版
	 **/
	public $user_type;
	
	/** 
	 * 30天销量
	 **/
	public $volume;
	
	/** 
	 * 折扣价（元） 若属于预售商品，付定金时间内，折扣价=预售价
	 **/
	public $zk_final_price;	
}
?>