## Waglpz Web Application component

The Library enables you to work with PDF files.

### Requirements

PHP 7.4 or higher (see composer json)

### Installation

composer require waglpz/pdfizer

## Docker

### Create specific environment file for docker
```bash
# fish
printf APPUID=(id -u)\nAPPUGID=(id -g)\nDBPORT=3367\nAPPPORT=8081\n > .env

# bash
printf "APPUID=$(id -u)\nAPPUGID=$(id -g)\nDBPORT=3367\nAPPPORT=8081\n" > .env
```
### Build docker with docker-compose

```bash
docker-compose build --parallel --force-rm --no-cache --pull pdfizer
```

### Build Docker container included php and composer for working within

```bash
# fish
docker build --force-rm --build-arg APPUID=(id -u) --build-arg APPUGID=(id -g) --tag waglpz/pdfizer .docker/

# bash
docker build --force-rm --build-arg APPUID=$(id -u) --build-arg APPUGID=$(id -g) --tag waglpz/pdfizer .docker/
```

## Code Quality and Testing ##

To check for coding style violations, run

```
composer waglpz:cs-check
```

To automatically fix (fixable) coding style violations, run

```
composer waglpz:cs-fix
```

To check for static type violations, run

```
composer waglpz:cs-fix
```

To check for regressions, run

```
composer waglpz:test
```

To check all violations at once, run

```
composer waglpz:check
```
