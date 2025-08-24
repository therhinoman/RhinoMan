# How To Install Odoo 18 with Docker on Ubuntu 22.04
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
    image: odoo:18.0
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
Next we will create `.env` file
```
nano .env
```
Insert the following lines into the file, replacing the highlighted values with your chosen **POSTGRES_USER** and **POSTGRES_PASSWORD**.

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
## `IMPORTANT:` Starting Odoo services automatically at boot
First we can check if the services for **Odoo** is running in Docker.
```
sudo docker ps -a
```
Now, you can see the `Container ID`, take a note of container IDs for `odoo` and `Postgres`.

We will use the container ID to set Restart Policy so that your containers restart automatically at boot.
```
sudo docker update --restart unless-stopped `Container ID`.
```
Do the above step for `odoo` and `Postgres` services.
# Step 5: Installing and Configuring Nginx
```
sudo apt install nginx
```
Allow public traffic to ports **80** and **443** (HTTP and HTTPS) using the Nginx Full UFW application profile
```
sudo ufw allow "Nginx Full"
```
Next, create a new Nginx configuration file in the `/etc/nginx/sites-available` directory. We'll name it **odoo.conf**, but you can choose a different name if you prefer.
```
sudo nano /etc/nginx/sites-available/odoo.conf
```
Paste the following into the new configuration file, making sure to replace `your_domain_here` with the domain you've set up to point to your Odoo server such as `odoo.example.com`, for example:
```
server {
    listen       80;
    listen       [::]:80;
    server_name  your_domain_here;

    access_log  /var/log/nginx/odoo.access.log;
    error_log   /var/log/nginx/odoo.error.log;

    location / {
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-Host $host;
      proxy_set_header X-Forwarded-Proto https;
      proxy_pass http://localhost:8069;
  }
}
```
Save and close the file, then enable the configuration by linking it into `/etc/nginx/sites-enabled/`
```
sudo ln -s /etc/nginx/sites-available/odoo.conf /etc/nginx/sites-enabled/
```
Let's verify that the configuration file syntax is correct.
```
sudo nginx -t
```
Finally, reload the **nginx** service with the new configuration
```
sudo systemctl reload nginx.service
```
# Step 6: Setting up Cloudfare with Zero Trust Tunnel
* Log in to your `Cloudfare Account`.
* Goto `Tunnels` under `Networks` on the Left Navigation Panel `Zero Trust Home`
* Then create a new Tunnel `Create a tunnel`
* `Select Cloudflared`
* Name your tunnel `odoo`
* Choosing your environment, since we are on Ubuntu we will be using Debian. Select `Debian`
* Install and run a connector. Copy and paste the code shown to your Ubuntu Machine Terminal.
# Step 7: Installing Certbot and Setting Up TLS Certificates

First, install Certbot and its Nginx plugin
```
sudo apt install certbot python3-certbot-nginx
```
Next, run certbot in --nginx mode, and specify the same domain that you used in the Nginx server_name configuration directive
```
sudo certbot --nginx -d your_domain_here
```
You’ll be prompted to agree to the Let’s Encrypt terms of service, and to enter an email address.

Afterwards, you’ll be asked if you want to redirect all HTTP traffic to HTTPS. It’s up to you, but this is generally recommended and safe to do.

After that, Let’s Encrypt will confirm your request and Certbot will download your certificate.

Certbot will automatically reload Nginx with the new configuration and certificates. Reload your site in your browser and it should switch you over to HTTPS automatically if you chose the redirect option.

Your site is now secure and it’s safe to continue with the web-based setup steps.
# Step 8: Setting up Odoo
In your web browser, refresh the page. You should now see Odoo’s database configuration screen loaded over a secure **https://** connection. From here, you can safely enter usernames and passwords to finish the installation.

The details you provide on this page will guide Odoo in creating its PostgreSQL database and setting up the default administrative account.

Complete the following fields:

**Database Name:** odoo

**Email:** your email address

**Password:** a strong, unique password for the administrator account

**Demo data:** select this option if this is your first Odoo installation

The remaining fields can be left as their default values. Be sure to save your chosen email and password, as you’ll need them to log in to Odoo later.

Finally, click **Create database** at the bottom left of the page. The setup may take a minute or two while Odoo creates the necessary database tables. Once finished, you’ll be redirected to the Odoo Apps administration page.
