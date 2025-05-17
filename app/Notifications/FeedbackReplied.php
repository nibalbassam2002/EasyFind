<?php

namespace App\Notifications;
use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FeedbackReplied extends Notification implements ShouldQueue
{
    use Queueable;
    public Feedback $feedback;

    /**
     * Create a new notification instance.
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
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
    //    $myFeedbackUrl = route('frontend.my-feedback.show', $this->feedback->id);
       $myFeedbackUrl = route('frontend.home'); // Temporarily using home page link

        $subject = $this->feedback->subject ? Str::limit($this->feedback->subject, 50) : 'No Subject';

        return (new MailMessage)
                    ->subject('Response to Your Feedback: ' . $subject)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('We are pleased to inform you that a response has been provided to your feedback regarding:')
                    ->line('"' . Str::limit($this->feedback->message, 100) . '"')
                    ->line('Administrator response:')
                    ->line($this->feedback->admin_reply) // Display admin's reply
                    // ->action('View Details', $myFeedbackUrl) // If you have a dedicated page
                    ->line('Thank you for helping us improve our services.');
    }
    public function toDatabase(object $notifiable): array
{
    // $myFeedbackUrl = route('frontend.my-feedback.show', $this->feedback->id);
    $myFeedbackUrl = '#'; // Temporarily, or link to "My Notifications" page
    $subject = $this->feedback->subject ? Str::limit($this->feedback->subject, 30) : Str::limit($this->feedback->message, 30);

    return [
        'feedback_id' => $this->feedback->id,
        'feedback_subject' => $subject,
        'message' => 'Your feedback regarding: "' . $subject . '" has received a response',
        'url' => $myFeedbackUrl,
        'icon' => 'bi bi-reply-all-fill text-success'
    ];
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
