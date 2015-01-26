What is this?
-------------

A (very) simple blogging platform that I created to gain experience with PHP and SQL.

How do I use it?
----------------

1. Grab the code and put it in your webserver's docroot.
2. Open up common.php and set values for $db_host, $db_user, $db_pass, $db_dbname, and $blogname
3. Optional: If you want clean URLs, set $use_clean_urls in common.php to true, and add the following rewrite rules to your Apache config:

```
RewriteEngine On
RewriteRule /index/(.*) /index.php?q=$1
RewriteRule /writer/(.*) /writer.php?q=$1
RewriteRule /admin/(.*) /admin.php?q=$1
```

Important things to note
------------------------

* Blog posts are just markdown -- the markdown is compiled into HTML on the client side by [marked](https://github.com/chjj/marked).
* This is by no means production ready. Like, really. **Please** do not use this in production.
* There's no way to delete posts...unless you log into the database and "DROP TABLE posts;"