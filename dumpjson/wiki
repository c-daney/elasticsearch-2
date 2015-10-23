dumpall.sh 自动执行以下三个shell文件:
dumpsong.sh 导出music_song_clear表成json文件并发送到elasticsearch server
dumpsinger.sh 导出music_singer_clear表成json文件并发送到elasticsearch server
dumpstation 导出公众电台(genre,theme,style,mood)表成json文件并发送到elasticsearch server

在json文件中有相关操作的年月日.log文件，方便查看

如果没有dump 这6个表成功，song singer genre theme style mood，可以执行以下命令：
###########################dump6个表成json文件###################################

1:dump singer表
命令：php index.php Index/singer/

2:dump song表（传下面几个参数目的是怕导入到elaticsearch的时候，文件太大执行不成功）
@param   minid 最小ID
@param   maxid 最大ID
@param   filenum 文件number
命令：php index.php Index/singer/minid/@minid/maxid/@maxid/filenum/@filenum
例子：php index.php Index/song/minid/89811/maxid/189811/filenum/2

3:dump genre表
命令：php index.php Index/genre

4:dump theme表
命令：php index.php Index/theme

5:dump style表
命令：php index.php Index/style

6:dump mood表
命令：php index.php Index/mood

