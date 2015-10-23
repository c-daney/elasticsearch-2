<?php
require 'vendor/autoload.php';
//elaticsearch管理控制器
class IndexAction extends Action{
	private $typeArr = array('theme','genre','style','mood','singer','song');
	private	$size= 15;//分页每页显示条数
	public function index(){
		
		$this->display();
	}
	//歌手管理页面
	public function singer(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'singer';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if(!isset($_REQUEST['status']) || empty($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = $_REQUEST['status'];
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$param['body']['query']['match']['searchfield'] = $keywords;
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = simplifyArr($rs['hits']['hits']);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$param['body']['query']['match_all'] = array();
			$rs = $client->search($param);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
			$show = $Page->show();// 分页显示输出
                        $list = simplifyArr($rs['hits']['hits']);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}
		$this->display();
	}
	//歌曲管理页面
	public function song(){
		
		$this->display();
	}
	//公众电台管理页面
	public function genre(){
		
		$this->display();
	}
	
}
