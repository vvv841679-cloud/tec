<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {recipient}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar un correo de prueba al destinatario especificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('recipient');

        $this->info('Enviando correo de prueba a: ' . $recipient);

        try {
            Mail::raw('Este es un correo de prueba desde el sistema hotelero usando el servidor SMTP de tecnoweb.org.bo', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Correo de Prueba - Sistema Hotelero');
            });

            $this->info('✅ Correo enviado exitosamente!');
            $this->info('Configuración utilizada:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Host', config('mail.mailers.smtp.host')],
                    ['Port', config('mail.mailers.smtp.port')],
                    ['Encryption', config('mail.mailers.smtp.encryption')],
                    ['From', config('mail.from.address')],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar el correo:');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
