<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Request as PropertyRequest; // تأكد أن هذا الاسم لا يتعارض إذا كان لديك موديل Request آخر
use App\Models\Transaction;
use App\Models\Subscription; // تأكد من استيراد هذا
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
        $role = $user->role; // هذا الدور سيكون 'property_lister' للمستخدم الذي اشترك مجانًا
        $userId = $user->id;

        // جلب الاشتراك النشط للمستخدم (سواء كان مجانيًا أو مدفوعًا)
        $activeSubscription = $user->subscriptions()
                                    ->where('status', 'active')
                                    ->where(function ($query) {
                                        $query->whereNull('ends_at')
                                              ->orWhere('ends_at', '>', now());
                                    })
                                    ->with('plan') // مهم لتحميل تفاصيل الخطة مع الاشتراك
                                    ->first();

        $period = $request->input('period', 'all');
        $type = $request->input('type'); // لفلترة المعاملات (بيع/إيجار)

        // تهيئة $viewData بقيم أساسية وافتراضية لكل الأدوار
        $viewData = [
            'role' => $role,
            'activeSubscription' => $activeSubscription, // نمرر الاشتراك النشط للـ view
            'period' => $period,
            'type' => $type,
            'totalUsers' => 0,
            'totalProperties' => 0,
            'totalRequests' => 0,
            'totalTransactions' => 0,
            'completedTransactions' => 0,
            'pendingPropertiesCount' => 0,
            'myPropertiesCount' => 0,
            'activeListingsCount' => 0,
            'pendingListingsCount' => 0,
            'totalEarnings' => 0,
            'recentTransactions' => collect(),
            // قيم افتراضية لمعلومات الخطة (سيتم ملؤها إذا كان المستخدم property_lister ولديه اشتراك)
            'planName' => 'N/A',
            'propertiesLimit' => 0,
            'propertiesListed' => 0,
            'propertiesRemaining' => 0,
            'planEndsAt' => 'N/A',
            'isFreePlan' => false,
            'allowedPropertyTypesString' => 'N/A',
        ];

        // دالة مساعدة لتطبيق فلتر الفترة الزمنية
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
            }
        };

        // --- منطق الأدوار المختلفة ---

        if ($role === 'admin' || $role === 'content_moderator') {
            // ... (الكود الخاص بالأدمن ومشرف المحتوى يبقى كما هو في نسختك الأصلية) ...
            // مثال:
            $usersQuery = User::query(); /* ... */ $applyTimeFilter($usersQuery, $period);
            $viewData['totalUsers'] = $usersQuery->count();
            // ... وهكذا لباقي إحصائيات الأدمن ...
            if ($role === 'content_moderator') {
                $viewData['pendingPropertiesCount'] = Property::where('status', 'pending')->count();
            }
            $recentTransactionsQueryAdmin = Transaction::with(['user', 'property'])->latest();
            // ... (تطبيق الفلاتر) ...
            $viewData['recentTransactions'] = $recentTransactionsQueryAdmin->paginate(10)->withQueryString();

        } elseif ($role === 'property_lister') {
            // هذا البلوك سيعالج الآن الـ property_lister العادي
            // وأيضًا الـ customer الذي اشترك في الخطة المجانية وتم تغيير دوره

            $viewData['myPropertiesCount'] = Property::where('user_id', $userId)->count();
            $viewData['activeListingsCount'] = Property::where('user_id', $userId)->where('status', 'approved')->count();
            $viewData['pendingListingsCount'] = Property::where('user_id', $userId)->where('status', 'pending')->count();

            $earningsQuery = Transaction::where('status', 'completed')
                                      ->whereHas('property', fn($q) => $q->where('user_id', $userId));
            $applyTimeFilter($earningsQuery, $period, 'transactions.created_at');
            $viewData['totalEarnings'] = $earningsQuery->sum('amount');

            $recentTransactionsQueryLister = Transaction::with(['user', 'property'])
                ->whereHas('property', fn($q) => $q->where('user_id', $userId))
                ->latest();
            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQueryLister->where('type', $type);
            }
            $applyTimeFilter($recentTransactionsQueryLister, $period, 'transactions.created_at');
            $viewData['recentTransactions'] = $recentTransactionsQueryLister->paginate(10)->withQueryString();

            // ▼▼▼ الجزء الأهم: جلب وتجهيز بيانات الخطة النشطة للبائع ▼▼▼
            if ($activeSubscription && $activeSubscription->plan) {
                $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
                $maxProperties = (int)($planFeatures['max_properties'] ?? 0);
                $listedCount = (int)($activeSubscription->properties_listed_count ?? 0);

                $viewData['planName'] = $activeSubscription->plan->name;
                $viewData['propertiesLimit'] = $maxProperties;
                $viewData['propertiesListed'] = $listedCount;
                $viewData['propertiesRemaining'] = max(0, $maxProperties - $listedCount);
                $viewData['planEndsAt'] = $activeSubscription->ends_at ? $activeSubscription->ends_at->translatedFormat('d F Y, H:i T') : 'لا ينتهي';
                $viewData['isFreePlan'] = ($activeSubscription->plan->price == 0.00);

                $allowedTypes = $planFeatures['allowed_types'] ?? [];
                if (is_array($allowedTypes)) {
                    if (in_array('all', array_map('strtolower', $allowedTypes))) {
                        $viewData['allowedPropertyTypesString'] = 'All Types Allowed';
                    } elseif (!empty($allowedTypes)) {
                        $viewData['allowedPropertyTypesString'] = implode(', ', array_map('ucfirst', $allowedTypes));
                    } else {
                        $viewData['allowedPropertyTypesString'] = 'No specific types restricted'; // أو رسالة مناسبة
                    }
                } else {
                    $viewData['allowedPropertyTypesString'] = ucfirst((string)$allowedTypes);
                }
            }
            // ▲▲▲ نهاية جلب بيانات الخطة ▲▲▲

        } elseif ($role === 'customer') {
            // هذا البلوك سيعالج فقط الـ customer الذي *لم* يشترك بعد ولم يتم تغيير دوره
            // (نظريًا، إذا كان التدفق يعمل، التوجيه في بداية الدالة قد يمنع الوصول لهنا إذا كان الـ customer ليس لديه اشتراك)
            // لكن كاحتياطي، نعرض له رسالة لدعوته للاشتراك
            $viewData['message'] = 'Welcome to your dashboard. To start listing properties, please choose a subscription plan.';
            // لا حاجة لإضافة إحصائيات أخرى هنا للـ customer العادي
        }

        return view('dashboard.index', $viewData);
    }

    public function chartData()
    {
        // ... (الكود الخاص بـ chartData كما عدلناه سابقًا ليتناسب مع property_lister) ...
        // تأكد من أن بلوك property_lister في chartData يجلب بيانات subscriptionUsage
        // بناءً على $activeSubscription بشكل مشابه لما فعلناه في دالة index.
        if (!Auth::check()) { /* ... */ }
        $user = Auth::user();
        $role = $user->role;
        $userId = $user->id;
        $activeSubscription = $user->subscriptions()->where('status', 'active') /* ... */ ->with('plan')->first();
        $responseData = [];
        $calculateMonthlyData = function ($baseQuery) { /* ... */ };

        if ($role === 'admin' || $role === 'content_moderator') {
            // ... بيانات الرسوم البيانية للأدمن والمشرف ...
        } elseif ($role === 'property_lister') {
            // بيانات الرسوم البيانية للبائع
            $myPropertyStatuses = Property::where('user_id', $userId) /* ... */ ->pluck('count', 'status');
            $responseData['myPropertiesStatus'] = [ /* ... */ ];
            $myTransactionsQuery = Transaction::query()->whereHas('property', fn($q) => $q->where('user_id', $userId));
            $responseData['myMonthlyTransactions'] = $calculateMonthlyData($myTransactionsQuery);

            if ($activeSubscription && $activeSubscription->plan) {
                $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
                $maxProperties = (int)($planFeatures['max_properties'] ?? 0);
                $listedCount = (int)($activeSubscription->properties_listed_count ?? 0);
                $responseData['subscriptionUsage'] = [
                    'listed' => $listedCount,
                    'limit' => $maxProperties,
                    'remaining' => max(0, $maxProperties - $listedCount),
                    'plan_name' => $activeSubscription->plan->name,
                    'ends_at' => $activeSubscription->ends_at ? $activeSubscription->ends_at->format('Y-m-d') : 'Never',
                ];
            }
        }
        return response()->json($responseData);
    }
}