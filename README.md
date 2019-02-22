##### swoole加速lumen 简单demo
###### 启动 php server/Websocket.php
###### 热加载 sh swoole_reload.sh  
###### Nginx 配置参考
```
//swoole转发模式
server {

    listen 80;
    server_name sw.lumen.cn  www.sw.lumen.cn;
    access_log /var/log/nginx/sw.lumen.cn.access.log main;
    error_log  /var/log/nginx/sw.lumen.cn.error.log;

    location / {

        root /var/www/sw-lumen/public;
        index index.html index.htm index.php;

        if (!-e $request_filename) {
            //代理地址
            proxy_pass http://172.18.0.2:8888;
        }

    }
}
```
```
//fpm模式
server {

    listen 80;
    server_name sw.lumen.cn  www.sw.lumen.cn;
    access_log /var/log/nginx/sw.lumen.cn.access.log main;
    error_log  /var/log/nginx/sw.lumen.cn.error.log;

    root /var/www/sw-lumen/public;
    index index.html index.htm index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_pass php71:9000;
    }

}
```

å

