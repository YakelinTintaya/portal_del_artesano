import os
import sys
import time
import datetime
import subprocess
import paramiko

HOST = "192.168.101.10"
USER = "angelca"
PWD = "141107."

def conectar():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(HOST, username=USER, password=PWD, timeout=10)
        return client
    except Exception as e:
        print(f"❌ Error conectando al servidor ({HOST}): {e}")
        return None

def query_mysql(client, sql):
    escaped = sql.replace('"', '\\"')
    cmd = f'mysql -u artesano_user -p"Artesano2026!" artesano_db -e "{escaped}"'
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode('utf-8').strip()
    err = stderr.read().decode('utf-8').strip()
    return out if out else err

def mostrar_reporte(client):
    ahora = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print("=" * 70)
    print("  📊 MONITOREO DE BASE DE DATOS - PORTAL DEL ARTESANO")
    print(f"  Servidor: angelca@{HOST} | Dominio: https://artesano.angelpaka.com")
    print(f"  Hora de consulta: {ahora}")
    print("=" * 70)

    print("\n[1] 📋 RESUMEN GENERAL:")
    print(query_mysql(client, """
    SELECT 
        (SELECT COUNT(*) FROM users) as total_usuarios,
        (SELECT COUNT(*) FROM artesanos) as total_artesanos,
        (SELECT COUNT(*) FROM productos) as total_productos,
        (SELECT COUNT(*) FROM pedidos) as total_pedidos,
        (SELECT COUNT(*) FROM conversaciones) as total_conversaciones,
        (SELECT COUNT(*) FROM mensajes) as total_mensajes;
    """))

    print("\n[2] 📦 ÚLTIMOS PEDIDOS REALIZADOS:")
    pedidos = query_mysql(client, """
    SELECT id, cliente_id, total, tipo_envio, estado_pedido, created_at 
    FROM pedidos ORDER BY id DESC LIMIT 5;
    """)
    if "Empty" in pedidos or not pedidos:
        print("  (Aún no hay pedidos registrados)")
    else:
        print(pedidos)

    print("\n[3] 👤 ÚLTIMOS USUARIOS REGISTRADOS:")
    print(query_mysql(client, """
    SELECT id, nombre, apellido, correo, rol, created_at 
    FROM users ORDER BY id DESC LIMIT 5;
    """))

    print("\n[4] 💬 ÚLTIMOS MENSAJES DE CHAT EN VIVO:")
    mensajes = query_mysql(client, """
    SELECT m.id, m.conversacion_id, u.nombre as remitente, LEFT(m.mensaje, 45) as mensaje, m.created_at
    FROM mensajes m
    JOIN users u ON m.remitente_id = u.id
    ORDER BY m.id DESC LIMIT 5;
    """)
    if "Empty" in mensajes or not mensajes:
        print("  (No hay mensajes recientes)")
    else:
        print(mensajes)

    print("\n" + "=" * 70)

def modo_en_vivo(intervalo=3):
    print("Iniciando monitoreo en vivo...")
    client = conectar()
    if not client:
        return

    try:
        while True:
            os.system('cls' if os.name == 'nt' else 'clear')
            mostrar_reporte(client)
            print(f"🔄 Actualizando en automático cada {intervalo} segundos...")
            print("🛑 Presiona [Ctrl + C] para detener el monitoreo.")
            time.sleep(intervalo)
    except KeyboardInterrupt:
        print("\n\n⏹️ Monitoreo detenido por el usuario.")
    finally:
        client.close()

def sincronizar_a_local():
    print("=" * 70)
    print("  📥 SINCRONIZANDO BASE DE DATOS: SERVIDOR ➡️ XAMPP LOCAL")
    print("=" * 70)
    client = conectar()
    if not client:
        return

    try:
        print("[1/4] Generando copia de seguridad en el servidor...")
        stdin, stdout, stderr = client.exec_command('mysqldump -u artesano_user -p"Artesano2026!" artesano_db > /tmp/artesano_live.sql')
        err = stderr.read().decode('utf-8').strip()
        if err and "Warning" not in err:
            print(f"Aviso del servidor: {err}")

        print("[2/4] Descargando dump SQL a tu máquina...")
        sftp = client.open_sftp()
        local_sql = os.path.join(os.path.dirname(os.path.abspath(__file__)), "database", "artesano_db_produccion.sql")
        sftp.get("/tmp/artesano_live.sql", local_sql)
        sftp.close()
        client.exec_command('rm -f /tmp/artesano_live.sql')
        print(f"  ✓ Archivo guardado en: {local_sql}")

        print("[3/4] Comprobando servidor MySQL de XAMPP local...")
        mysql_bin = r"C:\xampp\mysql\bin\mysql.exe"
        if not os.path.exists(mysql_bin):
            print("  ⚠️ No se encontró C:\\xampp\\mysql\\bin\\mysql.exe")
            print("  Puedes importar el archivo database/artesano_db_produccion.sql manualmente desde phpMyAdmin.")
            return

        # Verificar si MySQL local responde
        res = subprocess.run([mysql_bin, "-u", "root", "-e", "CREATE DATABASE IF NOT EXISTS artesano_db;"], capture_output=True, text=True)
        if res.returncode != 0:
            print(f"  ⚠️ MySQL de XAMPP no parece estar encendido o dio un error:")
            print(f"     {res.stderr.strip()}")
            print("  👉 Abre XAMPP Control Panel e inicia el módulo 'MySQL'.")
            print(f"  El archivo SQL descargado está listo en: database/artesano_db_produccion.sql")
            return

        print("[4/4] Importando datos a 'artesano_db' en tu XAMPP local...")
        with open(local_sql, "rb") as f:
            proc = subprocess.run([mysql_bin, "-u", "root", "artesano_db"], stdin=f, capture_output=True, text=True)
            if proc.returncode == 0:
                print("  🎉 ¡ÉXITO! Tu base de datos local en XAMPP ahora está 100% actualizada con los datos del servidor.")
            else:
                print(f"  Error al importar: {proc.stderr}")

    finally:
        client.close()

if __name__ == "__main__":
    if "--watch" in sys.argv:
        modo_en_vivo(intervalo=3)
    elif "--sync" in sys.argv:
        sincronizar_a_local()
    elif "--once" in sys.argv:
        c = conectar()
        if c:
            mostrar_reporte(c)
            c.close()
    else:
        # Menú interactivo por defecto
        print("=" * 60)
        print("  GESTIÓN DE BASE DE DATOS - PORTAL DEL ARTESANO")
        print("=" * 60)
        print(" [1] Ver estado actual (consulta una sola vez)")
        print(" [2] Monitoreo EN VIVO (actualización automática cada 3s)")
        print(" [3] Sincronizar base de datos del servidor a XAMPP local")
        print(" [0] Salir")
        print("=" * 60)
        opcion = input(" Selecciona una opción [1/2/3/0]: ").strip()

        if opcion == "1":
            c = conectar()
            if c:
                mostrar_reporte(c)
                c.close()
        elif opcion == "2":
            modo_en_vivo(intervalo=3)
        elif opcion == "3":
            sincronizar_a_local()
        else:
            print("Saliendo.")
