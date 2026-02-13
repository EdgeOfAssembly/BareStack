1) 
README.md

Create cool, short but memorably name for this educational dashboard project.
This project goal are to help beginners to create secure, fast, light-weight sites & login portals
following best practises, with minimal dependencies. Only HTML + CSS + PHP really needed plus some JavaScript for
some themes (like Matrix & Snow) animations. Don't forget to create new themes!

prerequisites are: 
- basic computer and computer networks knowledge
- willing to study code & learn.

Installation in three easy steps:

Step 1:

Get the FrankenPHP (https://frankenphp.dev/)
It's a combined Caddy www server + PHP server, with automatic HTTPS certs fetching.

I have provided already latest (as of this date; V1.11.2) static binary of frankenphp in this
repo.
But if you don't trust me (Good, never accept binaries from strangers) you can fetch the latest
version from here:

https://github.com/php/frankenphp/releases

To prove that the frankenphp I provided is geniuine, do:

wget https://github.com/php/frankenphp/releases/download/v1.11.2/frankenphp-linux-x86_64
md5sum franken*

<Here Step1.png>

Step 2:

rename and copy FrankenPHP into it's place:

mv frankenphp-linux-x86_64 frankenphp
sudo cp frankenphp /usr/local/bin


start FrankenPHP:

frankenphp php-server --listen 127.0.0.1:8080

Here I chosen port number 8080. You can use any port number as long as
it's greater thatn 1024. 1024 <= are reserved for root.

<Here Step2.png>

Step 3:

Open browser and type address http://localhost:8080/login.php
If it works you shoud see something like this;

<Here Step3.png>

Congratulations you have your local www/php server running & working!
No go on and study the code.

Add to this readme tips for beginners learning html + css + php + javascript
and read the code and experiment.
After readme ready, do extensive code review of codebase for security, modularity, ease of use,
best practises.



