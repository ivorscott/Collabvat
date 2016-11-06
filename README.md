# README #

## Collabvat
Collabvat is a central hub where creatives get together to critique art, share ideas, events and more.  

![Alt text](/assets/images/example.gif?raw=true "Preview")


## Purpose ##
Many artists have a hard time making a living and gaining insightful feedback about their work. What can be done now to
support them? There is an astonishing amount of resources available for artists: jobs, grants, schools and residencies
to name a few. Though these resources can be hard to find if you don’t know where to look or who to ask. A social
network for the arts could be a modern model where users can get information from each other and gain access to a
central hub of artist resources from 3rd party sites. Our solution is Collabvat, an app that allows the exchange of
ideas about art through a powerful critique platform. It is free of charge, for artists, by artists, and openly shares
artist resources found on the Internet.

[Requirements Doc](https://docs.google.com/document/d/1Ckl59U7LSX2dlPDxujrjHEP3dOsh1ufzaP7DbiLC2IE/) (needs revision)

## Mac instructions
### Install NodeJs, Sass, and Gulp
Installation will be much simpler once I integrate vagrant - sorry.

        $ brew install node   
        $ sudo gem install sass  
        $ npm install -g gulp  
        $ node -v && npm -v && sass -v  

### Setting up the Server

This is a PHP application. I only use node for npm.

1. Have Apache, MYSQL and PHP installed. I use XAMP to do this on my mac. Install it.  
[https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)
2. Start up MYSQL server, and Apache.
3. Go to [localhost/phpmyadmin](http://localhost/phpmyadmin)
4. Create database called *collabvat* and select it
5. Import collabvat.sql
6. Register a new virtual host in Apache (I use Mac, but this should be similar on other OS)

        $ vim /Applications/XAMPP/xamppfiles/etc/httpd.conf

        <VirtualHost *:80>
            DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/collabvat"
            ServerName dev.collabvat.co
        </VirtualHost>

7. Make host file changes

        $ vim /etc/hosts

        127.0.0.1       dev.collabvat.co

8. Restart the Apache Server.

9. Clone Repository and change folder permission

        $ cd /Applications/XAMPP/xamppfiles/htdocs    
        $ git clone https://github.com/ivorscott/collabvat.git    
        $ cd collabvat  
        $ chmod o+w model/images

10. copy config.sample.php and make config.php you won't need to change the file unless
 you change the database credentials because after a clean installation of XAMP
 the user is 'root' and there is no password

### Get Dependencies

        $ npm install  
        $ gulp

Done.

I use gulp to automate tasks like minifying javascript, launching the app, watching for changes, compiling sass,
automating a browser refresh, and much more.  

### Pusher

If you want to play with the chat portions of the code, or realtime notifications, I am using pusher to do this.
Make your own account and switch the API Key to yours.

        collabvat/views/layout.php line 24

[https://pusher.com/docs](https://pusher.com/docs)  
[pusher login](https://dashboard.pusher.com/accounts/sign_in)   
