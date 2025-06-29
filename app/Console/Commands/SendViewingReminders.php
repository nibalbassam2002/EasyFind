<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message; // استيراد موديل الرسائل
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging; // استيراد خدمة Firebase Messaging

class SendViewingReminders extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:send-viewing-reminders';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Scans for confirmed viewings and sends reminders to participants 24 hours before.';

    // حقن خدمة Firebase Messaging لكي نتمكن من استخدامها
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        parent::__construct();
        $this->messaging = $messaging;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send viewing reminders...');

        // تحديد النطاق الزمني: نريد المواعيد التي ستحصل خلال الـ 24 ساعة القادمة
        $reminderWindowStart = Carbon::now()->addHours(23)->addMinutes(55); // من بعد 23 ساعة و 55 دقيقة
        $reminderWindowEnd = Carbon::now()->addHours(24)->addMinutes(5);   // إلى قبل 24 ساعة و 5 دقائق

        // 1. جلب كل رسائل "تأكيد الموعد" التي لم يُرسل لها تذكير بعد
        $confirmedViewings = Message::where('type', 'viewing_confirmed')
            // نبحث داخل حقل الـ JSON
            ->whereNotNull('metadata->confirmed_slot')
            // **نقطة مهمة جداً**: نضيف شرطاً للتأكد من أننا لم نرسل تذكيراً من قبل
            ->where('metadata->reminder_sent', '!=', true) 
            ->get();
        
        $this->info("Found {$confirmedViewings->count()} confirmed viewings to check.");

        foreach ($confirmedViewings as $message) {
            $confirmedSlot = Carbon::parse($message->metadata['confirmed_slot']);

            // 2. التحقق مما إذا كان الموعد يقع في نافذتنا الزمنية
            if ($confirmedSlot->between($reminderWindowStart, $reminderWindowEnd)) {
                $conversation = $message->conversation;
                if (!$conversation) continue;

                $users = $conversation->users; // جلب طرفي المحادثة
                
                $this->info("Found a viewing for conversation ID: {$conversation->id}. Sending reminders...");

                // 3. إرسال الإشعار لكل مستخدم في المحادثة
                foreach ($users as $user) {
                    if ($user->fcm_token) { // تأكد من أن المستخدم لديه توكن للإشعارات
                        try {
                            $notificationTitle = 'Reminder: Property Viewing Tomorrow!';
                            $notificationBody = "Don't forget your appointment tomorrow at " . $confirmedSlot->format('h:i A') . ".";
                            
                            $notification = \Kreait\Firebase\Messaging\Notification::create($notificationTitle, $notificationBody);
                            $messageToSend = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
                                ->withNotification($notification)
                                ->withData(['click_action' => route('chat.index', ['activeConversation' => $conversation->id])]);
                            
                            $this->messaging->send($messageToSend);
                            $this->info("Reminder sent to user ID: {$user->id}");

                        } catch (\Throwable $e) {
                            Log::error('FCM_REMINDER_SEND_ERROR: ' . $e->getMessage());
                            $this->error("Failed to send reminder to user ID: {$user->id}");
                        }
                    }
                }

                // 4. **مهم جداً**: تحديث الرسالة لوضع علامة أنه تم إرسال التذكير
                // هذا يمنع إرسال نفس التذكير مرة أخرى في كل مرة تعمل فيها المهمة
                $metadata = $message->metadata;
                $metadata['reminder_sent'] = true;
                $message->metadata = $metadata;
                $message->save();

            }
        }
        
        $this->info('Finished sending viewing reminders.');
        return 0; // Command executed successfully
    }
}