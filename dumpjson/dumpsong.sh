#!/bin/bash
home=/home/yangrenqiang/dumpjson/
homejson=/home/yangrenqiang/dumpjson/json/
DATE=$(date +%Y年%m月%d日)
#开始dump song表
#dump song表之前读取music_song_clear表最大id
/usr/local/php/bin/php ${home}index.php Index/getmaxid
echo "开始dump song表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump song表" >> ${homejson}$DATE.log
read maxid<${homejson}song_maxid.txt
echo $maxid
exit
#把song表分成多少份 默认50份
total=50
per=`expr $maxid / $total`
trueper=${per%.*}
echo "song表总数是：$maxid,song表导出将分成$total份json文件"
for((i=1;i<=$total;i++))
do
echo "dump song表正在执行第$i次dump"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump song表正在执行第$i次dump" >> ${homejson}$DATE.log
maxids=`expr $i \* $trueper`
minids=`expr $i \* $trueper - $trueper`
if(($i==total))
then
        /usr/local/php/bin/php ${home}index.php Index/song/minid/$minids/maxid/$maxid/filenum/$i
        continue
else
        /usr/local/php/bin/php ${home}index.php Index/song/minid/$minids/maxid/$maxids/filenum/$i
        continue
fi
done
echo "dump song表完成，目录在$homejson"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump song表完成，目录在$homejson" >> ${homejson}$DATE.log
#开始把song导入到elasticsearch
echo "开始把song导入到elaticsearch服务器中"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始把song导入到elaticsearch服务器中" >> ${homejson}$DATE.log
for json in `find /home/yangrenqiang/dumpjson/json/ -name "song*.json"`
do
        curl -XPOST 'http://115.29.10.169:9200/moodbox/song/_bulk?pretty' --data-binary @$json
done
echo "导入song到elaticsearch服务器完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)导入song到elaticsearch服务器完成" >> ${homejson}$DATE.log
#删除/home/yangrenqiang/dumpjson/json/下面所有的json文件
rm -rf ${homejson}song*.json
echo "$(date +%Y年%m月%d日%H时%M分%S秒)删除/home/yangrenqiang/dumpjson/json/下面所有的json文件" >> ${homejson}$DATE.log
exit
