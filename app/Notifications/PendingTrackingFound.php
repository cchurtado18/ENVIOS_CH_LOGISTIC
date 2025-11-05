<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Shipment;

class PendingTrackingFound extends Notification
{
    use Queueable;

    public $shipment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('✅ ¡Tu tracking ya está disponible!')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('¡Buenas noticias! El tracking que estabas esperando ya está disponible:')
            ->line('📦 **Tracking:** ' . $this->shipment->tracking_number);
        
        if ($this->shipment->wrh && $this->shipment->wrh !== 'pendiente') {
            $message->line('🏷️ **WRH:** ' . $this->shipment->wrh);
        }
        
        if ($this->shipment->carrier) {
            $message->line('🚚 **Transportista:** ' . $this->shipment->carrier);
        }
        
        if ($this->shipment->pickup_date) {
            $message->line('📅 **Registrado el:** ' . $this->shipment->pickup_date->format('d/m/Y H:i'));
        }
        
        $message->action('Ver detalles en dashboard', route('dashboard'))
                ->line('Gracias por usar CH Logistic!');
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
