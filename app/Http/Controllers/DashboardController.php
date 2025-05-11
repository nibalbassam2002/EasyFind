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
        if ($role === 'customer') {
            return redirect()->route('frontend.home'); 
       }
        $userId = $user->id;

        // تحديد الفترة الزمنية (اليوم/الأسبوع/الشهر/الكلي)
        $period = $request->input('period', 'all');
        $type = $request->input('type'); // فلتر نوع المعاملات

    
        $viewData = [
            'role' => $role,
            'period' => $period,
            'type' => $type,

            'totalUsers' => 0,
            'totalProperties' => 0,
            'totalRequests' => 0,
            'totalTransactions' => 0,
            'completedTransactions' => 0,
            'pendingPropertiesCount' => 0,
            'activeListingsCount' => 0,
            'pendingListingsCount' => 0,
            'totalEarnings' => 0, 
            'myPropertiesCount' => 0,
            'recentTransactions' => collect(), 
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
               
            }
        };

 

        if ($role === 'admin' || $role === 'content_moderator') {


            $usersQuery = User::query();
            $propertiesQuery = Property::query();
            $requestsQuery = PropertyRequest::query();
            $transactionsQuery = Transaction::query(); 
            $completedTransactionsQuery = Transaction::query()->where('status', 'completed');

            $applyTimeFilter($usersQuery, $period);
            $applyTimeFilter($propertiesQuery, $period);
            $applyTimeFilter($requestsQuery, $period);
            $applyTimeFilter($completedTransactionsQuery, $period); 
          

            $viewData['totalUsers'] = $usersQuery->count();
            $viewData['totalProperties'] = $propertiesQuery->count();
            $viewData['totalRequests'] = $requestsQuery->count();
            $viewData['totalTransactions'] = Transaction::count(); 
            $viewData['completedTransactions'] = $completedTransactionsQuery->count();

        
            if ($role === 'content_moderator') {
                $pendingPropertiesQuery = Property::where('status', 'pending');
                
                $viewData['pendingPropertiesCount'] = $pendingPropertiesQuery->count();
            }

          
            $recentTransactionsQuery = Transaction::with(['user', 'property'])->latest();
            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQuery->where('type', $type);
            }
            
            $viewData['recentTransactions'] = $recentTransactionsQuery->paginate(10);

        } elseif ($role === 'property_lister') {
           

            
            $viewData['myPropertiesCount'] = Property::where('user_id', $userId)->count();
            $viewData['activeListingsCount'] = Property::where('user_id', $userId)->where('status', 'approved')->count();
            $viewData['pendingListingsCount'] = Property::where('user_id', $userId)->where('status', 'pending')->count();
           $earningsQuery = Transaction::where('status', 'completed')
                                      ->whereHas('property', fn($q) => $q->where('user_id', $userId));
           $applyTimeFilter($earningsQuery, $period, 'transactions.created_at'); // الفلترة على تاريخ المعاملة
           $viewData['totalEarnings'] = $earningsQuery->sum('amount');

            
            $recentTransactionsQuery = Transaction::with(['user', 'property'])
                ->whereHas('property', fn($q) => $q->where('user_id', $userId)) 
                ->latest();

            if ($type && in_array($type, ['sale', 'rent'])) {
                $recentTransactionsQuery->where('type', $type);
            }
            $applyTimeFilter($recentTransactionsQuery, $period, 'transactions.created_at');

            $viewData['recentTransactions'] = $recentTransactionsQuery->paginate(10)->withQueryString();;

        }  

   
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


   

        if ($role === 'admin' || $role === 'content_moderator') {
           
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
             
            $responseData['monthlyTransactions'] = $calculateMonthlyData(Transaction::query());

        } elseif ($role === 'property_lister') {
         

             
             $myPropertyStatuses = Property::where('user_id', $userId)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'); 

            $responseData['myPropertiesStatus'] = [
                'labels' => $myPropertyStatuses->keys()->map(fn($status) => ucfirst($status))->toArray(),
                'counts' => $myPropertyStatuses->values()->toArray()
             ];


             
             $myTransactionsQuery = Transaction::query()
                  ->whereHas('property', fn($q) => $q->where('user_id', $userId));
             $responseData['myMonthlyTransactions'] = $calculateMonthlyData($myTransactionsQuery);

        }
       

        return response()->json($responseData);
    }
}