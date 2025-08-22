# Lineal vs Parallel vs Concurrent


> This repository contains an application that allows to solve the same problem using lineal programming, parallel programming and concurrent programming. 


[TOC]



------



## Summary

This repository contains a _dockerized_ environment for building PHP applications based on **php:8.4.11-fpm-alpine** with Caddy support.

### Highlights

- Unified environment to build <abbr title="Command Line Interface">CLI</abbr>, <u>web applications</u>, and/or <u>micro-services</u> based on **PHP 8**.
- Multi-stage Dockerfile allows you to create optimized **development** or **production-ready** Docker images.
- Uses **Caddy webserver**.
- **Self-signed local domains** thanks to Caddy.
- **Everything on separated Docker services**.
- Uses **RabbitMQ**.




------



## Built with

This repository is based on [Dockerized PHP](https://github.com/alcidesrc/dockerized-php), a lightweight dockerized environment to build PHP applications.



------



## Getting Started

Just clone the repository into your preferred path:

```bash
$ mkdir -p ~/path/to/my-new-project && cd ~/path/to/my-new-project
$ git clone git@github.com:AlcidesRC/lineal-vs-parallel-vs-concurrent.git .
```

> [!Important]
>
> Kindly examine the `README.md` file in the [Dockerized PHP](https://github.com/AlcidesRC/dockerized-php) repository to understand the build process and start using it.



### The Problem

This application performs an image pixelation based on specific block sizes:

-  Generating a new image with multiple areas of 5px x 5px filled with the average color from the source image
-  Generating a new image with multiple areas of 10px x 10px filled with the average color from the source image 
-  Generating a new image with multiple areas of 20px x 20px filled with the average color from the source image

### Software Architecture

The source code is under the **app** folder, and follows the Hexagonal Architecture pattern:

```text
.
├── app
│   ├── Concurrent                   # Concurrent version
│   ├── Lineal                       # Lineal version
│   ├── Parallel                     # Parallel version
│   └── Shared
├── composer.json
├── composer.lock
├── LICENSE
├── Makefile
├── phpcs.xml
├── phpstan.neon
├── phpunit.xml
├── public
├── README.md
├── tests
│   ├── Fixtures
│   ├── Integration                  # Integration tests related with concurrent version
│   │   └── Concurrent
│   └── Unit
│       ├── Concurrent               # Unit tests related with concurrent version
│       ├── Lineal                   # Unit tests related with lineal version
│       ├── Parallel                 # Unit tests related with parallel version
│       └── Shared
└── vendor
```

### Running Test Cases

This repository provides a **Makefile** with relevant steps:

#### Running Lineal Tests

```bash
$ make test-lineal
```

> [!Note]
>
> Once the tests are executed, the resultant files are located at **src/tests/Fixtures/source_lineal_xxxxx.webp**

#### Running Parallel Tests

```bash
$ make test-parallel
```

> [!Note]
>
> Once the tests are executed, the resultant files are located at **src/tests/Fixtures/source_parallel_xxxxx.webp**

#### Running Concurrent Tests

```bash
$ make test-concurrent
```

> [!Warning]
>
> This test suite **only publishes the messages** into RabbitMQ.

##### Starting the Consumers

```bash
$ make shell
/var/www/html $ composer start-workers
```

#####  Process the Result

Once workers complete he consumption, please execute this command to process the result:

```bash
$ make shell
/var/www/html $ composer process-result
```



------




## Security Vulnerabilities

Please review our security policy on how to report security vulnerabilities:

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY**

### Supported Versions

Only the latest major version receives security fixes.

### Reporting a Vulnerability

If you discover a security vulnerability within this project, please [open an issue here](https://github.com/alcidesrc/dockerized-php/issues). All security vulnerabilities will be promptly addressed.



------



## License

The MIT License (MIT). Please see [LICENSE](./LICENSE) file for more information.
