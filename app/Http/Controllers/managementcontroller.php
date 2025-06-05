<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\PropertyRequest as PropertyRequestModel;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use App\Models\Feedback;
use Illuminate\Support\Facades\Validator; 

class ManagementController extends Controller
{
    
    protected $availableRoles = ['admin', 'content_moderator', 'property_lister', 'customer'];
    
    protected $availableStatuses = ['active', 'inactive'];

   
    public function index()
    {
        
        $users = User::with('Area')->orderBy('id', 'asc')->paginate(15);
        return view('dashboard.usermanagement.index', compact('users'));
    }


    public function create()
    {
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $roles = $this->availableRoles;
        $statuses = $this->availableStatuses;
        
        return view('dashboard.usermanagement.create', compact('governorates', 'roles', 'statuses'));
    }

  
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
            'role' => ['required', Rule::in($this->availableRoles)],
            'status' => ['required', Rule::in($this->availableStatuses)],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);


        if ($validator->fails()) {
            return redirect()->route('admin.users.create')
                        ->withErrors($validator)
                        ->withInput(); 
        }

   
        $validatedData = $validator->validated();

        // هاش لكلمة المرور قبل الحفظ
        $validatedData['password'] = Hash::make($validatedData['password']);

        // إنشاء المستخدم
        User::create($validatedData);

        // إعادة التوجيه لقائمة المستخدمين مع رسالة نجاح
        return redirect()->route('admin.users.index')->with('success', 'تم إضافة المستخدم بنجاح!');
    }

    
    public function edit(User $user) 
    {
       
        $governorates = Governorate::with('areas')->orderBy('name')->get();
        $roles = $this->availableRoles;
        $statuses = $this->availableStatuses;
        return view('dashboard.usermanagement.edit', compact('user', 'governorates', 'roles', 'statuses'));
    }

   
    public function update(Request $request, User $user) 
    {
         
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
           
            'role' => ['required', Rule::in($this->availableRoles), function ($attribute, $value, $fail) use ($user) {
                if ($user->id === Auth::id() && $user->role !== $value) {
                    $fail('لا يمكنك تغيير دورك الخاص.');
                }
            }],
            
             'status' => ['required', Rule::in($this->availableStatuses), function ($attribute, $value, $fail) use ($user) {
                if ($user->id === Auth::id() && $user->status !== $value) {
                    $fail('لا يمكنك تغيير حالتك الخاصة.');
                }
            }],
            
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

         // إذا فشل التحقق، ارجع لصفحة التعديل مع الأخطاء والبيانات القديمة
        if ($validator->fails()) {
            return redirect()->route('admin.users.edit', $user->id)
                        ->withErrors($validator)
                        ->withInput();
        }

        // جلب البيانات التي تم التحقق منها
        $validatedData = $validator->validated();


        // تحديث كلمة المرور فقط إذا تم إدخال كلمة مرور جديدة
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            // إزالة حقل كلمة المرور من المصفوفة إذا كان فارغًا
            unset($validatedData['password']);
        }

        // تحديث بيانات المستخدم
        $user->update($validatedData);

        // إعادة التوجيه لقائمة المستخدمين مع رسالة نجاح
        return redirect()->route('admin.users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح!');
    }

    /**
     * حذف مستخدم من قاعدة البيانات.
     * DELETE /dashboard/admin/users/{user}
     * Route Name: admin.users.destroy
     */
    public function destroy(User $user) // استخدام Route Model Binding
    {
        // منع الأدمن من حذف حسابه الشخصي
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'لا يمكنك حذف حسابك الخاص!');
        }

        // يمكنك إضافة منطق إضافي هنا (مثلاً: هل يمكن حذف مستخدم لديه عقارات؟)

        try {
            $user->delete(); // سيقوم بالحذف الناعم إذا كان المودل يستخدم SoftDeletes
            return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم بنجاح!');
        } catch (\Exception $e) {
            // التعامل مع أي أخطاء قد تحدث أثناء الحذف (مثل قيود المفتاح الأجنبي)
            return redirect()->route('admin.users.index')->with('error', 'حدث خطأ أثناء حذف المستخدم: ' . $e->getMessage());
        }
    }
public function show(User $user)
    {
        // تحميل العلاقات الأساسية للمستخدم
        $user->load(['area.governorate']);
        $viewData = []; // مصفوفة لتمرير البيانات الإضافية للـ view

        Log::info("ManagementController@show: Displaying details for User ID: {$user->id}, Role: {$user->role}");

        // --- بيانات خاصة بالزبون (Customer) ---
        if ($user->role === 'customer') {
            Log::info("ManagementController@show: Fetching data for CUSTOMER role (User ID: {$user->id}).");

            // 1. سجل المعاملات
            $viewData['customerTransactions'] = Transaction::where('user_id', $user->id)
                                                ->with('property:id,title') // جلب فقط الأعمدة المطلوبة من العقار
                                                ->latest()
                                                ->take(5)
                                                ->get();
            Log::info('Customer Transactions:', $viewData['customerTransactions']->toArray());

            // 2. العقارات المفضلة
            $viewData['customerFavorites'] = $user->favoriteProperties()
                                             ->select('properties.id', 'properties.title') // جلب الأعمدة المطلوبة
                                             ->withPivot('created_at') // لجلب تاريخ الإضافة للمفضلة
                                             ->latest('favorites.created_at') // الترتيب حسب تاريخ الإضافة
                                             ->take(5)
                                             ->get();
            Log::info('Customer Favorites:', $viewData['customerFavorites']->toArray());

            // 3. طلبات/استفسارات الزبون
            $viewData['customerRequests'] = $user->propertyRequests() // يفترض وجود علاقة propertyRequests في موديل User
                                    ->with('property:id,title') // جلب الأعمدة المطلوبة
                                    ->latest()
                                    ->take(5)
                                    ->get();
            Log::info('Customer Requests:', $viewData['customerRequests']->toArray());
        }
        // --- بيانات خاصة بمشرف المحتوى (Content Moderator) ---
        elseif ($user->role === 'content_moderator') {
            Log::info("ManagementController@show: Fetching data for CONTENT_MODERATOR role (User ID: {$user->id}).");

            // 1. إحصائيات مراجعة العقارات
            // تستخدم عمود 'moderated_by' من جدول 'properties'
            $viewData['total_properties_reviewed'] = Property::where('moderated_by', $user->id)
                                                        ->whereIn('status', ['approved', 'rejected'])->count();
            $viewData['properties_approved_count'] = Property::where('moderated_by', $user->id)
                                                           ->where('status', 'approved')->count();
            $viewData['properties_rejected_count'] = Property::where('moderated_by', $user->id)
                                                           ->where('status', 'rejected')->count();
            Log::info('Moderator Property Review Stats:', [
                'total' => $viewData['total_properties_reviewed'],
                'approved' => $viewData['properties_approved_count'],
                'rejected' => $viewData['properties_rejected_count'],
            ]);

            // 2. تفاصيل آخر العقارات المرفوضة (مع سبب الرفض)
            // تستخدم 'moderated_by', 'status', 'rejection_reason', 'moderated_at' من 'properties'
            $viewData['recent_rejected_properties_details'] = Property::where('moderated_by', $user->id)
                                                            ->where('status', 'rejected')
                                                            // ->whereNotNull('rejection_reason') // يمكنك إلغاء التعليق إذا أردت عرض فقط التي لها سبب
                                                            ->orderBy('moderated_at', 'desc')
                                                            ->take(5)
                                                            ->get(['id', 'title', 'rejection_reason', 'moderated_at', 'status']); // أضفت status
            Log::info('Moderator Recent Rejected Properties:', $viewData['recent_rejected_properties_details']->toArray());


            // 3. تفاصيل آخر العقارات الموافق عليها
            // تستخدم 'moderated_by', 'status', 'moderated_at' من 'properties'
            $viewData['recent_approved_properties_details'] = Property::where('moderated_by', $user->id)
                                                            ->where('status', 'approved')
                                                            ->orderBy('moderated_at', 'desc')
                                                            ->take(5)
                                                            ->get(['id', 'title', 'status', 'moderated_at']);
            Log::info('Moderator Recent Approved Properties:', $viewData['recent_approved_properties_details']->toArray());

            // 4. إحصائيات التعامل مع الملاحظات
            // تستخدم عمود 'replied_by' (أو الاسم الصحيح لديك) من جدول 'feedbacks'
            $viewData['feedback_handled_count'] = Feedback::where('replied_by', $user->id) // <--- تأكد أن 'replied_by' هو اسم العمود الصحيح
                                                      ->whereIn('status', ['resolved', 'closed', 'replied']) // أو الحالات التي تدل على المعالجة
                                                      ->count();
            Log::info('Moderator Feedback Handled Count (using replied_by): ' . $viewData['feedback_handled_count']);

            $viewData['recent_handled_feedbacks'] = Feedback::where('replied_by', $user->id) // تأكد من اسم العمود 'replied_by'
                                                        ->whereIn('status', ['resolved', 'closed', 'replied'])
                                                        ->with('user') // لجلب معلومات من قدم الملاحظة
                                                        ->orderBy('updated_at', 'desc') // أو replied_at
                                                        ->take(5)
                                                        ->get();
            Log::info('Moderator Recent Handled Feedbacks:', $viewData['recent_handled_feedbacks']->toArray());
            // 5. قائمة بأحدث إجراءات الإشراف (حالياً هي العقارات المراجعة)
            // تستخدم 'moderated_by', 'status', 'moderated_at', 'rejection_reason' من 'properties'
            $viewData['recent_moderation_actions_detailed'] = Property::where('moderated_by', $user->id)
                                                        ->whereIn('status', ['approved', 'rejected'])
                                                        ->orderBy('moderated_at', 'desc')
                                                        ->take(10)
                                                        ->get(['id', 'title', 'status', 'moderated_at', 'rejection_reason']);
            Log::info('Moderator Recent Moderation Actions (Properties):', $viewData['recent_moderation_actions_detailed']->toArray());
        }
        elseif ($user->role === 'property_lister') {
            Log::info("ManagementController@show: Fetching data for PROPERTY_LISTER role (User ID: {$user->id}).");

            // 1. جلب الاشتراك النشط والخطة وتفاصيلها
            $activeUserSubscription = $user->activeSubscriptionWithPlan(); // تأكد أن هذه الدالة موجودة في موديل User
            $viewData['listerSubscriptionDetails'] = null; // قيمة افتراضية

            if ($activeUserSubscription && $activeUserSubscription->plan) {
                $plan = $activeUserSubscription->plan;
                $planFeatures = $plan->features ?? ($activeUserSubscription->metadata ?? []);

                $maxProperties = (int)($planFeatures['max_properties'] ?? 0);
                $listedCount = (int)($activeUserSubscription->properties_listed_count ?? 0);

                $allowedTypes = $planFeatures['allowed_types'] ?? [];
                $allowedPropertyTypesString = 'N/A';
                if (is_array($allowedTypes)) {
                    if (in_array('all', array_map('strtolower', $allowedTypes))) {
                        $allowedPropertyTypesString = 'All Types Allowed';
                    } elseif (!empty($allowedTypes)) {
                        $allowedPropertyTypesString = implode(', ', array_map('ucfirst', $allowedTypes));
                    } else {
                        $allowedPropertyTypesString = 'No specific types restricted';
                    }
                } elseif (!empty($allowedTypes)){
                    $allowedPropertyTypesString = ucfirst((string)$allowedTypes);
                }

                $viewData['listerSubscriptionDetails'] = [
                    'plan_name' => $plan->name,
                    'status' => ucfirst($activeUserSubscription->status), // حالة الاشتراك (Active, Expired, etc.)
                    'starts_at' => $activeUserSubscription->starts_at?->format('F j, Y'),
                    'ends_at' => $activeUserSubscription->ends_at ? $activeUserSubscription->ends_at->translatedFormat('F j, Y, g:i a') : 'Does not expire',
                    'is_free_plan' => ($plan->price == 0.00), // هل الخطة مجانية؟
                    'properties_limit' => $maxProperties,
                    'properties_listed' => $listedCount,
                    'properties_remaining' => max(0, $maxProperties - $listedCount),
                    'allowed_property_types' => $allowedPropertyTypesString,
                    'featured_slots_limit' => (int)($planFeatures['featured_slots'] ?? 0),
                    // يمكنك إضافة أي تفاصيل أخرى من الخطة أو الاشتراك هنا
                ];
                Log::info('Lister Subscription Details Prepared: ', $viewData['listerSubscriptionDetails']);
            } else {
                Log::warning("ManagementController@show: Property Lister (User ID: {$user->id}) has NO active subscription or plan details.");
            }

              $viewData['listerProperties'] = $user->properties() // استخدام العلاقة 'properties'
                                             ->with('category') // تحميل التصنيف مع كل عقار
                                             ->orderBy('created_at', 'desc') // الأحدث أولاً
                                             ->take(10) // جلب آخر 10 عقارات
                                             ->get();
            Log::info('Lister Properties Fetched: ', $viewData['listerProperties']->toArray());


            // 3. إحصائيات أداء البائع
            $viewData['listerStats'] = [
                'total_properties' => $user->properties()->count(), // إجمالي العقارات للبائع
                'approved_properties' => $user->properties()->where('status', 'approved')->count(),
                'pending_properties' => $user->properties()->where('status', 'pending')->count(),
                'sold_properties' => $user->properties()->where('status', 'sold')->count(),
                'rented_properties' => $user->properties()->where('status', 'rented')->count(),
                'total_views' => $user->properties()->sum('views_count'),
                
            ];
            Log::info('Lister Stats Prepared: ', $viewData['listerStats']);
        }
        Log::info("ManagementController@show: Data being passed to 'dashboard.usermanagement.show' view: ", $viewData);
        return view('dashboard.usermanagement.show', compact('user', 'viewData'));
    }
    public function showPropertyForReview(Property $property) // استخدام Route Model Binding
    {
        // لا نحتاج للتحقق من ملكية العقار هنا لأن المشرف/الأدمن يمكنه رؤية كل العقارات المعلقة
        // ولكن يمكنك إضافة تحقق إذا كان العقار فعلاً 'pending' إذا أردت
        // if ($property->status !== 'pending') {
        //     return redirect()->route('moderator.properties.pending')->with('warning', 'This property is not currently pending review.');
        // }

        // تحميل العلاقات اللازمة لعرض التفاصيل الكاملة
        $property->load(['user', 'category', 'subCategory', 'listarea.governorate']);

        // يمكنك تمرير أي بيانات إضافية تحتاجها في صفحة المراجعة
        return view('dashboard.moderator.review_property_details', compact('property'));
        // سننشئ هذا الـ view: resources/views/dashboard/moderator/review_property_details.blade.php
    }
}

