# SF Template

## How to work with the project

Create the following files containing secrets un der the build/secrets directory:

-   DATABASE_PASSWORD
-   MAILER_DSN

In your .env.local file, set `COOKIE_SECURE=false` as we don't use HTTPS in our local environment.

Then run docker compose :

```
docker compose up -d
```

```
docker compose exec app composer i
```


Main useful commands:

```
docker compose exec app vendor/bin/php-cs-fixer fix
docker compose exec app vendor/bin/phpstan analyse
docker compose exec app vendor/bin/twigcs templates
```
