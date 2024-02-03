<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

## Get Started

1. Clone o repositório para sua máquina local:

```
git clone https://github.com/Aureo-Bueno/futsal-api.git
```

1. Clone o repositório para sua máquina local:

```
cd futsal-api
```

1. Clone o repositório para sua máquina local:

```
composer install --ignore-platform-req=ext-fileinfo
```

1. Clone o repositório para sua máquina local:

```
docker run -d --name futsal-mysql -e MYSQL_ROOT_PASSWORD=futsal2024 -e MYSQL_DATABASE=futsal_db -e MYSQL_USER=aureo_admin -e MYSQL_PASSWORD=aureo@2024 -p 3306:3306 mysql:latest
```

1. Clone o repositório para sua máquina local:

```
php artisan serve
```

# API Endpoints and Requests

A seguir estão os endpoints disponíveis e exemplos de requisições para a sua API.

## 1. Autenticação

### 1.1 Login

- **Método:** POST
- **Endpoint:** http://127.0.0.1:8000/api/login
- **Exemplo de Requisição:**
  ```json
  {
    "email": "admin@admin.com",
    "password": 123456
  }
  ```

### 1.2 Get User

- **Método:** POST
- **Endpoint:** http://127.0.0.1:8000/api/user
- **Requisito:** Adicionar o token ao cabeçalho como Bearer.


## 2. Player

### 2.1 Get Players

- **Método:** GET
- **Endpoint:** http://127.0.0.1:8000/api/player
- **Requisito:** Adicionar o token ao cabeçalho como Bearer.

### 2.2 Create Players

- **Método:** POST
- **Endpoint:** http://127.0.0.1:8000/api/player
- **Requisito:** Adicionar o token ao cabeçalho como Bearer.
  ```json
  {
    "name": "example",
    "jersey_number": 01
  }
  ```
