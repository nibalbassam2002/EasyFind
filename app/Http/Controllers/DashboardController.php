<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Request as PropertyRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;
        $userId = $user->id;

        // تحديد الفترة الزمنية (اليوم/الأسبوع/الشهر/الكلي)
        $period = $request->input('period', 'all');
        $type = $request->input('type'); // فلتر نوع المعاملات

        // --- تهيئة مصفوفة البيانات للـ View ---
        $viewData = [
            'role' => $role,
            'period' => $period,
            'type' => $type,
            // تعيين قيم افتراضية لتجنب الأخطاء في الـ Blade
            'totalUsers' => 0,
            'totalProperties' => 0,
            'totalRequests' => 0,
            'totalTransactions' => 0,
            'completedTransactions' => 0,
            'pendingPropertiesCount' => 0,
            'myPropertiesCount' => 0,
            'recentTransactions' => collect(), // مجموعة فارغة افتراضياً
        ];

        // --- تحديد دالة الفلترة الزمنية ---
        $applyTimeFilter = function ($query, $period, $column = 'created_at') {
            switch ($period) {
                case 'today':
                    $query->whereDate($column, today());
                    break;
                case 'week':
                    $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth($column, now()->month)->whereYear($column, now()->year);
                    break;
                // 'all' لا يفعل شيئًا
            }
        };

        // --- جلب البيانات بناءً على الدور ---

        if ($role === 'admin' || $role === 'content_moderator') {
            // --- بيانات Admin / Moderator ---

            // إحصائيات عامة مع فلتر زمني
            $usersQuery = User::query();
            $propertiesQuery = Property::query();
            $requestsQuery = PropertyRequest::query();
            $transactionsQuery = Transaction::query(); // عام
            $completedTransactionsQuery = Transaction::query()->where('status', 'completed');

            $applyTimeFilter($usersQuery, $period);
            $applyTimeFilter($propertiesQuery, $period);
            $applyTimeFilter($requestsQuery, $period);
            $applyTimeFilter($completedTransactionsQuery, $period); // Completed transactions in period
            // لا نطبق الفلتر الزمني على الإجمالي الكلي للمعاملات
            // $applyTimeFilter($transactionsQuery, $period);

            $viewData['totalUsers'] = $usersQuery->count();
            $viewData['totalProperties'] = $propertiesQuery->count();
            $viewData['totalRequests'] = $requestsQuery->count();
            $viewData['totalTransactions'] = Transaction::count(); // الإجمالي الكلي دائماً
            $viewData['completedTransactions'] = $completedTransactionsQuery->count();

            // عدد العقارات المعلقة (خاص بـ Moderator)
            if ($role === 'content_moderator') {
                $pendingPropertiesQuery = Property::where('status', 'pending');
                // يمكنك اختيارياً تطبيق فلتر زمني هنا أيضاً إذا أردت
                // $applyTimeFilter($pendingPropertiesQuery, $period);
                $viewData['pendingPropertiesCount'] = $pendingPropertiesQuery->count();
            }

            // المعاملات الأخيرة العامة
            $recentTransactionsQuery = Transaction::with(['user', 'property'])->latest();
            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQuery->where('type', $type);
            }
            // يمكنك اختيارياً تطبيق فلتر زمني على الجدول أيضاً
            // $applyTimeFilter($recentTransactionsQuery, $period);
            $viewData['recentTransactions'] = $recentTransactionsQuery->paginate(10);

        } elseif ($role === 'property_lister') {
            // --- بيانات Property Lister ---

            // عدد عقاراتي
            $myPropertiesBaseQuery = Property::where('user_id', $userId);
            $myPropertiesQuery = clone $myPropertiesBaseQuery; // نسخة للفلتر الزمني
            $applyTimeFilter($myPropertiesQuery, $period);
            $viewData['myPropertiesCount'] = $myPropertiesQuery->count();

            // معاملاتي الأخيرة (على عقاراتي)
            $recentTransactionsQuery = Transaction::with(['user', 'property'])
                ->whereHas('property', fn($q) => $q->where('user_id', $userId)) // <--- الفلترة الرئيسية هنا
                ->latest();

            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQuery->where('type', $type);
            }
            // يمكنك اختيارياً تطبيق فلتر زمني على الجدول أيضاً
            // $applyTimeFilter($recentTransactionsQuery, $period);
            $viewData['recentTransactions'] = $recentTransactionsQuery->paginate(10);

        } elseif ($role === 'customer') {
            // --- بيانات Customer ---
            // يمكنك إضافة أي إحصائيات خاصة بالعميل هنا لاحقاً
            // مثل عدد طلباته أو حجوزاته
             // $myRequestsQuery = PropertyRequest::where('user_id', $userId);
             // $applyTimeFilter($myRequestsQuery, $period);
             // $viewData['myRequestsCount'] = $myRequestsQuery->count();
        }

        // --- إرسال البيانات إلى الـ View ---
        return view('dashboard.index', $viewData);
    }

    public function chartData()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();
        $role = $user->role;
        $userId = $user->id;

        $responseData = [];

        // --- بيانات مشتركة أو عامة ---
        $calculateMonthlyData = function ($baseQuery) {
            $data = $baseQuery
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $labels = [];
            $counts = [];
            $currentDate = Carbon::now()->subMonths(11)->startOfMonth();
            for ($i = 0; $i < 12; $i++) {
                $labels[] = $currentDate->format('M Y');
                $counts[] = 0;
                $currentDate->addMonth();
            }

            $total = 0;
            foreach ($data as $item) {
                $label = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
                $index = array_search($label, $labels);
                if ($index !== false) {
                    $counts[$index] = $item->count;
                    $total += $item->count; // جمع الإجمالي
                }
            }
            return ['labels' => $labels, 'counts' => $counts, 'total' => $total];
        };


        // --- توليد بيانات الرسوم بناءً على الدور ---

        if ($role === 'admin' || $role === 'content_moderator') {
            // --- رسوم Admin / Moderator ---
            $responseData['users'] = [
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count()
            ];
            $responseData['properties'] = [ // حسب الغرض
                'sale' => Property::where('purpose', 'sale')->count(),
                'rent' => Property::where('purpose', 'rent')->count(),
                'lease' => Property::where('purpose', 'lease')->count() ?? 0,
            ];
             $responseData['transactionsStatus'] = [
                'completed' => Transaction::where('status', 'completed')->count(),
                'pending' => Transaction::where('status', 'pending')->count(),
                'failed' => Transaction::where('status', 'failed')->count()
            ];
             // بيانات المعاملات الشهرية العامة
            $responseData['monthlyTransactions'] = $calculateMonthlyData(Transaction::query());

        } elseif ($role === 'property_lister') {
             // --- رسوم Property Lister ---

             // حالة عقاراتي (Approved, Pending, Sold, etc.)
             $myPropertyStatuses = Property::where('user_id', $userId)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'); // ['pending' => 5, 'approved' => 10, ...]

            $responseData['myPropertiesStatus'] = [
                'labels' => $myPropertyStatuses->keys()->map(fn($status) => ucfirst($status))->toArray(),
                'counts' => $myPropertyStatuses->values()->toArray()
             ];


             // معاملاتي الشهرية (على عقاراتي)
             $myTransactionsQuery = Transaction::query()
                  ->whereHas('property', fn($q) => $q->where('user_id', $userId));
             $responseData['myMonthlyTransactions'] = $calculateMonthlyData($myTransactionsQuery);

        }
        // لا حاجة لبيانات رسوم للـ Customer حالياً

        return response()->json($responseData);
    }
}