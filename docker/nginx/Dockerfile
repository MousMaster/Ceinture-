# Dockerfile Nginx pour Laravel
FROM nginx:alpine

# Copie de la configuration
COPY default.conf /etc/nginx/conf.d/default.conf

# Création des répertoires de logs
RUN mkdir -p /var/log/nginx

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
