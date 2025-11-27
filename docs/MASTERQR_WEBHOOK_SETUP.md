# Configuración del Webhook de MasterQR

## 📋 URL del Webhook

La URL del webhook debe ser configurada en el panel de MasterQR para recibir notificaciones de pagos.

### URL de Producción
```
https://tu-dominio.tecnoweb.com/qr/callback
```

### URL de Desarrollo (opcional)
```
http://localhost:8000/qr/callback
```

**⚠️ IMPORTANTE:** MasterQR necesita poder acceder a esta URL desde internet, por lo que en desarrollo local necesitarás usar herramientas como ngrok.

---

## 🔧 Configuración en MasterQR

1. Accede al panel de administración de MasterQR
2. Ve a la sección de "Webhooks" o "Configuración de API"
3. Agrega la URL del webhook:
   ```
   https://tu-dominio.tecnoweb.com/qr/callback
   ```
4. Selecciona los eventos a notificar:
   - ✅ Pago completado (completed, success, paid)
   - ✅ Pago fallido (failed, rejected, cancelled)
   - ✅ Pago pendiente (pending)

5. Guarda la configuración

---

## 🔍 Cómo Funciona

### 1. Usuario Escanea el QR
El usuario escanea el código QR generado y realiza el pago desde su app bancaria.

### 2. MasterQR Procesa el Pago
MasterQR procesa el pago y verifica que sea exitoso.

### 3. MasterQR Envía Notificación
MasterQR envía una petición HTTP POST a tu webhook con los datos del pago:

```json
{
  "paymentNumber": "HOTEL-123-1234567890",
  "transactionId": "TRX-ABC123",
  "status": "completed",
  "amount": 0.10,
  "timestamp": "2025-01-15T10:30:00Z"
}
```

### 4. Tu Sistema Procesa la Notificación
Tu sistema recibe el webhook, valida la información y:
- ✅ Actualiza el estado del pago a "PAID"
- ✅ Actualiza la reserva a "RESERVED"
- ✅ Envía email de confirmación al cliente

---

## 📊 Estados del Pago

| Estado en MasterQR | Estado en tu Sistema | Descripción |
|-------------------|---------------------|-------------|
| `completed`, `success`, `paid`, `approved` | PAID | Pago exitoso |
| `failed`, `rejected`, `cancelled`, `error` | FAILED | Pago fallido |
| `pending` | PENDING | Pago en proceso |

---

## 🔐 Seguridad

### Validación de Firma (Recomendado)
MasterQR envía una firma en el header `X-Signature` para validar que la petición es auténtica.

En `QRPaymentController.php` líneas 238-249, descomenta el código de validación:

```php
$isValid = $this->masterQRService->validateWebhookSignature(
    $request->all(),
    $request->header('X-Signature')
);

if (!$isValid) {
    Log::warning('❌ Firma de webhook inválida de MasterQR');
    return response()->json(['error' => 'Invalid signature'], 403);
}
```

### Verificación de IP (Opcional)
Puedes agregar un middleware para verificar que las peticiones vienen de las IPs de MasterQR.

---

## 🧪 Pruebas

### 1. Probar en Desarrollo con ngrok

```bash
# Instalar ngrok
npm install -g ngrok

# Exponer tu servidor local
ngrok http 8000

# Usar la URL de ngrok en MasterQR
# Ejemplo: https://abc123.ngrok.io/qr/callback
```

### 2. Simular un Webhook Manualmente

```bash
curl -X POST https://tu-dominio.com/qr/callback \
  -H "Content-Type: application/json" \
  -d '{
    "paymentNumber": "HOTEL-123-1234567890",
    "transactionId": "TRX-TEST-001",
    "status": "completed",
    "amount": 0.10
  }'
```

### 3. Revisar Logs

Los logs del webhook se guardan en `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log | grep "MasterQR Callback"
```

Busca estos emojis para seguir el flujo:
- 🔔 Callback recibido
- 🔍 Buscando pago
- ✅ Pago encontrado
- 📋 Estado recibido
- 💾 Pago actualizado
- 📅 Reserva actualizada
- 📧 Email enviado

---

## ❗ Solución de Problemas

### El webhook no se recibe

1. **Verifica que la URL sea accesible desde internet**
   ```bash
   curl https://tu-dominio.com/qr/callback
   # Debe responder (aunque sea con error 400 por falta de datos)
   ```

2. **Verifica los logs de Laravel**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verifica la configuración de firewall**
   - Asegúrate de que el puerto 443 (HTTPS) esté abierto
   - Verifica que no haya restricciones de IP

4. **Revisa el panel de MasterQR**
   - Verifica que el webhook esté configurado
   - Revisa el historial de webhooks enviados

### El pago no se actualiza

1. **Verifica que el payment_number coincida**
   ```sql
   SELECT * FROM payments WHERE payment_number = 'HOTEL-123-1234567890';
   ```

2. **Revisa los logs** para ver si hay errores en el procesamiento

3. **Verifica el estado recibido**
   - MasterQR puede enviar diferentes nombres para el estado
   - El callback maneja: `completed`, `success`, `paid`, `approved`

---

## 📝 Checklist de Configuración

Antes de subir a producción, verifica:

- [ ] URL del webhook configurada en MasterQR
- [ ] SSL/HTTPS activo en tu dominio
- [ ] Logs funcionando correctamente
- [ ] Email de confirmación configurado
- [ ] Validación de firma habilitada (recomendado)
- [ ] Probado con un pago de prueba real
- [ ] Verificado que el polling del frontend funciona
- [ ] Documentado cómo revisar los logs en producción

---

## 🚀 Despliegue en Servidor Tecnoweb

1. **Subir código al servidor**
   ```bash
   git push origin main
   ```

2. **Actualizar dependencias en el servidor**
   ```bash
   composer install --no-dev
   php artisan migrate
   php artisan config:cache
   php artisan route:cache
   ```

3. **Configurar el webhook en MasterQR** con la URL de producción

4. **Hacer un pago de prueba** (Bs. 0.10) y verificar:
   - ✅ QR se genera correctamente
   - ✅ Pago se procesa en MasterQR
   - ✅ Webhook es recibido en tu servidor
   - ✅ Pago se actualiza a PAID
   - ✅ Reserva se actualiza a RESERVED
   - ✅ Email de confirmación se envía

---

## 📞 Soporte

Si tienes problemas con la integración:

1. Revisa los logs: `storage/logs/laravel.log`
2. Contacta al soporte de MasterQR con:
   - URL de tu webhook
   - Ejemplo de payment_number
   - Logs de errores

---

## 🔗 Enlaces Útiles

- Documentación de MasterQR: [URL]
- Panel de MasterQR: [URL]
- Repositorio del proyecto: [URL]
