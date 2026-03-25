# Frontend Vue (Standalone)

Frontend ini terpisah dari Laravel dan berjalan sebagai app Vue murni di port `5173`.

## Jalankan

1. Jalankan backend Laravel:

```bash
php artisan serve
```

2. Jalankan frontend Vue:

```bash
npm run frontend:dev
```

3. Buka:

`http://127.0.0.1:5173`

## Konfigurasi API

Copy env example:

```bash
cp frontend/.env.example frontend/.env
```

Default API base URL:

`VITE_API_BASE_URL=http://127.0.0.1:8000/api`

Jika endpoint backend butuh bearer token, set salah satu:

- `VITE_API_TOKEN` di `frontend/.env`
- `localStorage.setItem('api_token', 'TOKEN_ANDA')`
