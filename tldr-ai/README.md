# [TL;DR-AI](https://tl-dr-ai-26e826225710.herokuapp.com/)

TL;DR-AI is a **Laravel-based web application** that summarizes and organizes content into simple, digestible insights.  
It uses Laravel as the backend framework, PostgreSQL as the database, and can be deployed to modern PaaS providers like **Railway**, **Render**, **Heroku**, or **Azure App Service**.

You can view and manually test the application [here!](https://tl-dr-ai-26e826225710.herokuapp.com/)

---

## Features
- Laravel 10+ backend
- PostgreSQL database integration
- Authentication & session management
- RESTful API endpoints
- Error logging & monitoring
- Ready for containerized or PaaS deployment

---

## Project Structure
- `/app` → Core Laravel application code  
- `/routes` → API and web routes  
- `/database/migrations` → Database schema definitions  
- `/storage/logs` → Log files  
- `.env` → Environment configuration  

---

## Configuration
Before running the app, set the required environment variables in your `.env` file:

```
DB_CONNECTION=pgsql
DB_HOST=<your-database-host>
DB_PORT=5432
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-database-user>
DB_PASSWORD=<your-database-password>
APP_KEY=<your-laravel-app-key>
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-url.com
```

---

## Development
1. Install dependencies:  
```bash
composer install
npm install && npm run dev
```

2. Run migrations:  
 ```bash
php artisan migrate
```

3. Start the local server:  
```bash
php artisan serve
```

---

## Deployment
This app can be deployed on **Railway, Render, Heroku, or Azure**.  

### Railway
- Set up a PostgreSQL service  
- Add environment variables in Railway’s dashboard  
- Deploy via `railway up` or GitHub integration  

### Render
- Create a Web Service  
- Connect your GitHub repo  
- Add PostgreSQL database and link environment variables  

### Azure
- Use **Azure App Service** for deployment  
- Add **Azure Database for PostgreSQL**  
- Configure `.env` variables via the portal  

---

## Logs
Application logs are stored in:  
`/storage/logs/laravel.log`

You can also view logs via your hosting provider’s dashboard/CLI.  

---

## Usage

Once running, the API exposes a few endpoints. Examples below use **curl**:

### 1. Health Check
```bash
curl https://your-app-url.com/api/health
```

Expected response:
```json
{ "status": "ok" }
```

### 2. Create a Summary
Send raw text to be summarized:
```bash
curl -X POST https://your-app-url.com/api/summarize \
     -H "Content-Type: application/json" \
     -d '{"text": "Your long article or content goes here"}'
```

Expected response:
```json
{
  "summary": "This is the short version of your text."
}
```

### 3. Fetch Crumbs
Retrieve a paginated list of saved summaries:
```bash
curl https://your-app-url.com/api/crumbs?page=1
```

### 4. Authentication (if enabled)
Register:
```bash
curl -X POST https://your-app-url.com/api/register \
     -H "Content-Type: application/json" \
     -d '{"name": "John Doe", "email": "john@example.com", "password": "secret"}'
```

Login:
```bash
curl -X POST https://your-app-url.com/api/login \
     -H "Content-Type: application/json" \
     -d '{"email": "john@example.com", "password": "secret"}'
```

---

## Contributing
Pull requests are welcome! Please fork the repository and create a feature branch.  

---

## 📄 License
MIT License  

---


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
