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
