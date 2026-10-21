# Integración del botón de pago Handy

El checkout crea el pago en el backend, guarda el pedido y el identificador externo de Handy, y redirige al cliente al enlace devuelto por Handy. La notificación de Handy actualiza el estado de pago; el pedido solo avanza a confirmado cuando el pago resulta exitoso.

## Configuración

Configurar estas variables en `Private/docker/.env` (no incluir las credenciales en el repositorio):

| Variable | Valor |
| --- | --- |
| `HANDY_MERCHANT_SECRET_KEY` | Merchant secret entregado por Handy para el ambiente elegido. |
| `HANDY_API_BASE_URL` | URL API. El valor predeterminado es el ambiente de pruebas `https://api.payments.arriba.uy/api/v2`; para producción usar `https://api.payments.handy.uy/api/v2`. |
| `APP_BASE_URL` | URL pública HTTPS del sitio, sin barra final. Handy necesita acceder a `/php/scripts/handyWebhook.php`. |
| `HANDY_TAX_RATE` | Tasa en porcentaje incluida en los precios. Predeterminado `22`; usar `0` si los libros son exentos. |

`TaxedAmount` se calcula a partir del precio final ya cargado en el catálogo: por ejemplo, con una tasa de 22%, se divide por 1,22; con tasa 0, se informa 0 como monto gravado. Cambiar `HANDY_TAX_RATE` no modifica los precios del catálogo.

Después de configurar las variables, reconstruir y reiniciar el contenedor PHP para que la nueva extensión cURL quede disponible.

## Base de datos

Las instalaciones nuevas reciben las columnas desde `Private/sql/database.sql`. En una instalación existente, ejecutar una sola vez [handy_payments.sql](../Private/migrations/handy_payments.sql) contra la base de datos antes de habilitar Handy.

## Notificaciones

El endpoint público `POST /php/scripts/handyWebhook.php` procesa los estados documentados por Handy (iniciado, exitoso, fallido y pendiente). Comprueba el identificador externo, la moneda y el monto frente al pedido persistido, y no permite que una notificación posterior revierta un pago ya confirmado. Mantener el sitio y el endpoint disponibles por HTTPS. Los pedidos con pago pendiente o exitoso no se pueden cancelar desde la gestión; los pagos fallidos sí permiten cancelar y reponer stock. Las devoluciones de Handy no forman parte de esta integración.
