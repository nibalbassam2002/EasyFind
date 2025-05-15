<?php

namespace App\Http\Controllers;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorController extends Controller
{
    public function pendingProperties()
    {
        // جلب العقارات التي حالتها pending مع تحميل بعض العلاقات المفيدة
        $pendingProperties = Property::where('status', 'pending')
                                    ->with(['user', 'category', 'listarea']) // جلب المستخدم، التصنيف، الموقع
                                    ->latest() // عرض الأحدث أولاً
                                    ->paginate(15); // الترقيم

        // تمرير البيانات إلى الـ View (سننشئه لاحقاً)
        return view('dashboard.moderator.pending_properties', compact('pendingProperties'));
    }

    /**
     * الموافقة على عقار معلق.
     */
    public function approveProperty(Property $property) // استخدام Route Model Binding
    {
        // التأكد مرة أخرى أن العقار كان pending (احتياطي)
        if ($property->status !== 'pending') {
             return redirect()->route('moderator.properties.pending')->with('warning', 'This property is not pending approval.');
        }

        $property->status = 'approved';
        $property->save();

        // TODO: إرسال إشعار للمالك (اختياري)
        // $property->user->notify(new PropertyApproved($property));

        return redirect()->route('moderator.properties.pending')->with('success', "Property '{$property->title}' has been approved.");
    }

    /**
     * رفض عقار معلق.
     */
    public function rejectProperty(Property $property) // استخدام Route Model Binding
    {
         // التأكد مرة أخرى أن العقار كان pending (احتياطي)
         if ($property->status !== 'pending') {
            return redirect()->route('moderator.properties.pending')->with('warning', 'This property is not pending rejection.');
        }

        // يمكنك إضافة سبب للرفض إذا أردت (يتطلب حقل إضافي أو استخدام حقل موجود)
        // $property->rejection_reason = $request->input('reason'); // مثال
        $property->status = 'rejected';
        $property->save();

         // TODO: إرسال إشعار للمالك (اختياري)
         // $property->user->notify(new PropertyRejected($property, $rejectionReason));

        return redirect()->route('moderator.properties.pending')->with('success', "Property '{$property->title}' has been rejected.");
    }

}
