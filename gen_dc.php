<?php
process_dockerfile(".");
function process_dockerfile($dir="."){
    $list = glob("{$dir}/*");
    foreach($list as $item){
        if(is_dir($item)){
            process_dockerfile($item);
        }elseif(strtolower(basename($item)) == "dockerfile"){
            $dir = dirname($item);
            $rd = "{$dir}/README.md";
            if(!file_exists($rd)){
                continue;
            }
            // 提取 EXPOSE 端口
            $port = 80;
            $c = file_get_contents($item);
            preg_match("/EXPOSE\s([\d\s]+)/i", $c, $match);
            if($match && isset($match[1])){
                $port = trim($match[1]);
            }
            $ports = arr2ports(explode(" ", $port));
            if($ports == false)
                continue;
            // README.md 中提取镜像名
            $image = '';
            $c = file_get_contents($rd);
            preg_match("/medicean\/vulapps:([\w\.]+)/", $c, $match);
            if($match && isset($match[1])){
                $image = "medicean/vulapps:".trim($match[1]);
            }
            
            
            $dc = <<< EOT
version: '3'

services:
  vsapp:
    image: {$image}
    ports:
{$ports}
EOT;
            echo "$item\n$image\n$ports\n$dc\n===============\n";
            $dcfile = "{$dir}/docker-compose.yml";
            file_put_contents($dcfile, $dc);
        }
    }
}
function arr2ports($arr){
    $ports = "";
    $arr = array_unique($arr);
    foreach($arr as $item){
        $item = intval($item);
        if($item == false)
            continue;
        $ports .= "      - {$item}:{$item}\n";
    }
    return $ports;
}
