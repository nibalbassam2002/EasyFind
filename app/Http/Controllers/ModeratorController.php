<?php

namespace App\Http\Controllers;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PropertyApprovedNotification;
use App\Notifications\PropertyRejectedNotification;

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
    public function approveProperty(Property $property) // Route Model Binding
    {
        if ($property->status !== 'pending') {
             return redirect()->route('moderator.properties.pending')->with('warning', 'This property is not pending approval.');
        }

        $property->status = 'approved';

       
        $property->moderated_by = Auth::id(); 
        $property->moderated_at = now();     
        $property->rejection_reason = null;  
        

        $property->save(); 
        if ($property->user) { 
            $property->user->notify(new PropertyApprovedNotification($property));
        }
      

        return redirect()->route('moderator.properties.pending')->with('success', "Property '{$property->title}' has been approved.");
    }

    public function rejectProperty(Request $request, Property $property) 
    {
         if ($property->status !== 'pending') {
            return redirect()->route('moderator.properties.pending')->with('warning', 'This property is not pending rejection.');
        }

        // ▼▼▼ إضافة التحقق من سبب الرفض (يفترض أنه إلزامي عند الرفض) ▼▼▼
        $validatedData = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000', // اجعل قواعد التحقق مناسبة
        ]);
        // ▲▲▲ نهاية التحقق ▲▲▲

        $property->status = 'rejected';

        // ▼▼▼ بداية: إضافة الكود لتحديث حقول المراجعة ▼▼▼
        $property->moderated_by = Auth::id();
        $property->moderated_at = now();
        $property->rejection_reason = $validatedData['rejection_reason']; 
   

        $property->save();

         if ($property->user) {
       $property->user->notify(new PropertyRejectedNotification($property));
    }

        return redirect()->route('moderator.properties.pending')->with('success', "Property '{$property->title}' has been rejected.");
    }
    public function showPropertyForReview(Property $property) // استخدام Route Model Binding
    {
        
        return view('dashboard.moderator.review_property_details', compact('property'));
       
    }

}
