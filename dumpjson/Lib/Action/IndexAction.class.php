<?php
/*
*负责：从mysqlbackup里面的yrq_elasticsearch_json数据，六种类型,导出组成json文件 方便导入到elasticsearch服务器中。
*json文件导入方法： curl -XPOST 'HOST:9200/索引名/类型/_bulk?pretty' --data-binary @json文件名.json
*json文件格式：{"index":{"_id":"1"}}
*	       {"id":"1","name":"Avant-Garde","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Avant-Garde.jpg"}
*              {"index":{"_id":"2"}}
*              {"id":"2","name":"Blues","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Blues.jpg"}
*	       {"index":{"_id":"3"}}
*	       {"id":"3","name":"Children's","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Children's.jpg"}
*/
class IndexAction extends Action{
	private $home = '/home/yangrenqiang/dumpjson/json/';
	public function _initialize(){
		C('DB_TYPE','mysql');
                C('DB_HOST','127.0.0.1');
                C('DB_USER','root');
                C('DB_PWD','r00t123');
                C('DB_NAME','yrq_elasticsearch_json');
                C('DB_PREFIX',' ');
	}
	/*负责导出yrq_elasticsearch_json数据库里面music_singer表里面的数据。
	*相似歌手(similarid)为空的歌手需要压入一个字段标记similar_status 1为有相似歌手 0为没有
	*过滤掉没有歌曲的歌手(songid = ‘’）
	*status = 8过滤掉
	*param minid 最小id
	*param maxid 最大id
	*param filenum 文件名num
	*一般一个json文件10万条最多，不然要elasticsearch导入不成功
	*/
	public function singer(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		//$minid = $_GET['minid'];
		//$maxid = $_GET['maxid'];
		$filenum = $_GET['filenum'];
		$model = M('music_singer_clear');
		$map = array();
		//$map['status'] = array('neq',8);
		//$map['songid'] = array('neq','');
		//$map['id'] = array('between',array($minid,$maxid));
		$data = $model->field(array('id'=>'typeid','name'=>'title','similarStatus','coversrc','hotnum','searchfield'))->order('id asc')->select();
		foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
                                $code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                                $a = rand(1,15);
                                $code = $code[$a];
                                $data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
                        }
			$str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                	$data[$k]['type'] = 'singer';
                	$data[$k]['status'] = 1; //search的时候过滤掉不为1的，方便把一些不想显示的标记为0
                	$str .= json_encode($data[$k])."\r\n";
                	//file_put_contents($this->home."singer".$filenum.".json",$str,FILE_APPEND);
                	file_put_contents($this->home."singer.json",$str,FILE_APPEND);
		}
		unset($data);
	}
	/*
	*负责导出yrq_elasticsearch_json数据库里面music_song表里面的数据。
	*筛选出singerid <> 0 的数据 and status = 1
	*param minid 最小id
	*param maxid 最大id
	*param filenum 文件名num
	*一般一个json文件10万条最多，不然要elasticsearch导入不成功
	*/
	public function song(){
		set_time_limit(0);
		ini_set('memory_limit','99999M');
		$minid = $_GET['minid'];
                $maxid = $_GET['maxid'];
                $filenum = $_GET['filenum'];
		$model = M('music_song_clear');
		$map = array();
		$map['id'] = array('between',array($minid,$maxid));
		$data = $model->where($map)->field(array('id'=>'typeid','title','singerid','singername','coversrc','searchfield','hotnum'))->order('id asc')->select();
		unset($maxid);
		unset($minid);
		unset($map);
		foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
                                $code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                                $a = rand(1,15);
                                $code = $code[$a];
                                $data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
                        }
                        $str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                        $data[$k]['type'] = 'song';
                        $data[$k]['status'] = 1;
                        $str .= json_encode($data[$k])."\r\n";
                        file_put_contents($this->home."song".$filenum.".json",$str,FILE_APPEND);
                }
		ob_flush();
		flush();
		//ob_clean();
		ob_clean();
		unset($a);
		unset($code);
		unset($str);
		unset($data);
		unset($filenum);
	}
	/*
	*负责导出yrq_elasticsearch_json数据库里面box_genre表里面的数据。
	*筛选出status =1 
	*/
	public function genre(){
		set_time_limit(0);
		$model = M('box_genre');
		$map = array();
		$map['status'] = array('eq',1);
		$map['songids'] = array('neq','');
		$data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->select();
		foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
				$code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                        	$a = rand(1,15);
                        	$code = $code[$a];
                        	$data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
			}
                        $str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                        $data[$k]['type'] = 'genre';
                        $data[$k]['status'] = 1;
                        $str .= json_encode($data[$k])."\r\n";
                        file_put_contents($this->home."genre.json",$str,FILE_APPEND);
                }
	}
	/*
        *负责导出yrq_elasticsearch_json数据库里面box_mood表里面的数据。
        *筛选出status =1 
        */
	public function mood(){
		set_time_limit(0);
                $model = M('box_mood');
                $map = array();
                $map['status'] = array('eq',1);
		$map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->select();
                foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
                                $code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                                $a = rand(1,15);
                                $code = $code[$a];
                                $data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
                        }
                        $str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                        $data[$k]['type'] = 'mood';
                        $data[$k]['status'] = 1;
                        $str .= json_encode($data[$k])."\r\n";
                        file_put_contents($this->home."mood.json",$str,FILE_APPEND);
                }
	}
	/*
        *负责导出yrq_elasticsearch_json数据库里面box_style表里面的数据。
        *筛选出status =1 
        */
        public function style(){
                set_time_limit(0);
                $model = M('box_style');
                $map = array();
                $map['status'] = array('eq',1);
		$map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->select();
                foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
                                $code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                                $a = rand(1,15);
                                $code = $code[$a];
                                $data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
                        }
                        $str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                        $data[$k]['type'] = 'style';
                        $data[$k]['status'] = 1;
                        $str .= json_encode($data[$k])."\r\n";
                        file_put_contents($this->home."style.json",$str,FILE_APPEND);
                }
        }
	/*
        *负责导出yrq_elasticsearch_json数据库里面box_theme表里面的数据。
        *筛选出status =1 
        */
        public function theme(){
                set_time_limit(0);
                $model = M('box_theme');
                $map = array();
                $map['status'] = array('eq',0);
		$map['songids'] = array('neq','');
                $data = $model->where($map)->field(array('id'=>'typeid','name'=>'title','coversrc','similarStatus','searchfield'))->order('id asc')->select();
                foreach($data as $k=>$v){
			if(!$data[$k]['coversrc']){
                                $code = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f');
                                $a = rand(1,15);
                                $code = $code[$a];
                                $data[$k]['coversrc'] = "http://res2.yun.fm/Public/imgonline/default/album_$code.jpg";
                        }
                        $str = '{"index":{"_id":"'.$data[$k]['typeid'].'"}}'."\r\n";
                        $data[$k]['type'] = 'theme';
                        $data[$k]['status'] = 1;
                        $str .= json_encode($data[$k])."\r\n";
                        file_put_contents($this->home."theme.json",$str,FILE_APPEND);
                }
        }
	/*
	*负责查询出music_song_clear表数据最大ID
	*/
	public function getmaxid(){
		$model = M('music_song_clear');
		$maxid = $model->field('id')->order('id desc')->find();
		file_put_contents($this->home."song_maxid.txt",$maxid['id']);
	}
}
