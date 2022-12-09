# Laravel SMS Package

This is a laravel sms package with multi driver support. Supports laravel **v8.0+** and requires php **v7.4+**

> Star! if you used and liked this package.

## Supported Gateways

- [Kavenegar](https://kavenegar.com)
- [SMS.ir](https://sms.ir)

## Installation & Configuration

Install using composer

```bash 
  composer require omalizadeh/laravel-sms
```

Publish config file

```bash
  php artisan vendor:publish --provider=Omalizadeh\Sms\Providers\SmsServiceProvider
```

## Usage

Single message:

```php
    // On top...
    use Omalizadeh\Sms\Facades\Sms;

    ////

    Sms::send('09123456789', 'message');
```

Template message:

```php
    // On top...
    use Omalizadeh\Sms\Facades\Sms;

    ////

    Sms::sendTemplate('09123456789', 'template_name', [
        'token' => 'code',
    ]);
```
