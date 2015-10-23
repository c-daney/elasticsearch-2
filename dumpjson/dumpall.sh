#!/bin/bash
home=/home/yangrenqiang/dumpjson/
homejson=/home/yangrenqiang/dumpjson/json/
#开始执行dumpsong shell脚本
${home}dumpsong.sh
#开始执行dumpsinger shell脚本
${home}dumpsinger.sh
#开始执行dumpstation shell脚本
${home}dumpstation.sh
#删除/home/yangrenqiang/dumpjson/json/下面所有的json文件
rm -rf ${homejson}*.json
exit
