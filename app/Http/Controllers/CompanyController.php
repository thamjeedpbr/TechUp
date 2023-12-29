<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('Admin.company.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.company.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:companies,name',
            'website' => 'required|unique:companies,website',
            'logo' => 'required|image|dimensions:min_width=100,min_height=100',
        ]);
        DB::beginTransaction();
        try {
            $company = new Company;
            $company->name = $request->name;
            if ($request->hasfile('logo')) {
                $logo_url = \Storage::disk('public')->put("company", $request->file('logo'), 'public');
                $company->logo = $logo_url;
            }
            $company->website = $request->website;
            $company->save();
            DB::commit();
            return redirect()->route('company.index')->with('success', 'Company successfully added.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('company.index')->with('error', 'Something wentwrong');
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
        try {
            $company = Company::find($id);
            return view('Admin.company.edit')->with('company', $company);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something wentwrong');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:companies,name,' . $id,
            'website' => 'required|unique:companies,website,' . $id,
            'logo' => 'required|image|dimensions:min_width=100,min_height=100',
        ]);
        DB::beginTransaction();
        try {
            $company = Company::find($id);
            $company->name = $request->name;
            if ($request->hasfile('logo')) {
                //delete old logo
                \Storage::disk('public')->delete($company->logo);
                //update new logo
                $logo_url = \Storage::disk('public')->put("company", $request->file('logo'), 'public');
                $company->logo = $logo_url;
            }
            $company->website = $request->website;
            $company->update();
            DB::commit();
            return redirect()->route('company.index')->with('success', 'Company successfully added.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('company.index')->with('error', 'Something wentwrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $company = Company::find($id);
            //delete old logo
            \Storage::disk('public')->delete($company->logo);
            $company->delete();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
    public function data(Request $request)
    {
        if ($request->ajax()) {
            $company = Company::orderby('id', 'desc')->get();
            return datatables()->of($company)
                ->addColumn('logo', function (Company $company) {
                    $logo = \Storage::disk('public')->url($company->logo);
                    return '<img src=' . $logo . ' border="0" width="40" class="img-rounded" align="center" />';
                })

                ->addColumn('action', function (Company $company) {
                    $data = '  <a href="' . route('company.edit', $company->id) . '">
                    <button class="btn btn-status btn-gray-medium" style="text-decoration:none; display: inline-block; width: 30px;">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </button>
                    </a>
                    <a href="#">
                    <button class="btn btn-danger btn-gray-medium " onclick="deleteResource(' . $company->id . ')" style="text-decoration:none; display: inline-block; width: 30px;">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                    </a>';

                    return $data;
                })

                ->escapeColumns([])->addIndexColumn()->make(true);

        }
    }
}
