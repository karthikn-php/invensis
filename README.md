# Invensis

Application:

The search engine will index three types of entities : "users", "posts" and "comments"
- One user has multiple posts
- One post has multiple comments
- One user has multiple comments on a post


## Setup
Step 1: Setup Project in Apache root Directory
Step 2: In Linux environment, change sites config by using the following command
```
:/var/www/html/invensis$ sudo nano /etc/apache2/sites-available/000-default.conf
```
Step 3: Setup the site configuration as follows
```
<VirtualHost *:80>
        ServerName localhost
        ServerAdmin karthikn.php@gmail.com
        DocumentRoot /var/www/html/invensis/webroot
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
# CRTL + x to save the changes
```
Step 4: enabling URL rewrite
```
:/var/www/html/invensis$ sudo a2enmod rewrite
```
Step 5: restart apache server
```
:/var/www/html/invensis$ sudo service apache restart
:/var/www/html/invensis$ sudo service apache2 restart
```
Step 6: Create Database named:  test_myapp and invensis
Database configurations are under ./config/app_local.php
```
Create Database test_myapp;
Create Database invensis
```
Step 7: Cache configurations can be found in  /config/app.php

Step 8: Migrate Database using the below command
```
:/var/www/html/invensis$ ./bin/cake migrations migrate
```
Step 9: Migrations rollback can be performed using the below command
```
:/var/www/html/invensis$ ./bin/cake migrations rollback
```
Step 10: Migrations rollback can be performed using the below command
```
:/var/www/html/invensis$ ./bin/cake migrations rollback
```

Step 11: The Skeletons are prepared for Testcases, fixtures and yet to work.
```
:/var/www/html/invensis$ ./vendor/bin/phpunit
```
## API

1.Create User.
```
POST http://localhost/users/add

email:karthikn.php@gmail.com
username:karthikn_php
full_name:Karthikeyan Chinniah
imageURL:/image.png

```

2.List All Users.

```
To List All Users
GET http://localhost/users/list

To List Users by limit and page wise
GET http://localhost/users/list?limit=2&page=2
```

3.View User by UserID
```
GET http://localhost/users/view/2

GET http://localhost/users/view/:ID
```

4.Delete User by UserID
```
DELETE http://localhost/users/delete/2

DELETE http://localhost/users/delete/:ID
```

5.Update User
```
POST http://localhost/users/edit/2
POST http://localhost/users/edit/:ID

email: testuser@gmail.com
```

6.Search User
```
GET http://localhost/users/search?text=<Search Text>

POST http://localhost/users/search
text:karthik
```


7. Create Post
Post model requires user_id to identify which users post, so create user data initially later use this api

```
POST http://localhost/posts/add
title : test title
subtitle : test subtitle
content : sample content
imageURL : nothing.png
post_status : published
```

8.List All Posts.
```
To List All Posts
GET http://localhost/posts/list

To List Users by limit and page wise
GET http://localhost/posts/list?limit=2&page=1
```

9. Delete Post
Post model requires user_id to identify which users post, so create user data initially later use this api
```
DELETE http://localhost/posts/delete/1
DELETE http://localhost/posts/delete/:ID
```

10.Search Post
```
GET http://localhost/posts/search?text=<Search Text>

POST http://localhost/posts/search
text:karthik
```

11.Add Comments
```
POST http://localhost/comments/add
content:test content
post_id:3
```

12.List Comments by Post
```
GET http://localhost/comments/lists/3
```

13.View individual comment
```
GET http://localhost/comments/view/3
```
