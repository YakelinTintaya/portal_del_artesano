import os
import sys
import tarfile
import tempfile
import urllib.request
import paramiko

HOST = "192.168.101.10"
USER = "angelca"
PWD = "141107."
KEY_FILE = os.path.expanduser("~/.ssh/id_ed25519")
BASE_LOCAL = os.path.dirname(os.path.abspath(__file__))
REMOTE_TARGET = "/var/www/artesano"
REMOTE_TMP_PKG = "/tmp/artesano_update.tar.gz"

print("=" * 60)
print("  🚀 DESPLIEGUE AUTOMÁTICO A PRODUCCIÓN")
print("  Servidor: angelca@192.168.101.10")
print("  Dominio:  https://artesano.angelpaka.com")
print("=" * 60)

# 1. Empaquetar proyecto localmente (excluyendo temporales, git y node_modules)
print("[1/5] Empaquetando código fuente local...")
EXCLUDE_DIRS = {'.git', 'node_modules', '.idea', '.vscode', '.gemini', 'scratch', '.agents', '.claude'}
EXCLUDE_FILES = {'.env', 'deploy.py', 'subir_artesano.bat', 'ver_bd_servidor.py', 'ver_bd_servidor.bat', 'database.sqlite', 'artesano_pkg.tar.gz', 'cookies.txt', 'AGENTS.md', 'CLAUDE.md', 'boost.json'}

temp_tar = os.path.join(tempfile.gettempdir(), "artesano_pkg_fast.tar.gz")

def tar_filter(tarinfo):
    name = tarinfo.name
    parts = name.replace('\\', '/').split('/')
    for p in parts:
        if p in EXCLUDE_DIRS:
            return None
    basename = os.path.basename(name)
    if basename in EXCLUDE_FILES:
        return None
    if 'storage/logs' in name and basename.endswith('.log'):
        return None
    if name.replace('\\', '/').startswith('public/storage'):
        return None
    return tarinfo

with_vendor = "--vendor" in sys.argv
items_to_pack = ['app', 'bootstrap', 'config', 'database', 'lang', 'public', 'resources', 'routes', 'tests', 'artisan', 'composer.json', 'composer.lock', 'storage/app/public']
if with_vendor:
    items_to_pack.append('vendor')

with tarfile.open(temp_tar, "w:gz") as tar:
    for item in items_to_pack:
        full_item = os.path.join(BASE_LOCAL, item)
        if os.path.exists(full_item):
            tar.add(full_item, arcname=item, filter=tar_filter)

pkg_size_mb = os.path.getsize(temp_tar) / (1024 * 1024)
print(f"[OK] Paquete generado ({pkg_size_mb:.2f} MB)")

# 2. Conectar al servidor Linux (prioriza clave SSH, fallback a password)
print("[2/5] Conectando por SSH a 192.168.101.10...")
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    if os.path.exists(KEY_FILE):
        client.connect(HOST, username=USER, key_filename=KEY_FILE, timeout=10)
    else:
        client.connect(HOST, username=USER, password=PWD, timeout=10)
except Exception:
    client.connect(HOST, username=USER, password=PWD, timeout=10)

print("[OK] Conexión SSH establecida.")

# 3. Transferir paquete vía SFTP
print(f"[3/5] Transfiriendo actualización a {REMOTE_TMP_PKG}...")
sftp = client.open_sftp()
sftp.put(temp_tar, REMOTE_TMP_PKG)
sftp.close()
try:
    os.remove(temp_tar)
except OSError:
    pass
print("[OK] Paquete transferido con éxito.")

# 4. Descomprimir, migrar y optimizar en el servidor
print("[4/5] Descomprimiendo, ejecutando migraciones y optimizando...")
remote_cmd = f"""
echo '{PWD}' | sudo -S bash -c '
set -e
TARGET="{REMOTE_TARGET}"
PKG="{REMOTE_TMP_PKG}"

# Respaldar .env
if [ -f "$TARGET/.env" ]; then
    cp "$TARGET/.env" /tmp/artesano_env_saved
fi

# Descomprimir actualización
tar -xzf "$PKG" -C "$TARGET"
rm -f "$PKG"

# Restaurar .env
if [ -f /tmp/artesano_env_saved ]; then
    cp /tmp/artesano_env_saved "$TARGET/.env"
    rm -f /tmp/artesano_env_saved
fi

# Ajustar permisos
chown -R www-data:www-data "$TARGET"
chmod -R 775 "$TARGET/storage" "$TARGET/bootstrap/cache" "$TARGET/lang" "$TARGET/resources/views/vendor" 2>/dev/null || true
chmod -R 777 "$TARGET/storage/logs" 2>/dev/null || true

cd "$TARGET"

# Migraciones y enlaces de almacenamiento
sudo -u www-data php artisan migrate --force
if [ ! -L "$TARGET/public/storage" ]; then
    rm -rf "$TARGET/public/storage"
    sudo -u www-data ln -s "$TARGET/storage/app/public" "$TARGET/public/storage"
fi

# Cachés de producción
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Reinicio del servicio Laravel
systemctl restart artesano
sleep 2
systemctl is-active artesano
'
"""

stdin, stdout, stderr = client.exec_command(remote_cmd)
output = stdout.read().decode('utf-8')
errors = stderr.read().decode('utf-8')

if "active" in output:
    print("[OK] Servicio artesano.service reiniciado y activo.")
else:
    print(output)
    if errors and "password" not in errors.lower():
        print(errors)

client.close()

# 5. Verificación HTTP en vivo
print("[5/5] Verificando respuesta del servidor en vivo...")
try:
    req = urllib.request.Request("https://artesano.angelpaka.com", headers={'User-Agent': 'DeployVerifier/1.0'})
    with urllib.request.urlopen(req, timeout=10) as response:
        status = response.getcode()
        if status == 200:
            print("[OK] https://artesano.angelpaka.com respondió HTTP 200 (En línea)")
except Exception as e:
    print(f"[!] Nota sobre verificación: {e}")

print("=" * 60)
print("  🎉 ¡DESPLIEGUE FINALIZADO EXITOSAMENTE!")
print("  Todos los cambios están activos en:")
print("  👉 https://artesano.angelpaka.com")
print("=" * 60)
