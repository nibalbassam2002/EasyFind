<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
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
}