# How To Install Odoo with Docker on Ubuntu 22.04
Odoo is an open-source enterprise resource planning (ERP) platform developed in Python. It offers a wide range of plugins to support various business functions, including accounting, payroll, inventory management, and more.

In this tutorial, you’ll use Docker Compose to install both Odoo and a PostgreSQL database. After that, you’ll set up Nginx as a reverse proxy for your Odoo instance. Finally, you’ll secure your site with HTTPS by using Certbot to obtain and configure a TLS certificate from Let’s Encrypt.

# Requirements
To follow this guide, you need the following;
1. Ubuntu 22.04 OS running on your Local Computer/Virtual Machine or on a Remote Server(VPS).
2. Domain name (You can buy domain names from NameCheap or GoDaddy).
3. Domain Nameserver connected with Cloudfare.
4. Cloudfare Free Account (To use the Zero Trust Tunnel).
