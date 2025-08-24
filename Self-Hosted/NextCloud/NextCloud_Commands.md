```
mkdir nextcloud 
cd nextcloud
```
```
subl .env
```

```
NEXTCLOUD_ADMIN_PASSWORD=password
MYSQL_ROOT_PASSWORD=password
MYSQL_PASSWORD=password
```

```
subl docker-compose.yml
```

```
sudo docker-compose up -d
```

```
sudo subl /etc/nginx/sites-available/nextcloud.conf
```

```
    server {
        listen 80;
        listen [::]:80;

        server_name nextcloud.rhinoman.me; 

        location / {
            proxy_pass http://localhost:8025
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
```

```
sudo ln -s /etc/nginx/sites-available/nextcloud.conf /etc/nginx/sites-enabled/
```
```
sudo nginx -t
```
```
sudo systemctl reload nginx
```

```
sudo apt update
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d nextcloud.rhinoman.me
```

```
docker exec -it nextcloud_db /bin/bash
```


```
mysql -u root -p
MariaDB [(none)]> CREATE USER 'nextcloud' IDENTIFIED BY 'password';
MariaDB [(none)]> GRANT ALL PRIVILEGES ON nextcloud.* TO 'nextcloud' IDENTIFIED BY 'password';
MariaDB [(none)]> quit
```


docker exec -it -u33 nextcloud php occ config:app:set dashboard layout --value=welcome,recommendations,spreed,mail,calendar,announcementcenter
