
# How to Selfhost Latest Mautic on Ubuntu Machine - Step by Step Guide
In this guide, we’ll walk you through the process of setting up the Mautic email marketing platform on Ubuntu 22.04 with the Apache web server.

Mautic is a powerful, open-source alternative to popular commercial email marketing services such as MailChimp, giving you full control over your campaigns without the limitations of paid platforms. 

Mautic is a free open source alternative to commercial email service providers like MailChimp.

## Requirements
To follow this guide, you need the following;
1. Ubuntu 22.04 OS running on your Local Computer/Virtual Machine or on a Remote Server(VPS).
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
Now we need to set `www-data` (Apache user) as the owner of document root (otherwise known as web root). By default it’s owned by the root user.
```
sudo chown www-data:www-data /var/www/html/ -R
```
By default, Apache uses the system hostname as its global `ServerName`. If the system hostname can’t be resolved in DNS, then you will probably see the following error after running `sudo apache2ctl -t` command.

Sample Output:
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
# Step 4: Install PHP8.1
``` 
sudo apt-get install -y php8.1 php8.1-cli php8.1-curl php8.1-mbstring php8.1-mysql \
 php8.1-xml php8.1-zip php8.1-intl php8.1-gd php8.1-imap php8.1-bcmath \
 libapache2-mod-php8.1 unzip
```
Restart Apache for the changes to take effect.
```
sudo systemctl restart apache2
```

# Step 5: Install NPM (Required by Mautic 5 and above)
```
sudo apt install npm
```
Check npm version
```
npm --version
```
# Step 6: Create a MariaDB Database and User for Mautic

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
