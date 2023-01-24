# Laravel

## Stack
- Laravel
- Docker

## Local Development
1. Make ***.env*** from ***.env.example*** and set the variables:
```
cp .env.example .env
```
You can specify the host port by adding or changing the value "APP_PORT={port number}" to access localhost:{port number}

2. Start ***docker***:
```
docker-compose up
```

## Xdebug
Xdebug listenes for connections on port 9003.
More info on IDE setup:
- [PHPStorm](https://matthewsetter.com/setup-step-debugging-php-xdebug3-docker/)
- [VSCode (PHP Debug plugin)](https://dev.to/jackmiras/xdebug-in-vscode-with-docker-379l)

## Docker services (local)
+ app

```
docker-compose exec app sh
```

## Open site

- [Link](http://localhost:8083)