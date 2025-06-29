<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Request as PropertyRequestModel;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Review;

class DashboardController extends Controller
{
    /**
     * عرض صفحة الداشبورد الرئيسية بناءً على دور المستخدم.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;
        $userId = $user->id;

        $activeSubscription = $user->activeSubscriptionWithPlan(); // دالة مساعدة في موديل User

        $period = $request->input('period', 'all');
        $type = $request->input('type'); // لفلترة نوع المعاملات

        $viewData = [
            'role' => $role,
            'activeSubscription' => $activeSubscription,
            'period' => $period,
            'type' => $type,
            'totalUsers' => 0, 'totalProperties' => 0, 'totalRequests' => 0,
            'totalTransactions' => 0, 'completedTransactions' => 0,
            'pendingPropertiesCount' => 0, 'myPropertiesCount' => 0,
            'activeListingsCount' => 0, 'pendingListingsCount' => 0,
            'totalEarnings' => 0, 'recentTransactions' => collect(),
            'totalReviewsCount' => 0,
            'planName' => null, 'propertiesLimit' => null, 'propertiesListed' => null,
            'propertiesRemaining' => null, 'planEndsAt' => null, 'isFreePlan' => false,
            'allowedPropertyTypesString' => null, 'message' => null,
        ];

        $applyTimeFilter = function ($query, $period, $column = 'created_at') {
            switch ($period) {
                case 'today': $query->whereDate($column, today()); break;
                case 'week': $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]); break;
                case 'month': $query->whereMonth($column, now()->month)->whereYear($column, now()->year); break;
            }
        };

        if ($role === 'admin' || $role === 'content_moderator') {
            $usersQuery = User::query();
            $propertiesQuery = Property::query();
            $requestsQuery = Message::where('type', 'viewing_request');
            $transactionsQuery = Transaction::query();
            $completedTransactionsQuery = Transaction::query()->where('status', 'completed');

            $applyTimeFilter($usersQuery, $period);
            $applyTimeFilter($propertiesQuery, $period);
            $applyTimeFilter($requestsQuery, $period);
            $applyTimeFilter($completedTransactionsQuery, $period);

            $viewData['totalUsers'] = $usersQuery->count();
            $viewData['totalProperties'] = $propertiesQuery->count();
            $viewData['totalRequests'] = \App\Models\Message::where('type', 'viewing_request')->count();
            $viewData['totalTransactions'] = $transactionsQuery->count(); // إجمالي جميع المعاملات
            $viewData['completedTransactions'] = $completedTransactionsQuery->count();

            if ($role === 'content_moderator') {
                $viewData['pendingPropertiesCount'] = Property::where('status', 'pending')->count();
            }

            // المعاملات الأخيرة للأدمن/المشرف
            $recentTransactionsQueryForAdmin = Transaction::with(['user', 'property'])->latest();
            if ($type && in_array($type, ['sale', 'rent'])) { // افترض وجود هذه الأنواع
                $recentTransactionsQueryForAdmin->where('type', $type);
            }
            $applyTimeFilter($recentTransactionsQueryForAdmin, $period, 'transactions.created_at');
            $viewData['recentTransactions'] = $recentTransactionsQueryForAdmin->paginate(10)->withQueryString();

        } elseif ($role === 'property_lister') {
            $viewData['myPropertiesCount'] = Property::where('user_id', $userId)->count();
            $viewData['activeListingsCount'] = Property::where('user_id', $userId)->where('status', 'approved')->count();
            $viewData['pendingListingsCount'] = Property::where('user_id', $userId)->where('status', 'pending')->count();
            $viewData['totalReviewsCount'] = Review::whereHas('property', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();
            $earningsQuery = Transaction::where('status', 'completed')
                                      ->whereHas('property', fn($q) => $q->where('user_id', $userId));
            $applyTimeFilter($earningsQuery, $period, 'transactions.created_at');
            $viewData['totalEarnings'] = $earningsQuery->sum('amount');

            // المعاملات الأخيرة للبائع
            $recentTransactionsQueryLister = Transaction::with(['user', 'property'])
                ->whereHas('property', fn($q) => $q->where('user_id', $userId))
                ->latest();
            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQueryLister->where('type', $type);
            }
            $applyTimeFilter($recentTransactionsQueryLister, $period, 'transactions.created_at');
            $viewData['recentTransactions'] = $recentTransactionsQueryLister->paginate(10)->withQueryString();


            if ($activeSubscription && $activeSubscription->plan) {
                $plan = $activeSubscription->plan;
                $planFeatures = $plan->features ?? ($activeSubscription->metadata ?? []);
                $maxProperties = (int)($planFeatures['max_properties'] ?? 0);
                $listedCount = (int)($activeSubscription->properties_listed_count ?? 0);

                $viewData['planName'] = $plan->name;
                $viewData['isFreePlan'] = ($plan->price == 0.00);
                $viewData['propertiesLimit'] = $maxProperties;
                $viewData['propertiesListed'] = $listedCount;
                $viewData['propertiesRemaining'] = max(0, $maxProperties - $listedCount);
                $viewData['planEndsAt'] = $activeSubscription->ends_at ? $activeSubscription->ends_at->translatedFormat('d F Y, H:i T') : 'لا ينتهي';

                $allowedTypes = $planFeatures['allowed_types'] ?? [];
                if (is_array($allowedTypes)) {
                    if (in_array('all', array_map('strtolower', $allowedTypes))) {
                        $viewData['allowedPropertyTypesString'] = 'All Types Allowed';
                    } elseif (!empty($allowedTypes)) {
                        $viewData['allowedPropertyTypesString'] = implode(', ', array_map('ucfirst', $allowedTypes));
                    } else {
                        $viewData['allowedPropertyTypesString'] = 'No types specified';
                    }
                } else {
                    $viewData['allowedPropertyTypesString'] = ucfirst((string)$allowedTypes);
                }
            } else {
                $viewData['planName'] = 'No Active Plan';
            }
        } elseif ($role === 'customer') {
            $viewData['message'] = 'Welcome, '.e($user->name).'! To list properties and access seller features, please choose a subscription plan.';
        } else {
            $viewData['message'] = 'Welcome to your dashboard.';
        }
        return view('dashboard.index', $viewData);
    }

    /**
     * جلب البيانات للرسوم البيانية.
     */
    public function chartData()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();
        $role = $user->role;
        $userId = $user->id;
        $responseData = [];
        $activeSubscription = $user->activeSubscriptionWithPlan();

        if ($role === 'admin' || $role === 'content_moderator') {
            $responseData['users'] = [
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count()
            ];
            $responseData['properties'] = [
                'sale' => Property::where('purpose', 'sale')->count(),
                'rent' => Property::where('purpose', 'rent')->count(),
                
            ];
            $responseData['transactionsStatus'] = [
                'completed' => Transaction::where('status', 'completed')->count(),
                'pending' => Transaction::where('status', 'pending')->count(),
                'failed' => Transaction::where('status', 'failed')->count()
            ];
            $responseData['monthlyTransactions'] = $this->calculateMonthlyDataForChartJs(Transaction::query());
        } elseif ($role === 'property_lister') {
            $myPropertyStatusesData = Property::where('user_id', $userId)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')->pluck('count', 'status');
            $responseData['myPropertiesStatus'] = [
                'labels' => $myPropertyStatusesData->keys()->map(fn($status) => ucfirst($status))->toArray(),
                'counts' => $myPropertyStatusesData->values()->toArray()
            ];

            $isFreePlan = ($activeSubscription && $activeSubscription->plan) ? ($activeSubscription->plan->price == 0.00) : true;
            if (!$isFreePlan) {
                $myTransactionsQuery = Transaction::query()->whereHas('property', fn($q) => $q->where('user_id', $userId));
                $responseData['myMonthlyTransactions'] = $this->calculateMonthlyDataForChartJs($myTransactionsQuery, 'transactions.created_at');
            } else {
                $responseData['myMonthlyTransactions'] = ['labels' => [], 'counts' => []]; // بيانات فارغة للخطة المجانية
            }

            if ($activeSubscription && $activeSubscription->plan) {
                $planFeatures = $activeSubscription->plan->features ?? ($activeSubscription->metadata ?? []);
                $maxProperties = (int)($planFeatures['max_properties'] ?? 0);
                $listedCount = (int)($activeSubscription->properties_listed_count ?? 0);
                $responseData['subscriptionUsage'] = [
                    'listed' => $listedCount, 'limit' => $maxProperties,
                    'remaining' => max(0, $maxProperties - $listedCount),
                    'plan_name' => $activeSubscription->plan->name,
                    'ends_at' => $activeSubscription->ends_at ? $activeSubscription->ends_at->format('Y-m-d') : 'Never',
                ];
            }
        }
        return response()->json($responseData);
    }

    /**
     * دالة مساعدة لتجهيز البيانات الشهرية لـ Chart.js.
     */
    protected function calculateMonthlyDataForChartJs($baseQuery, $dateColumn = 'created_at')
    {
        $data = $baseQuery
            ->select(
                DB::raw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month_year_db"),
                DB::raw("DATE_FORMAT({$dateColumn}, '%b %Y') as month_year_label"),
                DB::raw('COUNT(*) as count')
            )
            ->where($dateColumn, '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_year_db', 'month_year_label')
            ->orderBy('month_year_db', 'asc')
            ->get();

        $labels = [];
        $counts = [];
        $currentPeriod = Carbon::now()->subMonths(11)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $monthLabel = $currentPeriod->format('M Y');
            $labels[] = $monthLabel;
            $monthData = $data->firstWhere('month_year_label', $monthLabel);
            $counts[] = $monthData ? $monthData->count : 0;
            $currentPeriod->addMonth();
        }
        return ['labels' => $labels, 'counts' => $counts];
    }
public function viewingRequests()
{
    $user = Auth::user();
    $userId = $user->id;

    // الخطوة 1: جلب ID العقارات التي يملكها البائع
    $propertyIds = Property::where('user_id', $userId)->pluck('id')->toArray();
    
    // إذا لم يكن للبائع أي عقارات، لا داعي لإكمال البحث
    if (empty($propertyIds)) {
        return view('dashboard.property_lister.viewing-requests', ['viewingRequests' => collect()]);
    }
    
    // الخطوة 2: بناء الاستعلام الرئيسي (هذا هو التعديل الأهم)
    // نستخدم whereJsonContains بدلاً من whereIn للبحث داخل حقل JSON بمرونة أكبر
    $viewingRequests = Message::query()
        ->whereIn('type', ['viewing_request', 'viewing_confirmed'])
        ->where(function($query) {
            $query->where(function($subQuery) {
                $subQuery->where('type', 'viewing_request')->where('metadata->status', 'pending');
            })->orWhere(function($subQuery) {
                $subQuery->where('type', 'viewing_confirmed')->where('metadata->confirmed_slot', '>=', now());
            });
        })
        // ▼▼▼ استعلام مرن للبحث في حقل JSON عن أي من ID العقارات ▼▼▼
        ->where(function ($query) use ($propertyIds) {
            foreach ($propertyIds as $id) {
                // هذا يبحث عن "property_id": 123 و "property_id": "123"
                $query->orWhereJsonContains('metadata->property_id', $id);
            }
        })
        ->with(['user', 'conversation'])
        ->latest('messages.created_at')
        ->paginate(15);
        
    // الخطوة 3: ربط بيانات العقار بالرسائل (الكود من الحل الأول)
    if ($viewingRequests->isNotEmpty()) {
        // استخراج كل معرفات العقارات من حقل الميتا-داتا
        $requestPropertyIds = $viewingRequests->pluck('metadata.property_id')->filter()->unique()->all();

        // جلب كل العقارات المطلوبة في استعلام واحد فقط
        $properties = Property::findMany($requestPropertyIds)->keyBy('id');

        // ربط كل رسالة بالعقار الخاص بها
        $viewingRequests->each(function ($message) use ($properties) {
            // نستخدم (int) لتوحيد النوع عند الربط
            $propertyId = (int) ($message->metadata['property_id'] ?? null);
            if ($propertyId && isset($properties[$propertyId])) {
                $message->setRelation('property', $properties[$propertyId]);
            }
        });
    }

    return view('dashboard.property_lister.viewing-requests', compact('viewingRequests'));
}


public function cancelViewingRequest(Request $request, Message $message)
{
    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
    //                  الحل هنا
    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼

    // الخطوة 1: استخراج ID العقار من الرسالة نفسها
    $propertyId = data_get($message, 'metadata.property_id');

    // إذا لم يكن هناك ID للعقار، فهذا إجراء غير مصرح به
    if (!$propertyId) {
        return back()->with('error', 'Cannot verify property ownership for this request.');
    }
    
    // الخطوة 2: التحقق من أن المستخدم الحالي هو مالك هذا العقار
    $isOwner = Property::where('id', $propertyId)->where('user_id', Auth::id())->exists();

    if (!$isOwner) {
        // إذا لم يكن المالك، نرجع رسالة الخطأ
        return back()->with('error', 'Unauthorized action.');
    }

    // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲
    //             نهاية التحقق من الصلاحيات
    // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲


    // الحالة 1: إلغاء موعد مؤكد وموعده لم يأتِ بعد
    if ($message->type === 'viewing_confirmed' && Carbon::parse($message->metadata['confirmed_slot'])->isFuture()) {
        // يمكننا هنا إضافة منطق لإرسال رسالة "إلغاء" في الشات
        // لكن للتبسيط الآن، سنحذف رسالة التأكيد فقط
        // ملاحظة: قد ترغب في تحديث حالة رسالة الطلب الأصلية أيضاً
        $message->delete(); 
        return back()->with('success', 'Appointment has been cancelled successfully.');
    }

    // الحالة 2: إلغاء طلب معاينة لم يتم الرد عليه بعد
    if ($message->type === 'viewing_request' && data_get($message, 'metadata.status') === 'pending') {
        $metadata = $message->metadata;
        $metadata['status'] = 'cancelled_by_owner'; // تحديث الحالة
        $message->metadata = $metadata;
        $message->save();

        // يمكنك إرسال رسالة نظام في الشات هنا أيضاً
        return back()->with('success', 'The pending request has been cancelled.');
    }

    return back()->with('error', 'This action cannot be performed on this request.');
}
}