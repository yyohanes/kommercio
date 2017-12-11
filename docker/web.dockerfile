FROM nginx

ADD ./docker/nginx/vhost.conf /etc/nginx/conf.d/default.conf