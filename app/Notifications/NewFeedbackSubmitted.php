<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewFeedbackSubmitted extends Notification implements ShouldQueue
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
        $feedbackUrl = route('moderator.feedback.show', $this->feedback->id); // Direct link to the feedback in dashboard
        $subject = $this->feedback->subject ? Str::limit($this->feedback->subject, 50) : 'No Subject';

        return (new MailMessage)
                    ->subject('New Feedback Submitted: ' . $subject)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('New feedback has been submitted by user: ' . $this->feedback->user->name)
                    ->line('Feedback Type: ' . $this->feedback->type)
                    ->lineIf($this->feedback->subject, 'Subject: ' . $this->feedback->subject)
                    ->line('Feedback Excerpt: ' . Str::limit($this->feedback->message, 150))
                    ->action('View Feedback Details', $feedbackUrl)
                    ->line('Thank you for using our platform.');
    }
    public function toDatabase(object $notifiable): array
    {
        
        $feedbackUrl = route('moderator.feedback.show', $this->feedback->id);
        $subject = $this->feedback->subject ? Str::limit($this->feedback->subject, 30) : Str::limit($this->feedback->message, 30);

        return [
            'feedback_id' => $this->feedback->id,
            'user_name' => $this->feedback->user->name,
            'feedback_subject' => $subject,
            'message' => 'New Feedback from' . $this->feedback->user->name . ': "' . $subject . '"',
            'url' => $feedbackUrl,
            'icon' => 'bi bi-chat-left-dots-fill text-primary' 
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
