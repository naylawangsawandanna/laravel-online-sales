## API Documentation

### Cancel Customer Order

Digunakan untuk membatalkan pesanan customer yang masih berstatus `pending`. Stok produk akan dikembalikan secara otomatis.

- **Method:** `PATCH`
- **Endpoint:** `/api/v1/orders/{order}/cancel`
- **Production URL:** `https://laravel-online-sales-mu.vercel.app/api/v1/orders/{order}/cancel`
- **Authorization:** Bearer Token (Laravel Sanctum)
- **Role:** Customer
- **Request Body:** Tidak diperlukan.

**Headers:**
```http
Accept: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
```

**Success Response (200):**
```json
{
  "message": "Pesanan berhasil dibatalkan dan stok dikembalikan."
}
```

**Error Responses:**
- `401 Unauthorized`: Token tidak valid atau belum login.
- `404 Not Found`: Pesanan tidak ditemukan atau bukan milik customer.
- `409 Conflict`: Status pesanan bukan `pending`.

**Automated Testing:**

```bash
php artisan test --filter=CustomerOrderCancelApiTest
```

Hasil: 2 tests passed, 9 assertions.

Endpoint telah diuji menggunakan Postman pada local dan production.
