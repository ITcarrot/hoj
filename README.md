# HOJ
一个基于UOJ开发的在线评测系统

### 我们的优势
- 更人性化的后台界面，更方便的系统管理

- 目标为校园OJ，配有注册审核机制

- 增加“练习”、“天梯”两大功能，方便管理题目

- 优化比赛系统，支持OI、ACM、IOI三种赛制

- 加入了题解系统，方便用户交流

- 自动加题系统，实现与SYZOJ（LOJ为首）兼容，拓宽题目来源

- 支持自选编译选项

- 增加大量用户个性化功能

- 美化了页面

- 更新了绝大部分插件，适配Ubuntu18.04

### 部署方法
#### Getting Started
你需要一个Ubuntu系统的server，建议使用Ubuntu 18.04。
You need a Ubuntu server, suggest Ubuntu 18.04.
- 在`https://pan.baidu.com/s/1AWBANZdKAsq0eoQV12Cf0g`输入`gwtd`下载`hoj_docker_containers.zip`并解压，得到`hoj_server.tar`和`hoj_judger.tar`
- Download `hoj_docker_containers.zip` in `https://pan.baidu.com/s/1AWBANZdKAsq0eoQV12Cf0g` with password `gwtd` and unzip it, get`hoj_server.tar` and `hoj_judger.tar`
- 执行`apt install docker docker.io`安装docker
- Execute `apt install docker docker.io` to install docker

#### 部署服务器镜像
##### Deploy server images
- 在`hoj_server.tar`的目录下执行`docker import hoj_server.tar hoj:stable`导入镜像
- Execute `docker import hoj_server.tar hoj:stable` in the directory where the `hoj_server.tar` is located
- 执行`docker run --name hoj -dit -p <port>:80 --cap-add SYS_PTRACE  hoj:stable /bin/sh -c /root/up`，`<port>`替换为网站的端口（一般替换为`80`），稍等片刻即可访问网站了。
- Execute `docker run --name hoj -dit -p <port>:80 --cap-add SYS_PTRACE  hoj:stable /bin/sh -c /root/up`, `<port>` should be replaced with the port of your website (suggest `80`) , the website will be ready in a minute.
- OJ默认管理员用户`std`，密码`stdstd`，数据库默认账户`root`，无密码。
- Default OJ super user is `std`, password `stdstd`, default mysql user is `root` with no password.
- 执行`docker exec -it hoj /bin/bash`可进入网站所在的“容器”，网站的配置文件在`/var/www/uoj/app/.config.php`，网站数据库名称`app_uoj233`，网页图片保存在`/var/www/uoj/pictures/`，网站文件保存在`/var/uoj_data/web/`，题目数据保存在`/var/uoj_data/`，用户文件保存在`/var/www/uoj/file/`。
- Execute `docker exec -it hoj /bin/bash` could enter the container of the site, the site config file is in `/var/www/uoj/app/.config.php`, the database name is `app_uoj233`, the uploaded pictures saved in `/var/www/uoj/pictures/`, the uploaded file saved in `/var/uoj_data/web/`, problem data saved in `/var/uoj_data/`，user's file saved in `/var/www/uoj/file/`.
- 如需修改数据库密码，请在网站配置文件中作出相应更改。
- If you changed the mysql password, please also change it in the site config file.

#### 部署评测机镜像
##### Deploy judger images
- 在`hoj_judger.tar`的目录下执行`docker import hoj_judger.tar hoj_judger:stable`导入镜像
- Execute `docker import hoj_judger.tar hoj_judger:stable` in the directory where the `hoj_judger.tar` is located
- 执行`docker run --name hoj_judger -dit -p 89:80 --cap-add SYS_PTRACE  hoj_judger:stable /bin/sh -c /root/up`。
- Execute `docker run --name hoj_judger -dit -p 89:80 --cap-add SYS_PTRACE  hoj_judger:stable /bin/sh -c /root/up`.
- 想一个评测机名称和密码
- Think of a judger name and its password
- 在服务器的数据库中执行`insert into judger_info value('<judger name>','<judger password>','<judger (ip) address>');`，请使用对应的值替换`<judger name><judger password><judger (ip) address>`。
- Execute in server's database `insert into judger_info value('<judger name>','<judger password>','<judger (ip) address>');`, please replace `<judger name><judger password><judger (ip) address>` with proper value.
- 在评测机的container中的`/home/local_main_judger/judge_client/.conf.json`和`/var/www/uoj/index.php`内指定位置填入评测机名称、密码和OJ的网址。
- Fill `/home/local_main_judger/judge_client/.conf.json` and `/var/www/uoj/index.php` in the judger's container with judger's name, password and OJ's web address at the specified location.
- 若评测机成功部署，你可以在网站“评测机”页面中看到评测机的运行状况。
- If everything is successful, you can see the judger running status in the page '评测机' of the site.