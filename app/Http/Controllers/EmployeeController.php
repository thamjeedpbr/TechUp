<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Admin.employee.index');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::select('id', 'name')->get();
        return view('Admin.employee.create')->with('companies', $companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'company_id' => 'required|exists:companies,id',
        ]);
        DB::beginTransaction();
        try {
            $employee = new Employee;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->company_id = $request->company_id;
            $employee->save();
            DB::commit();
            return redirect()->route('employee.index')->with('success', 'Employee successfully added.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('employee.index')->with('error', 'Something wentwrong');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $companies = Company::select('id', 'name')->get();
        $employee = Employee::find($id);
        return view('Admin.employee.edit')->with(compact('employee', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'company_id' => 'required|exists:companies,id',
        ]);
        DB::beginTransaction();
        try {
            $employee = Employee::find($id);
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->company_id = $request->company_id;
            $employee->update();
            DB::commit();
            return redirect()->route('employee.index')->with('success', 'Employee successfully Updated.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('employee.index')->with('error', 'Something wentwrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Employee = Employee::find($id);
            $Employee->delete();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $employee = Employee::orderby('id', 'desc')->with('company')->get();
            return datatables()->of($employee)
                ->addColumn('name', function (Employee $employee) {
                    return $employee->first_name . " " . $employee->last_name;
                })
                ->addColumn('company', function (Employee $employee) {
                    return $employee->company->name;
                })

                ->addColumn('action', function (Employee $employee) {
                    $data = '  <a href="' . route('employee.edit', $employee->id) . '">
                    <button class="btn btn-status btn-gray-medium" style="text-decoration:none; display: inline-block; width: 30px;">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </button>
                    </a>
                    <a href="#">
                    <button class="btn btn-danger btn-gray-medium " onclick="deleteResource(' . $employee->id . ')" style="text-decoration:none; display: inline-block; width: 30px;">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                    </a>';

                    return $data;
                })

                ->escapeColumns([])->addIndexColumn()->make(true);

        }
    }
}
