# ATM Management API

Bu layihə Laravel 8 və MySQL istifadə edilərək hazırlanmış ATM idarəetmə API-sidir.

Əsas məqsəd istifadəçi bankomatdan pul çıxardıqda minimum sayda əskinaz verilməsini təmin etməkdir.

---

## Texnologiyalar

- PHP
- Laravel 8
- MySQL
- Laravel Sanctum

---

## Əsas funksiyalar

- İkili valyuta dəstəyi: AZN və USD
- Əskinazların idarə edilməsi
- ATM-də əskinaz qalığının saxlanılması
- Hesabların idarə olunması
- Pul çıxarışı
- Minimum əskinaz sayı ilə çıxarış
- Uyğun əskinaz kombinasiyası yoxdursa əməliyyatın dayandırılması
- Paralel sorğularda balans və ATM qalığının qorunması
- Əməliyyat tarixçəsi
- Admin istifadəçinin əməliyyat silməsi
- Audit trail
- Performance measurement
- Rate limiting
- Retry protection

---

## Quraşdırma

Layihə fayllarını yüklədikdən sonra dependency-ləri quraşdırın:

```bash
composer install
```

`.env` faylını yaradın:

```bash
cp .env.example .env
```

Application key yaradın:

```bash
php artisan key:generate
```

`.env` faylında database məlumatlarını yazın:

```env
DB_DATABASE=atm_api
DB_USERNAME=root
DB_PASSWORD=
```

Migration və seeder-ləri işə salın:

```bash
php artisan migrate:fresh --seed
```

Serveri başladın:

```bash
php artisan serve
```

---

## Test istifadəçiləri

Seeder ilə iki istifadəçi yaradılır.

### Adi user

```txt
email: user@test.com
password: password
```

### Admin user

```txt
email: admin@test.com
password: password
```

---

## Authentication

### Login

```http
POST /api/login
```

Body:

```json
{
  "email": "user@test.com",
  "password": "password"
}
```

Response içində token gəlir.

Sonrakı request-lərdə bu header-lər əlavə edilməlidir:

```txt
Authorization: Bearer TOKEN
Accept: application/json
```

---

## Accounts

### User hesablarını görmək

```http
GET /api/accounts
```

### Tək hesabı görmək

```http
GET /api/accounts/{id}
```

---

## Withdrawal

### Pul çıxarmaq

```http
POST /api/withdrawals
```

Body:

```json
{
  "account_id": 1,
  "amount": 125,
  "idempotency_key": "withdraw-test-001"
}
```

Uğurlu halda sistem minimum sayda əskinaz qaytarır.

Məsələn 125 AZN üçün:

```txt
100 AZN - 1 ədəd
20 AZN - 1 ədəd
5 AZN - 1 ədəd
```

Hesab balansı da yenilənir.

Əgər hesabda 250 AZN varsa, 125 AZN çıxarıldıqdan sonra balans 125 AZN qalır.

---

## Retry Protection

`idempotency_key` eyni göndərilərsə, əməliyyat ikinci dəfə icra olunmur.

Məsələn eyni request təkrar göndərilsə:

```json
{
  "account_id": 1,
  "amount": 125,
  "idempotency_key": "withdraw-test-001"
}
```

Balans ikinci dəfə azalmır.

---

## ATM Cash Balance

### ATM qalığını görmək

```http
GET /api/atm-cash-balances
```

### ATM əskinaz sayını dəyişmək

Sadəcə admin istifadəçi edə bilər.

```http
PUT /api/atm-cash-balances/{id}
```

Body:

```json
{
  "quantity": 5
}
```

---

## Currencies

### Valyutaları görmək

```http
GET /api/currencies
```

### Tək valyutanı görmək

```http
GET /api/currencies/{id}
```

---

## Denominations

### Əskinazları görmək

```http
GET /api/denominations
```

Valyutaya görə filter:

```http
GET /api/denominations?currency_id=1
```

### Tək əskinazı görmək

```http
GET /api/denominations/{id}
```

---

## Withdrawals History

### Çıxarış tarixçəsi

```http
GET /api/withdrawals
```

### Tək çıxarışa baxmaq

```http
GET /api/withdrawals/{id}
```

### Çıxarışı silmək

Sadəcə admin istifadəçi edə bilər.

```http
DELETE /api/withdrawals/{id}
```

---

## Audit Logs

Audit log-lara sadəcə admin istifadəçi baxa bilər.

### Audit log siyahısı

```http
GET /api/audit-logs
```

### Tək audit log

```http
GET /api/audit-logs/{id}
```

---

## Rate Limiting

API route-larında rate limiting istifadə olunur.

```php
throttle:10,1
```

Bu o deməkdir ki, istifadəçi 1 dəqiqədə maksimum 10 request göndərə bilər.

Login üçün ayrıca limit istifadə olunur:

```php
throttle:5,1
```

Bu isə 1 dəqiqədə maksimum 5 login cəhdi deməkdir.

---

## Performance Measurement

API response header-lərində request-in işləmə müddəti göstərilir.

```txt
X-Response-Time-Ms: 35.24
```

Əgər request gec işləsə, sistem log-a warning yazır.

---

## Parallel Request Protection

Pul çıxarışı zamanı `DB::transaction()` və `lockForUpdate()` istifadə olunur.

Bu səbəbdən eyni vaxtda gələn paralel sorğularda:

- account balance səhv azalmır
- ATM cash balance səhv azalmır
- balans mənfiyə düşmür

---

## Əsas test nümunəsi

Başlanğıc balans:

```txt
250 AZN
```

Request:

```json
{
  "account_id": 1,
  "amount": 125,
  "idempotency_key": "test-125-001"
}
```

Nəticə:

```txt
100 AZN - 1 ədəd
20 AZN - 1 ədəd
5 AZN - 1 ədəd
```

Qalan balans:

```txt
125 AZN
```

---

## Qeyd

Bu layihədə əsas məqsəd ATM-dən pul çıxarışı zamanı minimum sayda əskinaz verilməsini təmin etməkdir.

Əgər ATM-də uyğun əskinaz kombinasiyası yoxdursa, əməliyyat icra olunmur və hesab balansı dəyişmir.