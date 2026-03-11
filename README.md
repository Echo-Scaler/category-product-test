<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Project Functions

<details>
<summary>Category API</summary>

- List categories with search and pagination: `GET /api/categories?search=term&per_page=10`
- Create category: `POST /api/categories`
- Show category: `GET /api/categories/{id}`
- Update category: `PUT /api/categories/{id}`
- Delete category: `DELETE /api/categories/{id}`
</details>

<details>
<summary>Product API</summary>

- List products with search, pagination, and category relation: `GET /api/products?search=term&per_page=10`
- Create product with image upload: `POST /api/products`
- Show product with category: `GET /api/products/{id}`
- Update product (replace image if provided): `PUT /api/products/{id}`
- Delete product (removes Cloudinary image if present): `DELETE /api/products/{id}`
</details>

<details>
<summary>Cloudinary Image Handling</summary>

- Upload images to Cloudinary on create/update
- Delete old Cloudinary image on update
- Delete Cloudinary image on product deletion
</details>

<details>
<summary>Cloudinary Config</summary>

- Service uses Cloudinary SDK with `.env` keys: `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`
- Cloudinary disk config (optional) lives in `config/filesystems.php` under `disks.cloudinary`
- `CLOUDINARY_URL` and related disk envs are present if you choose filesystem disk usage
</details>

<details>
<summary>Cloudinary Service Flow</summary>

- Upload flow
  - Controller `store()` and `update()` call `CloudinaryFileUploadService::upload($file, 'products')`
  - Service builds `UploadApi` client from `.env` values
  - Upload returns `secure_url`, saved into `products.image_url`
- Delete flow by URL
  - Controller `update()` and `destroy()` call `deleteByUrl($product->image_url)`
  - Service extracts `public_id` from the URL path after `/upload/`
  - Service calls `destroy($publicId, ['resource_type' => 'image'])`
</details>
