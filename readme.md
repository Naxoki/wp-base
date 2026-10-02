# WordPress Base

Base pre-cargada para sitios web.

## Requisitos

- Servidor local con Apache, PHP y MySQL/MariaDB (XAMPP, Laragon, etc.)
- Git
- Conexión a internet (para generar las claves de seguridad y descargar plugins)

## Clonar e instalar

1. Clonar el repositorio dentro de la carpeta pública del servidor (por ejemplo `htdocs`), con el nombre del nuevo proyecto:

   ```bash
   git clone https://github.com/Naxoki/wp-base.git nombre-del-proyecto
   cd nombre-del-proyecto
   ```
   **para una rama especifica**
   - git clone -b NOMBRE-RAMA https://github.com/Naxoki/wp-base.git

2. Iniciar Apache y MySQL.

3. Abrir el instalador rápido en el navegador:

   ```
   http://localhost/nombre-del-proyecto/wp-tools/wp-quick-setup.php
   ```

   Si el proyecto está en la raíz del servidor: `http://localhost/wp-tools/wp-quick-setup.php`

4. Completar el formulario:

   | Sección        | Campos                                                                            |
   | ---            | ---                                                                               |
   | Sitio          | Nombre del proyecto, título e idioma                                              |
   | Base de datos  | Host (`localhost`), nombre de la BD (vacío = se genera del nombre del proyecto),  |
                    |  usuario y contraseña MySQL (por defecto `root` / `root`),                        |
                    |  collation y prefijo de tablas                                                    |
   | Administrador  | Usuario, contraseña (vacía = se genera una segura) y email |

   En un solo paso crea la base de datos, genera `wp-config.php` con claves reales e instala WordPress.

5. Ingresar al panel en `/wp-admin` con el usuario administrador creado.

6. (Opcional) Instalar el pool de plugins base visitando `wp-tools/plugins-base.php`.

> `wp-config.php` está en `.gitignore`: cada instalación genera el suyo. El instalador no corre si ese archivo ya existe; para reinstalar, elimínalo y borra la base de datos.

> **Seguridad:** `wp-tools/` es solo para desarrollo local. Eliminar o restringir la carpeta antes de subir el proyecto a un entorno público.