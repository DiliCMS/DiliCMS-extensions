<?php if ( ! defined('IN_DILICMS')) exit('No direct script access allowed');
/**
 * DiliCMS
 *
 * 一款基于并面向CodeIgniter开发者的开源轻型后端内容管理系统.
 *
 * @package     DiliCMS
 * @author      DiliCMS Team
 * @copyright   Copyright (c) 2011 - 2012, DiliCMS Team.
 * @license     http://www.dilicms.com/license
 * @link        http://www.dilicms.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * DiliCMS 扩展字段演示
 *
 * 本字段用于取得目标地点的经纬度(基于百度地图)。
 * 
 * DiliCMS 版本需求:2.0Final(317214a)或者以上(注意：2.0Final还在不停更新，请保证为最新版.)
 * 使用方法：
 * 1. 将该文件放到extensions/fields/即可
 * 2. 更新字段类型缓存
 *
 * 特殊字段表单项利用说明
 * 1. 数据源，此处用于填写地图初始化中心点的地点和地图缩放级别，可以使用城市名(格式:城市名|缩放级别)或者经纬度(经度|纬度|缩放级别).
 * 2. 不支持搜索
 *
 * @package     DiliCMS
 * @subpackage  extensions
 * @category    fields
 * @author      Jeongee
 * @link        http://www.dilicms.com
 */
class field_geo
{
	/**
	 * $k
	 * 自定义的字段标识，需要唯一, 非官方开发请自由加上前缀
	 * 
	 * @var string
	 * @access  public
	 **/
	public $k = 'geo';
	
	/**
	 * $v
	 * 自定义的字段名称
	 *
	 * @var string
	 * @access  public
	 **/
	public $v = '经纬度(VARCHAR)';
	
	/**
	 * 构造函数
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		//可以根据需求初始化数据
	}
	
	/**
	 * 生成字段的创建信息
	 *
	 * @access  public
	 * @param   array  $data 该值为新建或修改字段时候表单提交的POST数组 
	 * @return  array 
	 */
	public function  on_info($data)
	{
		return array('type' => 'VARCHAR', 'constraint' => ($data['length'] ? $data['length'] : 30), 'default' => '');
	}

	/**
	 * 生成字段的表单控件
	 *
	 * 此处，我仅仅是加了个链接
	 *
	 * @access  public
	 * @param   array  $field 该值为字段的基本信息，结构见settings/model下的缓存文件，或者查看数据库表结构
	 * @param   string $default 默认值，用于提供默认值，或者表单回填所需
	 * @param   bool   $tip 是否显示,若是，则输出字段的验证规则
	 * @return  void
	 */
	public function on_form($field, $default = '', $has_tip = TRUE)
	{
		//加载百度地图API
		echo '<script src="http://api.map.baidu.com/api?v=1.3"></script>';
		$map = 'map_'.$field['name'];
		$handler = 'map_handler_'.$field['name'];
		if ( ! $default)
		{
			$config = @explode('|', $field['values']);
			switch (count($config))
			{
				case 2: $map_center = $map.'.centerAndZoom("'.$config[0].'", '.$config[1].');';break;
				case 3: $map_center = $map.'.centerAndZoom(new BMap.Point('.$config[0].', '.$config[1].'), '.$config[2].');';break;
				default: $map_center = $map.'.centerAndZoom("北京", 15);';break; 
			}
		}
		else
		{
			$config = @explode('|', $default);
			$map_center = $map.'.centerAndZoom(new BMap.Point('.$config[0].', '.$config[1].'), '.$config[2].');';
			$map_center .= 'var marker = new BMap.Marker(new BMap.Point('.$config[0].', '.$config[1].'));'.$map.'.addOverlay(marker);marker.setAnimation(BMAP_ANIMATION_BOUNCE);';
		}
		echo '<script src="'.base_url().'../extensions/fields/geo/jquery.hovercard.min.js"></script>';
		echo '<input type="text" id="'.$field['name'].'" name="'.$field['name'].'" class="normal" value="'.$default.'" />';
		if ($has_tip)
		{
			echo '<label>'.$field['ruledescription'].'</label>';
		}
		echo '<script>
				$(function(){
					var '.$handler.' = $("#'.$field['name'].'");
					'.$handler.'.hovercard({
						detailsHTML: \'<div style="padding:10px 0 0 0"><div id="'.$map.'" style="width:600px;height:400px"></div></div>\',
						width: 600
					});
					var '.$map.' = new BMap.Map("'.$map.'");
					'.$map_center.'
					'.$map.'.addEventListener("click", function(e){
						'.$handler.'.val(e.point.lng + "|" + e.point.lat + "|" + '.$map.'.getZoom());
					});
					'.$map.'.enableScrollWheelZoom(); 
					'.$map.'.addControl(new BMap.NavigationControl()); 
				});
			  </script>';
	}
	
	/**
	 * 生成字段的列表的控件
	 * 
	 * 这里简单的输出字段的值
	 *
	 * @access  public
	 * @param   array  $field 同上
	 * @param   object  $record 一条数据库记录
	 * @return  void 
	 */
	public function on_list($field, $record)
	{
		echo $record->$field['name'];
	}
	
	/**
	 * 生成字段的搜索表单的控件
	 *
	 * 此字段不支持搜索 
	 *
	 * @access  public
	 * @param   array $field 同上
	 * @param   string $default 同上上
	 * @return  void
	 */
	public function on_search($field, $default)
	{
		echo '对不起，此字段不支持搜索';
	}
	
	/**
	 * 执行字段在搜索操作的行为
	 *
	 * 不支持搜索
	 *
	 * @access  public
	 * @param   array $field 同上
	 * @param   array $condition ,引用传递，记录搜索条件的数组，此数组直接用于$this->db->where(),区别于下面的$where
	 * @param   array $where, 引用传递， 简单的对于GET数据的过滤后的产物，用于回填搜索的表单
	 * @param   string $suffix 引用传递，用于拼接搜索条件，此货的产生现在看来完全没有必要，下个版本必将消失
	 * @return  void
	 */
	public function on_do_search($field, & $condition, & $where, & $suffix )
	{
		//do nothing
	}
	
	
	/**
	 * 执行字段提交的行为
	 *
	 *
	 * @access  public
	 * @param   array $field 同上
	 * @param   array $post 引用传递, 用于记录post过来的值，用于插入数据库，处理请小心
	 * @return  void
	 */
	public function on_do_post($field, & $post)
	{
		$post[$field['name']] = $_POST[$field['name']];
	}
}

/* End of file field_geo.php */
/* Location: ./extensions/fields/field_geo.php */