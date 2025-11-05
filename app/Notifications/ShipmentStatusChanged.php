<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Shipment;

class ShipmentStatusChanged extends Notification
{
    use Queueable;

    public $shipment;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Shipment $shipment, string $oldStatus, string $newStatus)
    {
        $this->shipment = $shipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusLabels = [
            'pending' => 'Pendiente',
            'in_transit' => 'En Tránsito',
            'delivered' => 'Entregado',
            'exception' => 'Excepción',
        ];
        
        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? ucfirst(str_replace('_', ' ', $this->oldStatus));
        $newStatusLabel = $statusLabels[$this->newStatus] ?? ucfirst(str_replace('_', ' ', $this->newStatus));
        
        $message = (new MailMessage)
            ->subject('🚚 Actualización de estado de tu envío')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('El estado de tu paquete ha sido actualizado:')
            ->line('📦 **Tracking:** ' . $this->shipment->tracking_number);
        
        if ($this->shipment->wrh && $this->shipment->wrh !== 'pendiente') {
            $message->line('🏷️ **WRH:** ' . $this->shipment->wrh);
        }
        
        $message->line('📊 **Estado anterior:** ' . $oldStatusLabel)
                ->line('✅ **Nuevo estado:** ' . $newStatusLabel);
        
        if ($this->newStatus === 'delivered' && $this->shipment->delivery_date) {
            $message->line('🎉 **Entregado el:** ' . $this->shipment->delivery_date->format('d/m/Y H:i'));
        }
        
        if ($this->shipment->last_scan_location) {
            $message->line('📍 **Última ubicación:** ' . $this->shipment->last_scan_location);
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
