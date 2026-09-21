<?php

namespace App\Http\Controllers;

use App\Models\Results;
use App\Models\AdminSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminResultsController extends Controller
{
    public function index()
    {
        $recentResults = Results::whereDate('result_date', Carbon::today())
                                ->orderBy('result_time', 'desc')
                                ->get();

        $pastResults = Results::whereDate('result_date', '!=', Carbon::today())
                              ->orderBy('result_date', 'desc')
                              ->orderBy('result_time', 'desc')
                              ->paginate(20);

        return view('admin.results', compact('recentResults', 'pastResults'));
    }

    public function apiSettings()
    {
        $settings = AdminSettings::first();
        return view('admin.results-api', compact('settings'));
    }

    public function generateApiKey()
    {
        $settings = AdminSettings::first();
        $settings->results_api_key = Str::random(32);
        $settings->save();

        return redirect()->back()->withSuccess('API Key generated successfully!');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fd' => 'required|string|max:255',
            'result_date' => 'required|date',
            'result_time' => 'required',
            'first_prize_1' => 'required|max:1',
            'first_prize_2' => 'required|max:1',
            'first_prize_3' => 'required|max:1',
            'first_prize_4' => 'required|max:1',
            // other prizes can be nullable or required based on business logic, assuming required for now or nullable?
            // "four inputs name 1st, second, third snd fourth" - user didn't say optional.
            // I'll make them optional to be safe, or just follow the same pattern.
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Combine inputs
        $first_prize = $request->first_prize_1 . $request->first_prize_2 . $request->first_prize_3 . $request->first_prize_4;

        $second_prize = null;
        if ($request->filled(['second_prize_1', 'second_prize_2', 'second_prize_3', 'second_prize_4'])) {
            $second_prize = $request->second_prize_1 . $request->second_prize_2 . $request->second_prize_3 . $request->second_prize_4;
        }

        $third_prize = null;
        if ($request->filled(['third_prize_1', 'third_prize_2', 'third_prize_3', 'third_prize_4'])) {
            $third_prize = $request->third_prize_1 . $request->third_prize_2 . $request->third_prize_3 . $request->third_prize_4;
        }

        $fourth_prize = null;
        if ($request->filled(['fourth_prize_1', 'fourth_prize_2', 'fourth_prize_3', 'fourth_prize_4'])) {
            $fourth_prize = $request->fourth_prize_1 . $request->fourth_prize_2 . $request->fourth_prize_3 . $request->fourth_prize_4;
        }

        Results::create([
            'fd' => $request->fd,
            'result_date' => $request->result_date,
            'result_time' => $request->result_time,
            'first_prize' => $first_prize,
            'second_prize' => $second_prize,
            'third_prize' => $third_prize,
            'fourth_prize' => $fourth_prize,
        ]);

        return redirect()->back()->withSuccess('Result added successfully!');
    }

    public function update(Request $request, $id)
    {
        $result = Results::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'fd' => 'required|string|max:255',
            'result_date' => 'required|date',
            'result_time' => 'required',
            'first_prize_1' => 'required|max:1',
            'first_prize_2' => 'required|max:1',
            'first_prize_3' => 'required|max:1',
            'first_prize_4' => 'required|max:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Combine inputs
        $first_prize = $request->first_prize_1 . $request->first_prize_2 . $request->first_prize_3 . $request->first_prize_4;

        $second_prize = null;
        if ($request->filled(['second_prize_1', 'second_prize_2', 'second_prize_3', 'second_prize_4'])) {
            $second_prize = $request->second_prize_1 . $request->second_prize_2 . $request->second_prize_3 . $request->second_prize_4;
        }

        $third_prize = null;
        if ($request->filled(['third_prize_1', 'third_prize_2', 'third_prize_3', 'third_prize_4'])) {
            $third_prize = $request->third_prize_1 . $request->third_prize_2 . $request->third_prize_3 . $request->third_prize_4;
        }

        $fourth_prize = null;
        if ($request->filled(['fourth_prize_1', 'fourth_prize_2', 'fourth_prize_3', 'fourth_prize_4'])) {
            $fourth_prize = $request->fourth_prize_1 . $request->fourth_prize_2 . $request->fourth_prize_3 . $request->fourth_prize_4;
        }

        $result->update([
            'fd' => $request->fd,
            'result_date' => $request->result_date,
            'result_time' => $request->result_time,
            'first_prize' => $first_prize,
            'second_prize' => $second_prize,
            'third_prize' => $third_prize,
            'fourth_prize' => $fourth_prize,
        ]);

        return redirect()->back()->withSuccess('Result updated successfully!');
    }

    public function destroy($id)
    {
        $result = Results::findOrFail($id);
        $result->delete();
        return redirect()->back()->withSuccess('Result deleted successfully!');
    }
}
