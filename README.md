Project Guide
==================

##I. Setup Dev
###1. Extract file vendor.rar

###2. Setup env
and initialize the environment
~~~
php init
và chọn option 0
~~~
###3. Config server
####3.1. Thêm nội dung sau vào file C:\Windows\System32\drivers\etc\hosts
~~~
127.0.0.1  datn.lc
127.0.0.1  admin-datn.lc
~~~
####3.1. Thêm nội dung sau vào file xampp\apache\conf\extra\httpd-vhosts.conf
~~~
<VirtualHost *:80>
    ServerAdmin webmaster@datn.lc
    DocumentRoot "C:/xampp/htdocs/datn/frontend/web"
    ServerName datn.lc
    ErrorLog "logs/datn-error.log"
    CustomLog "logs/datn-access.log" common
	<Directory "C:/xampp/htdocs/datn/frontend/web">
		Options FollowSymLinks
		AllowOverride All
		DirectoryIndex index.php
		Require all granted
	</Directory>
</VirtualHost>
<VirtualHost *:80>
    ServerAdmin webmaster@admin-datn.lc
    DocumentRoot "C:/xampp/htdocs/datn/backend/web"
    ServerName admin-datn.lc
    ErrorLog "logs/admin-datn-error.log"
    CustomLog "logs/admin-datn-access.log" common
	<Directory "C:/xampp/htdocs/datn/backend/web">
		Options FollowSymLinks
		AllowOverride All
		DirectoryIndex index.php
		Require all granted
	</Directory>
</VirtualHost>
~~~

##II. Config server
###1. Start Realtime Server
```cmd
#run command
php yii realtime-server/start
```


