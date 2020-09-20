<?php

/**
 * resultList
 * @author auto create
 */
class MapData
{
	
	/** 
	 * 商品信息-叶子类目id
	 **/
	public $category_id;
	
	/** 
	 * 商品信息-叶子类目名称
	 **/
	public $category_name;
	
	/** 
	 * 商品信息-佣金比率。1550表示15.5%
	 **/
	public $commission_rate;
	
	/** 
	 * 商品信息-佣金类型。MKT表示营销计划，SP表示定向计划，COMMON表示通用计划
	 **/
	public $commission_type;
	
	/** 
	 * 优惠券（元） 若属于预售商品，该优惠券付尾款可用，付定金不可用
	 **/
	public $coupon_amount;
	
	/** 
	 * 优惠券信息-优惠券结束时间
	 **/
	public $coupon_end_time;
	
	/** 
	 * 优惠券信息-优惠券id
	 **/
	public $coupon_id;
	
	/** 
	 * 优惠券信息-优惠券满减信息
	 **/
	public $coupon_info;
	
	/** 
	 * 优惠券信息-优惠券剩余量
	 **/
	public $coupon_remain_count;
	
	/** 
	 * 链接-宝贝+券二合一页面链接
	 **/
	public $coupon_share_url;
	
	/** 
	 * 优惠券信息-优惠券起用门槛，满X元可用。如：满299元减20元
	 **/
	public $coupon_start_fee;
	
	/** 
	 * 优惠券信息-优惠券开始时间
	 **/
	public $coupon_start_time;
	
	/** 
	 * 优惠券信息-优惠券总量
	 **/
	public $coupon_total_count;
	
	/** 
	 * 本地化-到门店距离（米）
	 **/
	public $distance;
	
	/** 
	 * 商品信息-是否包含定向计划
	 **/
	public $include_dxjh;
	
	/** 
	 * 商品信息-是否包含营销计划
	 **/
	public $include_mkt;
	
	/** 
	 * 商品信息-定向计划信息
	 **/
	public $info_dxjh;
	
	/** 
	 * 商品信息-宝贝描述(推荐理由)
	 **/
	public $item_description;
	
	/** 
	 * 商品信息-宝贝id
	 **/
	public $item_id;
	
	/** 
	 * 链接-宝贝地址
	 **/
	public $item_url;
	
	/** 
	 * 拼团专用-拼团几人团
	 **/
	public $jdd_num;
	
	/** 
	 * 拼团专用-拼团拼成价，单位元
	 **/
	public $jdd_price;
	
	/** 
	 * 跨店满减信息
	 **/
	public $kuadian_promotion_info;
	
	/** 
	 * 商品信息-一级类目ID
	 **/
	public $level_one_category_id;
	
	/** 
	 * 商品信息-一级类目名称
	 **/
	public $level_one_category_name;
	
	/** 
	 * 锁住的佣金率
	 **/
	public $lock_rate;
	
	/** 
	 * 锁佣结束时间
	 **/
	public $lock_rate_end_time;
	
	/** 
	 * 锁佣开始时间
	 **/
	public $lock_rate_start_time;
	
	/** 
	 * 店铺信息-卖家昵称
	 **/
	public $nick;
	
	/** 
	 * 商品信息-宝贝id(该字段废弃，请勿再用)
	 **/
	public $num_iid;
	
	/** 
	 * 拼团专用-拼团结束时间
	 **/
	public $oetime;
	
	/** 
	 * 拼团专用-拼团一人价（原价)，单位元
	 **/
	public $orig_price;
	
	/** 
	 * 拼团专用-拼团开始时间
	 **/
	public $ostime;
	
	/** 
	 * 商品信息-商品主图
	 **/
	public $pict_url;
	
	/** 
	 * 预售商品-定金（元）
	 **/
	public $presale_deposit;
	
	/** 
	 * 预售商品-优惠
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
	 * 预售商品-付尾款结束时间（毫秒）
	 **/
	public $presale_tail_end_time;
	
	/** 
	 * 预售商品-付尾款开始时间（毫秒）
	 **/
	public $presale_tail_start_time;
	
	/** 
	 * 商品信息-宝贝所在地
	 **/
	public $provcity;
	
	/** 
	 * 商品邮费
	 **/
	public $real_post_fee;
	
	/** 
	 * 商品信息-商品一口价格
	 **/
	public $reserve_price;
	
	/** 
	 * 本地化-销售开始时间
	 **/
	public $sale_begin_time;
	
	/** 
	 * 本地化-销售结束时间
	 **/
	public $sale_end_time;
	
	/** 
	 * 活动价
	 **/
	public $sale_price;
	
	/** 
	 * 拼团专用-拼团已售数量
	 **/
	public $sell_num;
	
	/** 
	 * 店铺信息-卖家id
	 **/
	public $seller_id;
	
	/** 
	 * 店铺信息-店铺dsr评分
	 **/
	public $shop_dsr;
	
	/** 
	 * 店铺信息-店铺名称
	 **/
	public $shop_title;
	
	/** 
	 * 商品信息-商品短标题
	 **/
	public $short_title;
	
	/** 
	 * 商品信息-商品小图列表
	 **/
	public $small_images;
	
	/** 
	 * 拼团专用-拼团剩余库存
	 **/
	public $stock;
	
	/** 
	 * 商品信息-商品标题
	 **/
	public $title;
	
	/** 
	 * 商品信息-月支出佣金(该字段废弃，请勿再用)
	 **/
	public $tk_total_commi;
	
	/** 
	 * 商品信息-淘客30天推广量
	 **/
	public $tk_total_sales;
	
	/** 
	 * 营销-天猫营销玩法
	 **/
	public $tmall_play_activity_info;
	
	/** 
	 * 拼团专用-拼团库存数量
	 **/
	public $total_stock;
	
	/** 
	 * 链接-宝贝推广链接
	 **/
	public $url;
	
	/** 
	 * 本地化-可用店铺id
	 **/
	public $usable_shop_id;
	
	/** 
	 * 本地化-可用店铺名称
	 **/
	public $usable_shop_name;
	
	/** 
	 * 店铺信息-卖家类型。0表示集市，1表示天猫
	 **/
	public $user_type;
	
	/** 
	 * 预售专用-预售数量
	 **/
	public $uv_sum_pre_sale;
	
	/** 
	 * 商品信息-30天销量（饿了么卡券信息-总销量）
	 **/
	public $volume;
	
	/** 
	 * 商品信息-商品白底图
	 **/
	public $white_image;
	
	/** 
	 * 链接-物料块id(测试中请勿使用)
	 **/
	public $x_id;
	
	/** 
	 * 预售有礼-推广链接
	 **/
	public $ysyl_click_url;
	
	/** 
	 * 预售有礼-佣金比例（ 预售有礼活动享受的推广佣金比例，注：推广该活动有特殊分成规则，请详见：https://tbk.bbs.taobao.com/detail.html?appId=45301&postId=9334376 ）
	 **/
	public $ysyl_commission_rate;
	
	/** 
	 * 预售有礼-预估淘礼金（元）
	 **/
	public $ysyl_tlj_face;
	
	/** 
	 * 预售有礼-淘礼金发放时间
	 **/
	public $ysyl_tlj_send_time;
	
	/** 
	 * 预售有礼-淘礼金使用结束时间
	 **/
	public $ysyl_tlj_use_end_time;
	
	/** 
	 * 预售有礼-淘礼金使用开始时间
	 **/
	public $ysyl_tlj_use_start_time;
	
	/** 
	 * 折扣价（元） 若属于预售商品，付定金时间内，折扣价=预售价
	 **/
	public $zk_final_price;	
}
?>