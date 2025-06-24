<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PropertyApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Property $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // ▼▼▼ تأكد من أن اسم الروت هذا صحيح ▼▼▼
        // يمكنك تغييره إلى رابط لوحة التحكم إذا أردت
        $propertyUrl = route('frontend.property.show', $this->property->slug); 

        return (new MailMessage)
                    ->subject("Congratulations! Your Property '{$this->property->title}' is Now Live!")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Great news! Your property listing, '{$this->property->title}', has been reviewed and approved. It is now live on EasyFind.")
                    ->action('View Your Property', $propertyUrl)
                    ->line('Thank you for choosing our platform!');
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  object  $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        // ▼▼▼ هذا هو الجزء الأهم ▼▼▼
        // إنشاء رسالة قصيرة للعرض في القائمة المنسدلة
        $shortMessage = "Congratulations! Your property '" . Str::limit($this->property->title, 25) . "' has been approved.";
        
        // إنشاء رسالة كاملة للعرض في صفحة الإشعارات
        $fullMessage = "Great news! Your property listing, '{$this->property->title}', has been approved and is now live on the site. You can view it using the link provided.";

        // ▼▼▼ تأكد من أن اسم الروت هذا صحيح ▼▼▼
        // هذا الرابط الذي سينقر عليه المستخدم
        $url = route('frontend.property.show', $this->property->id); // <--- الأفضل توجيهه لصفحة العرض في لوحة التحكم

        return [
            // بيانات أساسية للإشعار
            'property_id' => $this->property->id,
            
            // البيانات التي يستخدمها الـ View الخاص بك
            'message'       => $shortMessage,      // رسالة مختصرة
            'full_message'  => $fullMessage,      // رسالة كاملة (للمستقبل)
            'url'           => $url,               // الرابط الذي سيتم فتحه عند النقر
            'icon'          => 'bi bi-patch-check-fill text-success', // أيقونة القبول
        ];
    }
}