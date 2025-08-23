# How To Install Odoo with Docker on Ubuntu 22.04
Odoo is an open-source enterprise resource planning (ERP) platform developed in Python. It offers a wide range of plugins to support various business functions, including accounting, payroll, inventory management, and more.

In this tutorial, you’ll use Docker Compose to install both Odoo and a PostgreSQL database. After that, you’ll set up Nginx as a reverse proxy for your Odoo instance. Finally, you’ll secure your site with HTTPS by using Certbot to obtain and configure a TLS certificate from Let’s Encrypt.

# Requirements
To follow this guide, you need the following;
1. Ubuntu 22.04 OS running on your Local Computer/Virtual Machine or on a Remote Server(VPS).
2. Domain name (You can buy domain names from NameCheap or GoDaddy).
3. Domain Nameserver connected with Cloudfare.
4. Cloudfare Free Account (To use the Zero Trust Tunnel).

# Step 1: Update Software Packages
```
sudo apt update
sudo apt upgrade
```
# Step 2: Install Docker Compose
```
sudo apt install docker-compose
```
# Step 3: Creating Odoo folder
```
mkdir odoo
cd odoo
```
# Step 4: Creating Odoo and PostgreSQL containers
The file specifies two services. The first, named **odoo**, runs the Odoo application, while the second, **postgres**, serves as the PostgreSQL database container. Both services utilize named volumes to persist data outside the container runtime environment. Additionally, the odoo service maps port 8069 on your server to port 8069 within the container, allowing external access to the application.

Once you've finished editing the file, save and close it. If you're using **nano**, press `CTRL+O` followed by `ENTER` to save, then `CTRL+X` to exit.
```
nano docker-compose.yml
```
Paste the below:
```
version: '3'
services:
  odoo:
    image: odoo:15.0
    env_file: .env
    depends_on:
      - postgres
    ports:
      - "127.0.0.1:8069:8069"
    volumes:
      - data:/var/lib/odoo
  postgres:
    image: postgres:13
    env_file: .env
    volumes:
      - db:/var/lib/postgresql/data/pgdata

volumes:
  data:
  db:
```
Next we will `env` file
```
nano .env
```
Paste the below:
```
# postgresql environment variables
POSTGRES_DB=postgres
POSTGRES_PASSWORD=a_strong_password_for_user
POSTGRES_USER=odoo
PGDATA=/var/lib/postgresql/data/pgdata

# odoo environment variables
HOST=postgres
USER=odoo
PASSWORD=a_strong_password_for_user
```
Once you've finished editing your `.env` file, save your changes and close the text editor.

Now you're ready to launch the **odoo** and **postgres** containers using the **docker-compose** command:
```
sudo docker-compose up -d
```
