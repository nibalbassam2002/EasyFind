<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertyRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public Property $property;

    /**
     * Create a new notification instance.
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $editUrl = route('lister.properties.edit', $this->property->id);
        return (new MailMessage)
                    ->subject("Regarding Your Property Listing: '{$this->property->title}'")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("We regret to inform you that your property listing '{$this->property->title}' has been reviewed and did not meet our platform's guidelines.")
                    ->line("**Reason for Rejection:**")
                    ->line($this->property->rejection_reason ?? 'No specific reason provided, please contact support for more details.')
                    ->line("We encourage you to review the feedback and make the necessary corrections. You can edit and resubmit your property for review.")
                    ->action('Edit Your Property', $editUrl)
                    ->line('If you have any questions, please feel free to contact our support team.')
                    ->salutation('Regards, <br>The EasyFind Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
         return [
            'property_id' => $this->property->id,
            'property_title' => Str::limit($this->property->title, 50),
            'message' => "Your property '{$this->property->title}' was rejected.",
            'reason' => Str::limit($this->property->rejection_reason ?? 'N/A', 100),
            'action_text' => 'View & Edit Property',
            'url' => route('lister.properties.edit', $this->property->id), // رابط لصفحة تعديل العقار في لوحة تحكم البائع
            'icon' => 'bi bi-x-octagon-fill text-danger', // أيقونة مناسبة
        ];
    }
    }

