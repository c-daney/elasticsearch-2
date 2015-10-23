#!/bin/bash
home=/home/yangrenqiang/dumpjson/
homejson=/home/yangrenqiang/dumpjson/json/
DATE=$(date +%Y年%m月%d日)
#开始dump singer
echo "开始dump singer表"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始dump singer表" >> ${homejson}$DATE.log
/usr/local/php/bin/php ${home}index.php Index/singer
echo "dump singer表完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)dump singer表完成" >> ${homejson}$DATE.log
echo "开始把singer导入到elasticsearch服务器中"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)开始把singer导入到elasticsearch服务器中" >> ${homejson}$DATE.log
curl -XPOST 'http://115.29.10.169:9200/moodbox/singer/_bulk?pretty' --data-binary @${homejson}singer.json
echo "导入singer到elasticsearch服务器中完成"
echo "$(date +%Y年%m月%d日%H时%M分%S秒)导入singer到elasticsearch服务器中完成" >> ${homejson}$DATE.log
#删除/home/yangrenqiang/dumpjson/json/下面所有的json文件
rm -rf ${homejson}singer.json
echo "$(date +%Y年%m月%d日%H时%M分%S秒)删除/home/yangrenqiang/dumpjson/json/下面的singer.json文件" >> ${homejson}$DATE.log
exit
