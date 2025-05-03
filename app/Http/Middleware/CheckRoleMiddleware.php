<?php

namespace App\Http\Middleware; // تأكد أن هذا صحيح

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- مهم جداً
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware // تأكد أن اسم الكلاس صحيح
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles // يستقبل الأدوار المسموحة من ملف التوجيه (مثل 'admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // تأكد أولاً أن المستخدم مسجل دخوله
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user(); // احصل على المستخدم الحالي

        // تحقق إذا كان دور المستخدم ($user->role) موجوداً ضمن قائمة الأدوار المسموحة ($roles)
        if (!in_array($user->role, $roles)) {
            // إذا لم يكن مسموحاً، أوقف الطلب وأظهر خطأ 403
            abort(403, 'Unauthorized action.');
        }

        // إذا كان مسموحاً، اسمح للطلب بالمرور
        return $next($request);
    }
}