<?php
require 'vendor/autoload.php';
//elaticsearch管理控制器
class IndexAction extends Action{
	private $typeArr = array('theme','genre','style','mood','singer','song');
	private	$size= 15;//分页每页显示条数
	public function index(){
		$this->redirect('Index/singer');	
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
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除歌手－－只是更新search server里面的status=0
	public function delSinger(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/singer/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/singer'));
	}
	//删除单个－－只是更新search server里面的status=0
	public function delSingerOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/singer/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/singer'));
	}
	//批量恢复歌手，歌手status字段将标为1
	public function recoverSinger(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/singer/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/singer'));
	}
	//恢复单个歌手
	public function recoverSingerOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/singer/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/singer'));
	}
	//更新单个歌手 重新从数据库对应的music_singer_clear表拿出该条数据进行重新索引
	public function updateSingerOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('music_singer_clear');
		$data = $model->where(array('id'=>$id))->field(array('id'=>'typeid','name'=>'title','similarStatus','coversrc','hotnum','searchfield'))->find();
		if(!$data) $this->error("该条数据不在music_singer_clear表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'singer';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/singer/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/singer'));
	}
	//歌曲管理页面
	public function song(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'song';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除歌曲－－只是更新search server里面的status=0
	public function delSong(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/song/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/song'));
	}
	//删除单个歌曲－－只是更新search server里面的status=0
	public function delSongOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/song/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/song'));
	}
	//批量恢复歌曲，歌曲status字段将标为1
	public function recoverSong(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/song/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/song'));
	}
	//恢复单个歌曲
	public function recoverSongOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/song/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/song'));
	}
	//更新单个歌曲 重新从数据库对应的music_song_clear表拿出该条数据进行重新索引
	public function updateSongOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('music_song_clear');
		$data = $model->where(array('id'=>$id))->field(array('id'=>'typeid','title','singerid','singername','coversrc','hotnum','searchfield'))->find();
		if(!$data) $this->error("该条数据不在music_song_clear表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'song';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/song/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/song'));
	}
	//公众电台genre管理页面
	public function genre(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'genre';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除genre电台－－只是更新search server里面的status=0
	public function delGenre(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/genre/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/genre'));
	}
	//删除单个genre电台－－只是更新search server里面的status=0
	public function delGenreOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/genre/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/genre'));
	}
	//批量恢复genre，status字段将标为1
	public function recoverGenre(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/genre/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/genre'));
	}
	//恢复单个genre status字段将标为1
	public function recoverGenreOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/genre/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/genre'));
	}
	//更新单个genre 重新从数据库对应的box_genre表拿出该条数据进行重新索引
	public function updateGenreOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('box_genre');
		$map = array();
		$map['id'] = $id;
                $map['status'] = array('eq',1);
                $map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->find();
		if(!$data) $this->error("该条数据不在box_genre表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'genre';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/genre/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/genre'));
	}
	//公众电台mood管理页面
	public function mood(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'mood';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除mood电台－－只是更新search server里面的status=0
	public function delMood(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/mood/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/mood'));
	}
	//删除单个mood电台－－只是更新search server里面的status=0
	public function delMoodOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/mood/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/mood'));
	}
	//批量恢复mood，status字段将标为1
	public function recoverMood(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/mood/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/mood'));
	}
	//恢复单个mood status字段将标为1
	public function recoverMoodOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/mood/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/mood'));
	}
	//更新单个mood 重新从数据库对应的box_mood表拿出该条数据进行重新索引
	public function updateMoodOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('box_mood');
		$map = array();
		$map['id'] = $id;
                $map['status'] = array('eq',1);
                $map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->find();
		if(!$data) $this->error("该条数据不在box_mood表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'mood';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/mood/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/mood'));
	}
	//公众电台style管理页面
	public function style(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'style';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除style电台－－只是更新search server里面的status=0
	public function delStyle(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/style/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/style'));
	}
	//删除单个style电台－－只是更新search server里面的status=0
	public function delStyleOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/style/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/style'));
	}
	//批量恢复style，status字段将标为1
	public function recoverStyle(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/style/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/style'));
	}
	//恢复单个style status字段将标为1
	public function recoverStyleOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/style/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/style'));
	}
	//更新单个style 重新从数据库对应的box_style表拿出该条数据进行重新索引
	public function updateStyleOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('box_style');
		$map = array();
		$map['id'] = $id;
                $map['status'] = array('eq',1);
                $map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->find();
		if(!$data) $this->error("该条数据不在box_style表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'style';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/style/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/style'));
	}
	//公众电台theme管理页面
	public function theme(){
		$keywords = $_REQUEST['keywords'];
		$host =array();
		$host['hosts'] = C('SEARCH_HOST');
		$client = new Elasticsearch\Client($host);
		$param = array();
		$param['index'] = 'moodbox';
                $param['type'] = 'theme';
                $param['size'] = $this->size;
		if(isset($_REQUEST['p']) && !empty($_REQUEST['p'])){
                        $p = $_REQUEST['p'];
                }else{
                        $p = 1;
                }
		if($_REQUEST['status'] || !isset($_REQUEST['status'])){
			$status = 1;
		}else{
			$status = 0;
		}
		$from = $this->size*($p-1);
		$param['body']['from'] = $from;
		if(!empty($keywords)){
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
                        $query = array();
                        $query['match']['searchfield'] = $keywords;
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
			$rs = $client->search($param);
			$list = simplifyArr($rs['hits']['hits']);
			$total = $rs['hits']['total'];
			import('ORG.Util.Page');// 导入分页类
			$Page = new Page($total,$this->size);
                        $show = $Page->show();// 分页显示输出
			$list = splitSort(simplifyArr($rs["hits"]["hits"]),'searchfield',$keywords);
			$this->assign('list',$list);
			$this->assign('page',$show);
		}else{
			$filter = array();
                        $filter['term']['status'] = $status;//过滤status
			$query = array();
			$query['match_all'] = array();
                        $param['body']['query']['filtered']=array("filter"=>$filter,"query"=>$query);
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
	//批量删除theme电台－－只是更新search server里面的status=0
	public function delTheme(){
		if(!$_POST) $this->error('页面错误...');
		if(!$_POST['id']) $this->error('id不存在');
		$idArr = $_POST['id'];
		$host= C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
		for($i=0;$i<count($idArr);$i++){
			system('curl -XPOST \''.$host[0].'/moodbox/theme/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		}
		$this->success('操作成功...',U('Index/theme'));
	}
	//删除单个theme电台－－只是更新search server里面的status=0
	public function delThemeOne(){
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/theme/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 0}}\'');
		$this->success('操作成功...',U('Index/theme'));
	}
	//批量恢复theme，status字段将标为1
	public function recoverTheme(){
		if(!$_POST) $this->error('页面错误');
		if(!$_POST['id']) $this->error('ID不能为空');
		$idArr = $_POST['id'];
		$host = C('SEARCH_HOST');
		//由于没有象搜索那样的api只能调用curl来修改
                for($i=0;$i<count($idArr);$i++){
                        system('curl -XPOST \''.$host[0].'/moodbox/theme/'.$idArr[$i].'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                }
                $this->success('操作成功...',U('Index/theme'));
	}
	//恢复单个theme status字段将标为1
	public function recoverThemeOne(){
		$id = intval($_GET['id']);
                if(!$id) $this->error('参数错误');
                $host = C('SEARCH_HOST');
                system('curl -XPOST \''.$host[0].'/moodbox/theme/'.$id.'/_update?pretty\' -d \'{"doc": {"status": 1}}\'');
                $this->success('操作成功...',U('Index/theme'));
	}
	//更新单个theme 重新从数据库对应的box_theme表拿出该条数据进行重新索引
	public function updateThemeOne(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$id = intval($_GET['id']);
		if(!$id) $this->error('参数错误');
		$model=M('box_theme');
		$map = array();
		$map['id'] = $id;
		$map['status'] = array('eq',0);
                $map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->find();
		if(!$data) $this->error("该条数据不在box_theme表中，肯定有人删除了该表该条数据");
		if(!$data['coversrc']){
			$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        $a = rand(1,15);
                        $code = $code[$a];
                        $data['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";	
		}
		$data['type'] = 'theme';//压入一个type
		$data['status'] = 1;//压入一个status方便搜素过滤
		$host = C('SEARCH_HOST');
		system('curl -XPOST \''.$host[0].'/moodbox/theme/'.$id.'/_update?pretty\' -d \'{"doc": '.json_encode($data).'}\'');
                $this->success('操作成功...',U('Index/theme'));
	}
}
