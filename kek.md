# Recommended Configs

## /etc/nginx/sites-available/$WEBSITE_NAME

```
server {
        root /var/www/$WEBSITE_NAME/html;
        index index.php index.html index.htm index.nginx-debian.html;
        server_name $WEBSITE_NAME www.$WEBSITE_NAME;
        
        client_max_body_size 4M;

        proxy_cookie_path / "/; HTTPOnly; Secure";
        add_header Set-Cookie "Path=/; Secure/ HttpOnly";
        add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
        add_header Content-Security-Policy "default-src https:; script-src https://code.jquery.com/jquery-3.6.0.min.js 'self' 'unsafe-inline'; img-src 'self'; style-src 'self'; frame-ancestors 'none'" always;
        add_header X-Content-Type-Options nosniff;
        add_header X-Frame-Options DENY;
        add_header X-XSS-Protection "1; mode=block";

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        }
        location ~ /\.ht {
                deny all;
        }
        location ~ /\.git {
                deny all;
        }
        location ~ ^/.well-known/acme-challenge/* {
                allow all;
        }
        location / {
                try_files $uri $uri/ =404;
        }
    ssl_protocols TLSv1.2 TLSv1.3;
}
server {
        listen 80;
        listen [::]:80;
        server_name $WEBSITE_NAME www.$WEBSITE_NAME;
}
```

## Change/add these lines for /etc/php/7.2/fpm/php.ini

```
session.cookie_httponly = 1
session.cookie_secure bool = 1
extension=gd2
extension=mysqli
```

## Change these lines for /etc/letsencrypt/options-ssl-nginx.conf

```
ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
```