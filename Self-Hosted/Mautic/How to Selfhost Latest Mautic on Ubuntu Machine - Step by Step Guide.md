
# How to Selfhost Latest Mautic on Ubuntu Machine - Step by Step Guide
In this guide, we’ll walk you through the process of setting up the Mautic email marketing platform on Ubuntu 24.04 with the Apache web server.

Mautic is a powerful, open-source alternative to popular commercial email marketing services such as MailChimp, giving you full control over your campaigns without the limitations of paid platforms. 

Mautic is a free open source alternative to commercial email service providers like MailChimp.

## Benefits of Self-Hosting Mautic
* Full Data Ownership & Privacy
* Cost Savings at Scale
* Unlimited Customization
* Scalability & Flexibility
* Integrations & API Freedom
* Advanced Features Without Extra Cost
* Community & Open Source Ecosystem


## Requirements
To follow this guide, you need the following;
1. Ubuntu 24.04/22.04 OS running on your Local Computer/Virtual Machine or on a Remote Server(VPS).
2. Domain name (You can buy domain names from NameCheap or GoDaddy).
3. Domain Nameserver connected with Cloudfare. **YouTube Guide**: https://www.youtube.com/watch?v=TngeYJmp5pU
4. Cloudfare Free Account (To use the Zero Trust Tunnel).
5. Apache web server.
6. MariaDB.
7. PHP8.1

For this guide, I'm using **Ubuntu 22.04** running locally on a **VMware® Workstation 17 Pro**

## Optional: Installing Sublime Text Editor
For users unfamiliar with terminal-based text editors like `Nano` or `Vim`, `Sublime Text` provides an easier and more user-friendly option.

The apt repository contains packages for both x86-64 and arm64.

Install the GPG key
```
wget -qO - https://download.sublimetext.com/sublimehq-pub.gpg | sudo tee /etc/apt/keyrings/sublimehq-pub.asc > /dev/null
```
Select the channel to use:
* Stable Version
```
echo -e 'Types: deb\nURIs: https://download.sublimetext.com/\nSuites: apt/stable/\nSigned-By: /etc/apt/keyrings/sublimehq-pub.asc' | sudo tee /etc/apt/sources.list.d/sublime-text.sources
```
Update apt sources and install Sublime Text
```
sudo apt-get update
sudo apt-get install sublime-text
```

# Step 1: Update Software Packages

Before we install the LAMP stack, it’s a good idea to update the repository and software packages. Run the following commands on your Ubuntu 24.04 OS.

```
sudo apt update
sudo apt upgrade
```


# Step 2: Install Apache Web Server
Enter the following command to install Apache Web server. The apache2-utils package will install some useful utilities like Apache HTTP server benchmarking tool (ab).
```
sudo apt install -y apache2 apache2-utils
```
After it’s installed, Apache should be automatically started. Check its status with ```systemctl.```
```
systemctl status apache2
```
Sample output:
```
● apache2.service - The Apache HTTP Server
     Loaded: loaded (/usr/lib/systemd/system/apache2.service; enabled; preset: enabled)
     Active: active (running) since Sun 2025-08-17 02:12:22 +04; 14s ago
       Docs: https://httpd.apache.org/docs/2.4/
   Main PID: 7054 (apache2)
      Tasks: 55 (limit: 9377)
     Memory: 5.5M (peak: 5.9M)
        CPU: 24ms
     CGroup: /system.slice/apache2.service
             ├─7054 /usr/sbin/apache2 -k start
             ├─7055 /usr/sbin/apache2 -k start
             └─7056 /usr/sbin/apache2 -k start

```
`Hint`: If the above command doesn’t quit immediately, you can press `Q key` to gain back control of the terminal.
If it’s not running, use systemctl to start it.

It’s also a good idea to enable Apache to automatically start at system boot time.
```
sudo systemctl enable apache2
```
Check Apache version:
```
apache2 -v
```
Sample Output:
```
Server version: Apache/2.4.58 (Ubuntu)
Server built:   2025-08-11T11:10:09
```
If you are installing **LAMP** on your local Ubuntu 24.04 computer, then type `127.0.0.1` or `localhost` in the browser address bar.

<img width="1241" height="666" alt="image" src="https://github.com/user-attachments/assets/621ffda9-e189-4582-8804-eeb926022825" />


Now we need to set `www-data` (Apache user) as the owner of document root (otherwise known as web root). By default it’s owned by the root user.
```
sudo chown www-data:www-data /var/www/html/ -R
```
By default, Apache uses the system hostname as its global `ServerName`. If the system hostname can’t be resolved in DNS, then you will probably see the following error after running `sudo apache2ctl -t` command.

```
AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 127.0.0.1. Set the 'ServerName' directive globally to suppress this message.
```
To solve this problem, we can set a global `ServerName` in Apache. Use the Sublime text editor to create a new configuration file.
```
sudo subl /etc/apache2/conf-available/servername.conf
```
Add the following line in this file.

`ServerName localhost`
Save and close the file. To save a file in `subl` or `subl` text editor, press `Ctrl+O`, then press `Enter` to confirm. To exit, press `Ctrl+X`. 

Then enable this config file.
```
sudo a2enconf servername.conf
```
Reload Apache for the change to take effect.
```
sudo systemctl reload apache2
```
Now if you run the `sudo apache2ctl -t` command again, you won’t see the above error message.

Sample Output:
```
user@ubuntu-vm:~$ sudo apache2ctl -t
Syntax OK
```

# Step 3: Install MariaDB Database Server
MariaDB is a drop-in replacement for MySQL. It is developed by former members of MySQL team who are concerned that Oracle might turn MySQL into a closed-source product. Enter the following command to install MariaDB on Ubuntu 24.04.
```
sudo apt install mariadb-server mariadb-client
```
After it’s installed, MariaDB server should be automatically started. Use **systemctl** to check its status.
```
systemctl status mariadb
```
Sample Output:
```
● mariadb.service - MariaDB 10.11.13 database server
     Loaded: loaded (/usr/lib/systemd/system/mariadb.service; enabled; preset: enabled)
     Active: active (running) since Sun 2025-08-17 02:23:43 +04; 4s ago
       Docs: man:mariadbd(8)
             https://mariadb.com/kb/en/library/systemd/
   Main PID: 10048 (mariadbd)
     Status: "Taking your SQL requests now..."
      Tasks: 13 (limit: 61890)
     Memory: 78.7M (peak: 82.5M)
        CPU: 253ms
     CGroup: /system.slice/mariadb.service
             └─10048 /usr/sbin/mariadbd

```
To enable MariaDB to automatically start at boot time, run
```
sudo systemctl enable mariadb
```
By default, the MariaDB package on Ubuntu uses `unix_socket` to authenticate user login, which basically means you can use username and password of the OS to log into MariaDB console. So you can run the following command to login without providing MariaDB root password.
```
sudo mariadb -u root
```
To exit, run
```
exit;
```
Check MariaDB server version information.
```
mariadb --version
```
As you can see, we have installed MariaDB Ver 15.1 Distrib 10.11.13.
```
mariadb  Ver 15.1 Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64) using  EditLine wrapper
```
# Step 4: Install PHP7.4
PHP7.4 is the most stable version of PHP and has a minor performance edge over PHP7.3. Enter the following command to install PHP7.4 and some common PHP modules. It is most compatible version with Mautic.

Install **software-properties-common** first
```
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
```
Press `Enter` when prompted
```
Adding repository.
Press [ENTER] to continue or Ctrl-c to cancel.
```
Install Required and Recommended PHP Modules
```
sudo apt install -y php7.4 php7.4-cli php7.4-json php7.4-common php7.4-mysql php7.4-zip php7.4-gd php7.4-mbstring php7.4-curl php7.4-xml php7.4-bcmath php7.4-imagick php7.4-fpm php7.4-imap php7.4-bz2 php7.4-intl php7.4-gmp

```

Enable the Apache PHP7.4 module then restart Apache Web server.
```
sudo a2enmod php7.4
sudo systemctl restart apache2
```
Sample Output:
```
user@ubuntu-vm:~$ sudo a2enmod php7.4
sudo systemctl restart apache2
Considering dependency mpm_prefork for php7.4:
Considering conflict mpm_event for mpm_prefork:
Considering conflict mpm_worker for mpm_prefork:
Module mpm_prefork already enabled
Considering conflict php5 for php7.4:
Module php7.4 already enabled
```
Check PHP version information.
```
php --version
```
Sample Output:
```
PHP 7.4.33 (cli) (built: Jul  3 2025 16:41:49) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
    with Zend OPcache v7.4.33, Copyright (c), by Zend Technologies
```
To test PHP scripts with Apache server, we need to create a `info.php` file in the document root directory.
```
sudo subl /var/www/html/info.php
```
Paste the following PHP code into the file.
```
<?php phpinfo(); ?>
```
Save the file. Now in the browser address bar, enter `127.0.0.1/info.php` or `localhost/info.php`.

You should see your server’s PHP information. This means PHP scripts can run properly with Apache web server.

<img width="1240" height="770" alt="image" src="https://github.com/user-attachments/assets/382ee1f3-c557-4553-be3f-514a27716ab5" />



## How to Run PHP-FPM with Apache
There are basically two ways to run PHP code with Apache web server:

* Apache PHP module
* PHP-FPM.

**PHP-FPM**, or PHP FastCGI Process Manager, is an alternative FastCGI daemon for PHP that provides advanced process management for high-traffic websites. It is widely used with web servers like Nginx and Apache to efficiently handle PHP requests.

In the above steps, the Apache PHP7.4 module is used to handle PHP code, which is usually fine. But in some cases, you need to run PHP code with PHP-FPM instead. Here’s how.

Disable the Apache PHP7.4 module.
```
sudo a2dismod php7.4
```
Install PHP-FPM.
```
sudo apt install php7.4-fpm
```
Enable `proxy_fcgi` and `setenvif` module.
```
sudo a2enmod proxy_fcgi setenvif
```
Enable the `/etc/apache2/conf-available/php7.4-fpm.conf` configuration file.
```
sudo a2enconf php7.4-fpm
```
Restart Apache for the changes to take effect.
```
sudo systemctl restart apache2
```
Now if you refresh the `info.php` page in your browser, you will find that Server API is changed from `Apache 2.0 Handler` to `FPM/FastCGI`, which means Apache web server will pass PHP requests to PHP-FPM.

Sample Output:

<img width="1237" height="729" alt="image" src="https://github.com/user-attachments/assets/a31f06c3-5981-4856-8909-7115c4cac9d7" />

You have successfully installed LAMP stack (Apache, MariaDB and PHP7.4) on Ubuntu 24.04. 
For your server’s security, you should delete `info.php`.
```
sudo rm /var/www/html/info.php
```
# Step 5: Download Mautic 4.2 onto Your Ubuntu 24.04 Server
Download the Mautic 4.2 version by executing the following command on your server
```
wget https://github.com/mautic/mautic/releases/download/4.2.1/4.2.1-update.zip
```
Install the unzip utility and unzip it to `/var/www/mautic/` directory.
```
sudo apt install unzip

sudo mkdir -p /var/www/mautic/

sudo unzip 4.2.1-update.zip -d /var/www/mautic/
```
Then make the web server user `(www-data)` as the owner of this directory.
```
sudo chown -R www-data:www-data /var/www/mautic/
```
# Step 6: Create a MariaDB Database and User for Mautic
Log in to MariaDB console.
```
sudo mysql -u root
```
Next, create a new database for Mautic using the following command. This tutorial names it `mautic`, you can use whatever name you like for the database.
```
CREATE DATABASE mautic DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
```
The following command will create a database user and password, and at the same time grant all permission of the new database to the new user so later on Mautic can write to the database. Replace with your preferred database name, username and password.
```
GRANT ALL ON mautic.* TO 'mauticuser'@'localhost' IDENTIFIED BY 'password';
```
Flush privileges table and exit MariaDB console.
```
FLUSH PRIVILEGES;

EXIT;
```
### If you use Apache web server, then you need to disable the PHP module for Apache.
```
sudo a2dismod php7.4
```
You also need to disable the prefork MPM module in Apache.
```
sudo a2dismod mpm_prefork
```
Now you need to run the following command to enable three modules in order to use PHP-FPM in Apache, regardless of whether `mod_php` is installed on your server.
```
sudo a2enmod mpm_event proxy_fcgi setenvif
```
Then restart Apache.
```
sudo systemctl restart apache2
```
# Step 7: Create Apache Virtual Host Config File for Mautic

**Apache**

If you use Apache web server, create a virtual host for Mautic.
```
sudo subl /etc/apache2/sites-available/mautic.conf
```
Put the following text into the file. Replace `mautic.example.com` with your real domain name. 
Later in this guide we will connect the domain with `Cloudfare Zero Trust Tunnel`.
```
<VirtualHost *:80>
  ServerName mautic.example.com
  DocumentRoot /var/www/mautic/

  ErrorLog ${APACHE_LOG_DIR}/error.log
  CustomLog ${APACHE_LOG_DIR}/access.log combined

  <Directory />
    Options FollowSymLinks
    AllowOverride All
  </Directory>

  <Directory /var/www/mautic/>
    Options FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>

</VirtualHost>
```
Save and close the file. Then enable this virtual host with:
```
sudo a2ensite mautic.conf
```
Reload Apache for the changes to take effect.
```
sudo systemctl reload apache2
```

# Step 8: Setting up Cloudfare with Zero Trust Tunnel.

Assuming that you have already connected your Godaddy domain `Nameserver` with Cloudfare.

* Log in to your `Cloudfare Account`.

* Goto `Tunnels` under `Networks` on the Left Navigation Panel `Zero Trust Home`

* Then create a new Tunnel `Create a tunnel`
* `Select Clodflared`
* Name your tunnel
* Choosing your environment, since we are on Ubuntu we will be using Debian. Select `Debian`
* Install and run a connector. Copy and paste the code shown to your Ubuntu Machine Terminal.
  
<img width="1218" height="918" alt="image" src="https://github.com/user-attachments/assets/58a86e74-a293-4ffb-84fd-0966aa17a292" />

Sample Output:
```
2025-08-16T23:58:26Z INF Using Systemd
2025-08-16T23:58:27Z INF Linux service for cloudflared installed successfully
```

Your connectors will automatically show here once cloudflared has been successfully installed on your machine.
<img width="1356" height="157" alt="image" src="https://github.com/user-attachments/assets/d28694b3-1ce4-4ff8-bc58-c0c8cbae31e0" />

In your terminal in Ubuntu Machine, check your IP Address
```
ip a
```
<img width="1063" height="355" alt="image" src="https://github.com/user-attachments/assets/7cf6a8da-7003-43dc-a7ba-503d2e0ecfba" />
<img width="2120" height="517" alt="image" src="https://github.com/user-attachments/assets/4ed9300f-5fb2-4715-b10f-ed15651f2540" />



Route Traffic and Add public hostname for `mautic`

<img width="2273" height="815" alt="image" src="https://github.com/user-attachments/assets/cf1d3d90-fae7-44e1-b135-9d4baa2635d7" />

Your Cloudfare tunnel is successfully connected with your Ubuntu Machine

<img width="2120" height="517" alt="image" src="https://github.com/user-attachments/assets/8013f271-a176-4c4c-9a06-5ec914d6975f" />



Now you should be able to see the Mautic web-based install wizard at `http://mautic.example.com/installer`.


# Step 9: Finish Mautic Installation in Web Browser

Now in your browser address bar, type in your domain name for Mautic to access the web install wizard.
```
https://mautic.your-domain.com/index.php/installer
```
<img width="1236" height="790" alt="image" src="https://github.com/user-attachments/assets/73633908-2838-469d-a6bc-a230e4601f5f" />

Let's fix the warning. If you see the following warning, click on `Some Recommendations`.

The **memory_limit** setting in your PHP configuration is lower than the suggested minimum limit of 512M. Mautic can have performance issues with large datasets without sufficient memory.
Then edit the php.ini file.
```
sudo nano /etc/php/7.4/fpm/php.ini
```

Find the following line
```
memory_limit = 128M
```
Change its value to 512M.
```
memory_limit = 512M
```
Also, you can set a default timezone in PHP. Find the following line.
```
;date.timezone =
```
Change it to:
```
date.timezone = America/New_York
```
You can find your own time zone format on the official PHP website. Save and close the file. Then reload PHP7.4-FPM, and the warning should be gone.
```
sudo systemctl reload php7.4-fpm
```
Refresh your Web Browser. And Click `Next Step`.

# Step 10: Mautic Installation - Database Setup
We will use the Database details used in **Step 6**. You can leave **Database Table Prefix** blank.

<img width="1216" height="862" alt="image" src="https://github.com/user-attachments/assets/e176b8aa-6377-4f9e-a0c9-f0d674c62db9" />

### Mautic Installation - Administrative User

We need set up our admin priviledges to access Mautic.
Please use your prefered `Admin Username` `Admin Password` `First name` `Last Name` `E-mail Address`.

You can log in to Mautic with your `Admin Username` or `E-mail Address`

<img width="1205" height="725" alt="image" src="https://github.com/user-attachments/assets/eefd1875-af72-498c-bb38-cb45eb3c3173" />

### Mautic Installation - Email Configuration
You can set up your E-mail account to be used by Mautic to send Marketing emails. If you are using **Gmail**, make sure to create **Gmail App Password** before proceeding.

<img width="1224" height="687" alt="image" src="https://github.com/user-attachments/assets/2b7c8e34-5433-445c-a529-e05c4a00d2c1" />

# First time Mautic Log in: Error Handling
If you logging into Mautic for the first time you will might see an error `Unable to resolve binding type, invalid or unsupported http request`

<img width="1192" height="732" alt="image" src="https://github.com/user-attachments/assets/15461a2f-c7dc-47df-9a01-6ba1501020bb" />


To fix this, let's go back to your Ubuntu Machine Terminal and run the below code.
```
sudo a2enmod rewrite
```
Then, reload Apache Server
```
sudo systemctl reload apache2
```

# Congratulations! You have successfully installed Mautic 4.2 Self Hosted on your Ubuntu Machine.

Welcome to your Mautic Dashboard.

<img width="1285" height="999" alt="image" src="https://github.com/user-attachments/assets/11347516-d979-4ae8-be71-730202b933cf" />
