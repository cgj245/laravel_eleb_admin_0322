<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }
    public function index()
    {
        $admins=Admin::all();
        return view('admin/index',compact('admins'));
    }

    public function create()
    {
        $roles=Role::all();
        return view('admin/create',compact('roles'));
    }
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|unique:admins',
            'password'=>'required'
        ],[
            'name.require'=>'名称不能为空',
            'email.require'=>'邮箱不能为空',
            'email.unique'=>'邮箱已存在',
            'password.require'=>'密码不能为空'
        ]);
        $password=bcrypt($request->password);
        $admin=Admin::create(['name'=>$request->name,'email'=>$request->email,'password'=>$password]);
        $admin->assignRole($request->role_id);

        session()->flash('success',"添加成功");
        return redirect()->route('admins.index');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();
        session()->flash('success',"删除成功");
        return redirect()->route('admins.index');
    }

    public function edit(Admin $admin)
    {
        $roles=Role::all();
        $myRole=$admin->roles;
        return view('admin/edit',compact('admin','roles','myRole'));
    }

    public function update(Request $request,Admin $admin)
    {
        //dd($request->all());
        $this->validate($request,[
//            'email'=>[
//                'required',
//                'email' => Rule::unique('admins')->ignore($admin->email),
//            ],
            'password'=>'required|confirmed'
        ],[
//            'email.require'=>'邮箱不能为空',
//            'email.unique'=>'邮箱已存在',
            'password.require'=>'密码不能为空',
            'password.confirmed'=>'两次密码不一致'
        ]);
        if ($request->oldpassword!=null){
            if (!Hash::check($request->oldpassword, $admin->password)) {
                return back()->with('danger',"旧密码错误");
            }
        }
        $password=bcrypt($request->password);
        $admin->update(['password'=>$password]);
        $admin->syncRoles($request->role_id);

        session()->flash('success',"修改成功");
        return redirect()->route('admins.index');
    }
}
