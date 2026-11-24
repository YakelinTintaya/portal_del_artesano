<?php

namespace App\Console\Commands;

use App\Models\Artesano;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MonitorDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bd:monitor 
                            {--watch : Monitorea en vivo actualizando la consola automáticamente} 
                            {--interval=3 : Intervalo de actualización en segundos para el modo en vivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Visualiza en tiempo real los registros y actividad de la base de datos (pedidos, usuarios, chats)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $watch = (bool) $this->option('watch');
        $interval = max(1, (int) $this->option('interval'));

        if (! $watch) {
            $this->renderDashboard();
            return self::SUCCESS;
        }

        $this->info("Iniciando monitoreo en vivo (cada {$interval}s)... Presiona [Ctrl + C] para salir.");

        while (true) {
            $this->clearScreen();
            $this->renderDashboard();
            $this->line('');
            $this->comment("🔄 Actualizando automáticamente cada {$interval} segundos... [Presiona Ctrl+C para detener]");
            sleep($interval);
        }

        return self::SUCCESS;
    }

    /**
     * Renderiza las tablas y métricas del estado actual de la base de datos.
     */
    protected function renderDashboard(): void
    {
        $ahora = now()->format('Y-m-d H:i:s');
        $driver = config('database.default');
        $dbName = config("database.connections.{$driver}.database");

        $this->line('================================================================================');
        $this->info("  📊 MONITOR DE BASE DE DATOS - PORTAL DEL ARTESANO CUSCO");
        $this->line("  Conexión: {$driver} | BD: {$dbName} | Hora: {$ahora}");
        $this->line('================================================================================');

        try {
            // 1. Resumen General
            $totalUsuarios = User::count();
            $totalArtesanos = Artesano::count();
            $totalProductos = Producto::count();
            $totalPedidos = Pedido::count();
            $totalChats = Conversacion::count();
            $totalMensajes = Mensaje::count();

            $this->table(
                ['Usuarios', 'Artesanos', 'Productos', 'Pedidos', 'Conversaciones', 'Mensajes'],
                [[
                    $totalUsuarios,
                    $totalArtesanos,
                    $totalProductos,
                    $totalPedidos,
                    $totalChats,
                    $totalMensajes,
                ]]
            );

            // 2. Últimos Pedidos
            $this->line('');
            $this->comment('📦 ÚLTIMOS 5 PEDIDOS:');
            $pedidos = Pedido::with('cliente')->latest('id')->take(5)->get();

            if ($pedidos->isEmpty()) {
                $this->line('  (Sin pedidos registrados aún)');
            } else {
                $pedidosRows = $pedidos->map(function (Pedido $p) {
                    return [
                        $p->id,
                        $p->cliente ? "{$p->cliente->nombre} {$p->cliente->apellido}" : 'N/A',
                        'S/ '.number_format((float) $p->total, 2),
                        $p->tipo_envio ?? 'Estándar',
                        strtoupper($p->estado_pedido),
                        $p->created_at ? $p->created_at->format('Y-m-d H:i') : '-',
                    ];
                });

                $this->table(['ID', 'Cliente', 'Total', 'Envío', 'Estado', 'Fecha'], $pedidosRows);
            }

            // 3. Últimos Mensajes de Chat
            $this->line('');
            $this->comment('💬 ÚLTIMOS 5 MENSAJES DE CHAT:');
            $mensajes = Mensaje::with(['remitente', 'conversacion'])->latest('id')->take(5)->get();

            if ($mensajes->isEmpty()) {
                $this->line('  (Sin mensajes de chat recientes)');
            } else {
                $mensajesRows = $mensajes->map(function (Mensaje $m) {
                    $remitente = $m->remitente ? "{$m->remitente->nombre} ({$m->remitente->rol})" : 'Sistema';
                    return [
                        $m->id,
                        "#{$m->conversacion_id}",
                        $remitente,
                        Str::limit($m->mensaje, 40),
                        $m->created_at ? $m->created_at->format('H:i:s d/m') : '-',
                    ];
                });

                $this->table(['ID', 'Sala', 'Remitente', 'Mensaje', 'Fecha/Hora'], $mensajesRows);
            }

            // 4. Últimos Usuarios Registrados
            $this->line('');
            $this->comment('👤 ÚLTIMOS 5 USUARIOS REGISTRADOS:');
            $usuarios = User::latest('id')->take(5)->get();

            $usuariosRows = $usuarios->map(function (User $u) {
                return [
                    $u->id,
                    "{$u->nombre} {$u->apellido}",
                    $u->correo ?? $u->email,
                    strtoupper($u->rol),
                    $u->created_at ? $u->created_at->format('Y-m-d H:i') : '-',
                ];
            });

            $this->table(['ID', 'Nombre Completo', 'Correo', 'Rol', 'Registrado'], $usuariosRows);
            $this->line('================================================================================');
        } catch (\Throwable $e) {
            $this->error('  ❌ Error de conexión con la base de datos: ' . $e->getMessage());
            $this->warn('  💡 Si estás en local XAMPP, asegúrate de haber encendido el módulo MySQL en XAMPP Control Panel.');
            $this->line('================================================================================');
        }
    }

    /**
     * Limpia la terminal según el sistema operativo.
     */
    protected function clearScreen(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows
            system('cls');
        } else {
            // Linux / Unix
            system('clear');
        }
    }
}
