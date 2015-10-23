<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Elasticsearch管理界面</title>
<!-- Bootstrap core CSS -->
<link href="Public/bootstrap.min.css" rel="stylesheet">
<link href="data:text/css;charset=utf-8," data-href="Public/bootstrap-theme.min.css" rel="stylesheet" id="bs-theme-stylesheet">
<style>
.navbar-header a,nav a { color:#6f5499; }
.icon-bar { background:#6f5499; }
</style>
</head>
<body>
<div class="container">
    <div class="navbar-header" style="color:#6f5499;">
      <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="/" class="navbar-brand">Elasticsearch</a>
    </div>
    <nav class="collapse navbar-collapse bs-navbar-collapse">
      <ul class="nav navbar-nav">
        <li>
          <a href="<?php echo U('Index/singer');?>">歌手管理</a>
        </li>
        <li>
          <a href="<?php echo U('Index/song');?>">歌曲管理</a>
        </li>
        <li>
          <a href="<?php echo U('Index/genre');?>">电台管理</a>
        </li>
	<li>
	  <a href="<?php echo U('Index/genre');?>">API文档</a>
	</li>
      </ul>
    </nav>
</div>
<div class="container">
<h1>1. 关于elasticsearch</h1>
<pre>
1.1:常用命令

1.1.1:  curl 'localhost:9200/_cat/indices?v' #查看当前server中有哪些索引

1.1.2:  curl -XDELETE 'localhost:9200/index名/?pretty' #删除索引  
	例子: curl -XDELETE 'localhost:9200/moodbox/?pretty' ＃删除叫moodbox的索引
	      curl -XDELETE 'localhost:9200/moodbox/song/?pretty' #删除moodbox索引中是song类型的数据

1.1.3:  curl -XPUT 'localhost:9200/index名?pretty' #创建索引
	例子：curl -XPUT 'localhost:9200/moodbox?pretty' #创建一个叫moodbox的索引
	      curl -XPUT 'localhost:9200/moodbox/song?pretty' #创建一个moodbox下面类型是song的索引类型

1.2:elaticsearch管理查询界面：<a href="http://115.29.10.169:9200/_plugin/head/" target="_blank">http://115.29.10.169:9200/_plugin/head/</a>
</pre>
<h1>2. 安装elasticsearch</h1>
<h3>2.1 安装java环境。</h3>
<p>前提要安装 Oracle Java 7，Elasticsearch 官方推荐使用 Oracle JDK 7 就不要尝试 JDK 8 和 OpenJDK （其实试了OpenJDK7也是ok的）</p>
<h4>2.2 elasticsearch 安装</h4>
<p>在https://www.elastic.co/downloads/elasticsearch 下载tar包或者zip包，然后解压 ，进入 elasticsearch/bin/elasticsearch 执行./elasticsearch 
然后再开一个窗口：执行 ps aux | grep elasticsearch 如果有elasticsearch的进程 就说明启动成功了。</p>
<h4>2.3 把elasticsearch加入到系统服务</h4>
<pre>很显然，上面这样方式的启动不是我们想要的，得假如系统服务啊，像redis mysql。实际上有插件可以实现。
elasticsearch 作为一个系统service应用 ，可以安装elasticsearch-servicewrapper插件。
在https://github.com/elasticsearch/elasticsearch-servicewrapper下载该插件后，解压缩。将service目录拷贝到elasticsearch目录的bin目录下。运行这个插件的好处是：elasticsearch需要的jvm参数和其它配置都已经配置好了，非常方便。
然后进入service 目录里面有一个elasticsearch 文件 。执行 ./elasticsearch install 就把elasticsearch加入系统服务 就可以service elasticsearch start ...
具体参数：
console	在前台开启elasticsearch
start	在后台开启elasticsearch
stop	Stops elasticsearch if its running.
install	Install elasticsearch to run on system startup (init.d / service). 
remove	Removes elasticsearch from system startup (init.d / service).
————具体参数描述可以参考https://github.com/elasticsearch/elasticsearch-servicewrapper的文档
</pre>

<h1>3. elasticsearch有几个核心概念</h1>
<h3>3.1 接近实时（NRT）</h3>
<p>Elasticsearch是一个接近实时的搜索平台。这意味着，从索引一个文档直到这个文档能够被搜索到有一个轻微的延迟（通常是1秒）。</p>
       
<h3>3.2 集群（cluster）</h3>
<p>一个集群就是由一个或多个节点组织在一起，它们共同持有你整个的数据，并一起提供索引和搜索功能。一个集群由一个唯一的名字标识，这个名字默认就是“elasticsearch”。这个名字是重要的，因为一个节点只能通过指定某个集群的名字，来加入这个集群。在产品环境中显式地设定这个名字是一个好习惯，但是使用默认值来进行测试/开发也是不错的。</p>
<p>要检查集群健康，我们将使用_cat API。需要事先记住的是，我们的节点HTTP的端口是9200：</p>
            <pre>curl 'localhost:9200/_cat/health?v'</pre>
<p>相应的响应是：</p>
     <pre>epoch  timestamp cluster   status node.total node.data shards pri relo init unassign
     1394735289 14:28:09  elasticsearch green    1   1    0   0  0    0  0</pre>
            
<p>可以看到，我们集群的名字是“elasticsearch”，正常运行，并且状态是绿色。<p>
        
<p>当我们询问集群状态的时候，我们要么得到绿色、黄色或红色。绿色代表一切正常（集群功能齐全），黄色意味着所有的数据都是可用的，但是某些复制没有被分配（集群功能齐全），红色则代表因为某些原因，某些数据不可用。注意，即使是集群状态是红色的，集群仍然是部分可用的（它仍然会利用可用的分片来响应搜索请求），但是可能你需要尽快修复它，因为你有丢失的数据。
</p><p>        
也是从上面的响应中，我们可以看到，一共有一个节点，由于里面没有数据，我们有0个分片。注意，由于我们使用默认的集群名字（elasticsearch），并且由于Elasticsearch默认使用网络多播（multicast）发现其它节点，如果你在你的网络中启动了多个节点，你就已经把她们加入到一个集群中了。在这种情形下，你可能在上面的响应中看到多个节点。</p>
<h3>3.3 节点（node）</h3>
<p>一个节点是你集群中的一个服务器，作为集群的一部分，它存储你的数据，参与集群的索引和搜索功能。和集群类似，一个节点也是由一个名字来标识的，默认情况下，这个名字是一个随机的漫威漫画角色的名字，这个名字会在启动的时候赋予节点。这个名字对于管理工作来说挺重要的，因为在这个管理过程中，你会去确定网络中的哪些服务器对应于Elasticsearch集群中的哪些节点。
 </p><p>   
一个节点可以通过配置集群名称的方式来加入一个指定的集群。默认情况下，每个节点都会被安排加入到一个叫做“elasticsearch”的集群中，这意味着，如果你在你的网络中启动了若干个节点，并假定它们能够相互发现彼此，它们将会自动地形成并加入到一个叫做“elasticsearch”的集群中。
    </p><p>
在一个集群里，只要你想，可以拥有任意多个节点。而且，如果当前你的网络中没有运行任何Elasticsearch节点，这时启动一个节点，会默认创建并加入一个叫做“elasticsearch”的集群。</p>
<p>我们也可以获得节集群中的节点列表：</p>
<pre>curl 'localhost:9200/_cat/nodes?v'
对应的响应是:
curl 'localhost:9200/_cat/nodes?v'
host     ip   heap.percent ram.percent load node.role master name
mwubuntu1    127.0.1.1    8      4 0.00 d    *      New Goblin</pre>
<p>这儿，我们可以看到我们叫做“New Goblin”的节点，这个节点是我们集群中的唯一节点。   </p> 
<h3>3.4 索引（index）</h3>
<p>    
一个索引就是一个拥有几分相似特征的文档的集合。比如说，你可以有一个客户数据的索引，另一个产品目录的索引，还有一个订单数据的索引。一个索引由一个名字来标识（必须全部是小写字母的），并且当我们要对对应于这个索引中的文档进行索引、搜索、更新和删除的时候，都要使用到这个名字。
在一个集群中，如果你想，可以定义任意多的索引。</p>
<p>列出所有的索引</p>
<p>让我们看一下我们的索引：</p>
<pre>curl 'localhost:9200/_cat/indices?v'
响应是：
curl 'localhost:9200/_cat/indices?v'
health index pri rep docs.count docs.deleted store.size pri.store.size</pre>
<p>这个结果意味着，在我们的集群中，我们没有任何索引。</p>
<p>创建一个索引</p>
<p>现在让我们创建一个叫做“customer”的索引，然后再列出所有的索引：</p>
<pre>curl -XPUT 'localhost:9200/customer?pretty'
curl 'localhost:9200/_cat/indices?v'
第一个命令使用PUT创建了一个叫做“customer”的索引。我们简单地将pretty附加到调用的尾部，使其以美观的形式打印出JSON响应（如果有的话）
响应是：
   curl -XPUT 'localhost:9200/customer?pretty'
            {
              "acknowledged" : true
            }
 curl 'localhost:9200/_cat/indices?v'
 health index    pri rep docs.count docs.deleted store.size pri.store.size
yellow customer   5   1          0            0       495b           495b

第二个命令的结果告知我们，我们现在有一个叫做customer的索引，并且它有5个主分片和1份复制（都是默认值），其中包含0个文档。
</pre>
<p>
你可能也注意到了这个customer索引有一个黄色健康标签。回顾我们之前的讨论，黄色意味着某些复制没有（或者还未）被分配。这个索引之所以这样，是因为Elasticsearch默认为这个索引创建一份复制。由于现在我们只有一个节点在运行，那一份复制就分配不了了（为了高可用），直到当另外一个节点加入到这个集群后，才能分配。一旦那份复制在第二个节点上被复制，这个节点的健康状态就会变成绿色。</p>
<h3>3.5 类型（type）</h3>
    <p>在一个索引中，你可以定义一种或多种类型。一个类型是你的索引的一个逻辑上的分类/分区，其语义完全由你来定。通常，会为具有一组共同字段的文档定义一个类型。比如说，我们假设你运营一个博客平台并且将你所有的数据存储到一个索引中。在这个索引中，你可以为用户数据定义一个类型，为博客数据定义另一个类型，当然，也可以为评论数据定义另一个类型。
</p>    
<h3>3.6 文档（document）</h3>
<p>一个文档是一个可被索引的基础信息单元。比如，你可以拥有某一个客户的文档，某一个产品的一个文档，当然，也可以拥有某个订单的一个文档。文档以JSON（Javascript Object Notation）格式来表示，而JSON是一个到处存在的互联网数据交互格式。</p>
<p>在一个index/type里面，只要你想，你可以存储任意多的文档。注意，尽管一个文档，物理上存在于一个索引之中，文档必须被索引/赋予一个索引的type。
索引并查询一个文档</p>
<p>现在让我们放一些东西到customer索引中。首先要知道的是，为了索引一个文档，我们必须告诉Elasticsearch这个文档要到这个索引的哪个类型（type）下。
  </p><p>  
    让我们将一个简单的客户文档索引到customer索引、“external”类型中，这个文档的ID是1，操作如下：</p>
    <pre>    
        curl -XPUT 'localhost:9200/customer/external/1?pretty' -d '
        {
          "name": "John Doe"
        }'
        
    响应如下：
    
        curl -XPUT 'localhost:9200/customer/external/1?pretty' -d '
        {
          "name": "John Doe"
        }'
        {
          "_index" : "customer",
          "_type" : "external",
          "_id" : "1",
          "_version" : 1,
          "created" : true
        }
        </pre>
    <p>从上面的响应中，我们可以看到，一个新的客户文档在customer索引和external类型中被成功创建。文档也有一个内部id 1， 这个id是我们在索引的时候指定的。
    </p><p>
    有一个关键点需要注意，Elasticsearch在你想将文档索引到某个索引的时候，并不强制要求这个索引被显式地创建。在前面这个例子中，如果customer索引不存在，Elasticsearch将会自动地创建这个索引。
    </p><p>
    现在，让我们把刚刚索引的文档取出来：</p>
    <pre>
        curl -XGET 'localhost:9200/customer/external/1?pretty'
        
    响应如下：
    
        curl -XGET 'localhost:9200/customer/external/1?pretty'
        {
          "_index" : "customer",
          "_type" : "external",
          "_id" : "1",
          "_version" : 1,
          "found" : true, "_source" : { "name": "John Doe" }
        }
       </pre> 
    <p>除了一个叫做found的字段来指明我们找到了一个ID为1的文档，和另外一个字段——_source——返回我们前一步中索引的完整JSON文档之外，其它的都没有什么特别之处。
    </p>
    
<h5>删除一个文档</h5>
    <p>
    现在让我们删除我们刚刚创建的索引，并再次列出所有的索引：
    </p>
       <pre> curl -XDELETE 'localhost:9200/customer?pretty'
        curl 'localhost:9200/_cat/indices?v'
        
    响应如下：
    
        curl -XDELETE 'localhost:9200/customer?pretty'
        {
          "acknowledged" : true
        }
        curl 'localhost:9200/_cat/indices?v'
        health index pri rep docs.count docs.deleted store.size pri.store.size
        </pre><p>
    这表明我们成功地删除了这个索引，现在我们回到了集群中空无所有的状态。
    </p>
    <p>在更进一步之前，我们再细看一下一些我们学过的API命令：</p>
        <pre>
        curl -XPUT 'localhost:9200/customer'
        curl -XPUT 'localhost:9200/customer/external/1' -d '
        {
          "name": "John Doe"
        }'
        curl 'localhost:9200/customer/external/1'
        curl -XDELETE 'localhost:9200/customer'
        </pre>
    <p>如果我们仔细研究以上的命令，我们可以发现访问Elasticsearch中数据的一个模式。这个模式可以被总结为：
    </p><pre>
        curl -<REST Verb> <Node>:<Port>/<Index>/<Type><ID>
        </pre>
    <p>这个REST访问模式普遍适用于所有的API命令，如果你能记住它，你就会为掌握Elasticsearch开一个好头。</p>
<h5>更新文档</h5>
    
    <p>除了可以索引、替换文档之外，我们也可以更新一个文档。但要注意，Elasticsearch底层并不支持原地更新。在我们想要做一次更新的时候，Elasticsearch先删除旧文档，然后在索引一个更新过的新文档。
    </p>
    <p>下面的例子展示了怎样将我们ID为1的文档的name字段改成“Jane Doe”：</p>
    <pre>
        curl -XPOST 'localhost:9200/customer/external/1/_update?pretty' -d '
        {
          "doc": { "name": "Jane Doe" }
        }'
        </pre>
    <p>下面的例子展示了怎样将我们ID为1的文档的name字段改成“Jane Doe”的同时，给它加上age字段：</p>
    <pre>
        curl -XPOST 'localhost:9200/customer/external/1/_update?pretty' -d '
        {
          "doc": { "name": "Jane Doe", "age": 20 }
        }'
        
    更新也可以通过使用简单的脚本来进行。这个例子使用一个脚本将age加5：
    
        curl -XPOST 'localhost:9200/customer/external/1/_update?pretty' -d '
        {
          "script" : "ctx._source.age += 5"
        }'
        
    在上面的例子中，ctx._source指向当前要被更新的文档。
    
    注意，在写作本文时，更新操作只能一次应用在一个文档上。将来，Elasticsearch将提供同时更新符合指定查询条件的多个文档的功能（类似于SQL的UPDATE-WHERE语句）。
    </pre>


</h3>删除文档<h3>
    
   <p> 删除文档是相当直观的。以下的例子展示了我们怎样删除ID为2的文档：</p>
    
   <pre>     curl -XDELETE 'localhost:9200/customer/external/2?pretty'
    
    我们也能够一次删除符合某个查询条件的多个文档。以下的例子展示了如何删除名字中包含“John”的所有的客户：
    
        curl -XDELETE 'localhost:9200/customer/external/_query?pretty' -d '
        {
          "query": { "match": { "name": "John" } }
        }'
</pre>
<h3>3.7 分片和复制（shards & replicas）</h3>
<p>一个索引可以存储超出单个结点硬件限制的大量数据。比如，一个具有10亿文档的索引占据1TB的磁盘空间，而任一节点都没有这样大的磁盘空间；或者单个节点处理搜索请求，响应太慢。</p>

<p>为了解决这个问题，Elasticsearch提供了将索引划分成多份的能力，这些份就叫做分片。当你创建一个索引的时候，你可以指定你想要的分片的数量。每个分片本身也是一个功能完善并且独立的“索引”，这个“索引”可以被放置到集群中的任何节点上。</p>
<p>分片之所以重要，主要有两方面的原因：</p>
<p>- 允许你水平分割/扩展你的内容容量</p>
<p>- 允许你在分片（潜在地，位于多个节点上）之上进行分布式的、并行的操作，进而提高性能/吞吐量</p>
<p>至于一个分片怎样分布，它的文档怎样聚合回搜索请求，是完全由Elasticsearch管理的，对于作为用户的你来说，这些都是透明的。</p>
在一个网络/云的环境里，失败随时都可能发生，在某个分片/节点不知怎么的就处于离线状态，或者由于任何原因消失了，这种情况下，有一个故障转移机制是非常有用并且是强烈推荐的。为此目的，Elasticsearch允许你创建分片的一份或多份拷贝，这些拷贝叫做复制分片，或者直接叫复制。</p>
<p>复制之所以重要，有两个主要原因：</p>
<p>- 在分片/节点失败的情况下，提供了高可用性。因为这个原因，注意到复制分片从不与原/主要（original/primary）分片置于同一节点上是非常重要的。</p>
<p>- 扩展你的搜索量/吞吐量，因为搜索可以在所有的复制上并行运行
        </p>
  <p>  总之，每个索引可以被分成多个分片。一个索引也可以被复制0次（意思是没有复制）或多次。一旦复制了，每个索引就有了主分片（作为复制源的原来的分片）和复制分片（主分片的拷贝）之别。分片和复制的数量可以在索引创建的时候指定。在索引创建之后，你可以在任何时候动态地改变复制的数量，但是你事后不能改变分片的数量。
    </p><p>
    默认情况下，Elasticsearch中的每个索引被分片5个主分片和1个复制，这意味着，如果你的集群中至少有两个节点，你的索引将会有5个主分片和另外5个复制分片（1个完全拷贝），这样的话每个索引总共就有10个分片。
    </p>

<h1>4. 批处理</h1>
<p>除了能够对单个的文档进行索引、更新和删除之外，Elasticsearch也提供了以上操作的批量处理功能，这是通过使用_bulk API实现的。这个功能之所以重要，在于它提供了非常高效的机制来尽可能快的完成多个操作，与此同时使用尽可能少的网络往返。
  </p><p>  
    作为一个快速的例子，以下调用在一次bulk操作中索引了两个文档（ID 1 - John Doe and ID 2 - Jane Doe）:
        </p>
        <pre>curl -XPOST 'localhost:9200/customer/external/_bulk?pretty' -d '
        {"index":{"_id":"1"}}
        {"name": "John Doe" }
        {"index":{"_id":"2"}}
        {"name": "Jane Doe" }
        '
        
    以下例子在一个bulk操作中，首先更新第一个文档（ID为1），然后删除第二个文档（ID为2）：
    
        curl -XPOST 'localhost:9200/customer/external/_bulk?pretty' -d '
        {"update":{"_id":"1"}}
        {"doc": { "name": "John Doe becomes Jane Doe" } }
        {"delete":{"_id":"2"}}
        '
        </pre>
    <p>注意上面的delete动作，由于删除动作只需要被删除文档的ID，所以并没有对应的源文档。
    </p>
    <p>bulk API按顺序执行这些动作。如果其中一个动作因为某些原因失败了，将会继续处理它后面的动作。当bulk API返回时，它将提供每个动作的状态（按照同样的顺序），所以你能够看到某个动作成功与否。</p>
<h3>4.1 样本数据集</h3>
<p>现在我们对于基本的东西已经有了一些感觉，现在让我们尝试使用一些更加贴近现实的数据集。我已经准备了一些假想的客户的银行账户信息的JSON文档的样本。文档具有以下的模式（schema）：
  </p><pre>      
            {
                "account_number": 0,
                "balance": 16623,
                "firstname": "Bradshaw",
                "lastname": "Mckenzie",
                "age": 29,
                "gender": "F",
                "address": "244 Columbus Place",
                "employer": "Euron",
                "email": "bradshawmckenzie@euron.com",
                "city": "Hobucken",
                "state": "CO"
            }
            
        我是在http://www.json-generator.com/上生成这些数据的。
        </pre>
    <p>载入样本数据</p>
    
    <pre>你可以从https://github.com/bly2k/files/blob/master/accounts.zip?raw=true下载这个样本数据集。将其解压到当前目录下，如下，将其加载到我们的集群里：
        
            curl -XPOST 'localhost:9200/bank/account/_bulk?pretty' --data-binary @accounts.json
            curl 'localhost:9200/_cat/indices?v'
        
        响应是：
            curl 'localhost:9200/_cat/indices?v'
            health index pri rep docs.count docs.deleted store.size pri.store.size
            yellow bank    5   1       1000            0    424.4kb        424.4kb
            
这意味着我们成功批量索引了1000个文档到银行索引中（account类型）。
</pre>
<h1>5. 搜索API</h1>
<p>现在，让我们以一些简单的搜索来开始。有两种基本的方式来运行搜索：一种是在REST请求的URI中发送搜索参数，另一种是将搜索参数发送到REST请求体中。请求体方法的表达能力更好，并且你可以使用更加可读的JSON格式来定义搜索。我们将尝试使用一次请求URI作为例子，但是教程的后面部分，我们将仅仅使用请求体方法。
  </p>      
    <pre>    搜索的REST　API可以通过_search端点来访问。下面这个例子返回bank索引中的所有的文档：
        
            curl 'localhost:9200/bank/_search?q=*&pretty'
            
        我们仔细研究一下这个查询调用。我们在bank索引中搜索（_search端点），并且q=*参数指示Elasticsearch去匹配这个索引中所有的文档。pretty参数，和以前一样，仅仅是告诉Elasticsearch返回美观的JSON结果。
        
        以下是响应（部分列出）：
            
            curl 'localhost:9200/bank/_search?q=*&pretty'
            {
              "took" : 63,
              "timed_out" : false,
              "_shards" : {
                "total" : 5,
                "successful" : 5,
                "failed" : 0
              },
              "hits" : {
                "total" : 1000,
                "max_score" : 1.0,
                "hits" : [ {
                  "_index" : "bank",
                  "_type" : "account",
                  "_id" : "1",
                  "_score" : 1.0, "_source" : {"account_number":1,"balance":39225,"firstname":"Amber","lastname":"Duke","age":32,"gender":"M","address":"880 Holmes Lane","employer":"Pyrami","email":"amberduke@pyrami.com","city":"Brogan","state":"IL"}
                }, {
                  "_index" : "bank",
                  "_type" : "account",
                  "_id" : "6",
                  "_score" : 1.0, "_source" : {"account_number":6,"balance":5686,"firstname":"Hattie","lastname":"Bond","age":36,"gender":"M","address":"671 Bristol Street","employer":"Netagy","email":"hattiebond@netagy.com","city":"Dante","state":"TN"}
                }, {
                  "_index" : "bank",
                  "_type" : "account",
                  
        对于这个响应，我们看到了以下的部分：
          - took —— Elasticsearch执行这个搜索的耗时，以毫秒为单位
          - timed_out —— 指明这个搜索是否超时
          - _shards —— 指出多少个分片被搜索了，同时也指出了成功/失败的被搜索的shards的数量
          - hits —— 搜索结果
          - hits.total —— 能够匹配我们查询标准的文档的总数目
          - hits.hits —— 真正的搜索结果数据（默认只显示前10个文档）
          - _score和max_score —— 现在先忽略这些字段
            
        使用请求体方法的等价搜索是：
        
            curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
            {
              "query": { "match_all": {} }
            }'
            
        这里的不同之处在于，并不是向URI中传递q=*，取而代之的是，我们在_search API的请求体中POST了一个JSON格式请求体。我们将在下一部分中讨论这个JSON查询。
        
        响应是：
            
            curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
            {
              "query": { "match_all": {} }
            }'
            {
              "took" : 26,
              "timed_out" : false,
              "_shards" : {
                "total" : 5,
                "successful" : 5,
                "failed" : 0
              },
              "hits" : {
                "total" : 1000,
                "max_score" : 1.0,
                "hits" : [ {
                  "_index" : "bank",
                  "_type" : "account",
                  "_id" : "1",
                  "_score" : 1.0, "_source" : {"account_number":1,"balance":39225,"firstname":"Amber","lastname":"Duke","age":32,"gender":"M","address":"880 Holmes Lane","employer":"Pyrami","email":"amberduke@pyrami.com","city":"Brogan","state":"IL"}
                }, {
                  "_index" : "bank",
                  "_type" : "account",
                  "_id" : "6",
                  "_score" : 1.0, "_source" : {"account_number":6,"balance":5686,"firstname":"Hattie","lastname":"Bond","age":36,"gender":"M","address":"671 Bristol Street","employer":"Netagy","email":"hattiebond@netagy.com","city":"Dante","state":"TN"}
                }, {
                  "_index" : "bank",
                  "_type" : "account",
                  "_id" : "13",
        
        有一点需要重点理解一下，一旦你取回了你的搜索结果，Elasticsearch就完成了使命，它不会维护任何服务器端的资源或者在你的结果中打开游标。这是和其它类似SQL的平台的一个鲜明的对比， 在那些平台上，你可以在前面先获取你查询结果的一部分，然后如果你想获取结果的剩余部分，你必须继续返回服务端去取，这个过程使用一种有状态的服务器端游标技术。
        </pre>
<h3>介绍查询语言</h3>

    <p>Elasticsearch提供一种JSON风格的特定领域语言，利用它你可以执行查询。这杯称为查询DSL。这个查询语言相当全面，第一眼看上去可能有些咄咄逼人，但是最好的学习方法就是以几个基础的例子来开始。
    </p><pre>
    回到我们上一个例子，我们执行了这个查询：
    
        {
          "query": { "match_all": {} }
        }
        
    分解以上的这个查询，其中的query部分告诉我查询的定义，match_all部分就是我们想要运行的查询的类型。match_all查询，就是简单地查询一个指定索引下的所有的文档。
    
    除了这个query参数之外，我们也可以通过传递其它的参数来影响搜索结果。比如，下面做了一次match_all并只返回第一个文档：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match_all": {} },
          "size": 1
        }'
    
    注意，如果没有指定size的值，那么它默认就是10。
    
    下面的例子，做了一次match_all并且返回第11到第20个文档：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match_all": {} },
          "from": 10,
          "size": 10
        }'
        
    其中的from参数（0-based）从哪个文档开始，size参数指明从from参数开始，要返回多少个文档。这个特性对于搜索结果分页来说非常有帮助。注意，如果不指定from的值，它默认就是0。
    
    下面这个例子做了一次match_all并且以账户余额降序排序，最后返前十个文档：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match_all": {} },
          "sort": { "balance": { "order": "desc" } }
        }'
        
        </pre>
<h3>执行搜索</h3>


   <p> 现在我们已经知道了几个基本的参数，让我们进一步发掘查询语言吧。首先我们看一下返回文档的字段。默认情况下，是返回完整的JSON文档的。这可以通过source来引用（搜索hits中的_sourcei字段）。如果我们不想返回完整的源文档，我们可以指定返回的几个字段。
    </p>
    <pre>下面这个例子说明了怎样返回两个字段account_number和balance（当然，这两个字段都是指_source中的字段），以下是具体的搜索：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match_all": {} },
          "_source": ["account_number", "balance"]
        }'
        
    注意到上面的例子仅仅是简化了_source字段。它仍将会返回一个叫做_source的字段，但是仅仅包含account_number和balance来年改革字段。
    
    如果你有SQL背景，上述查询在概念上有些像SQL的SELECT FROM。
    </pre>
    <p>现在让我们进入到查询部分。之前，我们看到了match_all查询是怎样匹配到所有的文档的。现在我们介绍一种新的查询，叫做match查询，这可以看成是一个简单的字段搜索查询（比如对应于某个或某些特定字段的搜索）。
    </p>
    <p>下面这个例子返回账户编号为20的文档：
    </p><pre>
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match": { "account_number": 20 } }
        }'
        
    下面这个例子返回地址中包含“mill”的所有账户：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match": { "address": "mill" } }
        }'
        
    下面这个例子返回地址中包含“mill”或者包含“lane”的账户：
    
       curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match": { "address": "mill lane" } }
        }' 
        
    下面这个例子是match的变体（match_phrase），它会去匹配短语“mill lane”：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": { "match_phrase": { "address": "mill lane" } }
        }'
        
    现在，让我们介绍一下布尔查询。布尔查询允许我们利用布尔逻辑将较小的查询组合成较大的查询。
    
    现在这个例子组合了两个match查询，这个组合查询返回包含“mill”和“lane”的所有的账户：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": {
            "bool": {
              "must": [
                { "match": { "address": "mill" } },
                { "match": { "address": "lane" } }
              ]
            }
          }
        }'
        
    在上面的例子中，bool must语句指明了，对于一个文档，所有的查询都必须为真，这个文档才能够匹配成功。
    
    相反的，下面的例子组合了两个match查询，它返回的是地址中包含“mill”或者“lane”的所有的账户:
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": {
            "bool": {
              "should": [
                { "match": { "address": "mill" } },
                { "match": { "address": "lane" } }
              ]
            }
          }
        }'
        
    在上面的例子中，bool should语句指明，对于一个文档，查询列表中，只要有一个查询匹配，那么这个文档就被看成是匹配的。
    
    现在这个例子组合了两个查询，它返回地址中既不包含“mill”，同时也不包含“lane”的所有的账户信息：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": {
            "bool": {
              "must_not": [
                { "match": { "address": "mill" } },
                { "match": { "address": "lane" } }
              ]
            }
          }
        }'
        
    在上面的例子中， bool must_not语句指明，对于一个文档，查询列表中的的所有查询都必须都不为真，这个文档才被认为是匹配的。
    
    我们可以在一个bool查询里一起使用must、should、must_not。此外，我们可以将bool查询放到这样的bool语句中来模拟复杂的、多等级的布尔逻辑。
    
    下面这个例子返回40岁以上并且不生活在ID（daho）的人的账户：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": {
            "bool": {
              "must": [
                { "match": { "age": "40" } }
              ],
              "must_not": [
                { "match": { "state": "ID" } }
              ]
            }
          }
        }'
        
    </pre>
<h3>执行过滤器</h3>
    
   <p> 在先前的章节中，我们跳过了文档得分的细节（搜索结果中的_score字段）。这个得分是与我们指定的搜索查询匹配程度的一个相对度量。得分越高，文档越相关，得分越低文档的相关度越低。
    </p>
    <p>Elasticsearch中的所有的查询都会触发相关度得分的计算。对于那些我们不需要相关度得分的场景下，Elasticsearch以过滤器的形式提供了另一种查询功能。过滤器在概念上类似于查询，但是它们有非常快的执行速度，这种快的执行速度主要有以下两个原因:
    </p>
       <p> - 过滤器不会计算相关度的得分，所以它们在计算上更快一些</p>
        <p>- 过滤器可以被缓存到内存中，这使得在重复的搜索查询上，其要比相应的查询快出许多。
       </p> 
    <p>为了理解过滤器，我们先来介绍“被过滤”的查询，这使得你可以将一个查询（像是match_all，match，bool等）和一个过滤器结合起来。作为一个例子，我们介绍一下范围过滤器，它允许我们通过一个区间的值来过滤文档。这通常被用在数字和日期的过滤上。
    </p>
    <p>这个例子使用一个被过滤的查询，其返回值是越在20000到30000之间（闭区间）的账户。换句话说，我们想要找到越大于等于20000并且小于等于30000的账户。
    </p><pre>
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "query": {
            "filtered": {
              "query": { "match_all": {} },
              "filter": {
                "range": {
                  "balance": {
                    "gte": 20000,
                    "lte": 30000
                  }
                }
              }
            }
          }
        }'
        </pre>
    <p>分解上面的例子，被过滤的查询包含一个match_all查询（查询部分）和一个过滤器（filter部分）。我们可以在查询部分中放入其他查询，在filter部分放入其它过滤器。在上面的应用场景中，由于所有的在这个范围之内的文档都是平等的（或者说相关度都是一样的），没有一个文档比另一个文档更相关，所以这个时候使用范围过滤器就非常合适了。
    </p><p>
    通常情况下，要决定是使用过滤器还是使用查询，你就需要问自己是否需要相关度得分。如果相关度是不重要的，使用过滤器，否则使用查询。如果你有SQL背景，查询和过滤器在概念上类似于SELECT WHERE语句， although more so for filters than queries。
    </p><p>
    除了match_all, match, bool,filtered和range查询，还有很多其它类型的查uxn/过滤器，我们这里不会涉及。由于我们已经对它们的工作原理有了基本的理解，将其应用到其它类型的查询、过滤器上也不是件难事。
    </p>
<h3>执行聚合</h3>


   <p> 聚合提供了分组并统计数据的能力。理解聚合的最简单的方式是将其粗略地等同为SQL的GROUP BY和SQL聚合函数。在Elasticsearch中，你可以在一个响应中同时返回命中的数据和聚合结果。你可以使用简单的API同时运行查询和多个聚合，并以一次返回，这避免了来回的网络通信，这是非常强大和高效的。
    </p>
    <p>作为开始的一个例子，我们按照state分组，按照州名的计数倒序排序：
    </p>
       <pre> curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "size": 0,
          "aggs": {
            "group_by_state": {
              "terms": {
                "field": "state"
              }
            }
          }
        }'


    在SQL中，上面的聚合在概念上类似于：
       SELECT COUNT(*) from bank GROUP BY state ORDER BY COUNT(*) DESC
   
   响应（其中一部分）是：
   
        "hits" : {
            "total" : 1000,
            "max_score" : 0.0,
            "hits" : [ ]
          },
          "aggregations" : {
            "group_by_state" : {
              "buckets" : [ {
                "key" : "al",
                "doc_count" : 21
              }, {
                "key" : "tx",
                "doc_count" : 17
              }, {
                "key" : "id",
                "doc_count" : 15
              }, {
                "key" : "ma",
                "doc_count" : 15
              }, {
                "key" : "md",
                "doc_count" : 15
              }, {
                "key" : "pa",
                "doc_count" : 15
              }, {
                "key" : "dc",
                "doc_count" : 14
              }, {
                "key" : "me",
                "doc_count" : 14
              }, {
                "key" : "mo",
                "doc_count" : 14
              }, {
                "key" : "nd",
                "doc_count" : 14
              } ]
            }
          }
        }
        
    我们可以看到AL（abama）有21个账户，TX有17个账户，ID（daho）有15个账户，依此类推。
    </pre>
    <p>注意我们将size设置成0，这样我们就可以只看到聚合结果了，而不会显示命中的结果。
    </p>
    <pre>在先前聚合的基础上，现在这个例子计算了每个州的账户的平均余额（还是按照账户数量倒序排序的前10个州）：
    
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "size": 0,
          "aggs": {
            "group_by_state": {
              "terms": {
                "field": "state"
              },
              "aggs": {
                "average_balance": {
                  "avg": {
                    "field": "balance"
                  }
                }
              }
            }
          }
        }'
        
    注意，我们把average_balance聚合嵌套在了group_by_state聚合之中。这是所有聚合的一个常用模式。你可以任意的聚合之中嵌套聚合，这样你就可以从你的数据中抽取出想要的概述。
    </pre>
    <p>基于前面的聚合，现在让我们按照平均余额进行排序：</p>
    <pre>
        curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "size": 0,
          "aggs": {
            "group_by_state": {
              "terms": {
                "field": "state",
                "order": {
                  "average_balance": "desc"
                }
              },
              "aggs": {
                "average_balance": {
                  "avg": {
                    "field": "balance"
                  }
                }
              }
            }
          }
        }'
        </pre>
    <p>下面的例子显示了如何使用年龄段（20-29，30-39，40-49）分组，然后在用性别分组，然后为每一个年龄段的每一个性别计算平均账户余额：
    </p>
       <pre> curl -XPOST 'localhost:9200/bank/_search?pretty' -d '
        {
          "size": 0,
          "aggs": {
            "group_by_age": {
              "range": {
                "field": "age",
                "ranges": [
                  {
                    "from": 20,
                    "to": 30
                  },
                  {
                    "from": 30,
                    "to": 40
                  },
                  {
                    "from": 40,
                    "to": 50
                  }
                ]
              },
              "aggs": {
                "group_by_gender": {
                  "terms": {
                    "field": "gender"
                  },
                  "aggs": {
                    "average_balance": {
                      "avg": {
                        "field": "balance"
                      }
                    }
                  }
                }
              }
            }
          }
        }'
        </pre>
    <p>有很多关于聚合的细节，我们没有涉及。如果你想做更进一步的实验，http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html是一个非常好的起点。</p>
<h1>6. elasticsearch-php</h1>
<p>除了curl这样命令行发送请求进行操作，elasticsearch官方也有针对各门语言的api。elasticsearch－php 这个api可以帮助我们以php 关联数组的形式去存储index 和 document ，搜索
</p><p>安装方法：</p>
<pre>
1:在你的项目中 新建一个文件composer.json，并填入下面内容。
{
    "require": {
        "elasticsearch/elasticsearch": "~1.0"
    }
}</pre>
<pre>
2:
curl -s http://getcomposer.org/installer | php
php composer.phar install</pre>
<pre>
3:然后在需要用到的php文件 
require 'vendor/autoload.php';
$client = new Elasticsearch\Client();
其中你要实现查询，存储实际就跟前面curl....很像了。
具体可以看官方文档：http://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html
</pre>
<h1>7. elasticsearch在我们公司的部署情况</h1>
<h3>7.1 服务器账号+密码+elasticsearch所在目录和文件作用</h3>
<pre>
user:root 
pwd: iVelda2014
ip: 115.29.10.169
目录和文件：
1:/root/elasticsearch-1.4.4 这个是elasticseach程序相关程序的目录。
2:/root/elasticsearch-1.4.4/bin/ elasticsearch  这个是如果没有安装elasticsearch-servicewrapper插件，需要手动开启elasticsearch 的shell文件。
3:/root/elasticsearch-1.4.4/bin/service  elasticsearch-servicewrapper插件目录，这个插件需要放到/root/elasticsearch-1.4.4/bin下面，然后执行/root/elasticsearch-1.4.4/bin/service/ elasticsearch install （作用是加入系统服务）
加入进系统服务之后就可以用/etc/init.d/elasticsearch status或者service elasticsearch status等命令查看状态阿，等等。
4: /root/ Elasticsearch  这个是之前anik用nodejs写的一个导入数据的文件，实际我们完全可以不用。
</pre>
	
<h3>7.2 开启和关闭elasticsearch</h3>
<pre>
现在假如elasticsearch服务未开启。需要开启。
1:cd /root/elasticsearch-1.4.4/bin/service
2: 然后执行/root/elasticsearch-1.4.4/bin/service/ elasticsearch install (作用是加入系统服务)
3:在后台开启elasticsearch
/etc/init.d/elasticsearch start或者service elasticsearch start
4:在前台显示的开启elasticsearch，目的是有些时候，elasticsearch开启不成功，可以具体看是在报什么错误
/etc/init.d/elasticsearch console或者service elasticsearch console
5:关闭elasticsearch
/etc/init.d/elasticsearch stop或者service elasticsearch stop
6:把elasticsearch从系统服务中移出
/etc/init.d/elasticsearch remove或者service elasticsearch remove
</pre>
<h3>7.3 批量导入数据</h3>
<pre>
可以把表中数据导成如下格式到一个*.json文件中
{"index":{"_id":"1"}}
{"id":"1","name":"Avant-Garde","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Avant-Garde.jpg"}
{"index":{"_id":"2"}}
{"id":"2","name":"Blues","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Blues.jpg"}
{"index":{"_id":"3"}}
{"id":"3","name":"Children's","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Children's.jpg"}
{"index":{"_id":"4"}}
{"id":"4","name":"Classical","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Classical.jpg"}
{"index":{"_id":"5"}}
{"id":"5","name":"Comedy\/Spoken","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Comedy_Spoken.jpg"}
{"index":{"_id":"6"}}
{"id":"6","name":"Country","imgurl":"\/Public\/imgonline\/moodbox\/genre\/Country.jpg"}
批量导入命令
curl -XPOST 'localhost:9200/索引名/类型/_bulk?pretty' --data-binary @文件名.json (路径要对啊)
（索引名相当于mysql的数据库，类型相当于表）
（注意如果json太大，是不成功的二十多万行都ok）
</pre>
<h3>7.4 禁止外网访问elasticsearch服务</h3>
<pre>
一旦elasticsearch服务运行起来的话，任何人访问http://www.example.com:9200都是可以访问的
1:
vi /etc/sysconfig/iptables
添加以下代码即可，设置内网网段
iptables -A INPUT -p tcp --dport 9200 ! -s 127.0.0.1 -j DROP
2:
或者修改elasticsearch的配置文件  /root/elasticsearch-1.4.4/config/elasticsearch.yml
network.host为内网IP，当然了也可以通过network.publish_host和network.bind_host分别进行设置
</pre>
</div>
<script src="Public/jquery.min.js"></script>
<script src="Public/bootstrap.min.js"></script>
</body>
</html>